import mysql.connector
import pandas as pd
from flask import Flask, jsonify, request, make_response
import numpy as np
from sklearn.linear_model import LinearRegression
import matplotlib.pyplot as plt
import io
import base64
import json

# Initialize Flask app
app = Flask(__name__)

# Database connection
def get_db_connection():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="FiscalPoint"
    )

# Fetch expense data from database
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

# Predict next month expenses
def predict_next_month(Uid):
    data = fetch_expense_data(Uid)
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

    current_month_data = data[(data['Year'] == last_year) & (data['Month'] == last_month)]
    current_category_expenses = current_month_data.groupby('category')['amount'].sum().to_dict()

    category_predictions = {}
    unique_categories = data['category'].unique()

    for category in unique_categories:
        category_data = data[data['category'] == category]
        X_category = category_data[['Time']].values
        y_category = category_data['amount'].values

        model = LinearRegression()
        model.fit(X_category, y_category)

        predicted = model.predict(next_time)[0]
        category_predictions[category] = float(round(predicted, 2))

    graph = generate_comparison_graph(current_category_expenses, category_predictions)

    return {
        "year": next_year,
        "month": next_month,
        "predicted_expense": float(round(sum(category_predictions.values()), 2)),
        "category_predictions": category_predictions,
        "current_category_expenses": current_category_expenses,
        "comparison_graph": graph
    }

# Generate base64 bar chart comparing expenses
def generate_comparison_graph(current, predicted):
    categories = sorted(set(current.keys()) | set(predicted.keys()))
    current_values = [current.get(cat, 0) for cat in categories]
    predicted_values = [predicted.get(cat, 0) for cat in categories]

    x = np.arange(len(categories))
    width = 0.35

    fig, ax = plt.subplots()
    ax.bar(x - width/2, current_values, width, label='Current')
    ax.bar(x + width/2, predicted_values, width, label='Predicted')

    ax.set_ylabel('Expenses (₹)')
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

# Route to predict and return insights
@app.route('/predict_budget', methods=['GET'])
def predict_budget():
    user_id = request.args.get('user_id', type=int)
    if not user_id:
        return jsonify({"error": "user_id parameter is required"}), 400

    prediction = predict_next_month(user_id)

    if "message" in prediction:
        return jsonify({"message": prediction["message"]})

    year = prediction['year']
    month = prediction['month']
    predicted_total = prediction['predicted_expense']
    current_exp = prediction['current_category_expenses']
    predicted_exp = prediction['category_predictions']
    graph = prediction['comparison_graph']

    # Format human-readable insights
    insights = f"📅 Predicted Insights for {month:02d}/{year}\n"
    insights += f"\n🔮 Total Predicted Spending: ₹{predicted_total:.2f}\n\n"
    insights += "📊 Category-wise Prediction:\n"
    for cat, val in predicted_exp.items():
        insights += f"- {cat}: ₹{val:.2f}\n"

    insights += "\n🟩 Current Spending Breakdown (This Month):\n"
    for cat, val in current_exp.items():
        insights += f"- {cat}: ₹{val:.2f}\n"

    insights += "\n🪄 Insights:\n"
    for cat in predicted_exp:
        current_val = current_exp.get(cat, 0)
        predicted_val = predicted_exp[cat]
        if predicted_val > current_val:
            insights += f"- 📈 {cat} expenses may increase.\n"
        elif predicted_val < current_val:
            insights += f"- 📉 {cat} expenses may decrease.\n"
        else:
            insights += f"- ➖ {cat} expenses expected to stay the same.\n"

    return jsonify({
        "summary": insights,
        "graph_base64": graph
    })

# Run the app
if __name__ == '__main__':
    app.run(debug=True)
