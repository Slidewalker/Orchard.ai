import pika
import json
import sys
import os
import mysql.connector
import time
import re

try:
    import requests
except ImportError:
    requests = None

try:
    import boto3
except ImportError:
    boto3 = None

# Database configuration from environment variables
DB_CONFIG = {
    'host': os.environ.get('DB_HOST', 'db'),
    'user': os.environ.get('DB_USER', 'orchard_user'),
    'password': os.environ.get('DB_PASSWORD', 'orchard_pass'),
    'database': os.environ.get('DB_DATABASE', 'orchard_ai'),
}

AI_PROVIDER = os.environ.get('AI_PROVIDER', 'auto').lower()

def get_db_connection():
    conn = None
    while not conn:
        try:
            conn = mysql.connector.connect(**DB_CONFIG)
            print("Successfully connected to the database.")
        except mysql.connector.Error as err:
            print(f"Database connection failed: {err}. Retrying in 2 seconds...")
            time.sleep(2)
    return conn

def simulate_ai_winnowing(text):
    """
    Simulates AI scoring for Utility, Privacy, and Sustainability.
    Returns a 'health_score' (0-100).
    """
    normalized = (text or "").lower()

    utility_terms = [
        "compliance", "gdpr", "hipaa", "latency", "incident", "audit",
        "sprint", "deployment", "api", "bug", "risk", "mitigation", "metrics",
    ]
    utility_hits = sum(1 for term in utility_terms if term in normalized)
    utility = min(1.0, 0.45 + (utility_hits * 0.08) + min(0.15, len(normalized) / 1200))

    pii_terms = ["patient", "mrn", "address", "email"]
    privacy = 0.35 if any(term in normalized for term in pii_terms) else 0.94
    sustainability = 0.88

    composite = (utility * 0.5) + (privacy * 0.3) + (sustainability * 0.2)
    score = int(round(max(0.0, min(1.0, composite)) * 100))
    print(f" [AI] Winnowing content: '{text}' -> Score: {score}")
    return score

def detect_provider():
    if AI_PROVIDER and AI_PROVIDER != 'auto':
        return AI_PROVIDER
    if os.environ.get('LOCAL_AI_BASE_URL') or os.environ.get('LOCAL_AI_MODEL'):
        return 'local'
    if os.environ.get('AWS_ACCESS_KEY_ID') and (os.environ.get('AWS_REGION') or os.environ.get('AWS_DEFAULT_REGION')):
        return 'bedrock'
    if os.environ.get('OPENAI_API_KEY'):
        return 'openai'
    if os.environ.get('ANTHROPIC_API_KEY'):
        return 'anthropic'
    return 'none'

def extract_json_object(text):
    if not text:
        return None
    text = text.strip()
    try:
        return json.loads(text)
    except json.JSONDecodeError:
        match = re.search(r'\{.*\}', text, re.DOTALL)
        if not match:
            return None
        try:
            return json.loads(match.group(0))
        except json.JSONDecodeError:
            return None

