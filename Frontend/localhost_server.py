import mysql.connector
import pandas as pd
from flask import Flask, jsonify, request
from flask_cors import CORS
import numpy as np
from sklearn.linear_model import LinearRegression
import matplotlib
# Use a non-interactive backend to avoid NSException errors
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import io
import base64
import os
import traceback
from dotenv import load_dotenv
from datetime import datetime

# Load environment variables
load_dotenv()

# Initialize Flask app
app = Flask(__name__)
CORS(app)  # Enable CORS for all routes

# Add a simple route to check if server is running
@app.route('/', methods=['GET'])
def health_check():
    return jsonify({"status": "online", "message": "Prediction server is running"})

# Add a debug endpoint to check database connection
@app.route('/test_db', methods=['GET'])
def test_db():
    try:
        conn = get_db_connection()
        if conn:
            cursor = conn.cursor()
            cursor.execute("SELECT 1")
            cursor.close()
            conn.close()
            return jsonify({"status": "success", "message": "Database connection successful"})
        else:
            return jsonify({"status": "error", "message": "Could not connect to database"}), 500
    except Exception as e:
        error_detail = traceback.format_exc()
        return jsonify({"status": "error", "message": str(e), "detail": error_detail}), 500

# Database connection
def get_db_connection():
    try:
        conn = mysql.connector.connect(
            host=os.getenv("DB_HOST", "localhost"),
            user=os.getenv("DB_USER", "root"),
            password=os.getenv("DB_PASSWORD", ""),
            database=os.getenv("DB_NAME", "FiscalPoint")
        )
        return conn
    except mysql.connector.Error as err:
        print(f"Error connecting to database: {err}")
        return None

# Simple prediction endpoint
@app.route('/predict_budget', methods=['POST'])
def predict_budget():
    try:
        data = request.get_json()
        if not data or 'user_id' not in data:
            return jsonify({"error": "user_id is required"}), 400

        user_id = data['user_id']
        print(f"Received request for user_id: {user_id}")
        
        # For testing, just return a simple response
        return jsonify({
            "summary": "This is a test prediction response.\nPlease add more expense data for real predictions.",
            "graph_base64": ""
        })
        
    except Exception as e:
        print(f"Error: {e}")
        return jsonify({"error": f"An error occurred: {str(e)}"}), 500

# Run the app - ONLY on localhost
if __name__ == '__main__':
    try:
        print("Starting prediction server on http://127.0.0.1:5001")
        print("To test if server is running, visit: http://127.0.0.1:5001")
        print("Press CTRL+C to stop the server")
        # Note: Using localhost (127.0.0.1) ONLY, not 0.0.0.0
        app.run(host='127.0.0.1', port=5001, debug=True)
    except Exception as e:
        print(f"Failed to start the server: {e}")
        print(traceback.format_exc()) 