<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login1.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "collegep";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pnr = $_POST['pnr'];
    $email = $_POST['email'];
    
    // Check if PNR and email match in bookings and users tables
    $sql = "SELECT b.*, u.email 
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            WHERE b.pnr_number = ? AND u.email = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $pnr, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Valid PNR and email
        $_SESSION['cancel_pnr'] = $pnr;
        header("Location: confirm_cancellation.php");
        exit();
    } else {
        $error_message = "Invalid PNR number or email. Please check and try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Ticket - TrainDekho</title>
    <style>
        /* Copy your common styles from rose.php */
        
        .cancellation-container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
            max-width: 600px;
            margin: 40px auto;
        }

        .cancellation-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-weight: bold;
            color: #003366;
        }

        .form-group input {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
        }

        .form-group input:focus {
            border-color: #1a237e;
            outline: none;
        }

        .cancel-button {
            background-color: #dc3545;
            color: white;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .cancel-button:hover {
            background-color: #c82333;
        }

        .warning-text {
            color: #dc3545;
            font-size: 14px;
            margin-top: 20px;
            text-align: center;
        }

        .refund-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
        }

        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <!-- Copy your header from rose.php -->
    <header>
        <!-- Copy your existing header code here -->
    </header>

    <div class="booking-section">
        <div class="cancellation-container">
            <h1 style="color: #1a237e; text-align: center; margin-bottom: 30px;">Ticket Cancellation</h1>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form class="cancellation-form" method="POST">
                <div class="form-group">
                    <label for="pnr">PNR Number</label>
                    <input type="text" id="pnr" name="pnr" required>
                </div>

                <div class="form-group">
                    <label for="email">Email ID</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="refund-info">
                    <p><strong>Refund Policy:</strong></p>
                    <ul>
                        <li>Standard cancellation: 90% refund of total fare</li>
                        <li>Refund will be processed within 5-7 working days</li>
                        <li>Refund will be credited to the original payment method</li>
                    </ul>
                </div>

                <button type="submit" class="cancel-button">
                    Check Ticket Details
                </button>
            </form>
        </div>
    </div>

    <!-- Copy your footer from rose.php -->
    <footer>
        <!-- Copy your existing footer code here -->
    </footer>
</body>
</html>

<?php
$conn->close();
?> 