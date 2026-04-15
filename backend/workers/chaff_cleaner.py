import mysql.connector
import os
import time
from datetime import datetime, timedelta

# Database configuration
DB_CONFIG = {
    'host': os.environ.get('DB_HOST', 'db'),
    'user': os.environ.get('DB_USER', 'orchard_user'),
    'password': os.environ.get('DB_PASSWORD', 'orchard_pass'),
    'database': os.environ.get('DB_DATABASE', 'orchard_ai'),
}

LOG_FILE = "/var/www/html/compliance_audit.log"

def get_db_connection():
    try:
        return mysql.connector.connect(**DB_CONFIG)
    except mysql.connector.Error as err:
        print(f"Cleaner: Database connection failed: {err}")
        return None

def log_mitigation(count):
    if count == 0:
        return
    
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    log_entry = {
        "timestamp": timestamp,
        "action": "AUTO_PURGE_CHAFF",
        "clause": "ISO 9001:2015 6.1 / GDPR",
        "records_deleted": count,
        "status": "COMPLIANT"
    }
    
    try:
        # We try to write to the shared audit log if accessible
        with open(LOG_FILE, "a") as f:
            f.write(f"[{timestamp}] MITIGATION: Automated 24h Purge removed {count} chaff records. Status: COMPLIANT\n")
        print(f"Cleaner: Successfully logged {count} deletions to audit trail.")
    except Exception as e:
        print(f"Cleaner: Could not write to audit log file: {e}")

def purge_chaff():
    db = get_db_connection()
    if not db:
        return
    
    try:
        cursor = db.cursor()
        # Clause 6.1 Mitigation: Delete chaff > 24 hours
        # For testing purposes in Alpha-01, we might want a shorter window, 
        # but we'll stick to the 24h requirement as stated in the Plan.
        sql = "DELETE FROM trees WHERE verdict = 'chaff' AND created_at < NOW() - INTERVAL 24 HOUR"
        
        cursor.execute(sql)
        deleted_count = cursor.rowcount
        db.commit()
        
        if deleted_count > 0:
            print(f"Cleaner: Successfully purged {deleted_count} expired chaff records.")
            log_mitigation(deleted_count)
        else:
            print("Cleaner: No expired chaff found.")
            
        cursor.close()
        db.close()
    except mysql.connector.Error as err:
        print(f"Cleaner: Purge Error: {err}")

if __name__ == '__main__':
    print("Orchard.ai Compliance Cleaner (GDPR/ISO 9001) Started.")
    while True:
        purge_chaff()
        # Run every hour
        print("Cleaner: Sleeping for 1 hour...")
        time.sleep(3600)
