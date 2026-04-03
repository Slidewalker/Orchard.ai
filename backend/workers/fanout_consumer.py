import pika
import json
import sys

def callback(ch, method, properties, body):
    data = json.loads(body)
    print(f" [x] Received {data}")
    # Process fan-out message
    print(" [x] Fanning out to subscribers...")

def start_consumer():
    connection = pika.BlockingConnection(pika.ConnectionParameters(host='localhost'))
    channel = connection.channel()

    channel.queue_declare(queue='orchard_fanout')

    channel.basic_consume(queue='orchard_fanout',
                          on_message_callback=callback,
                          auto_ack=True)

    print(' [*] Waiting for messages. To exit press CTRL+C')
    channel.start_consuming()

if __name__ == '__main__':
    try:
        start_consumer()
    except KeyboardInterrupt:
        sys.exit(0)
