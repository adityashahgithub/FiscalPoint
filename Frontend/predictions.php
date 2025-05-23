<?php 
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DB connection (still required for session verification)
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "FiscalPoint"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if logged in
if (!isset($_SESSION["Uid"])) {
    echo "<script>alert('Session expired. Please log in again.'); window.location.href='login.php';</script>";
    exit();
}

$uid = $_SESSION["Uid"];
$api_url = "http://127.0.0.1:5001/predict_budget";
$health_check_url = "http://127.0.0.1:5001/";

// Function to check if Python server is running
function isPythonServerRunning($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 second timeout
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $http_code >= 200 && $http_code < 300;
}

// First check if server is running at all
$server_running = isPythonServerRunning($health_check_url);
$diagnostic_info = [];

if (!$server_running) {
    $prediction = ['error' => 'Python prediction server is not running. Please start the server.'];
    $diagnostic_info['server_status'] = 'Offline';
    $diagnostic_info['curl_version'] = function_exists('curl_version') ? curl_version()['version'] : 'Not available';
} else {
    // Server is running, let's try the actual API
    $diagnostic_info['server_status'] = 'Online';
    
    // Prepare POST request with user_id in JSON body
    $data = json_encode(["user_id" => (int)$uid]);

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 second timeout
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ));

    // Execute request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // Save diagnostic info
    $diagnostic_info['http_code'] = $http_code;
    $diagnostic_info['curl_error'] = $curl_error;
    $diagnostic_info['response_length'] = strlen($response);

    // Handle response
    if ($http_code === 0) {
        $prediction = ['error' => 'Unable to connect to prediction server. Please ensure the Python server is running.'];
    } elseif ($http_code !== 200) {
        $prediction = ['error' => "Server error occurred (HTTP $http_code). Please try again later."];
    } else {
        $prediction = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $prediction = ['error' => 'Invalid response from server. JSON parsing failed.'];
            $diagnostic_info['json_error'] = json_last_error_msg();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expense Predictions - FiscalPoint</title>
    <link rel="stylesheet" href="css/predictions.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .chart-container {
            height: 360px;
            margin: 0 auto;
            background-color: #fff;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .prediction-card {
            background-color: #f5f5f5;
            border-radius: 15px;
            padding: 10px;
            margin-bottom: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .summary-text {
            background-color: #fff;
            padding: 8px;
            border-radius: 8px;
            margin-bottom: 8px;
            line-height: 1.2;
            white-space: pre-wrap;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .prediction-graph {
            display: none;
        }
        
        .prediction-card h2 {
            margin: 0 0 8px 0;
            font-size: 18px;
            padding: 0;
        }
        
        /* Remove extra space in diagnostic info section */
        .diagnostic-info {
            margin-top: 10px;
            padding: 10px;
        }
        
        /* Fix the spacing in the server status indicator */
        .server-status {
            padding: 4px 8px;
            margin: 0;
            font-size: 12px;
        }
        
        /* Only apply this to content area, not sidebar */
        .main-content br {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <img src="css/logo.png" alt="Logo" class="logo" onclick="location.href='landing.html'"> 
    </header>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="profile">
            <img src="css/profile.png" alt="Profile Image" class="avatar">
        </div>
        <ul class="menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <strong>Dashboard</strong></a></li><br>
            <li><a href="addincome.php"><i class="fas fa-wallet"></i> <span style="font-weight: bold;">Income</span></a></li><br>
            <li><a href="setbudget.php"><i class="fas fa-coins"></i> <strong>Budget</strong></a></li><br>
            <li><a href="addexpense.php"><i class="fas fa-plus-circle"></i> <strong>Add Expense</strong></a></li><br>
            <li class="dropdown">
                <a href="#"><i class="fas fa-chart-bar"></i> <strong><em>Graph Reports:</em></strong></a>
                <ul>
                    <li><a href="linegraph.php"><i class="fas fa-chart-line"></i> Line Graph Report</a></li>
                    <li><a href="piegraph.php"><i class="fas fa-chart-pie"></i> Pie Graph Report</a></li>
                </ul>
            </li><br>
            <li class="dropdown">
                <a href="#"><i class="fas fa-table"></i> <strong><em>Tabular Reports:</em></strong></a><br>
                <ul>
                    <li><a href="tabularreport.php"><i class="fas fa-list-alt"></i> All Expenses</a></li>
                    <li><a href="categorywisereport.php"><i class="fas fa-layer-group"></i> Category-wise Expense</a></li>
                </ul>
            </li><br>
            <li><a href="insights.php"><i class="fas fa-lightbulb"></i> <strong>Insights</strong></a></li><br>
            <li><a href="predictions.php"><i class="fas fa-chart-line"></i> <strong>Predictions</strong></a></li><br>            
            <li><a href="profile.php"><i class="fas fa-user"></i> <strong>Profile</strong></a></li><br>
            
            <li><a href="query.php"><i class="fas fa-question-circle"></i> <strong>Query</strong></a></li><br>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <strong>Logout</strong></a></li><br>
        </ul>
    </aside>


    <!-- Server Status -->
    <div class="server-status <?php echo $server_running ? 'server-online' : 'server-offline'; ?>">
        <i class="fas <?php echo $server_running ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
        Prediction Server: <?php echo $server_running ? 'Online' : 'Offline'; ?>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <div class="prediction-card">
            <h2><i class="fas fa-brain"></i> AI-Powered Expense Predictions</h2>
            
            <?php if (isset($prediction['error'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($prediction['error']); ?>
                    <?php if (strpos($prediction['error'], 'Python') !== false): ?>
                        <p>To fix this:</p>
                        <ol>
                            <li>Open a terminal in the project directory</li>
                            <li>Run: <code>pip install flask flask-cors pandas numpy scikit-learn matplotlib mysql-connector-python python-dotenv</code></li>
                            <li>Then run: <code>python Frontend/predictions.py</code></li>
                            <li>You should see "Starting prediction server..." message</li>
                            <li>Open <a href="http://127.0.0.1:5001" target="_blank">http://127.0.0.1:5001</a> in your browser to verify the server is running</li>
                            <li>Then refresh this page</li>
                        </ol>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php if (isset($prediction['summary'])): ?>
                    <div class="summary-text">
                        <?php 
                        $summary = $prediction['summary'];
                        // Make text more concise and readable
                        $summary = str_replace('📅 Predicted Insights for Month', '<strong style="font-size:16px">📅 Prediction for Month</strong>', $summary);
                        $summary = str_replace('🔮 Prediction based on your past spending patterns:', '<strong style="font-size:16px">🔮 Based on your spending patterns:</strong>', $summary);
                        // Remove extra blank lines
                        $summary = preg_replace('/\n\s*\n/', "\n", $summary);
                        echo nl2br($summary);
                        ?>
                    </div>
                    
                    <?php 
                    // Parse category data from the summary
                    $categoryData = [];
                    $summaryLines = explode("\n", $prediction['summary']);
                    foreach ($summaryLines as $line) {
                        if (strpos($line, '-') === 0) {
                            preg_match('/- ([^:]+): ₹([0-9.]+)/', $line, $matches);
                            if (count($matches) === 3) {
                                $categoryData[$matches[1]] = floatval($matches[2]);
                            }
                        }
                    }
                    
                    // Simplified month and year extraction
                    preg_match('/Month ([0-9]+), ([0-9]+)/', $prediction['summary'], $dateMatches);
                    $predictedMonth = isset($dateMatches[1]) ? $dateMatches[1] : '';
                    $predictedYear = isset($dateMatches[2]) ? $dateMatches[2] : '';
                    $monthName = $predictedMonth ? date("F", mktime(0, 0, 0, $predictedMonth, 1)) : '';
                    ?>
                    
                    <div class="chart-container">
                        <canvas id="categoryExpenseChart"></canvas>
                    </div>
                    
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        const ctx = document.getElementById('categoryExpenseChart').getContext('2d');
                        
                        const categoryChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: <?php echo json_encode(array_keys($categoryData)); ?>,
                                datasets: [{
                                    label: 'Predicted Expenses for <?php echo $monthName . " " . $predictedYear; ?>',
                                    data: <?php echo json_encode(array_values($categoryData)); ?>,
                                    backgroundColor: [
                                        '#3498db', '#2ecc71', '#9b59b6', '#e74c3c', 
                                        '#f1c40f', '#1abc9c', '#34495e', '#e67e22',
                                        '#95a5a6', '#16a085', '#d35400', '#c0392b'
                                    ],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    title: {
                                        display: true,
                                        text: 'Category-wise Predicted Expenses',
                                        color: '#333',
                                        font: {
                                            size: 16,
                                            weight: 'bold'
                                        },
                                        padding: {
                                            top: 5,
                                            bottom: 10
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return '₹' + context.parsed.y.toFixed(2);
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        title: {
                                            display: true,
                                            text: 'Amount (₹)',
                                            color: '#333'
                                        },
                                        ticks: {
                                            callback: function(value) {
                                                return '₹' + value;
                                            },
                                            font: {
                                                size: 10
                                            }
                                        }
                                    },
                                    x: {
                                        title: {
                                            display: false
                                        },
                                        ticks: {
                                            maxRotation: 45,
                                            minRotation: 45,
                                            font: {
                                                size: 10
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                <?php elseif (isset($prediction['message'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($prediction['message']); ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Diagnostic Information (shown if there's an error) -->
            <?php if (isset($prediction['error']) || (isset($prediction['message']) && $http_code != 200)): ?>
                <div class="diagnostic-info">
                    <h3>Diagnostic Information</h3>
                    <pre><?php echo json_encode($diagnostic_info, JSON_PRETTY_PRINT); ?></pre>
                    
                    <?php if (!empty($response)): ?>
                        <h4>Server Response</h4>
                        <pre><?php echo htmlspecialchars($response); ?></pre>
                    <?php endif; ?>
                    
                    <h4>Test Connections</h4>
                    <p>Check these URLs to diagnose issues:</p>
                    <ul>
                        <li><a href="http://127.0.0.1:5001" target="_blank">Test Python Server</a> - Should show: <code>{"status":"online","message":"Prediction server is running"}</code></li>
                        <li><a href="http://127.0.0.1:5001/test_db" target="_blank">Test Database Connection</a> - Should show: <code>{"status":"success","message":"Database connection successful"}</code></li>
                    </ul>
                    
                    <h4>Steps to Fix</h4>
                    <ol>
                        <li>Make sure Python server is running in a terminal window with: <code>python Frontend/predictions.py</code></li>
                        <li>Watch the terminal window for error messages when you refresh this page</li>
                        <li>Check that your database is running and contains data for this user (ID: <?php echo $uid; ?>)</li>
                        <li>Try adding a few expenses if you don't have enough data for predictions</li>
                    </ol>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Check server status periodically
        setInterval(function() {
            fetch('http://127.0.0.1:5001/', {
                method: 'GET'
            }).then(function(response) {
                const statusDiv = document.querySelector('.server-status');
                if (response.ok) {
                    statusDiv.className = 'server-status server-online';
                    statusDiv.innerHTML = '<i class="fas fa-check-circle"></i> Prediction Server: Online';
                } else {
                    statusDiv.className = 'server-status server-offline';
                    statusDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Prediction Server: Offline';
                }
            }).catch(function() {
                const statusDiv = document.querySelector('.server-status');
                statusDiv.className = 'server-status server-offline';
                statusDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Prediction Server: Offline';
            });
        }, 30000); // Check every 30 seconds
    </script>
</body>
</html>
