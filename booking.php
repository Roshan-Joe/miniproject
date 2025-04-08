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

// Get train details
if (isset($_GET['train_id'])) {
    $train_id = $_GET['train_id'];
    $sql = "SELECT * FROM trains WHERE train_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $train_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $train = $result->fetch_assoc();
} else {
    header("Location: rose.php");
    exit();
}

// Add this after the database connection
$sql = "SELECT ac_fare, sleeper_fare FROM trains WHERE train_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $train_id);
$stmt->execute();
$result = $stmt->get_result();
$fares = $result->fetch_assoc();

// Update the food items array with descriptions and image paths
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Ticket - TrainDekho</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f1f1f1;
            color: #333;
        }

        .header {
            background: #213d77;
            padding: 10px 20px;
            color: white;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            padding: 0 15px;
        }

        .main-content {
            background: white;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .fare-summary {
            background: white;
            border-radius: 4px;
            padding: 15px;
            height: fit-content;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .train-header {
            background: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }

        .section {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }

        .section-title {
            font-size: 16px;
            font-weight: 500;
            color: #213d77;
            margin-bottom: 15px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            color: #666;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .fare-title {
            font-size: 16px;
            font-weight: 500;
            color: #213d77;
            margin-bottom: 15px;
        }

        .fare-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }

        .total-row {
            border-top: 1px solid #ddd;
            margin-top: 10px;
            padding-top: 10px;
            font-weight: 500;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-back {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }

        .btn-continue {
            background: #f47721;
            color: white;
        }

        /* Food Order Section Styles */
        .food-section {
            padding: 20px;
        }

        .food-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .food-item {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
        }

        .food-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 4px;
        }

        .food-details {
            margin-top: 10px;
        }

        .food-name {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .food-price {
            color: #213d77;
            font-weight: 500;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        .qty-btn {
            padding: 5px 10px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
        }

        /* Alert styles */
        .alert {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 13px;
        }

        /* Add these logo-specific styles to your existing styles */
        .logo-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo {
            width: 40px;  /* Reduced size */
            height: 40px; /* Maintain aspect ratio */
            object-fit: contain;
        }

        .brand-name {
            font-size: 18px;
            font-weight: bold;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .age-message {
            font-size: 12px;
            color: #856404;
            margin-top: 5px;
            display: block;
        }
        
        .passenger-row {
            padding: 15px;
            border: 1px solid #eee;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .category-display {
            font-size: 13px;
            color: #213d77;
            margin-top: 5px;
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
            <div class="header-right">
                <div class="date"><?php echo date('l, d M Y'); ?></div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="main-content">
            <div class="alert">
                IRCTC User Information: All classes are available for this Train/Class/Date. Person With Disability Journey/Bed Rolls may check after selecting seats.
            </div>

            <div class="train-header">
                <h3><?php echo htmlspecialchars($train['train_name']); ?> (<?php echo htmlspecialchars($train['train_number']); ?>)</h3>
                <div><?php echo htmlspecialchars($train['source_station']); ?> → <?php echo htmlspecialchars($train['destination_station']); ?></div>
                <div>Departure: <?php echo date('H:i', strtotime($train['departure_time'])); ?></div>
            </div>

            <form action="review_journey.php" method="POST" id="bookingForm">
                <div class="section">
                    <div class="section-title">Booking Details</div>
                    <div class="form-group">
                        <label>Number of Passengers (Max 6)</label>
                        <input type="number" name="passenger_count" id="passenger_count" min="1" max="6" required value="1">
                    </div>
                    <div class="form-group">
                        <label>Class</label>
                        <select name="class" id="class_select" required onchange="updateFare()">
                            <option value="">Select Class</option>
                            <option value="AC">AC Class (₹<?php echo $fares['ac_fare']; ?>)</option>
                            <option value="SL">Sleeper Class (₹<?php echo $fares['sleeper_fare']; ?>)</option>
                        </select>
                    </div>
                </div>

                <div class="section">
                    <div class="section-title">Passenger Details</div>
                    <div id="passenger_details">
                        <div class="form-grid passenger-row">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="passenger_name[]" required>
                            </div>
                            <div class="form-group">
                                <label>Age</label>
                                <input type="number" name="age[]" class="age-input" required min="1" max="120" onchange="validateAge(this)">
                                <span class="age-message"></span>
                            </div>
                            <div class="form-group">
                                <label>Gender</label>
                                <select name="gender[]" required>
                                    <option value="">Select</option>
                                    <option value="M">Male</option>
                                    <option value="F">Female</option>
                                    <option value="O">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <input type="text" name="category[]" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="train_id" value="<?php echo htmlspecialchars($train_id); ?>">
                <input type="hidden" name="journey_date" value="<?php echo htmlspecialchars($journey_date); ?>">

                <div class="section food-section">
                    <div class="section-title">Food Order (Optional)</div>
                    <div class="food-grid">
                        <?php foreach($food_items as $item): ?>
                        <div class="food-item">
                            <img src="images/food/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="food-details">
                                <div class="food-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="food-price">₹<?php echo htmlspecialchars($item['price']); ?></div>
                                <div class="quantity-control">
                                    <button type="button" class="qty-btn" onclick="updateQuantity(this, -1)">-</button>
                                    <input type="number" name="food[<?php echo $item['id']; ?>]" 
                                           class="quantity-input" value="0" min="0" max="5">
                                    <button type="button" class="qty-btn" onclick="updateQuantity(this, 1)">+</button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="section">
                    <div class="button-group">
                        <button type="button" class="btn btn-back" onclick="history.back()">Back</button>
                        <button type="submit" class="btn btn-continue">Continue</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="fare-summary">
            <div class="fare-title">Fare Summary</div>
            <div class="fare-row">
                <span>Base Fare</span>
                <span id="baseFare">₹0</span>
            </div>
            <div class="fare-row">
                <span>Food Charges</span>
                <span id="foodCharges">₹0</span>
            </div>
            <div class="fare-row">
                <span>Service Charge</span>
                <span>₹20</span>
            </div>
            <div class="fare-row total-row">
                <span>Total Amount</span>
                <span id="totalFare">₹20</span>
            </div>
        </div>
    </div>

    <script>
        // Store fare values from PHP
        const fares = {
            AC: <?php echo $fares['ac_fare']; ?>,
            SL: <?php echo $fares['sleeper_fare']; ?>
        };

        // Passenger category validation
        function validateAge(input) {
            const age = parseInt(input.value);
            const messageSpan = input.nextElementSibling;
            const categoryInput = input.parentElement.parentElement.querySelector('input[name="category[]"]');
            
            if (age < 0 || age > 120) {
                messageSpan.textContent = "Please enter a valid age between 0 and 120";
                input.value = "";
                categoryInput.value = "";
                return;
            }
            
            if (age < 5) {
                messageSpan.textContent = "Free ticket for children under 5";
                categoryInput.value = "Free Ticket";
            } else if (age >= 5 && age <= 12) {
                messageSpan.textContent = "Child fare applicable";
                categoryInput.value = "Child";
            } else if (age >= 60) {
                messageSpan.textContent = "Senior citizen fare applicable";
                categoryInput.value = "Senior Citizen";
            } else {
                messageSpan.textContent = "";
                categoryInput.value = "Adult";
            }
            
            updateFare();
        }

        // Update passenger rows when count changes
        document.getElementById('passenger_count').addEventListener('change', function() {
            const count = parseInt(this.value);
            const container = document.getElementById('passenger_details');
            const template = container.querySelector('.passenger-row').cloneNode(true);
            
            // Clear existing rows
            container.innerHTML = '';
            
            // Add new rows based on passenger count
            for (let i = 0; i < count; i++) {
                const newRow = template.cloneNode(true);
                // Clear input values
                newRow.querySelectorAll('input').forEach(input => {
                    input.value = '';
                    if (input.classList.contains('age-input')) {
                        input.onchange = function() { validateAge(this); };
                    }
                });
                newRow.querySelectorAll('select').forEach(select => select.value = '');
                container.appendChild(newRow);
            }
            
            updateFare();
        });

        // Update total fare calculation
        function updateFare() {
            const classSelect = document.getElementById('class_select');
            const selectedClass = classSelect.value;
            let totalFare = 0;
            
            if (selectedClass) {
                const baseFare = fares[selectedClass];
                
                // Calculate fare for each passenger based on category
                document.querySelectorAll('.passenger-row').forEach(row => {
                    const category = row.querySelector('input[name="category[]"]').value;
                    const age = parseInt(row.querySelector('.age-input').value) || 0;
                    
                    if (age < 5) {
                        // Free ticket
                        return;
                    } else if (category === 'Child') {
                        totalFare += baseFare * 0.5; // 50% of base fare for children
                    } else if (category === 'Senior Citizen') {
                        totalFare += baseFare * 0.7; // 30% discount for senior citizens
                    } else if (category === 'Adult') {
                        totalFare += baseFare; // Full fare for adults
                    }
                });
            }
            
            document.getElementById('baseFare').textContent = '₹' + totalFare.toFixed(2);
            updateTotal();
        }

        // Update total amount including food charges
        function updateTotal() {
            const baseFare = parseFloat(document.getElementById('baseFare').textContent.replace('₹', '')) || 0;
            let foodTotal = 0;
            
            // Calculate food charges
            const foodPrices = <?php echo json_encode(array_column($food_items, 'price', 'id')); ?>;
            document.querySelectorAll('.quantity-input').forEach(input => {
                const foodId = input.name.match(/\d+/)[0];
                const quantity = parseInt(input.value) || 0;
                if (foodPrices[foodId]) {
                    foodTotal += quantity * foodPrices[foodId];
                }
            });

            document.getElementById('foodCharges').textContent = '₹' + foodTotal.toFixed(2);
            const totalAmount = baseFare + foodTotal + 20; // Including service charge
            document.getElementById('totalFare').textContent = '₹' + totalAmount.toFixed(2);
            
            // Update hidden input for total amount
            const totalAmountInput = document.createElement('input');
            totalAmountInput.type = 'hidden';
            totalAmountInput.name = 'total_amount';
            totalAmountInput.value = totalAmount;
            document.getElementById('bookingForm').appendChild(totalAmountInput);
        }

        // Food quantity update function
        function updateQuantity(btn, change) {
            const input = btn.parentElement.querySelector('.quantity-input');
            let value = parseInt(input.value) || 0;
            value = Math.max(0, Math.min(5, value + change)); // Limit between 0 and 5
            input.value = value;
            updateTotal();
        }

        // Form validation before submission
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Check if class is selected
            if (!document.getElementById('class_select').value) {
                alert('Please select a class');
                return;
            }

            // Validate passenger details
            const adults = Array.from(document.querySelectorAll('.age-input')).filter(input => 
                parseInt(input.value) >= 18
            ).length;
            
            const minors = Array.from(document.querySelectorAll('.age-input')).filter(input => 
                parseInt(input.value) < 18 && parseInt(input.value) >= 5
            ).length;
            
            if (minors > 0 && adults === 0) {
                alert('At least one adult passenger is required when booking tickets for minors.');
                return;
            }

            // If all validations pass, submit the form
            this.submit();
        });

        // Initialize the form
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial passenger row
            const passengerCount = document.getElementById('passenger_count');
            passengerCount.dispatchEvent(new Event('change'));
            
            // Add change event listeners to existing age inputs
            document.querySelectorAll('.age-input').forEach(input => {
                input.onchange = function() { validateAge(this); };
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?> 