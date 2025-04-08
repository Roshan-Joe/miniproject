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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $train_number = $_POST['train_number'];
    $train_name = $_POST['train_name'];
    $source = $_POST['source'];
    $destination = $_POST['destination'];
    $departure = $_POST['departure'];
    $arrival = $_POST['arrival'];
    $duration = $_POST['duration'];
    $total_seats = $_POST['total_seats'];
    $ac_seats = $_POST['ac_seats'];
    $sleeper_seats = $_POST['sleeper_seats'];
    $ac_fare = $_POST['ac_fare'];
    $sleeper_fare = $_POST['sleeper_fare'];
    $running_days = implode(',', $_POST['running_days']);
    $status = $_POST['status'];

    $sql = "INSERT INTO trains (train_number, train_name, source_station, destination_station, 
            departure_time, arrival_time, duration, total_seats, ac_seats, sleeper_seats, 
            ac_fare, sleeper_fare, running_days, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssiiiidss", $train_number, $train_name, $source, $destination, 
                      $departure, $arrival, $duration, $total_seats, $ac_seats, $sleeper_seats, 
                      $ac_fare, $sleeper_fare, $running_days, $status);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Train added successfully!";
        header("Location: manage_trains.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error adding train!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Train - TrainDekho Admin</title>
    <style>
        /* Add your CSS styles here */
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
        }

        .checkbox-group label {
            font-weight: normal;
        }

        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .submit-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Add New Train</h2>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="train_number">Train Number</label>
                <input type="text" id="train_number" name="train_number" required>
            </div>

            <div class="form-group">
                <label for="train_name">Train Name</label>
                <input type="text" id="train_name" name="train_name" required>
            </div>

            <div class="form-group">
                <label for="source">Source Station</label>
                <input type="text" id="source" name="source" required>
            </div>

            <div class="form-group">
                <label for="destination">Destination Station</label>
                <input type="text" id="destination" name="destination" required>
            </div>

            <div class="form-group">
                <label for="departure">Departure Time</label>
                <input type="time" id="departure" name="departure" required>
            </div>

            <div class="form-group">
                <label for="arrival">Arrival Time</label>
                <input type="time" id="arrival" name="arrival" required>
            </div>

            <div class="form-group">
                <label for="duration">Duration</label>
                <input type="text" id="duration" name="duration" placeholder="e.g., 12h 30m" required>
            </div>

            <div class="form-group">
                <label for="total_seats">Total Seats</label>
                <input type="number" id="total_seats" name="total_seats" required>
            </div>

            <div class="form-group">
                <label for="ac_seats">AC Seats</label>
                <input type="number" id="ac_seats" name="ac_seats" required>
            </div>

            <div class="form-group">
                <label for="sleeper_seats">Sleeper Seats</label>
                <input type="number" id="sleeper_seats" name="sleeper_seats" required>
            </div>

            <div class="form-group">
                <label for="ac_fare">AC Fare</label>
                <input type="number" id="ac_fare" name="ac_fare" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="sleeper_fare">Sleeper Fare</label>
                <input type="number" id="sleeper_fare" name="sleeper_fare" step="0.01" required>
            </div>

            <div class="form-group">
                <label>Running Days</label>
                <div class="checkbox-group">
                    <label><input type="checkbox" name="running_days[]" value="Mon"> Monday</label>
                    <label><input type="checkbox" name="running_days[]" value="Tue"> Tuesday</label>
                    <label><input type="checkbox" name="running_days[]" value="Wed"> Wednesday</label>
                    <label><input type="checkbox" name="running_days[]" value="Thu"> Thursday</label>
                    <label><input type="checkbox" name="running_days[]" value="Fri"> Friday</label>
                    <label><input type="checkbox" name="running_days[]" value="Sat"> Saturday</label>
                    <label><input type="checkbox" name="running_days[]" value="Sun"> Sunday</label>
                </div>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>

            <button type="submit" class="submit-btn">Add Train</button>
        </form>

        <div style="margin-top: 20px;">
            <a href="manage_trains.php" style="color: #003366;">← Back to Train Management</a>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?> 