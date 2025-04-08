<?php
session_start();

// Add debug information
error_log("booking_confirmation.php started at " . date('Y-m-d H:i:s'));
error_log("GET parameters: " . print_r($_GET, true));
error_log("SESSION data: " . print_r($_SESSION, true));

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "collegep";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

error_log("Database connection successful");

// Check if payment_id is provided
if (empty($_GET['payment_id'])) {
    error_log("No payment_id provided, redirecting to rose.php");
    header("Location: rose.php");
    exit();
}

$payment_id = $_GET['payment_id'];
error_log("Looking for payment with ID: $payment_id");

// Fetch payment details
$sql = "SELECT * FROM payments WHERE payment_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $payment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Payment not found - try to look it up differently
    error_log("Payment not found with ID: $payment_id");
    
    // Try to get from session instead
    if (!empty($_SESSION['current_booking'])) {
        error_log("Using session data instead");
        $booking = $_SESSION['current_booking'];
        $train_id = $booking['train_id'];
        
        // Get train details
        $sql = "SELECT * FROM trains WHERE train_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $train_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $train = $result->fetch_assoc();
        
        // Generate temporary PNR
        $pnr = 'TEMP' . date('Ymd') . rand(100000, 999999);
        
        // Show confirmation page with warning
        $payment = [
            'amount' => $booking['total_amount'],
            'payment_date' => date('Y-m-d H:i:s'),
            'pnr' => $pnr
        ];
        
        error_log("Created temporary confirmation data");
        $showWarning = true;
    } else {
        // No data available, redirect
        error_log("No data available for confirmation, redirecting to rose.php");
        header("Location: rose.php");
        exit();
    }
} else {
    // Payment found
    $payment = $result->fetch_assoc();
    $showWarning = false;
    error_log("Payment found: " . print_r($payment, true));
    
    // Fetch booking details from bookings table
    $sql = "SELECT * FROM bookings WHERE pnr_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $payment['pnr']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $booking_db = $result->fetch_assoc();
        error_log("Booking found in database: " . print_r($booking_db, true));
    } else {
        $booking_db = null;
        error_log("No booking found in database for PNR: " . $payment['pnr']);
    }
    
    // Get train details
    $sql = "SELECT * FROM trains WHERE train_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $payment['train_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $train = $result->fetch_assoc();
}

// Get booking details from session as a fallback
$booking = $_SESSION['current_booking'] ?? [];
error_log("Booking details from session: " . print_r($booking, true));

// Use PNR from payment record
$pnr = $payment['pnr'] ?? '';

// If PNR is still empty, generate it and update database
if (empty($pnr)) {
    // Format: TD + Year + Month + Day + 6 random digits
    $pnr = 'TD' . date('Ymd') . rand(100000, 999999);
    
    // Update payment record with PNR
    $sql = "UPDATE payments SET pnr = ? WHERE payment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $pnr, $payment_id);
    $stmt->execute();
    
    // Also update bookings table if it exists but doesn't have PNR
    if ($booking_db && empty($booking_db['pnr_number'])) {
        $sql = "UPDATE bookings SET pnr_number = ? WHERE booking_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $pnr, $booking_db['booking_id']);
        $stmt->execute();
    }
}

