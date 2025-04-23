<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION["Uid"]) || $_SESSION["Role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FiscalPoint";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['qid'], $_POST['status'])) {
        $qid = intval($_POST['qid']);
        $status = ($_POST['status'] === 'Solved') ? 'Solved' : 'Pending';
        $admin_response = isset($_POST['admin_response']) ? trim($_POST['admin_response']) : '';
        $response_date = date('Y-m-d H:i:s');

        $update = $conn->prepare("UPDATE Query SET Status = ?, Admin_Response = ?, Response_Date = ? WHERE Qid = ?");
        $update->bind_param("sssi", $status, $admin_response, $response_date, $qid);
        if ($update->execute()) {
            echo "<script>alert('Query updated successfully.');</script>";
        } else {
            echo "<script>alert('Failed to update query.');</script>";
        }
        $update->close();
    }
}

$sql = "SELECT Qid, Email, Query_type, Description, Created_At, Status, Admin_Response, Response_Date FROM Query ORDER BY Created_At DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="css/admin_registered_users.css"> <!-- Use the same CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<header>
    <img src="css/logo.png" alt="Logo" class="logo" onclick="location.href='landing.html'">
</header>

<aside class="sidebar">
    <div class="profile">
        <img src="css/profile.png" alt="Admin Profile" class="avatar">
    </div>
    <ul class="menu">
        <li><a href="admin_category.php"><i class="fas fa-tags"></i> Category</a></li><br>
        <li><a href="admin_registered_users.php"><i class="fas fa-user-friends"></i> Reg Users</a></li><br>
        <li><a href="admin_query.php"><i class="fas fa-question-circle"></i> <strong>Query</strong></a></li><br>
        <li><a href="add_admin.php"><i class="fas fa-user-plus"></i> <strong>Add Admin</strong></a></li><br>
        <li><a href="manage_admin.php"><i class="fas fa-user-cog"></i> <strong>Manage Admin</strong></a></li><br>
        <li><a href="admin_profile.php"><i class="fas fa-id-card"></i> Profile</a></li><br>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li><br>
    </ul>
    </aside>
    <div>
    <h1 style="text-align:center;">User Queries</h1>
    <div class="table-container">
        <table class="query-table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Query Type</th>
                    <th>Description</th>
                    <th>Submitted At</th>
                    <th>Status</th>
                    <th>Admin Response</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row['Email']) . "</td>
                            <td>" . htmlspecialchars($row['Query_type']) . "</td>
                            <td>" . htmlspecialchars($row['Description']) . "</td>
                            <td>" . date('d-m-Y H:i', strtotime($row['Created_At'])) . "</td>
                            <td>
                                <form method='POST' action='' class='status-form'>
                                    <input type='hidden' name='qid' value='{$row['Qid']}'>
                                    <select name='status' class='status-select'>
                                        <option value='Pending'" . ($row['Status'] == 'Pending' ? ' selected' : '') . ">Pending</option>
                                        <option value='Solved'" . ($row['Status'] == 'Solved' ? ' selected' : '') . ">Solved</option>
                                    </select>
                                </form>
                            </td>
                            <td>" . (!empty($row['Admin_Response']) ? 
                                htmlspecialchars($row['Admin_Response']) . 
                                "<br><small>Responded: " . date('d-m-Y H:i', strtotime($row['Response_Date'])) . "</small>" 
                                : "-") . 
                            "</td>
                            <td class='action-buttons'>
                                <button onclick='openResponseModal({$row['Qid']})' class='edit-btn'>Respond</button>
                            </td>
                          </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No queries submitted yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
    </div>
</div>

<!-- Add Modal for Response -->
<div id="responseModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Respond to Query</h2>
        <form method="POST" action="" id="responseForm">
            <input type="hidden" name="qid" id="modalQid">
            <div class="form-group">
                <label for="admin_response">Your Response:</label>
                <textarea name="admin_response" id="admin_response" required></textarea>
            </div>
            <div class="form-group">
                <label for="status">Status:</label>
                <select name="status" id="status">
                    <option value="Pending">Pending</option>
                    <option value="Solved">Solved</option>
                </select>
            </div>
            <button type="submit" class="edit-btn">Submit Response</button>
        </form>
    </div>
</div>

<style>
/* Existing styles remain... */

.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #86a69c;
    margin: 15% auto;
    padding: 20px;
    border-radius: 8px;
    width: 50%;
    color: white;
}

.close {
    color: white;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
}

.form-group textarea {
    width: 100%;
    padding: 8px;
    border-radius: 4px;
    border: 1px solid #ddd;
    min-height: 100px;
}

.status-select {
    padding: 5px;
    border-radius: 4px;
    border: 1px solid #ddd;
}

.action-buttons {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.edit-btn {
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
}

.edit-btn:hover {
    background-color: #45a049;
}
</style>

<script>
const modal = document.getElementById('responseModal');
const span = document.getElementsByClassName('close')[0];
const responseForm = document.getElementById('responseForm');
const modalQid = document.getElementById('modalQid');

function openResponseModal(qid) {
    modalQid.value = qid;
    modal.style.display = "block";
}

span.onclick = function() {
    modal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

// Auto-submit status changes
document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', function() {
        this.closest('form').submit();
    });
});
</script>

</body>
</html>