def provider_score(text):
    provider = detect_provider()
    if provider == 'local' and requests:
        base_url = os.environ.get('LOCAL_AI_BASE_URL', 'http://ollama:11434').rstrip('/')
        model = os.environ.get('LOCAL_AI_MODEL', 'llama3.2:1b')
        response = requests.post(
            f'{base_url}/api/chat',
            headers={'Content-Type': 'application/json'},
            json={
                'model': model,
                'messages': [
                    {'role': 'system', 'content': 'Return valid JSON only.'},
                    {'role': 'user', 'content': prompt},
                ],
                'stream': False,
                'format': 'json',
            },
            timeout=30,
        )
        response.raise_for_status()
        content = response.json().get('message', {}).get('content')
        return extract_json_object(content)

    prompt = (
        "You are Orchard.ai's scoring model. Score this content from 0.0 to 1.0 for "
        "utility, privacy, and sustainability. Return strict JSON with keys "
        "utility, privacy, sustainability only.\n\nContent:\n"
        f"{text}"
    )

    if provider == 'openai' and requests:
        model = os.environ.get('OPENAI_MODEL', 'gpt-4o-mini')
        response = requests.post(
            'https://api.openai.com/v1/chat/completions',
            headers={
                'Authorization': f"Bearer {os.environ['OPENAI_API_KEY']}",
                'Content-Type': 'application/json',
            },
            json={
                'model': model,
                'messages': [
                    {'role': 'system', 'content': 'Return valid JSON only.'},
                    {'role': 'user', 'content': prompt},
                ],
                'temperature': 0.2,
            },
            timeout=20,
        )
        response.raise_for_status()
        content = response.json()['choices'][0]['message']['content']
        return extract_json_object(content)

    if provider == 'anthropic' and requests:
        model = os.environ.get('ANTHROPIC_MODEL', 'claude-3-5-haiku-latest')
        response = requests.post(
            'https://api.anthropic.com/v1/messages',
            headers={
                'x-api-key': os.environ['ANTHROPIC_API_KEY'],
                'anthropic-version': '2023-06-01',
                'Content-Type': 'application/json',
            },
            json={
                'model': model,
                'max_tokens': 350,
                'temperature': 0.2,
                'system': 'Return valid JSON only.',
                'messages': [{'role': 'user', 'content': prompt}],
            },
            timeout=20,
        )
        response.raise_for_status()
        content = response.json()['content'][0]['text']
        return extract_json_object(content)

    if provider == 'bedrock' and boto3:
        model_id = os.environ.get('BEDROCK_MODEL_ID', 'anthropic.claude-3-haiku-20240307-v1:0')
        region = os.environ.get('AWS_REGION') or os.environ.get('AWS_DEFAULT_REGION') or 'us-east-1'
        client = boto3.client('bedrock-runtime', region_name=region)
        body = {
            'anthropic_version': 'bedrock-2023-05-31',
            'max_tokens': 350,
            'temperature': 0.2,
            'messages': [{'role': 'user', 'content': prompt}],
        }
        response = client.invoke_model(modelId=model_id, body=json.dumps(body))
        payload = json.loads(response['body'].read())
        content = payload['content'][0]['text']
        return extract_json_object(content)

    return None

def score_text(text):
    try:
        provider_result = provider_score(text)
        if provider_result:
            utility = max(0.0, min(1.0, float(provider_result.get('utility', 0))))
            privacy = max(0.0, min(1.0, float(provider_result.get('privacy', 0))))
            sustainability = max(0.0, min(1.0, float(provider_result.get('sustainability', 0))))
            composite = (utility * 0.5) + (privacy * 0.3) + (sustainability * 0.2)
            score = int(round(composite * 100))
            print(f" [AI] Provider scoring ({detect_provider()}): '{text}' -> Score: {score}")
            return score
    except Exception as err:
        print(f" [AI] Provider scoring failed, falling back to heuristics: {err}")

    return simulate_ai_winnowing(text)

def callback(ch, method, properties, body):
    data = json.loads(body)
    text = data.get('text', 'Unknown Fruit')
    print(f" [x] Received: {text}")

    # Process: AI Winnowing
    health_score = score_text(text)
    score_decimal = health_score / 100.0
    # Keep this threshold aligned with backend/config/winnowing.php (0.70).
    verdict = 'wheat' if health_score >= 70 else 'chaff'

    # Persistence: Save BOTH Wheat and Chaff for auditability (ISO 9001 Clause 7.5)
    try:
        db = get_db_connection()
        cursor = db.cursor()
        
        # New Schema Insertion
        sql = "INSERT INTO trees (name, content, score, verdict, shard_id) VALUES (%s, %s, %s, %s, %s)"
        # truncating name for the 'name' column, full text in 'content'
        display_name = (text[:47] + '..') if len(text) > 50 else text
        
        cursor.execute(sql, (display_name, text, score_decimal, verdict, 1))
        db.commit()
        
        status = "Preserved WHEAT" if verdict == 'wheat' else "Logged CHAFF (Pending 24h Purge)"
        print(f" [x] {status} in Database: {text}")
        
        cursor.close()
        db.close()
    except mysql.connector.Error as err:
        print(f" [!] Database Error: {err}")

def start_consumer():
    connection = None
    while not connection:
        try:
            connection = pika.BlockingConnection(pika.ConnectionParameters(host='rabbitmq'))
        except pika.exceptions.AMQPConnectionError:
            print("RabbitMQ not ready yet, retrying in 2 seconds...")
            time.sleep(2)

    channel = connection.channel()
    channel.queue_declare(queue='orchard_fanout')
    channel.basic_consume(queue='orchard_fanout', on_message_callback=callback, auto_ack=True)

    print(f" [*] Waiting for messages. Provider mode: {detect_provider()}. To exit press CTRL+C")
    channel.start_consuming()

if __name__ == '__main__':
    try:
        start_consumer()
    except KeyboardInterrupt:
        sys.exit(0)
