import mysql.connector
import pandas as pd
from flask import Flask, jsonify, request
from flask_cors import CORS
import numpy as np
from sklearn.linear_model import LinearRegression
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
from sklearn.model_selection import train_test_split
import matplotlib
# Use a non-interactive backend to avoid the NSException
matplotlib.use('Agg') 
import matplotlib.pyplot as plt
import io
import base64
import os
import traceback
import sys
from dotenv import load_dotenv
from datetime import datetime, timedelta

# Load environment variables
load_dotenv()

# Initialize Flask app
app = Flask(__name__)
CORS(app, resources={r"/*": {"origins": "*"}})  # More permissive CORS

# Enable more detailed logging
import logging
logging.basicConfig(level=logging.DEBUG)
logger = logging.getLogger(__name__)

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

# Add an option route to handle preflight requests
@app.route('/predict_budget', methods=['OPTIONS'])
def options():
    response = jsonify({'status': 'success'})
    response.headers.add('Access-Control-Allow-Origin', '*')
    response.headers.add('Access-Control-Allow-Headers', 'Content-Type,Authorization')
    response.headers.add('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE,OPTIONS')
    return response

# Database connection
def get_db_connection():
    try:
        conn = mysql.connector.connect(
            host=os.getenv("DB_HOST", "localhost"),
            user=os.getenv("DB_USER", "root"),
            password=os.getenv("DB_PASSWORD", ""),
            database=os.getenv("DB_NAME", "FiscalPoint")
        )
        logger.info("Database connection successful")
        return conn
    except mysql.connector.Error as err:
        logger.error(f"Error connecting to database: {err}")
        return None

# Fetch expense data from database
def fetch_expense_data(Uid):
    try:
        conn = get_db_connection()
        if not conn:
            logger.error("No database connection in fetch_expense_data")
            return None
            
        cursor = conn.cursor(dictionary=True)

        # Query matches your database schema
        query = """
            SELECT date, amount, category 
            FROM Expense 
            WHERE Uid = %s AND date IS NOT NULL 
            ORDER BY date
        """
        logger.info(f"Executing query for user {Uid}")
        cursor.execute(query, (Uid,))
        result = cursor.fetchall()

        logger.info(f"Query returned {len(result) if result else 0} rows")
        if not result:
            return None

        df = pd.DataFrame(result)
        
        # Log the data types and sample data for debugging
        logger.info(f"DataFrame columns: {df.columns}")
        logger.info(f"DataFrame types: {df.dtypes}")
        if not df.empty:
            logger.info(f"Sample data: {df.head(1).to_dict('records')}")
        
        # Convert date to datetime if needed
        if 'date' in df.columns:
            df['date'] = pd.to_datetime(df['date'], errors='coerce')
            # Check for NaT values after conversion
            if df['date'].isna().any():
                logger.warning(f"Some dates could not be converted: {df[df['date'].isna()]}")
                df = df.dropna(subset=['date'])
                
            # Extract year and month
            df['Year'] = df['date'].dt.year
            df['Month'] = df['date'].dt.month
            
            return df
        else:
            logger.error(f"Column 'date' not found in result. Columns: {df.columns}")
            return None

    except mysql.connector.Error as err:
        logger.error(f"Database error in fetch_expense_data: {err}")
        return None
    except Exception as e:
        logger.error(f"Unexpected error in fetch_expense_data: {e}")
        logger.error(traceback.format_exc())
        return None
    finally:
        if 'cursor' in locals() and cursor:
            cursor.close()
        if 'conn' in locals() and conn:
            conn.close()

