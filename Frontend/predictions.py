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

    query = "SELECT Date, amount, category FROM Expense WHERE Uid = %s ORDER BY Date"
    cursor.execute(query, (user_id,))
    result = cursor.fetchall()

    cursor.close()
    conn.close()

    df = pd.DataFrame(result)
    if df.empty:
        return None  # No data available

    # Prepare data for prediction
    df['Date'] = pd.to_datetime(df['Date'])
    df['Year'] = df['Date'].dt.year
    df['Month'] = df['Date'].dt.month

    # Group by Year & Month and sum amounts
    monthly_expenses = df.groupby(['Year', 'Month'])['amount'].sum().reset_index()

    return monthly_expenses

# Train model
def train_model(user_id):
    data = fetch_expense_data(user_id)
    if data is None or len(data) < 2:
        return None, None

    data['Time'] = data['Year'] * 100 + data['Month']
    X = data[['Time']].values
    y = data['amount'].values

    model = LinearRegression()
    model.fit(X, y)

    return model, data

# Predict next month
def predict_next_month(user_id):
    model, data = train_model(user_id)
    if model is None or data is None:
        return {"message": "Not enough data to predict."}

    last_year = int(data['Year'].iloc[-1])
    last_month = int(data['Month'].iloc[-1])

    if last_month == 12:
        next_month = 1
        next_year = last_year + 1
    else:
        next_month = last_month + 1
        next_year = last_year

    next_time = np.array([[next_year * 100 + next_month]])
    predicted_expense = model.predict(next_time)[0]

    return {
        "year": int(next_year),
        "month": int(next_month),
        "predicted_expense": float(round(predicted_expense, 2))
    }

# Flask route
@app.route('/predict_budget', methods=['GET'])
def predict_budget():
    user_id = request.args.get('user_id', type=int)
    if not user_id:
        return jsonify({"error": "user_id parameter is required"}), 400

    prediction = predict_next_month(user_id)
    return jsonify(prediction)

# Run Flask app
if __name__ == '__main__':
    app.run(debug=True)
