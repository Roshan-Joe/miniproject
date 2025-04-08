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

// Handle Delete Train
if (isset($_POST['delete_train'])) {
    $train_id = $_POST['train_id'];
    $sql = "DELETE FROM trains WHERE train_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $train_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Train deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting train!";
    }
    header("Location: manage_trains.php");
    exit();
}

// Fetch all trains
$sql = "SELECT * FROM trains ORDER BY train_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Trains - TrainDekho Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .add-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
        }

        .trains-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #003366;
            color: white;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .edit-btn, .delete-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .edit-btn {
            background-color: #2196F3;
            color: white;
        }

        .delete-btn {
            background-color: #f44336;
            color: white;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .success {
            background-color: #dff0d8;
            color: #3c763d;
        }

        .error {
            background-color: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Manage Trains</h1>
        <a href="add_train.php" class="add-btn">Add New Train</a>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="message success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="message error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <div class="trains-table">
        <table>
            <thead>
                <tr>
                    <th>Train Number</th>
                    <th>Train Name</th>
                    <th>Source</th>
                    <th>Destination</th>
                    <th>Departure</th>
                    <th>Arrival</th>
                    <th>AC Fare</th>
                    <th>Sleeper Fare</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($train = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($train['train_number']); ?></td>
                    <td><?php echo htmlspecialchars($train['train_name']); ?></td>
                    <td><?php echo htmlspecialchars($train['source_station']); ?></td>
                    <td><?php echo htmlspecialchars($train['destination_station']); ?></td>
                    <td><?php echo htmlspecialchars($train['departure_time']); ?></td>
                    <td><?php echo htmlspecialchars($train['arrival_time']); ?></td>
                    <td>₹<?php echo htmlspecialchars($train['ac_fare']); ?></td>
                    <td>₹<?php echo htmlspecialchars($train['sleeper_fare']); ?></td>
                    <td><?php echo htmlspecialchars($train['status']); ?></td>
                    <td class="action-buttons">
                        <a href="edit_train.php?id=<?php echo $train['train_id']; ?>" class="edit-btn">Edit</a>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this train?');">
                            <input type="hidden" name="train_id" value="<?php echo $train['train_id']; ?>">
                            <button type="submit" name="delete_train" class="delete-btn">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        <a href="admin_dashboard.php" style="color: #003366;">← Back to Dashboard</a>
    </div>
</body>
</html>

<?php
$conn->close();
?> 