# Predict next month expenses
def predict_next_month(Uid):
    data = fetch_expense_data(Uid)
    if data is None:
        return {"message": "No expense data found for this user."}
    
    if len(data) < 2:
        return {"message": "Not enough expense data for prediction. Please add more expenses."}

    # Get current date for reference
    current_date = datetime.now()
    data['Time'] = data['Year'] * 100 + data['Month']
    
    # Get the most recent month's data
    last_year = current_date.year
    last_month = current_date.month
    
    # Calculate next month
    if last_month == 12:
        next_year = last_year + 1
        next_month = 1
    else:
        next_year = last_year
        next_month = last_month + 1
    
    next_time = np.array([[next_year * 100 + next_month]])

    # Get current month's expenses
    current_month_data = data[
        (data['Year'] == last_year) & 
        (data['Month'] == last_month)
    ]
    current_category_expenses = current_month_data.groupby('category')['amount'].sum().to_dict()

    # Predict for each category
    category_predictions = {}
    unique_categories = data['category'].unique()

    for category in unique_categories:
        category_data = data[data['category'] == category]
        
        if len(category_data) >= 2:  # Need at least 2 points for linear regression
            X_category = category_data[['Time']].values
            y_category = category_data['amount'].values

            model = LinearRegression()
            model.fit(X_category, y_category)

            predicted = max(0, model.predict(next_time)[0])  # Ensure no negative predictions
            category_predictions[category] = float(round(predicted, 2))
        else:
            # If not enough data, use the average of existing expenses
            category_predictions[category] = float(round(category_data['amount'].mean(), 2))

    # Generate comparison graph
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

    # Create figure without using seaborn
    fig, ax = plt.subplots(figsize=(12, 6))
    
    rects1 = ax.bar(x - width/2, current_values, width, label='Current Month', color='#2ecc71')
    rects2 = ax.bar(x + width/2, predicted_values, width, label='Next Month (Predicted)', color='#3498db')

    ax.set_ylabel('Expenses (₹)', fontsize=12)
    ax.set_title('Category-wise Expense Comparison', fontsize=14, pad=20)
    ax.set_xticks(x)
    ax.set_xticklabels(categories, rotation=45, ha='right')
    ax.legend()

    # Add value labels on bars
    def autolabel(rects):
        for rect in rects:
            height = rect.get_height()
            ax.annotate(f'₹{height:,.0f}',
                       xy=(rect.get_x() + rect.get_width() / 2, height),
                       xytext=(0, 3),
                       textcoords="offset points",
                       ha='center', va='bottom', rotation=0)

    autolabel(rects1)
    autolabel(rects2)

    fig.tight_layout()

    buffer = io.BytesIO()
    plt.savefig(buffer, format='png', dpi=300, bbox_inches='tight')
    buffer.seek(0)
    img_base64 = base64.b64encode(buffer.read()).decode('utf-8')
    plt.close(fig)

    return img_base64

# Route to predict and return insights
@app.route('/predict_budget', methods=['POST'])
def predict_budget():
    try:
        logger.info("Received predict_budget request")
        data = request.get_json()
        
        if not data:
            logger.error("No JSON data received")
            return jsonify({"error": "No data provided"}), 400
            
        if 'user_id' not in data:
            logger.error("No user_id in request data")
            return jsonify({"error": "user_id is required"}), 400

        user_id = data['user_id']
        logger.info(f"Processing request for user_id: {user_id}")
        
        # First test database connection
        expense_data = fetch_expense_data(user_id)
        
        # If we don't have expense data, return a simple message
        if expense_data is None or len(expense_data) < 2:
            logger.info(f"Not enough data for user {user_id} to make predictions.")
            return jsonify({
                "message": "Not enough expense data for prediction. Please add more expenses."
            })
        
        # If we have data, create a simplified prediction
        try:
            # Get unique categories
            categories = expense_data['category'].unique()
            
            # Create simple predictions (just for testing)
            current_month = datetime.now().month
            current_year = datetime.now().year
            
            next_month = current_month + 1 if current_month < 12 else 1
            next_year = current_year if current_month < 12 else current_year + 1
            
            # Simple average-based prediction
            # Group by category and get mean amount
            category_avgs = expense_data.groupby('category')['amount'].mean().to_dict()
            
            # Generate text summary
            summary = f"📅 Predicted Insights for Month {next_month}, {next_year}\n\n"
            summary += "🔮 Prediction based on your past spending patterns:\n\n"
            for cat, avg in category_avgs.items():
                summary += f"- {cat}: ₹{avg:.2f}\n"
            # Create a simple graph
            fig, ax = plt.subplots(figsize=(10, 6))
            # Simple bar chart of category averages
            cats = list(category_avgs.keys())
            values = [category_avgs[cat] for cat in cats]
            # Use default colors instead of seaborn styling
            ax.bar(cats, values, color='#3498db')
            ax.set_ylabel('Average Expense (₹)', fontsize=12)
            ax.set_title('Category-wise Average Expenses', fontsize=14)
            plt.xticks(rotation=45, ha='right')
            fig.tight_layout()  # Add this to ensure proper spacing
            
            # Convert to base64
            buf = io.BytesIO()
            plt.savefig(buf, format='png', bbox_inches='tight')
            buf.seek(0)
            img_str = base64.b64encode(buf.read()).decode('utf-8')
            plt.close(fig)
            
            return jsonify({
                "summary": summary,
                "graph_base64": img_str
            })
            
        except Exception as e:
            logger.error(f"Error generating prediction: {e}")
            logger.error(traceback.format_exc())
            return jsonify({
                "message": f"Error generating prediction: {str(e)}"
            })
            
    except Exception as e:
        logger.error(f"Error in predict_budget: {e}")
        logger.error(traceback.format_exc())
        return jsonify({
            "error": f"An error occurred: {str(e)}",
            "detail": traceback.format_exc()
        }), 500

