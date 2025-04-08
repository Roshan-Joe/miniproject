<?php
session_start();

// Check if user is logged in and PNR is set
if (!isset($_SESSION['username']) || !isset($_SESSION['cancel_pnr'])) {
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

$pnr = $_SESSION['cancel_pnr'];

// Get booking details
$sql = "SELECT b.*, t.train_name, t.source_station, t.destination_station, 
               u.email, u.id as user_id
        FROM bookings b 
        JOIN trains t ON b.train_id = t.train_id
        JOIN users u ON b.user_id = u.id 
        WHERE b.pnr_number = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $pnr);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

// Handle cancellation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Calculate refund amount (90% of total fare)
        $refund_amount = $booking['total_fare'] * 0.9;
        
        // First, insert into cancellations table
        $sql = "INSERT INTO cancellations (booking_id, user_id, pnr_number, refund_amount, status) 
               VALUES (?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issd", 
            $booking['booking_id'],
            $booking['user_id'],
            $pnr,
            $refund_amount
        );
        
        // Execute the insert
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert into cancellations table");
        }
        
        // Update the status of the booking to 'cancelled'
        $sql = "UPDATE bookings SET status = 'cancelled' WHERE pnr_number = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $pnr);
        
        // Execute the update
        if (!$stmt->execute()) {
            throw new Exception("Failed to update booking status");
        }
        
        // If both operations successful, commit transaction
        $conn->commit();
        
        // Clear session variable
        unset($_SESSION['cancel_pnr']);
        
        // Redirect to success page
        header("Location: cancellation_success.php");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error_message = "Error processing cancellation: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Cancellation - TrainDekho</title>
    <style>
        /* ... copy existing styles from cancel_ticket.php ... */
        .ticket-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .detail-label {
            font-weight: bold;
            color: #495057;
        }
        
        .confirm-cancel-btn {
            background-color: #dc3545;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
        }
        
        .confirm-cancel-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="booking-section">
        <div class="cancellation-container">
            <h1>Confirm Ticket Cancellation</h1>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="ticket-details">
                <div class="detail-row">
                    <span class="detail-label">PNR Number:</span>
                    <span><?php echo htmlspecialchars($booking['pnr_number']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Train:</span>
                    <span><?php echo htmlspecialchars($booking['train_name']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">From:</span>
                    <span><?php echo htmlspecialchars($booking['source_station']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">To:</span>
                    <span><?php echo htmlspecialchars($booking['destination_station']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Travel Date:</span>
                    <span><?php echo isset($booking['date']) ? htmlspecialchars($booking['date']) : 'Not specified'; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Fare:</span>
                    <span>₹<?php echo htmlspecialchars($booking['total_fare']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Refund Amount:</span>
                    <span>₹<?php echo htmlspecialchars($booking['total_fare'] * 0.9); ?></span>
                </div>
            </div>
            
            <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this ticket? This action cannot be undone.');">
                <button type="submit" class="confirm-cancel-btn">
                    Confirm Cancellation
                </button>
            </form>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?> 