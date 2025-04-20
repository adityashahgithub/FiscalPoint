import mysql.connector
import pandas as pd
from flask import Flask, jsonify, request
import numpy as np
from sklearn.linear_model import LinearRegression
import matplotlib.pyplot as plt
import io
import base64

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
        return None

    df['Date'] = pd.to_datetime(df['Date'])
    df['Year'] = df['Date'].dt.year
    df['Month'] = df['Date'].dt.month
    return df

# Predict next month
def predict_next_month(user_id):
    data = fetch_expense_data(user_id)
    if data is None or len(data) < 2:
        return {"message": "Not enough data to predict."}

    data['Time'] = data['Year'] * 100 + data['Month']
    last_year = int(data['Year'].iloc[-1])
    last_month = int(data['Month'].iloc[-1])

    if last_month == 12:
        next_month = 1
        next_year = last_year + 1
    else:
        next_month = last_month + 1
        next_year = last_year

    next_time = np.array([[next_year * 100 + next_month]])

    # Group by category for last month
    current_month_data = data[(data['Year'] == last_year) & (data['Month'] == last_month)]
    current_category_expenses = current_month_data.groupby('category')['amount'].sum().to_dict()

    # Predict category-wise expenses
    category_predictions = {}
    unique_categories = data['category'].unique()
    for category in unique_categories:
        category_data = data[data['category'] == category]
        X_category = category_data[['Time']].values
        y_category = category_data['amount'].values

        model_category = LinearRegression()
        model_category.fit(X_category, y_category)

        predicted_expense = model_category.predict(next_time)[0]
        category_predictions[category] = float(round(predicted_expense, 2))

    # Generate comparison graph
    img = generate_comparison_graph(current_category_expenses, category_predictions)

    return {
        "year": next_year,
        "month": next_month,
        "predicted_expense": float(round(sum(category_predictions.values()), 2)),
        "category_predictions": category_predictions,
        "current_category_expenses": current_category_expenses,
        "comparison_graph": img
    }

# Generate graph as base64 string
def generate_comparison_graph(current, predicted):
    categories = sorted(set(current.keys()) | set(predicted.keys()))
    current_values = [current.get(cat, 0) for cat in categories]
    predicted_values = [predicted.get(cat, 0) for cat in categories]

    x = np.arange(len(categories))
    width = 0.35

    fig, ax = plt.subplots()
    ax.bar(x - width/2, current_values, width, label='Current')
    ax.bar(x + width/2, predicted_values, width, label='Predicted')

    ax.set_ylabel('Expenses')
    ax.set_title('Category-wise Expenses Comparison')
    ax.set_xticks(x)
    ax.set_xticklabels(categories, rotation=45)
    ax.legend()

    fig.tight_layout()

    buffer = io.BytesIO()
    plt.savefig(buffer, format='png')
    buffer.seek(0)
    img_base64 = base64.b64encode(buffer.read()).decode('utf-8')
    plt.close(fig)

    return img_base64

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
