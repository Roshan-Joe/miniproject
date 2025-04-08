<?php
session_start();

// Debug output
error_log("save_payment.php started at " . date('Y-m-d H:i:s'));
error_log("SESSION data: " . print_r($_SESSION, true));
error_log("POST data: " . print_r($_POST, true));

// At the beginning of your file, after session_start()
if(!isset($_SESSION['user_id'])) {
    error_log("Warning: user_id not set in session");
    // You might want to set a default value or redirect to login
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "collegep";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die(json_encode(['success' => false, 'error' => "Connection failed: " . $conn->connect_error]));
}

error_log("Database connection successful");

// Debug output
error_log("save_payment.php received: " . print_r($_POST, true));

// Add this at the beginning of your POST processing
$test_query = $conn->query("SELECT 1");
if($test_query) {
    error_log("Database connection is working");
} else {
    error_log("Database test query failed: " . $conn->error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = $_POST['payment_id'] ?? '';
    $train_id = $_POST['train_id'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $user_id = $_SESSION['user_id'] ?? 1; // Default to 1 if not set
    $payment_date = date('Y-m-d H:i:s');
    
    // Generate PNR
    $pnr = 'TD' . date('Ymd') . rand(100000, 999999);
    
    // Get booking details from session
    $booking = $_SESSION['current_booking'] ?? [];
    $seat_type = $booking['selected_class'] ?? '';
    $no_of_seats = count($booking['passenger_names'] ?? []);
    $journey_date = date('Y-m-d'); // Use actual journey date if available
    
    try {
        // Check if bookings table exists
        $tableCheckResult = $conn->query("SHOW TABLES LIKE 'bookings'");
        if ($tableCheckResult->num_rows === 0) {
            error_log("Bookings table doesn't exist - creating it");
            
            // Create bookings table
            $createTableSql = "CREATE TABLE bookings (
                booking_id INT(11) AUTO_INCREMENT PRIMARY KEY,
                user_id INT(11) NOT NULL,
                train_id INT(11) NOT NULL,
                pnr_number VARCHAR(20) NOT NULL,
                booking_date DATETIME NOT NULL,
                journey_date DATE NOT NULL,
                seat_type VARCHAR(50) NOT NULL,
                no_of_seats INT(11) NOT NULL,
                total_fare DECIMAL(10,2) NOT NULL,
                status VARCHAR(50) DEFAULT 'confirmed',
                created_at DATETIME NOT NULL
            )";
            
            if (!$conn->query($createTableSql)) {
                throw new Exception("Failed to create bookings table: " . $conn->error);
            }
            
            error_log("Successfully created bookings table");
        } else {
            error_log("Bookings table exists");
        }
        
        // Check if payments table exists
        $tableCheckResult = $conn->query("SHOW TABLES LIKE 'payments'");
        if ($tableCheckResult->num_rows === 0) {
            error_log("Payments table doesn't exist - creating it");
            
            // Create payments table
            $createTableSql = "CREATE TABLE payments (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                payment_id VARCHAR(255) NOT NULL,
                user_id INT(11) NOT NULL,
                train_id INT(11) NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                payment_date DATETIME NOT NULL,
                pnr VARCHAR(20) NOT NULL,
                status VARCHAR(50) DEFAULT 'completed'
            )";
            
            if (!$conn->query($createTableSql)) {
                throw new Exception("Failed to create payments table: " . $conn->error);
            }
            
            error_log("Successfully created payments table");
        } else {
            error_log("Payments table exists");
        }
        
        // Start transaction
        $conn->begin_transaction();
        error_log("Started database transaction");
        
        // First insert into payments table
        $sql = "INSERT INTO payments (payment_id, user_id, train_id, amount, payment_date, pnr, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'completed')";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed for payments: " . $conn->error);
        }
        
        $stmt->bind_param("siidss", $payment_id, $user_id, $train_id, $amount, $payment_date, $pnr);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed for payments: " . $stmt->error);
        }
        
        error_log("Successfully inserted into payments table");
        
        // Now insert into bookings table
        $sql = "INSERT INTO bookings (user_id, train_id, pnr_number, booking_date, journey_date, 
                seat_type, no_of_seats, total_fare, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', ?)";
                
        error_log("Preparing SQL for bookings table: $sql");
        error_log("Values: user_id=$user_id, train_id=$train_id, pnr=$pnr, 
                   booking_date=$payment_date, journey_date=$journey_date, 
                   seat_type=$seat_type, no_of_seats=$no_of_seats, 
                   total_fare=$amount, created_at=$payment_date");
                   
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed for bookings: " . $conn->error);
        }
        
        $stmt->bind_param("iissssids", 
            $user_id, 
            $train_id, 
            $pnr, 
            $payment_date, 
            $journey_date, 
            $seat_type, 
            $no_of_seats, 
            $amount, 
            $payment_date
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed for bookings: " . $stmt->error);
        }
        
        error_log("Successfully inserted into bookings table");
        
        // Save food orders if any exist
        if (isset($_POST['food_orders'])) {
            $foodOrders = json_decode($_POST['food_orders'], true);
            
            if (!empty($foodOrders)) {
                $sql = "INSERT INTO food_order (pnr_number, food_item, quantity, price, total_price) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);

                foreach ($foodOrders as $foodId => $quantity) {
                    if ($quantity > 0) {
                        // Get food item details
                        $foodItem = null;
                        switch ($foodId) {
                            case '1': 
                                $foodItem = ['name' => 'Veg Thali', 'price' => 120];
                                break;
                            case '2':
                                $foodItem = ['name' => 'Non-Veg Thali', 'price' => 150];
                                break;
                            case '3':
                                $foodItem = ['name' => 'Sandwich', 'price' => 60];
                                break;
                            case '4':
                                $foodItem = ['name' => 'Biryani', 'price' => 130];
                                break;
                            case '5':
                                $foodItem = ['name' => 'Fruit Plate', 'price' => 80];
                                break;
                        }

                        if ($foodItem) {
                            $totalPrice = $foodItem['price'] * $quantity;
                            $stmt->bind_param("ssids", 
                                $pnr,
                                $foodItem['name'],
                                $quantity,
                                $foodItem['price'],
                                $totalPrice
                            );
                            
                            if (!$stmt->execute()) {
                                throw new Exception("Error saving food order: " . $stmt->error);
                            }
                        }
                    }
                }
            }
        }

        // Commit transaction
        $conn->commit();
        error_log("Transaction committed successfully");
        
        // Send success response
        echo json_encode(['success' => true, 'pnr' => $pnr]);
        error_log("Sent success response with PNR: $pnr");
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        error_log("Transaction rolled back due to error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

$conn->close();

error_log("save_payment.php completed at " . date('Y-m-d H:i:s'));
?> 