// Determine journey date - use from bookings table, or from session, or fallback to current date
$journey_date = $booking_db['journey_date'] ?? $booking['journey_date'] ?? date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - TrainDekho</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fa;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #213d77 0%, #1e4f9e 100%);
            color: white;
            padding: 1rem;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }

        .brand-name {
            font-size: 24px;
            font-weight: bold;
            color: white;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .confirmation-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .success-banner {
            background: #4CAF50;
            color: white;
            padding: 15px;
            text-align: center;
            margin: -30px -30px 30px;
        }

        .pnr-container {
            background: #f1f8e9;
            border: 2px dashed #4CAF50;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
            border-radius: 8px;
        }

        .pnr-number {
            font-size: 24px;
            font-weight: 600;
            color: #2e7d32;
            letter-spacing: 2px;
        }

        .passenger-details {
            margin-top: 30px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 500;
            color: #213d77;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }

        .journey-details {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 20px;
            align-items: center;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .station-info {
            text-align: center;
        }

        .station-name {
            font-size: 18px;
            font-weight: 500;
        }

        .journey-line {
            height: 2px;
            background: #ddd;
            position: relative;
        }

        .passenger-card {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 10px;
            display: grid;
            grid-template-columns: auto 1fr auto auto;
            gap: 20px;
            align-items: center;
        }

        .passenger-number {
            width: 24px;
            height: 24px;
            background: #213d77;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #f47721;
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background: #e56a12;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: white;
            color: #213d77;
            border: 1px solid #213d77;
        }

        .btn-outline:hover {
            background: #f8f9fa;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.05;
            font-size: 150px;
            font-weight: bold;
            color: #000;
            white-space: nowrap;
            pointer-events: none;
        }

        .payment-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #ddd;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .category-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .category-child { background: #e1f5fe; color: #0288d1; }
        .category-adult { background: #e8f5e9; color: #388e3c; }
        .category-senior { background: #fff3e0; color: #f57c00; }
        .category-free { background: #f3e5f5; color: #7b1fa2; }
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

    <div class="container">
        <div class="confirmation-card">
            <div class="watermark">CONFIRMED</div>
            <div class="success-banner">
                <h2 style="margin: 0;">Booking Confirmed</h2>
                <p style="margin: 5px 0 0;">Your train ticket has been successfully booked</p>
            </div>

            <div class="pnr-container">
                <div>PNR NUMBER</div>
                <div class="pnr-number"><?php echo $pnr; ?></div>
            </div>

            <div class="section-title">Journey Details</div>
            <div class="journey-details">
                <div class="station-info">
                    <div class="station-name"><?php echo htmlspecialchars($train['source_station']); ?></div>
                    <div><?php echo date('H:i', strtotime($train['departure_time'])); ?></div>
                </div>
                <div class="journey-line"></div>
                <div class="station-info">
                    <div class="station-name"><?php echo htmlspecialchars($train['destination_station']); ?></div>
                    <div><?php echo date('H:i', strtotime($train['arrival_time'])); ?></div>
                </div>
            </div>

            <div>
                <strong><?php echo htmlspecialchars($train['train_name']); ?></strong> (<?php echo htmlspecialchars($train['train_number']); ?>)
            </div>
            <div>Class: <?php echo htmlspecialchars($booking['selected_class']); ?></div>

            <div class="section-title">Passenger Details</div>
            <div class="passenger-details">
                <?php for($i = 0; $i < count($booking['passenger_names']); $i++): ?>
                    <div class="passenger-card">
                        <div class="passenger-number"><?php echo $i + 1; ?></div>
                        <div>
                            <strong><?php echo htmlspecialchars($booking['passenger_names'][$i]); ?></strong>
                            <div><?php echo htmlspecialchars($booking['passenger_genders'][$i]); ?> | <?php echo htmlspecialchars($booking['passenger_ages'][$i]); ?> Years</div>
                        </div>
                        <div class="category-badge category-<?php echo strtolower(str_replace(' ', '-', $booking['passenger_categories'][$i])); ?>">
                            <?php echo htmlspecialchars($booking['passenger_categories'][$i]); ?>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>

            <div class="section-title">Payment Information</div>
            <div class="payment-info">
                <div class="info-row">
                    <div>Payment ID</div>
                    <div><?php echo htmlspecialchars($payment_id); ?></div>
                </div>
                <div class="info-row">
                    <div>Amount Paid</div>
                    <div>₹<?php echo number_format($payment['amount'], 2); ?></div>
                </div>
                <div class="info-row">
                    <div>Payment Date</div>
                    <div><?php echo date('d M Y, H:i', strtotime($payment['payment_date'])); ?></div>
                </div>
            </div>

            <div class="action-buttons">
                <a href="generate_ticket_pdf.php?pnr=<?php echo $pnr; ?>" class="btn btn-primary">Download Ticket</a>
                <a href="rose.php" class="btn btn-outline">Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?> 