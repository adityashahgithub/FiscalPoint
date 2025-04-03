import mysql.connector
import pandas as pd
from flask import Flask, jsonify, request
import numpy as np
from sklearn.linear_model import LinearRegression

# Flask app
app = Flask(__name__)

# Database connection function
def get_db_connection():
    return mysql.connector.connect(
        host="localhost",
        user="root",  
        password="",  
        database="FiscalPoint"
    )

# Fetch expense data from MySQL
def fetch_expense_data(user_id):
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)

    query = """
    SELECT Date, amount, category FROM Expense WHERE Uid = %s ORDER BY Date
    """
    cursor.execute(query, (user_id,))
    result = cursor.fetchall()

    cursor.close()
    conn.close()
    
    return pd.DataFrame(result)  # Convert MySQL data into a Pandas DataFrame
    df = pd.DataFrame(result)
    if df.empty:
        return None  # Handle case where no data is available

    # Convert Date column to datetime format
    df['Date'] = pd.to_datetime(df['Date'])
    
    # Extract month and year for analysis
    df['Year'] = df['Date'].dt.year
    df['Month'] = df['Date'].dt.month
    
    # Group by month and sum expenses
    monthly_expenses = df.groupby(['Year', 'Month'])['amount'].sum().reset_index()

    return monthly_expenses
def train_model(user_id):
    data = fetch_expense_data(user_id)
    
    if data is None or len(data) < 3:  # Ensure enough data points
        return None

    # Feature: Month-Year as a single number (e.g., 2024-03 becomes 202403)
    data['Time'] = data['Year'] * 100 + data['Month']

    X = data[['Time']].values  # Features
    y = data['amount'].values  # Target (expenses)

    model = LinearRegression()
    model.fit(X, y)

    return model, data  # Return trained model and data
def predict_next_month(user_id):
    model, data = train_model(user_id)
    
    if model is None:
        return {"message": "Not enough data to predict."}

    # Get the last recorded month
    last_year = data['Year'].iloc[-1]
    last_month = data['Month'].iloc[-1]

    # Compute next month
    if last_month == 12:
        next_month = 1
        next_year = last_year + 1
    else:
        next_month = last_month + 1
        next_year = last_year

    # Format next month as feature input
    next_time = np.array([[next_year * 100 + next_month]])

    # Predict
    predicted_expense = model.predict(next_time)[0]

    return {
        "year": next_year,
        "month": next_month,
        "predicted_expense": round(predicted_expense, 2)
    }

@app.route('/predict_budget', methods=['GET'])
def predict_budget():
    user_id = request.args.get('user_id', type=int)  
    prediction = predict_next_month(user_id)
    return jsonify(prediction)  

if __name__ == '__main__':
    app.run(debug=True)
