<?php
session_start();  // Start session for authentication

// Display errors for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";  
$password = "";  
$dbname = "collegep";  

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    // Check in users table
    $stmt = $conn->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            // Store user data in session
            $_SESSION["user_id"] = $user['id'];
            $_SESSION["username"] = $user['username'];
            $_SESSION["role"] = $user['role'];

            // Debug: Print role information
            echo "User Role: " . $user['role'];

            // Redirect based on role
            if ($user['role'] == 'admin') {
                header("Location: admin_dashboard.php");
                exit();
            } else {
                header("Location: rose.php");
                exit();
            }
        } else {
            $_SESSION["error_message"] = "Invalid password!";
        }
    } else {
        $_SESSION["error_message"] = "User not found!";
    }
    
    header("Location: login1.php");
    exit();
}

$conn->close();
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Train Booking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
                        url('images/login1.jpg') center/cover no-repeat fixed;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .header {
            background: linear-gradient(135deg, rgba(0, 51, 102, 0.95) 0%, rgba(0, 68, 136, 0.95) 100%);
            backdrop-filter: blur(5px);
            width: 100%;
            padding: 15px 0;
            margin-bottom: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

        .brand-name {
            color: white;
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .login-container {
            background: linear-gradient(rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.95)),
                        url('images/login1.jpg') center/cover no-repeat;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            width: 380px;
            text-align: center;
            margin-top: 20px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #003366, #004488, #003366);
        }

        .login-container h2 {
            margin-bottom: 25px;
            color: #003366;
            font-size: 28px;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.1);
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .login-container input {
            width: 100%;
            padding: 12px;
            margin: 12px 0;
            border: 2px solid rgba(0, 51, 102, 0.2);
            border-radius: 8px;
            box-sizing: border-box;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .login-container input:focus {
            border-color: #003366;
            box-shadow: 0 0 8px rgba(0, 51, 102, 0.2);
            outline: none;
        }

        .login-container button {
            background: linear-gradient(135deg, #003366 0%, #004488 100%);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-top: 15px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
        }

        .login-container button:hover {
            background: linear-gradient(135deg, #004488 0%, #005599 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 51, 102, 0.3);
        }

        .links {
            margin-top: 15px;
        }

        .links a {
            color: #003366;
            text-decoration: none;
            font-size: 14px;
            margin: 5px 0;
            display: inline-block;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo-section">
                <img src="images/TRAIN MANIA YOU TUBE CHANNEL LOGO.png" alt="TrainDekho Logo" class="logo">
                <span class="brand-name">TrainDekho</span>
            </div>
        </div>
    </div>

    <div class="login-container">
        <h2>Login</h2>
        
        <?php if (isset($_SESSION["error_message"])): ?>
            <p class="error-message"><?php echo $_SESSION["error_message"]; ?></p>
            <?php unset($_SESSION["error_message"]); ?>
        <?php endif; ?>

        <form action="login1.php" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>

        <div class="links">
            <p>Don't have an account? <a href="register.php">Register</a></p>
            <a href="forgot_password.php">Forgot Password?</a>
        </div>
    </div>
</body>
</html>