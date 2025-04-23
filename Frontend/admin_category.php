<?php
session_start();
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

// Handle Update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_category"])) {
    $updated_category = trim($_POST["updated_category"]);
    $original_category = trim($_POST["original_category"]);

    // Check for duplicate category name
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM Expense WHERE category = ? AND category != ?");
    $stmt_check->bind_param("ss", $updated_category, $original_category);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        echo "<script>alert('Category name already exists. Please choose a different name.'); window.location.href='admin_category.php';</script>";
    } else {
        $stmt_update = $conn->prepare("UPDATE Expense SET category = ? WHERE category = ?");
        $stmt_update->bind_param("ss", $updated_category, $original_category);
        if ($stmt_update->execute()) {
            echo "<script>alert('Category updated successfully.'); window.location.href='admin_category.php';</script>";
        } else {
            echo "<script>alert('Update failed.');</script>";
        }
        $stmt_update->close();
    }
}

// Handle Delete
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
    $delete_category = $_POST["delete_id"];
    $stmt_delete = $conn->prepare("DELETE FROM Expense WHERE category = ?");
    $stmt_delete->bind_param("s", $delete_category);
    if ($stmt_delete->execute()) {
        echo "<script>
            alert('Category deleted successfully.');
            window.location.reload();
        </script>";
    } else {
        echo "<script>alert('Failed to delete category.');</script>";
    }
    $stmt_delete->close();
}

// Fetch unique categories
$sql = "SELECT category, MIN(Date) as CreatedDate FROM Expense GROUP BY category ORDER BY CreatedDate DESC";
$result_expense = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Category</title>
    <link rel="stylesheet" href="css/admin_category.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-inline input[type="text"] {
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-inline input[type="text"]:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76,175,80,0.5);
            outline: none;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .action-buttons .edit-btn,
        .action-buttons .delete-btn,
        .form-inline .edit-btn,
        .form-inline .delete-btn {
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
            display: inline-block;
            font-weight: 500;
        }
        .action-buttons .edit-btn,
        .form-inline .edit-btn {
            background-color: #4CAF50;
        }
        .action-buttons .edit-btn:hover,
        .form-inline .edit-btn:hover {
            background-color: #45a049;
        }
        .action-buttons .delete-btn,
        .form-inline .delete-btn {
            background-color: #f44336;
        }
        .action-buttons .delete-btn:hover,
        .form-inline .delete-btn:hover {
            background-color: #d32f2f;
        }
        .action-buttons form {
            display: inline;
            margin: 0;
        }
    </style>
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
    <h2 style="text-align:center;">Manage Category</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Sr No.</th>
                    <th>Category Name</th>
                    <th>Created Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
<?php
$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : null;
if ($result_expense->num_rows > 0) {
    $sr_no = 1;
    while ($row = $result_expense->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$sr_no}</td>";

        if ($edit_id === $sr_no) {
            echo "<form method='POST' class='form-inline' style='display: flex; align-items: center; gap: 10px; width: 100%;'>";
            echo "<td style='width: 40%;'>
                    <input type='text' name='updated_category' value='" . htmlspecialchars($row["category"]) . "' 
                           style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;' required>
                    <input type='hidden' name='original_category' value='" . htmlspecialchars($row["category"]) . "'>
                  </td>";
            echo "<td style='width: 30%;'>" . date("d-m-Y", strtotime($row["CreatedDate"])) . "</td>";
            echo "<td style='width: 30%;'>
                    <button type='submit' name='update_category' class='edit-btn' style='margin-right: 5px;'>Save</button>
                    <a href='admin_category.php' class='delete-btn'>Cancel</a>
                  </td>";
            echo "</form>";
        } else {
            echo "<td>" . htmlspecialchars($row["category"]) . "</td>";
            echo "<td>" . date("d-m-Y", strtotime($row["CreatedDate"])) . "</td>";
            echo "<td class='action-buttons'>
                    <a href='admin_category.php?edit_id={$sr_no}' class='edit-btn'>Edit</a>
                    <form method='POST' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this category?\");'>
                        <input type='hidden' name='delete_id' value='" . htmlspecialchars($row["category"]) . "'>
                        <button type='submit' class='delete-btn'>Delete</button>
                    </form>
                  </td>";
        }

        echo "</tr>";
        $sr_no++;
    }
} else {
    echo "<tr><td colspan='4'>No categories found.</td></tr>";
}
?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
