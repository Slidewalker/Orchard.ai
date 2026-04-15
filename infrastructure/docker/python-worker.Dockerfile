FROM python:3.10-slim
WORKDIR /app
RUN pip install pika mysql-connector-python requests boto3
CMD ["python", "-u", "fanout_consumer.py"]
