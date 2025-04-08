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

// Create food_order table if it doesn't exist
$createTableSQL = "CREATE TABLE IF NOT EXISTS food_order (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    pnr_number VARCHAR(20) NOT NULL,
    food_item VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($createTableSQL)) {
    die("Error creating table: " . $conn->error);
}

$existingOrders = [];
$bookingDetails = [];
$message = "";

// Check if PNR form is submitted
if (isset($_POST['check_pnr'])) {
    $pnr = $_POST['pnr_number'];
    
    // First check if PNR exists in bookings
    $bookingSql = "SELECT b.*, t.train_name, t.source_station, t.destination_station 
                   FROM bookings b 
                   JOIN trains t ON b.train_id = t.train_id 
                   WHERE b.pnr_number = ?";
    $stmt = $conn->prepare($bookingSql);
    $stmt->bind_param("s", $pnr);
    $stmt->execute();
    $bookingResult = $stmt->get_result();
    
    if ($bookingResult->num_rows > 0) {
        $bookingDetails = $bookingResult->fetch_assoc();
        
        // Now check for existing food orders
        $orderSql = "SELECT * FROM food_order WHERE pnr_number = ?";
        $stmt = $conn->prepare($orderSql);
        $stmt->bind_param("s", $pnr);
        $stmt->execute();
        $orderResult = $stmt->get_result();
        
        if ($orderResult->num_rows > 0) {
            while ($row = $orderResult->fetch_assoc()) {
                $existingOrders[] = $row;
            }
        }
        
        $_SESSION['current_pnr'] = $pnr;
    } else {
        $message = "No booking found with this PNR number. Please check and try again.";
    }
}