def evaluate_model_accuracy(Uid):
    try:
        # Fetch data
        data = fetch_expense_data(Uid)
        if data is None or len(data) < 3:  
            return {
                "error": "Insufficient data for model evaluation. Need at least 3 months of data."
            }

        # Initialize results dictionary
        evaluation_results = {
            "overall": {},
            "category_wise": {},
            "visualization": None
        }

        # Prepare data
        data['Time'] = data['Year'] * 100 + data['Month']
        unique_categories = data['category'].unique()

        # Category-wise evaluation
        for category in unique_categories:
            category_data = data[data['category'] == category]
            
            if len(category_data) >= 3:  # Need at least 3 points for meaningful split
                X = category_data[['Time']].values
                y = category_data['amount'].values

                # Split data - use 80% for training, 20% for testing
                X_train, X_test, y_train, y_test = train_test_split(
                    X, y, test_size=0.2, random_state=42
                )

                # Train model
                model = LinearRegression()
                model.fit(X_train, y_train)

                # Make predictions
                y_pred = model.predict(X_test)

                # Calculate metrics
                mae = mean_absolute_error(y_test, y_pred)
                mse = mean_squared_error(y_test, y_pred)
                rmse = np.sqrt(mse)
                r2 = r2_score(y_test, y_pred)

                evaluation_results["category_wise"][category] = {
                    "mae": float(mae),
                    "mse": float(mse),
                    "rmse": float(rmse),
                    "r2_score": float(r2),
                    "sample_size": len(category_data)
                }

        # Calculate overall metrics
        all_true = []
        all_pred = []
        
        for category in unique_categories:
            if category in evaluation_results["category_wise"]:
                category_data = data[data['category'] == category]
                X = category_data[['Time']].values
                y = category_data['amount'].values
                
                model = LinearRegression()
                model.fit(X, y)
                
                # Predict next value
                next_time = np.max(X) + 1
                pred = model.predict([[next_time]])
                
                all_true.extend(y)
                all_pred.extend(model.predict(X))

        # Overall metrics
        overall_mae = mean_absolute_error(all_true, all_pred)
        overall_mse = mean_squared_error(all_true, all_pred)
        overall_rmse = np.sqrt(overall_mse)
        overall_r2 = r2_score(all_true, all_pred)

        evaluation_results["overall"] = {
            "mae": float(overall_mae),
            "mse": float(overall_mse),
            "rmse": float(overall_rmse),
            "r2_score": float(overall_r2),
            "total_samples": len(all_true)
        }

        # Generate visualization
        fig, (ax1, ax2) = plt.subplots(2, 1, figsize=(12, 12))

        # Plot 1: Category-wise R² scores
        categories = list(evaluation_results["category_wise"].keys())
        r2_scores = [evaluation_results["category_wise"][cat]["r2_score"] for cat in categories]
        
        ax1.bar(categories, r2_scores, color='#3498db')
        ax1.set_title('R² Score by Category')
        ax1.set_xlabel('Category')
        ax1.set_ylabel('R² Score')
        plt.xticks(rotation=45, ha='right')

        # Plot 2: Category-wise RMSE
        rmse_scores = [evaluation_results["category_wise"][cat]["rmse"] for cat in categories]
        ax2.bar(categories, rmse_scores, color='#e74c3c')
        ax2.set_title('RMSE by Category')
        ax2.set_xlabel('Category')
        ax2.set_ylabel('RMSE')
        plt.xticks(rotation=45, ha='right')

        plt.tight_layout()

        # Convert plot to base64
        buffer = io.BytesIO()
        plt.savefig(buffer, format='png', dpi=300, bbox_inches='tight')
        buffer.seek(0)
        evaluation_results["visualization"] = base64.b64encode(buffer.getvalue()).decode('utf-8')
        plt.close()

        return evaluation_results

    except Exception as e:
        logger.error(f"Error in evaluate_model_accuracy: {e}")
        logger.error(traceback.format_exc())
        return {"error": str(e)}

# Add new route for model evaluation
@app.route('/evaluate_model', methods=['POST'])
def evaluate_model():
    try:
        data = request.get_json()
        if not data or 'user_id' not in data:
            return jsonify({"error": "user_id is required"}), 400

        user_id = data['user_id']
        results = evaluate_model_accuracy(user_id)
        
        if "error" in results:
            return jsonify(results), 400

        return jsonify(results)

    except Exception as e:
        logger.error(f"Error in evaluate_model route: {e}")
        logger.error(traceback.format_exc())
        return jsonify({"error": str(e)}), 500

# Run the app
if __name__ == '__main__':
    try:
        print("Starting prediction server on http://127.0.0.1:5001")
        print("To test if server is running, open a browser and go to: http://127.0.0.1:5001")
        print("To test database connection, open: http://127.0.0.1:5001/test_db")
        print("Press CTRL+C to stop the server")
        app.run(host='0.0.0.0', port=5001, debug=True)
    except Exception as e:
        print(f"Failed to start the server: {e}")
        print(traceback.format_exc())