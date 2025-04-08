<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "collegep";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if PNR is provided
if (empty($_GET['pnr'])) {
    header("Location: rose.php");
    exit();
}

$pnr = $_GET['pnr'];

// Fetch ticket details
$sql = "SELECT * FROM payments WHERE pnr = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $pnr);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // PNR not found - handle error
    header("Location: rose.php");
    exit();
}

$payment = $result->fetch_assoc();

// Fetch booking details from bookings table
$sql = "SELECT * FROM bookings WHERE pnr_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $pnr);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $booking_db = $result->fetch_assoc();
    $journey_date = $booking_db['journey_date'];
    $seat_type = $booking_db['seat_type'];
} else {
    $booking_db = null;
    $journey_date = date('Y-m-d');
    $seat_type = '';
}

// Get booking details from session as a fallback
$booking = $_SESSION['current_booking'] ?? [];
if (empty($seat_type)) {
    $seat_type = $booking['selected_class'] ?? '';
}

// Fetch train details
$sql = "SELECT * FROM trains WHERE train_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $payment['train_id']);
$stmt->execute();
$result = $stmt->get_result();
$train = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrainDekho E-Ticket - <?php echo $pnr; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f7fa;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #213d77;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .logo-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logo {
            width: 60px;
            height: 60px;
        }
        .brand-name {
            font-size: 24px;
            font-weight: bold;
            color: #213d77;
        }
        .title-section {
            text-align: right;
        }
        .ticket-title {
            font-size: 20px;
            color: #213d77;
            margin: 0;
        }
        .pnr-section {
            background: #f1f8e9;
            border: 2px dashed #4CAF50;
            padding: 10px;
            text-align: center;
            margin: 15px 0;
        }
        .pnr-number {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            background: #213d77;
            color: white;
            padding: 8px 15px;
            font-weight: bold;
        }
        .journey-details {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .station-details {
            width: 45%;
        }
        .station-name {
            font-size: 18px;
            font-weight: bold;
        }
        .station-time {
            color: #666;
        }
        .journey-middle {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .train-details {
            background: #f8f9fa;
            padding: 10px;
            margin-top: 10px;
        }
        .passenger-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .passenger-table th, .passenger-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .passenger-table th {
            background-color: #f2f2f2;
        }
        .payment-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            background: #f8f9fa;
            padding: 10px;
            margin-top: 10px;
        }
        .info-item {
            display: flex;
        }
        .info-label {
            font-weight: bold;
            width: 120px;
        }
        .important-info {
            background: #fff3cd;
            padding: 10px;
            margin-top: 15px;
        }
        .important-info ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 12px;
        }
        .print-button {
            background: #f47721;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            margin: 20px auto;
            display: block;
        }
        @media print {
            .print-button {
                display: none;
            }
            body {
                background: white;
            }
            .container {
                box-shadow: none;
                margin: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <button class="print-button" onclick="window.print()">Print Ticket</button>
        
        <div class="header">
            <div class="logo-section">
                <img src="images/TRAIN MANIA YOU TUBE CHANNEL LOGO.png" alt="TrainDekho Logo" class="logo">
                <div class="brand-name">TrainDekho</div>
            </div>
            <div class="title-section">
                <h2 class="ticket-title">E-TICKET / RESERVATION VOUCHER</h2>
                <p>Booking Date: <?php echo date('d M Y, H:i', strtotime($payment['payment_date'])); ?></p>
            </div>
        </div>

        <div class="pnr-section">
            <div>PNR NUMBER</div>
            <div class="pnr-number"><?php echo $pnr; ?></div>
        </div>

        <div class="section">
            <div class="section-title">JOURNEY DETAILS</div>
            <div class="journey-details">
                <div class="station-details">
                    <div>DEPARTURE STATION</div>
                    <div class="station-name"><?php echo htmlspecialchars($train['source_station']); ?></div>
                    <div class="station-time"><?php echo date('H:i', strtotime($train['departure_time'])); ?></div>
                    <div><?php echo date('D, d M Y', strtotime($train['departure_time'])); ?></div>
                </div>
                <div class="journey-middle">
                    <div>→</div>
                </div>
                <div class="station-details" style="text-align: right;">
                    <div>ARRIVAL STATION</div>
                    <div class="station-name"><?php echo htmlspecialchars($train['destination_station']); ?></div>
                    <div class="station-time"><?php echo date('H:i', strtotime($train['arrival_time'])); ?></div>
                    <div><?php echo date('D, d M Y', strtotime($train['arrival_time'])); ?></div>
                </div>
            </div>
            <div class="train-details">
                <div><strong>Train:</strong> <?php echo htmlspecialchars($train['train_name']); ?> (<?php echo htmlspecialchars($train['train_number']); ?>)</div>
                <div><strong>Class:</strong> <?php echo htmlspecialchars($seat_type); ?></div>
                <div><strong>Distance:</strong> <?php echo isset($train['distance']) ? htmlspecialchars($train['distance']) . ' km' : 'Not available'; ?></div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">PASSENGER DETAILS</div>
            <table class="passenger-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Age/Gender</th>
                        <th>Category</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for($i = 0; $i < count($booking['passenger_names']); $i++): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo htmlspecialchars($booking['passenger_names'][$i]); ?></td>
                        <td><?php echo htmlspecialchars($booking['passenger_ages'][$i]); ?>/<?php echo htmlspecialchars($booking['passenger_genders'][$i]); ?></td>
                        <td><?php echo htmlspecialchars($booking['passenger_categories'][$i]); ?></td>
                        <td>Confirmed</td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <div class="section-title">PAYMENT INFORMATION</div>
            <div class="payment-info">
                <div class="info-item">
                    <div class="info-label">Payment ID:</div>
                    <div><?php echo htmlspecialchars($payment['payment_id']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Amount Paid:</div>
                    <div>₹<?php echo number_format($payment['amount'], 2); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Payment Date:</div>
                    <div><?php echo date('d M Y, H:i', strtotime($payment['payment_date'])); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status:</div>
                    <div>CONFIRMED</div>
                </div>
            </div>
        </div>

        <div class="important-info">
            <strong>IMPORTANT INFORMATION:</strong>
            <ul>
                <li>Please carry your ID proof in original during the journey in specified form as mentioned on the ticket.</li>
                <li>E-ticket passenger is permitted in the train against a berth/seat only when his/her name appears in the reservation chart.</li>
                <li>Please arrive at the station at least 30 minutes before departure.</li>
                <li>This ticket is non-transferable.</li>
                <li>For cancellations and refunds, please visit our website or contact customer support.</li>
            </ul>
        </div>

        <div class="footer">
            <p>For any assistance, please contact our 24x7 customer support:</p>
            <p>Email: support@traindekho.com | Phone: +91 1234567890</p>
            <p>This is a digitally generated ticket and does not require physical signature.</p>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>