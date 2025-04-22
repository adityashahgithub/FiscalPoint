from flask import Flask, jsonify, request
from flask_cors import CORS

# Initialize Flask app
app = Flask(__name__)
CORS(app)  # Enable CORS for all routes

# Add a simple route to check if server is running
@app.route('/', methods=['GET'])
def health_check():
    return jsonify({"status": "online", "message": "Minimal prediction server is running"})

# Very simple prediction endpoint
@app.route('/predict_budget', methods=['POST'])
def predict_budget():
    try:
        data = request.get_json()
        if not data or 'user_id' not in data:
            return jsonify({"error": "user_id is required"}), 400

        user_id = data['user_id']
        print(f"Received request for user_id: {user_id}")
        
        # Return a static response with no graph
        return jsonify({
            "summary": "This is a minimal test server.\n\nPredictions will be available once you add more expense data and connect to the full prediction server.",
            "graph_base64": ""  # Empty graph
        })
        
    except Exception as e:
        print(f"Error: {e}")
        return jsonify({"error": f"An error occurred: {str(e)}"}), 500

# Run the app
if __name__ == '__main__':
    try:
        print("Starting minimal prediction server on http://127.0.0.1:5001")
        print("To test if server is running, visit: http://127.0.0.1:5001")
        print("Press CTRL+C to stop the server")
        app.run(host='127.0.0.1', port=5001, debug=True)
    except Exception as e:
        print(f"Failed to start the server: {e}")
        import traceback
        print(traceback.format_exc()) 