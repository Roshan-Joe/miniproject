<?php
// Database connection
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password
$dbname = "collegep";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get search parameters
$from = isset($_GET['from']) ? $_GET['from'] : '';
$to = isset($_GET['to']) ? $_GET['to'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';
$class = isset($_GET['class']) ? $_GET['class'] : 'ALL';

// For debugging - check if parameters are received correctly
// echo "From: $from, To: $to, Date: $date, Class: $class<br>";

// Get day of week (1-7) from date
$day_of_week = date('N', strtotime($date));
$day_name = date('l', strtotime($date)); // Get day name for display

// Debug check for the database
$check_query = "SHOW TABLES LIKE 'trains'";
$check_result = $conn->query($check_query);

if ($check_result->num_rows == 0) {
    die("The 'trains' table does not exist in the database.");
}

// Check the structure of the trains table
$structure_query = "DESCRIBE trains";
$structure_result = $conn->query($structure_query);

$columns = array();
if ($structure_result) {
    while ($row = $structure_result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
}

// Simplified query first - check if any trains exist at all
$simple_query = "SELECT COUNT(*) as count FROM trains";
$simple_result = $conn->query($simple_query);
$train_count = $simple_result->fetch_assoc()['count'];

// If no trains in the table, show error
if ($train_count == 0) {
    $trains = array();
    $error_message = "No trains found in the database. Please add train data first.";
} else {
    // Map day number to running_days column position
    switch ($day_of_week) {
        case 1: $day_column = "SUBSTRING(running_days, 1, 1)"; break; // Monday
        case 2: $day_column = "SUBSTRING(running_days, 2, 1)"; break; // Tuesday
        case 3: $day_column = "SUBSTRING(running_days, 3, 1)"; break; // Wednesday
        case 4: $day_column = "SUBSTRING(running_days, 4, 1)"; break; // Thursday
        case 5: $day_column = "SUBSTRING(running_days, 5, 1)"; break; // Friday
        case 6: $day_column = "SUBSTRING(running_days, 6, 1)"; break; // Saturday
        case 7: $day_column = "SUBSTRING(running_days, 7, 1)"; break; // Sunday
    }

    // Check if the running_days column exists
    if (!in_array('running_days', $columns)) {
        // If running_days doesn't exist, use a simpler query without that condition
        $sql = "SELECT * FROM trains 
                WHERE source_station = ? 
                AND destination_station = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $from, $to);
    } else {
        // Use the original query with running_days check
        $sql = "SELECT * FROM trains 
                WHERE source_station = ? 
                AND destination_station = ? 
                AND $day_column = 'Y'";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $from, $to);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $trains = $result->fetch_all(MYSQLI_ASSOC);
    
    // If no trains found with the day check, try without it
    if (count($trains) == 0 && in_array('running_days', $columns)) {
        $sql = "SELECT * FROM trains 
                WHERE source_station = ? 
                AND destination_station = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $from, $to);
        $stmt->execute();
        $result = $stmt->get_result();
        $trains = $result->fetch_all(MYSQLI_ASSOC);
        
        if (count($trains) > 0) {
            $note = "Note: Some trains shown may not run on $day_name. Please check the running days.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Train Search Results - TrainDekho</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        
        .header {
            background-color: #003366;
            color: white;
            padding: 10px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .search-details {
            background-color: #003366;
            color: white;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .journey-info {
            display: flex;
            align-items: center;
        }
        
        .journey-info h2 {
            margin: 0;
            font-size: 18px;
        }
        
        .journey-info .arrow {
            margin: 0 10px;
            font-size: 20px;
        }
        
        .journey-date {
            margin-left: 20px;
            font-size: 14px;
        }
        
        .modify-search {
            background-color: #ff6600;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .train-list {
            background-color: white;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .train-card {
            border-bottom: 1px solid #e0e0e0;
            padding: 15px;
        }
        
        .train-card:last-child {
            border-bottom: none;
        }
        
        .train-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .train-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        .train-number {
            color: #666;
            font-size: 14px;
        }
        
        .running-days {
            display: flex;
            gap: 5px;
            margin-top: 5px;
        }
        
        .day {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            border-radius: 50%;
            background-color: #f0f0f0;
        }
        
        .day.running {
            background-color: #28a745;
            color: white;
        }
        
        .train-schedule {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .departure, .arrival {
            text-align: center;
        }
        
        .time {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        
        .station {
            font-size: 14px;
            color: #666;
        }
        
        .duration {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .duration-time {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .duration-line {
            width: 100px;
            height: 2px;
            background-color: #e0e0e0;
            position: relative;
        }
        
        .duration-line::before,
        .duration-line::after {
            content: '';
            position: absolute;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: #e0e0e0;
            top: -2px;
        }
        
        .duration-line::before {
            left: 0;
        }
        
        .duration-line::after {
            right: 0;
        }
        
        .class-availability {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .class-item {
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .class-item:hover {
            border-color: #003366;
        }
        
        .class-item.selected {
            border-color: #003366;
            background-color: #f0f5ff;
        }
        
        .class-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .fare {
            color: #28a745;
            font-weight: bold;
        }
        
        .availability {
            color: #666;
            font-size: 12px;
        }
        
        .book-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        
        .book-btn:hover {
            background-color: #218838;
        }
        
        .no-trains {
            text-align: center;
            padding: 50px;
            font-size: 18px;
            color: #666;
        }
        
        .logo {
            height: 40px;
            margin-right: 10px;
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }
        
        .note {
            background-color: #fff3cd;
            color: #856404;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #ffeeba;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .train-schedule {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }
            
            .duration-line {
                transform: rotate(90deg);
                margin: 20px 0;
            }
            
            .class-availability {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <div class="logo-container">
                <img src="images/TRAIN MANIA YOU TUBE CHANNEL LOGO.png" alt="TrainDekho" class="logo">
                <span>TrainDekho</span>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="search-details">
            <div class="journey-info">
                <h2><?php echo htmlspecialchars($from); ?></h2>
                <span class="arrow">→</span>
                <h2><?php echo htmlspecialchars($to); ?></h2>
                <div class="journey-date">
                    <?php echo date('D, d M Y', strtotime($date)); ?>
                </div>
            </div>
            <button class="modify-search" onclick="window.location.href='index.php'">Modify Search</button>
        </div>

        <?php if (isset($note)): ?>
        <div class="note">
            <?php echo $note; ?>
        </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
        <div class="error">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <div class="train-list">
            <?php if (count($trains) > 0): ?>
                <div style="padding: 15px; background-color: #f5f5f5; border-bottom: 1px solid #e0e0e0;">
                    <strong><?php echo count($trains); ?> Trains Found</strong> for <?php echo htmlspecialchars($from); ?> to <?php echo htmlspecialchars($to); ?> | <?php echo date('D, d M Y', strtotime($date)); ?>
                </div>
                
                <?php foreach ($trains as $train): ?>
                    <div class="train-card">
                        <div class="train-header">
                            <div>
                                <div class="train-name">
                                    <?php 
                                    echo isset($train['train_name']) ? htmlspecialchars($train['train_name']) : 'Unnamed Train'; 
                                    echo " (";
                                    echo isset($train['train_number']) ? htmlspecialchars($train['train_number']) : 'No Number';
                                    echo ")";
                                    ?>
                                </div>
                                <div class="running-days">
                                    <?php
                                    $days = ['M', 'T', 'W', 'T', 'F', 'S', 'S'];
                                    if (isset($train['running_days'])) {
                                        for ($i = 0; $i < 7; $i++) {
                                            $isRunning = substr($train['running_days'], $i, 1) === 'Y';
                                            echo '<span class="day ' . ($isRunning ? 'running' : '') . '">' . $days[$i] . '</span>';
                                        }
                                    } else {
                                        // If running_days column doesn't exist, show all days as running
                                        foreach ($days as $day) {
                                            echo '<span class="day running">' . $day . '</span>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <div>
                                <a href="#" onclick="showDetails('<?php echo isset($train['train_id']) ? $train['train_id'] : '0'; ?>')">Train Schedule</a>
                            </div>
                        </div>

                        <div class="train-schedule">
                            <div class="departure">
                                <div class="time">
                                    <?php 
                                    echo isset($train['departure_time']) ? date('H:i', strtotime($train['departure_time'])) : '--:--'; 
                                    ?>
                                </div>
                                <div class="station"><?php echo htmlspecialchars($train['source_station']); ?></div>
                            </div>
                            
                            <div class="duration">
                                <div class="duration-time">
                                    <?php 
                                    echo isset($train['duration']) ? htmlspecialchars($train['duration']) : '--:--'; 
                                    ?>
                                </div>
                                <div class="duration-line"></div>
                            </div>
                            
                            <div class="arrival">
                                <div class="time">
                                    <?php 
                                    echo isset($train['arrival_time']) ? date('H:i', strtotime($train['arrival_time'])) : '--:--'; 
                                    ?>
                                </div>
                                <div class="station"><?php echo htmlspecialchars($train['destination_station']); ?></div>
                            </div>
                        </div>

                        <div class="class-availability">
                            <?php if ($class === 'ALL' || $class === '1A' || $class === '2A' || $class === '3A'): ?>
                            <div class="class-item <?php echo ($class === '3A') ? 'selected' : ''; ?>">
                                <div class="class-name">AC 3 Tier (3A)</div>
                                <div class="fare">
                                    ₹<?php echo isset($train['ac_fare']) ? htmlspecialchars($train['ac_fare']) : '---'; ?>
                                </div>
                                <div class="availability">
                                    Available <?php echo isset($train['ac_seats']) ? htmlspecialchars($train['ac_seats']) : '0'; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($class === 'ALL' || $class === 'SL'): ?>
                            <div class="class-item <?php echo ($class === 'SL') ? 'selected' : ''; ?>">
                                <div class="class-name">Sleeper (SL)</div>
                                <div class="fare">
                                    ₹<?php echo isset($train['sleeper_fare']) ? htmlspecialchars($train['sleeper_fare']) : '---'; ?>
                                </div>
                                <div class="availability">
                                    Available <?php echo isset($train['sleeper_seats']) ? htmlspecialchars($train['sleeper_seats']) : '0'; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <button class="book-btn" onclick="location.href='login1.php'">Book Now</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-trains">
                    <p>No trains found for the selected route and date.</p>
                    <p>Please try with different stations or date.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function showDetails(trainId) {
            alert('Train schedule details for train ID: ' + trainId);
            // You can implement a modal or redirect to a details page
        }
    </script>
</body>
</html>

<?php
// Close connection
$conn->close();
?>