// Process food order
if (isset($_POST['add_food'])) {
    $pnr = $_SESSION['current_pnr'];
    $foodItem = $_POST['food_item'];
    $quantity = $_POST['quantity'];
    $price = 0;
    
    // Get price based on selected food item
    switch ($foodItem) {
        case 'Biryani': $price = 250; break;
        case 'Veg Thali': $price = 180; break;
        case 'Non-Veg Thali': $price = 220; break;
        case 'Fruit Plate': $price = 120; break;
        case 'Veg Meal': $price = 150; break;
        case 'Burger': $price = 90; break;
        case 'Pizza': $price = 200; break;
        default: $price = 100;
    }
    
    $totalPrice = $price * $quantity;
    
    // Insert new food order
    $sql = "INSERT INTO food_order (pnr_number, food_item, quantity, price, total_price) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssids", $pnr, $foodItem, $quantity, $price, $totalPrice);
    
    if ($stmt->execute()) {
        $message = "Food order added successfully!";
        // Refresh existing orders
        $orderSql = "SELECT * FROM food_order WHERE pnr_number = ?";
        $stmt = $conn->prepare($orderSql);
        $stmt->bind_param("s", $pnr);
        $stmt->execute();
        $orderResult = $stmt->get_result();
        
        $existingOrders = [];
        while ($row = $orderResult->fetch_assoc()) {
            $existingOrders[] = $row;
        }
    } else {
        $message = "Error adding food order: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrainDekho E-Catering</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            color: #2c3e50;
        }

        header {
            background: linear-gradient(135deg, #1a2a6c 0%, #b21f1f 50%, #fdbb2d 100%);
            padding: 20px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logo {
            height: 60px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }

        nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 500;
            position: relative;
            transition: all 0.3s ease;
        }

        nav a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: #fdbb2d;
            transition: width 0.3s ease;
        }

        nav a:hover::after {
            width: 100%;
        }

        .menu-section {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .menu-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5em;
            text-align: center;
            color: #1a2a6c;
            margin-bottom: 40px;
            position: relative;
        }

        .menu-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(to right, #1a2a6c, #b21f1f, #fdbb2d);
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            padding: 20px;
        }

        .menu-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
            position: relative;
        }

        .menu-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .menu-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5em;
            color: #1a2a6c;
            margin: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #fdbb2d;
        }

        .menu-list {
            padding: 0 20px 20px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            transition: all 0.3s ease;
        }

        .menu-item:hover {
            transform: translateX(10px);
        }

        .menu-item img {
            width: 30px;
            height: 30px;
            margin-right: 15px;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .search-section, .pnr-check-section, .order-section {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            padding: 40px 20px;
            text-align: center;
            border-radius: 20px;
            margin: 40px auto;
            max-width: 800px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .search-section input, .pnr-check-section input, .order-section input, .order-section select {
            padding: 15px 25px;
            border: 2px solid #e0e0e0;
            border-radius: 30px;
            font-size: 16px;
            width: 300px;
            margin: 10px;
            transition: all 0.3s ease;
        }

        .search-section input:focus, .pnr-check-section input:focus, .order-section input:focus, .order-section select:focus {
            outline: none;
            border-color: #1a2a6c;
            box-shadow: 0 0 10px rgba(26,42,108,0.1);
        }

        .search-section button, .pnr-check-section button, .order-section button {
            background: linear-gradient(135deg, #1a2a6c 0%, #b21f1f 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 30px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(26,42,108,0.2);
        }

        .search-section button:hover, .pnr-check-section button:hover, .order-section button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26,42,108,0.3);
        }

        footer {
            background: #1a2a6c;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
        }

        .message {
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
            color: #3c763d;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .error-message {
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            color: #a94442;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        th, td {
            text-align: left;
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #1a2a6c;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .booking-details {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: left;
        }

        .booking-details h3 {
            color: #1a2a6c;
            margin-bottom: 15px;
            border-bottom: 2px solid #fdbb2d;
            padding-bottom: 10px;
        }

        .food-items {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .food-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .food-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        .food-item img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .food-item h4 {
            color: #1a2a6c;
            margin: 10px 0;
        }

        .food-item p {
            color: #b21f1f;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }

            nav {
                margin-top: 20px;
            }

            .menu-title {
                font-size: 2em;
            }

            .search-section input, .pnr-check-section input, .order-section input, .order-section select {
                width: calc(100% - 70px);
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo-section">
                <img src="images/train_logo.png" alt="TrainDekho Logo" class="logo">
                <h1 style="color: white; margin: 0;">TrainDekho E-Catering</h1>
            </div>
            <nav>
                <a href="rose.php">Home</a>
                <a href="contactus.php">Contact Us</a>
                <a href="help.php">Help</a>
            </nav>
        </div>
    </header>

    <section class="pnr-check-section">
        <h2 style="color: #1a2a6c; font-family: 'Playfair Display', serif;">Check Your Food Orders</h2>
        
        <?php if (!empty($message)): ?>
            <div class="<?php echo strpos($message, 'Error') !== false ? 'error-message' : 'message'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="text" name="pnr_number" placeholder="Enter your PNR Number" required>
            <button type="submit" name="check_pnr">Check Orders</button>
        </form>
    </section>

    <?php if (!empty($bookingDetails)): ?>
    <section class="menu-section">
        <h2 class="menu-title">Your Booking Details</h2>
        <div class="booking-details">
            <h3>PNR: <?php echo $bookingDetails['pnr_number']; ?></h3>
            <p><strong>From:</strong> <?php echo isset($bookingDetails['source_station']) ? $bookingDetails['source_station'] : 'Not specified'; ?></p>
            <p><strong>To:</strong> <?php echo isset($bookingDetails['destination_station']) ? $bookingDetails['destination_station'] : 'Not specified'; ?></p>
            <p><strong>Travel Date:</strong> <?php echo isset($bookingDetails['date']) ? $bookingDetails['date'] : 'Not specified'; ?></p>
            <p><strong>Train:</strong> <?php echo isset($bookingDetails['train_name']) ? $bookingDetails['train_name'] : 'Not specified'; ?></p>
        </div>
        
        <?php if (!empty($existingOrders)): ?>
            <h3>Your Existing Food Orders</h3>
            <table>
                <thead>
                    <tr>
                        <th>Food Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Order Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($existingOrders as $order): ?>
                    <tr>
                        <td><?php echo $order['food_item']; ?></td>
                        <td><?php echo $order['quantity']; ?></td>
                        <td>₹<?php echo $order['price']; ?></td>
                        <td>₹<?php echo $order['total_price']; ?></td>
                        <td><?php echo $order['order_date']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center;">No food orders found for this PNR. Add some delicious food to your journey!</p>
        <?php endif; ?>
    </section>

    <section class="order-section">
        <h2 style="color: #1a2a6c; font-family: 'Playfair Display', serif;">Add Food to Your Journey</h2>
        <form method="POST" action="">
            <select name="food_item" required>
                <option value="">Select Food Item</option>
                <option value="Biryani">Biryani - ₹250</option>
                <option value="Veg Thali">Veg Thali - ₹180</option>
                <option value="Non-Veg Thali">Non-Veg Thali - ₹220</option>
                <option value="Fruit Plate">Fruit Plate - ₹120</option>
                <option value="Veg Meal">Veg Meal - ₹150</option>
                <option value="Burger">Burger - ₹90</option>
                <option value="Pizza">Pizza - ₹200</option>
            </select>
            <input type="number" name="quantity" min="1" max="10" value="1" required>
            <button type="submit" name="add_food">Add to Order</button>
        </form>

        <div class="food-items">
            <div class="food-item">
                <img src="images/biryani.jpg" alt="Biryani">
                <h4>Biryani</h4>
                <p>₹250</p>
            </div>
            <div class="food-item">
                <img src="images/veg_thali.jpg" alt="Veg Thali">
                <h4>Veg Thali</h4>
                <p>₹180</p>
            </div>
            <div class="food-item">
                <img src="images/non_veg_thali.jpg" alt="Non-Veg Thali">
                <h4>Non-Veg Thali</h4>
                <p>₹220</p>
            </div>
            <div class="food-item">
                <img src="images/fruit_plate.jpg" alt="Fruit Plate">
                <h4>Fruit Plate</h4>
                <p>₹120</p>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="menu-section">
        <h2 class="menu-title">Our Special Menu Categories</h2>
        <div class="menu-grid">
            <div class="menu-card">
                <h3>Premium Express Menu</h3>
                <div class="menu-list">
                    <div class="menu-item">
                        <img src="images/beverages.png" alt="Beverages">
                        <span>Gourmet Beverages</span>
                    </div>
                    <div class="menu-item">
                        <img src="images/breakfast.png" alt="Breakfast">
                        <span>Continental Breakfast</span>
                    </div>
                    <div class="menu-item">
                        <img src="images/meal.png" alt="Meal">
                        <span>Signature Meals</span>
                    </div>
                </div>
            </div>

            <div class="menu-card">
                <h3>Luxury Class Dining</h3>
                <div class="menu-list">
                    <div class="menu-item">
                        <img src="images/1ac.png" alt="1AC">
                        <span>First Class A/C Special</span>
                    </div>
                    <div class="menu-item">
                        <img src="images/2ac.png" alt="2AC">
                        <span>Executive Class Dining</span>
                    </div>
                </div>
            </div>

            <div class="menu-card">
                <h3>Signature Specials</h3>
                <div class="menu-list">
                    <div class="menu-item">
                        <img src="images/vande.png" alt="Vande Bharat">
                        <span>Vande Bharat Exclusive</span>
                    </div>
                    <div class="menu-item">
                        <img src="images/tejas.png" alt="Tejas">
                        <span>Tejas Gourmet Selection</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; 2025 TrainDekho. All Rights Reserved.</p>
    </footer>
</body>
</html>
<?php $conn->close(); ?>
