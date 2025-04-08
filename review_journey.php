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

// Fetch user details from database
$user_id = $_SESSION['user_id']; // Assuming you have user_id in session

// First, check if mobile column exists
$check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'mobile'");
if($check_column->num_rows == 0) {
    // If mobile column doesn't exist, only select email
    $sql = "SELECT email FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $user['mobile'] = 'Not Available'; // Set default value
} else {
    // If mobile column exists, select both email and mobile
    $sql = "SELECT email, mobile FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}

// Get train and booking details
$train_id = $_POST['train_id'];
$passenger_names = $_POST['passenger_name'];
$ages = $_POST['age'];
$genders = $_POST['gender'];
$categories = $_POST['category'];
$selected_class = $_POST['class'];

// Fetch train details
$sql = "SELECT * FROM trains WHERE train_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $train_id);
$stmt->execute();
$result = $stmt->get_result();
$train = $result->fetch_assoc();

// Calculate total passengers
$total_passengers = count($passenger_names);

// Add this after getting train details and before the HTML
// Define food items array
$food_items = [
    [
        'id' => 1, 
        'name' => 'Veg Thali', 
        'description' => 'Complete Indian meal with dal, curry, naan bread, rice, and fresh salad',
        'price' => 120, 
        'type' => 'veg',
        'image' => 'veg thali.webp'
    ],
    [
        'id' => 2, 
        'name' => 'Non-Veg Thali', 
        'description' => 'Delicious non-vegetarian thali with chicken curry, dal, rice, and naan',
        'price' => 150, 
        'type' => 'non-veg',
        'image' => 'non veg thali.webp'
    ],
    [
        'id' => 3, 
        'name' => 'Sandwich', 
        'description' => 'Fresh vegetable sandwich with cheese and special sauce',
        'price' => 60, 
        'type' => 'veg',
        'image' => 'sandwich.webp'
    ],
    [
        'id' => 4, 
        'name' => 'Biryani', 
        'description' => 'Aromatic rice dish with spices and tender meat',
        'price' => 130, 
        'type' => 'non-veg',
        'image' => 'Biriyani.webp'
    ],
    [
        'id' => 5, 
        'name' => 'Fruit Plate', 
        'description' => 'Assorted fresh seasonal fruits',
        'price' => 80, 
        'type' => 'veg',
        'image' => 'Fruit Plate.webp'
    ]
];

// Calculate base fare based on class selection and passenger categories
$base_fare = 0;
if ($selected_class == 'AC') {
    $base_fare = $train['ac_fare'];
} else {
    $base_fare = $train['sleeper_fare'];
}

// Calculate total base fare considering passenger categories
$total_base_fare = 0;
for($i = 0; $i < $total_passengers; $i++) {
    if($categories[$i] == 'Free Ticket') {
        continue; // Skip fare for children under 5
    } else if($categories[$i] == 'Child') {
        $total_base_fare += $base_fare * 0.5; // 50% fare for children
    } else if($categories[$i] == 'Senior Citizen') {
        $total_base_fare += $base_fare * 0.7; // 30% discount for senior citizens
    } else {
        $total_base_fare += $base_fare; // Full fare for adults
    }
}

// Update the food order section to use the correct variable
if(!empty($_POST['food'])) {
    $total_food_cost = 0;
    foreach($_POST['food'] as $food_id => $quantity) {
        if($quantity > 0) {
            foreach($food_items as $item) {
                if($item['id'] == $food_id) {
                    $total_food_cost += $item['price'] * $quantity;
                    break;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Booking - TrainDekho</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Add these new styles */
        .ticket-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 20px auto;
            max-width: 800px;
            position: relative;
            overflow: hidden;
        }

        .ticket-header {
            background: linear-gradient(135deg, #213d77 0%, #1e4f9e 100%);
            color: white;
            padding: 20px;
            position: relative;
        }

        .ticket-header::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            right: 0;
            height: 20px;
            background: white;
            border-radius: 50% 50% 0 0;
        }

        .train-details {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 20px;
            margin: 15px 0;
        }

        .station-info {
            text-align: center;
        }

        .station-name {
            font-size: 1.2em;
            font-weight: 500;
        }

        .journey-line {
            height: 2px;
            background: rgba(255,255,255,0.5);
            position: relative;
        }

        .journey-line::before,
        .journey-line::after {
            content: '•';
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-size: 24px;
        }

        .journey-line::before { left: -5px; }
        .journey-line::after { right: -5px; }

        .passenger-list {
            padding: 20px;
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

        .contact-info {
            background: #fff3cd;
            border-radius: 6px;
            padding: 15px;
            margin: 20px;
        }

        .food-order {
            padding: 20px;
            border-top: 1px dashed #ddd;
        }

        .food-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .price-summary {
            background: #f8f9fa;
            padding: 20px;
            border-top: 1px solid #ddd;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }

        .total-price {
            font-size: 1.2em;
            font-weight: 500;
            color: #213d77;
            border-top: 2px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            padding: 20px;
            justify-content: flex-end;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
        }

        .btn-primary {
            background: #f47721;
            color: white;
            border: none;
        }

        .btn-secondary {
            background: white;
            border: 1px solid #ddd;
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

        .header {
            background: #213d77;
            padding: 15px 0;
            color: white;
            margin-bottom: 20px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 800px;
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

    <div class="ticket-container">
        <div class="ticket-header">
            <h2>Booking Review</h2>
            <div class="train-details">
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
            <div>Class: <?php echo htmlspecialchars($selected_class); ?></div>
        </div>

        <div class="passenger-list">
            <h3>Passenger Details</h3>
            <?php for($i = 0; $i < $total_passengers; $i++): ?>
                <div class="passenger-card">
                    <div class="passenger-number"><?php echo $i + 1; ?></div>
                    <div>
                        <strong><?php echo htmlspecialchars($passenger_names[$i]); ?></strong>
                        <div><?php echo htmlspecialchars($genders[$i]); ?> | <?php echo htmlspecialchars($ages[$i]); ?> Years</div>
                    </div>
                    <div class="category-badge category-<?php echo strtolower(str_replace(' ', '-', $categories[$i])); ?>">
                        <?php echo htmlspecialchars($categories[$i]); ?>
                    </div>
                </div>
            <?php endfor; ?>
                </div>

        <div class="contact-info">
            <h4>Contact Information</h4>
            <div>Email: <?php echo htmlspecialchars($user['email']); ?></div>
            <div>Phone: <?php echo htmlspecialchars($user['mobile']); ?></div>
                    </div>

        <?php if(!empty($_POST['food'])): ?>
        <div class="food-order">
            <h3>Food Order Details</h3>
            <?php 
            $total_food_cost = 0;
            foreach($_POST['food'] as $food_id => $quantity):
                if($quantity > 0):
                    $food_item = array_filter($food_items, function($item) use ($food_id) {
                        return $item['id'] == $food_id;
                    });
                    $food_item = reset($food_item);
                    $item_total = $food_item['price'] * $quantity;
                    $total_food_cost += $item_total;
            ?>
                <div class="food-item">
                    <div>
                        <?php echo htmlspecialchars($food_item['name']); ?> × <?php echo $quantity; ?>
                    </div>
                    <div>₹<?php echo $item_total; ?></div>
                </div>
            <?php endif; endforeach; ?>
                            </div>
                        <?php endif; ?>

        <div class="price-summary">
            <div class="price-row">
                <span>Base Fare (<?php echo $total_passengers; ?> passengers)</span>
                <span>₹<?php echo $total_base_fare; ?></span>
                    </div>
            <?php if(isset($total_food_cost) && $total_food_cost > 0): ?>
            <div class="price-row">
                <span>Food Charges</span>
                <span>₹<?php echo $total_food_cost; ?></span>
                </div>
                <?php endif; ?>
            <div class="price-row">
                <span>Service Charge</span>
                <span>₹20</span>
            </div>
            <div class="price-row total-price">
                <span>Total Amount</span>
                <span>₹<?php echo $total_base_fare + ($total_food_cost ?? 0) + 20; ?></span>
            </div>
            </div>

        <div class="action-buttons">
            <button type="button" class="btn btn-secondary" onclick="history.back()">Back</button>
            <form action="payment.php" method="POST" style="display: inline;">
                <input type="hidden" name="train_id" value="<?php echo htmlspecialchars($train_id); ?>">
                <input type="hidden" name="total_amount" value="<?php echo $total_base_fare + ($total_food_cost ?? 0) + 20; ?>">
                <?php foreach($passenger_names as $index => $name): ?>
                    <input type="hidden" name="passenger_name[]" value="<?php echo htmlspecialchars($name); ?>">
                    <input type="hidden" name="passenger_age[]" value="<?php echo htmlspecialchars($ages[$index]); ?>">
                    <input type="hidden" name="passenger_gender[]" value="<?php echo htmlspecialchars($genders[$index]); ?>">
                    <input type="hidden" name="passenger_category[]" value="<?php echo htmlspecialchars($categories[$index]); ?>">
                <?php endforeach; ?>
                <?php if(!empty($_POST['food'])): ?>
                    <?php foreach($_POST['food'] as $food_id => $quantity): ?>
                        <input type="hidden" name="food[<?php echo $food_id; ?>]" value="<?php echo $quantity; ?>">
                    <?php endforeach; ?>
                <?php endif; ?>
                <input type="hidden" name="selected_class" value="<?php echo htmlspecialchars($selected_class); ?>">
                <button type="submit" class="btn btn-primary">Proceed to Payment</button>
            </form>
        </div>
    </div>
</body>
</html> 

<?php $conn->close(); ?>