<?php
session_start();

// // Check if user is logged in and has admin role
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     header("Location: login1.php");
//     exit();
// }

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

// Fetch statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role != 'admin'")->fetch_assoc()['count'];
$total_bookings = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$total_trains = $conn->query("SELECT COUNT(*) as count FROM trains")->fetch_assoc()['count'];

// Fetch recent users
$recent_users = $conn->query("SELECT username, email, created_at FROM users WHERE role != 'admin' ORDER BY created_at DESC LIMIT 5");

// Fetch recent bookings
$recent_bookings = $conn->query("SELECT b.booking_id, u.username, t.train_name, b.journey_date, b.status 
                                FROM bookings b 
                                JOIN users u ON b.user_id = u.id 
                                JOIN trains t ON b.train_id = t.train_id 
                                ORDER BY b.booking_date DESC LIMIT 5");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TrainDekho</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .header {
            background-color: #003366;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .brand-name {
            font-size: 24px;
            font-weight: bold;
            color: white;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .welcome-text {
            font-size: 1.2rem;
        }

        .logout-btn {
            background-color: #ff4444;
            color: white;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #ff0000;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
                padding: 1rem 0;
            }

            .header-right {
                flex-direction: column;
                gap: 10px;
            }
        }

        .container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #003366;
        }

        .admin-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .action-card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .action-card a {
            display: block;
            padding: 10px;
            background-color: #003366;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }

        .action-card a:hover {
            background-color: #004488;
        }

        .recent-activity {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .recent-activity h3 {
            color: #003366;
            margin-top: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f8f8;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-section">
            <img src="images/TRAIN MANIA YOU TUBE CHANNEL LOGO.png" alt="TrainDekho Logo" class="logo">
            <span class="brand-name">TrainDekho</span>
        </div>
        <div class="header-right">
            <div class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <h1>Admin Dashboard</h1>

        <!-- Statistics Section -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="stat-number"><?php echo $total_users; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Bookings</h3>
                <div class="stat-number"><?php echo $total_bookings; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Trains</h3>
                <div class="stat-number"><?php echo $total_trains; ?></div>
            </div>
        </div>

        <!-- Admin Actions -->
        <h2>Management</h2>
        <div class="admin-actions">
            <div class="action-card">
                <h3>User Management</h3>
                <p>View and manage user accounts</p>
                <a href="manage_users.php">Manage Users</a>
            </div>
            <div class="action-card">
                <h3>Booking Management</h3>
                <p>View and manage bookings</p>
                <a href="manage_bookings.php">Manage Bookings</a>
            </div>
            <div class="action-card">
                <h3>Train Management</h3>
                <p>View and manage trains</p>
                <a href="manage_trains.php">Manage Trains</a>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="recent-activity">
            <h3>Recent Users</h3>
            <table>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Registration Date</th>
                </tr>
                <?php while($user = $recent_users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- Recent Bookings -->
        <div class="recent-activity">
            <h3>Recent Bookings</h3>
            <table>
                <tr>
                    <th>Booking ID</th>
                    <th>User</th>
                    <th>Train</th>
                    <th>Journey Date</th>
                    <th>Status</th>
                </tr>
                <?php while($booking = $recent_bookings->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                    <td><?php echo htmlspecialchars($booking['username']); ?></td>
                    <td><?php echo htmlspecialchars($booking['train_name']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($booking['journey_date'])); ?></td>
                    <td><?php echo htmlspecialchars($booking['status']); ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?> 