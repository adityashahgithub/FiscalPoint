<?php
// This script tries to start the Python prediction server

// Check if Python is installed
$python_check = shell_exec('python --version 2>&1');
if (empty($python_check) || strpos($python_check, 'Python') === false) {
    echo "<h1>Python Not Found</h1>";
    echo "<p>Unable to find Python on your system. Please install Python 3.x and try again.</p>";
    exit();
}

// Check if the prediction.py file exists
if (!file_exists(__DIR__ . '/predictions.py')) {
    echo "<h1>predictions.py Not Found</h1>";
    echo "<p>The predictions.py file was not found in the Frontend directory.</p>";
    exit();
}

// Check for required Python packages
$required_packages = [
    'flask', 'flask-cors', 'pandas', 'numpy', 'scikit-learn', 'matplotlib', 
    'mysql-connector-python', 'python-dotenv'
];

echo "<h1>Checking Python Packages</h1>";
echo "<ul>";
foreach ($required_packages as $package) {
    $check_cmd = "python -c \"import {$package}\" 2>&1";
    $output = shell_exec($check_cmd);
    if (empty($output)) {
        echo "<li style='color:green'>✓ {$package} is installed</li>";
    } else {
        echo "<li style='color:red'>✗ {$package} is NOT installed</li>";
    }
}
echo "</ul>";

// Try to start the server
echo "<h1>Starting Prediction Server</h1>";
echo "<p>Attempting to start the Python prediction server...</p>";

$command = 'python ' . __DIR__ . '/predictions.py > /dev/null 2>&1 &';
$output = shell_exec($command);

echo "<p>The server is now starting in the background.</p>";
echo "<p>Please wait a few seconds and then go to: <a href='predictions.php'>Predictions Page</a></p>";
echo "<p>Or check server status: <a href='http://127.0.0.1:5000' target='_blank'>http://127.0.0.1:5000</a></p>";

echo "<h2>Manual Start Instructions:</h2>";
echo "<p>If the automatic start doesn't work, please run this command in your terminal:</p>";
echo "<pre>python " . __DIR__ . "/predictions.py</pre>";
?> 