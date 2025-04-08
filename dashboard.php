<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: login1.php");
    exit();
}

// Get user details from session (Assuming you store them after login)
$username = $_SESSION["username"];
$email = $_SESSION["email"] ?? "Not Provided";
$mobile = $_SESSION["mobile"] ?? "Not Provided";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
        }
        .header {
            background-color: #003366;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h2 {
            margin: 0;
        }
        .nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-size: 14px;
        }
        .nav a:hover {
            text-decoration: underline;
        }
        .container {
            padding: 40px;
            max-width: 800px;
            margin: auto;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            text-align: center;
        }
        .btn {
            background-color: #ff6f00;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        <div class="nav">
            <a href="dashboard.php">Home</a>
            <a href="#">Profile</a>
            <a href="#">Settings</a>
            <a href="logout.php" class="btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>User Dashboard</h2>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
        <p><strong>Mobile:</strong> <?php echo htmlspecialchars($mobile); ?></p>
        <button class="btn" onclick="window.location.href='logout.php'">Logout</button>
    </div>
</body>
</html>
