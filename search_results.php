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

// Get search parameters
$from = isset($_GET['from']) ? trim($_GET['from']) : '';
$to = isset($_GET['to']) ? trim($_GET['to']) : '';

// Debug message to see what values are being searched
$debug_message = "Searching for trains from '$from' to '$to'";

// Simple search query for matching source and destination
$sql = "SELECT * FROM trains 
        WHERE source_station = ? 
        AND destination_station = ? 
        ORDER BY departure_time";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $from, $to);
$stmt->execute();
$result = $stmt->get_result();

// If no exact matches found, try partial matches
if ($result->num_rows === 0) {
    $sql = "SELECT * FROM trains 
            WHERE (source_station LIKE ? OR source_station = ?)
            AND (destination_station LIKE ? OR destination_station = ?)
            ORDER BY departure_time";
    
    $from_like = "%$from%";
    $to_like = "%$to%";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $from_like, $from, $to_like, $to);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Get all available trains for debugging if needed
$all_trains_sql = "SELECT DISTINCT source_station, destination_station FROM trains LIMIT 10";
$all_trains_result = $conn->query($all_trains_sql);
$available_routes = [];
if ($all_trains_result->num_rows > 0) {
    while($route = $all_trains_result->fetch_assoc()) {
        $available_routes[] = $route['source_station'] . " → " . $route['destination_station'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - TrainDekho</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f1f1f1;
            color: #333;
        }

        .header {
            background: #213d77;
            color: white;
            padding: 10px 20px;
        }

        .search-summary {
            background: #213d77;
            color: white;
            padding: 15px 20px;
            margin: 0;
            border-radius: 0;
        }

        .route {
            font-size: 16px;
        }

        .train-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: none;
        }

        .train-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .train-name {
            color: #213d77;
            font-size: 16px;
            font-weight: bold;
        }

        .train-number {
            color: #666;
            font-size: 13px;
        }

        .running-days {
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 4px;
            color: #666;
            font-size: 12px;
        }

        .train-details {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            gap: 15px;
        }

        .detail-group {
            flex: 1;
            border-right: 1px solid #eee;
            padding: 0 15px;
        }

        .detail-label {
            color: #666;
            font-size: 12px;
        }

        .detail-value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        .fare-section {
            display: flex;
            background: white;
            padding: 10px;
            gap: 20px;
            align-items: center;
        }

        .fare-group {
            flex: 1;
            background: #f8f9fa;
            padding: 8px;
            border-radius: 4px;
            text-align: left;
        }

        .fare-label {
            font-size: 12px;
            color: #666;
        }

        .fare-value {
            font-size: 14px;
            font-weight: bold;
            color: #213d77;
        }

        .book-btn {
            background: #f47721;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .book-btn:hover {
            background: #e06612;
            transform: none;
            box-shadow: none;
        }

        /* Add sorting controls */
        .sort-controls {
            background: white;
            padding: 10px 20px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }

        .sort-btn {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 5px 15px;
            margin-right: 10px;
            cursor: pointer;
            font-size: 13px;
        }

        .sort-btn.active {
            background: #213d77;
            color: white;
            border-color: #213d77;
        }

        /* Add/modify these logo-related styles */
        .logo-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo {
            width: 40px;  /* Reduced from original size */
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
        }

        /* Add styles for debug info */
        .debug-info {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            margin: 10px 0;
            font-family: monospace;
            white-space: pre-wrap;
        }
        
        .back-btn {
            display: inline-block;
            background: #213d77;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        
        .no-trains {
            background: white;
            border: 1px solid #ddd;
            padding: 20px;
            margin: 20px;
            text-align: center;
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
            <div class="search-date">
                <?php echo date('l, d M Y'); ?>
            </div>
        </div>
    </div>

    <div class="search-summary">
        <div class="route">
            <span class="source"><?php echo htmlspecialchars($from); ?></span>
            <span class="route-arrow">→</span>
            <span class="destination"><?php echo htmlspecialchars($to); ?></span>
        </div>
    </div>

    <!-- Add sort controls after search-summary -->
    <div class="sort-controls">
        <button class="sort-btn active">Sort By | Duration</button>
        <button class="sort-btn">Show Available Trains</button>
    </div>

    <div class="results-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while($train = $result->fetch_assoc()): ?>
                <div class="train-card">
                    <div class="train-header">
                        <div>
                            <div class="train-name"><?php echo htmlspecialchars($train['train_name']); ?> (<?php echo htmlspecialchars($train['train_number']); ?>)</div>
                            <div class="running-days">Runs On: <?php 
                                $days = str_split($train['running_days']);
                                echo implode(" ", $days); 
                            ?></div>
                        </div>
                        <a href="#" class="train-schedule-link">Train Schedule</a>
                    </div>

                    <div class="train-details">
                        <div class="detail-group">
                            <div class="detail-value"><?php echo date('H:i', strtotime($train['departure_time'])); ?></div>
                            <div class="detail-label"><?php echo htmlspecialchars($train['source_station']); ?></div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-value"><?php echo htmlspecialchars($train['duration']); ?></div>
                            <div class="detail-label">Duration</div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-value"><?php echo date('H:i', strtotime($train['arrival_time'])); ?></div>
                            <div class="detail-label"><?php echo htmlspecialchars($train['destination_station']); ?></div>
                        </div>
                    </div>

                    <div class="fare-section">
                        <div class="fare-group">
                            <div class="fare-label">AC Class</div>
                            <div class="fare-value">₹<?php echo htmlspecialchars($train['ac_fare']); ?></div>
                            <small>Available: <?php echo htmlspecialchars($train['ac_seats']); ?> seats</small>
                        </div>
                        <div class="fare-group">
                            <div class="fare-label">Sleeper Class</div>
                            <div class="fare-value">₹<?php echo htmlspecialchars($train['sleeper_fare']); ?></div>
                            <small>Available: <?php echo htmlspecialchars($train['sleeper_seats']); ?> seats</small>
                        </div>
                        <form action="booking.php" method="GET">
                            <input type="hidden" name="train_id" value="<?php echo $train['train_id']; ?>">
                            <button type="submit" class="book-btn">Book Now</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-trains">
                <h2>No Trains Available</h2>
                <p>Sorry, we couldn't find any trains between <?php echo htmlspecialchars($from); ?> and <?php echo htmlspecialchars($to); ?>.</p>
                <p>Try searching for a different route or date.</p>
                
                <!-- Debug information section -->
                <div class="debug-info">
                    <h3>Debug Information</h3>
                    <p><?php echo $debug_message; ?></p>
                    <h4>Sample Available Routes in Database:</h4>
                    <ul>
                        <?php foreach($available_routes as $route): ?>
                            <li><?php echo htmlspecialchars($route); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <a href="rose.php" class="back-btn">Back to Search</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Add functionality to sort buttons if needed
        document.querySelectorAll('.sort-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('.sort-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                this.classList.add('active');
                // Add sorting logic here
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?> 