<?php
session_start();

// Check if user is logged in and has admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login1.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "collegep";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get train details
if (isset($_GET['id'])) {
    $train_id = $_GET['id'];
    $sql = "SELECT * FROM trains WHERE train_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $train_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $train = $result->fetch_assoc();
    
    if (!$train) {
        $_SESSION['error_message'] = "Train not found!";
        header("Location: manage_trains.php");
        exit();
    }
} else {
    header("Location: manage_trains.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate train number (max 10 characters)
    $train_number = substr($_POST['train_number'], 0, 10);
    
    // Validate other fields
    $train_name = substr($_POST['train_name'], 0, 100);
    $source = substr($_POST['source'], 0, 100);
    $destination = substr($_POST['destination'], 0, 100);
    $departure = $_POST['departure'];
    $arrival = $_POST['arrival'];
    $duration = substr($_POST['duration'], 0, 20);
    $total_seats = (int)$_POST['total_seats'];
    $ac_seats = (int)$_POST['ac_seats'];
    $sleeper_seats = (int)$_POST['sleeper_seats'];
    $ac_fare = (float)$_POST['ac_fare'];
    $sleeper_fare = (float)$_POST['sleeper_fare'];
    $running_days = substr(implode(',', $_POST['running_days'] ?? []), 0, 50);
    
    // Validate status enum
    $status = $_POST['status'];
    if (!in_array($status, ['active', 'cancelled', 'delayed'])) {
        $status = 'active'; // Default value
    }

    // Check for duplicate train number
    if ($train_number !== $train['train_number']) {
        $check_sql = "SELECT train_id FROM trains WHERE train_number = ? AND train_id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $train_number, $train_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $_SESSION['error_message'] = "Train number already exists!";
            header("Location: edit_train.php?id=" . $train_id);
            exit();
        }
    }

    $sql = "UPDATE trains SET 
            train_number = ?, 
            train_name = ?, 
            source_station = ?, 
            destination_station = ?, 
            departure_time = ?, 
            arrival_time = ?, 
            duration = ?, 
            total_seats = ?, 
            ac_seats = ?, 
            sleeper_seats = ?, 
            ac_fare = ?, 
            sleeper_fare = ?, 
            running_days = ?, 
            status = ? 
            WHERE train_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssiiiddssi", 
        $train_number, 
        $train_name, 
        $source, 
        $destination, 
        $departure, 
        $arrival, 
        $duration, 
        $total_seats, 
        $ac_seats, 
        $sleeper_seats, 
        $ac_fare, 
        $sleeper_fare, 
        $running_days, 
        $status, 
        $train_id
    );

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Train updated successfully!";
        header("Location: manage_trains.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error updating train!";
    }
}

$running_days_array = explode(',', $train['running_days']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Train - TrainDekho Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"],
        input[type="time"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .checkbox-group {
            display: flex;
            gap: 15px;
            margin-top: 5px;
            flex-wrap: wrap;
        }

        .checkbox-group label {
            font-weight: normal;
        }

        .submit-btn {
            background-color: #2196F3;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .submit-btn:hover {
            background-color: #1976D2;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #003366;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Train</h2>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="train_number">Train Number</label>
                <input type="text" id="train_number" name="train_number" value="<?php echo htmlspecialchars($train['train_number']); ?>" required>
            </div>

            <div class="form-group">
                <label for="train_name">Train Name</label>
                <input type="text" id="train_name" name="train_name" value="<?php echo htmlspecialchars($train['train_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="source">Source Station</label>
                <input type="text" id="source" name="source" value="<?php echo htmlspecialchars($train['source_station']); ?>" required>
            </div>

            <div class="form-group">
                <label for="destination">Destination Station</label>
                <input type="text" id="destination" name="destination" value="<?php echo htmlspecialchars($train['destination_station']); ?>" required>
            </div>

            <div class="form-group">
                <label for="departure">Departure Time</label>
                <input type="time" id="departure" name="departure" value="<?php echo htmlspecialchars($train['departure_time']); ?>" required>
            </div>

            <div class="form-group">
                <label for="arrival">Arrival Time</label>
                <input type="time" id="arrival" name="arrival" value="<?php echo htmlspecialchars($train['arrival_time']); ?>" required>
            </div>

            <div class="form-group">
                <label for="duration">Duration</label>
                <input type="text" id="duration" name="duration" value="<?php echo htmlspecialchars($train['duration']); ?>" required>
            </div>

            <div class="form-group">
                <label for="total_seats">Total Seats</label>
                <input type="number" id="total_seats" name="total_seats" value="<?php echo htmlspecialchars($train['total_seats']); ?>" required>
            </div>

            <div class="form-group">
                <label for="ac_seats">AC Seats</label>
                <input type="number" id="ac_seats" name="ac_seats" value="<?php echo htmlspecialchars($train['ac_seats']); ?>" required>
            </div>

            <div class="form-group">
                <label for="sleeper_seats">Sleeper Seats</label>
                <input type="number" id="sleeper_seats" name="sleeper_seats" value="<?php echo htmlspecialchars($train['sleeper_seats']); ?>" required>
            </div>

            <div class="form-group">
                <label for="ac_fare">AC Fare</label>
                <input type="number" id="ac_fare" name="ac_fare" step="0.01" value="<?php echo htmlspecialchars($train['ac_fare']); ?>" required>
            </div>

            <div class="form-group">
                <label for="sleeper_fare">Sleeper Fare</label>
                <input type="number" id="sleeper_fare" name="sleeper_fare" step="0.01" value="<?php echo htmlspecialchars($train['sleeper_fare']); ?>" required>
            </div>

            <div class="form-group">
                <label>Running Days</label>
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="running_days[]" value="Mon" <?php echo in_array('Mon', $running_days_array) ? 'checked' : ''; ?>> Monday
                    </label>
                    <label>
                        <input type="checkbox" name="running_days[]" value="Tue" <?php echo in_array('Tue', $running_days_array) ? 'checked' : ''; ?>> Tuesday
                    </label>
                    <label>
                        <input type="checkbox" name="running_days[]" value="Wed" <?php echo in_array('Wed', $running_days_array) ? 'checked' : ''; ?>> Wednesday
                    </label>
                    <label>
                        <input type="checkbox" name="running_days[]" value="Thu" <?php echo in_array('Thu', $running_days_array) ? 'checked' : ''; ?>> Thursday
                    </label>
                    <label>
                        <input type="checkbox" name="running_days[]" value="Fri" <?php echo in_array('Fri', $running_days_array) ? 'checked' : ''; ?>> Friday
                    </label>
                    <label>
                        <input type="checkbox" name="running_days[]" value="Sat" <?php echo in_array('Sat', $running_days_array) ? 'checked' : ''; ?>> Saturday
                    </label>
                    <label>
                        <input type="checkbox" name="running_days[]" value="Sun" <?php echo in_array('Sun', $running_days_array) ? 'checked' : ''; ?>> Sunday
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="active" <?php echo $train['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="cancelled" <?php echo $train['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="delayed" <?php echo $train['status'] == 'delayed' ? 'selected' : ''; ?>>Delayed</option>
                </select>
            </div>

            <button type="submit" class="submit-btn">Update Train</button>
        </form>

        <a href="manage_trains.php" class="back-link">← Back to Train Management</a>
    </div>
</body>
</html>

<?php
$conn->close();
?> 