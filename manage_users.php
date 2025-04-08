<?php
session_start();

// Check admin authentication
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

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Analytics queries
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role != 'admin'")->fetch_assoc()['count'];
$active_users = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM bookings WHERE booking_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['count'];
$new_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role != 'admin' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['count'];

// Search and filter functionality
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? 'all';

$sql = "SELECT u.*, 
               COUNT(DISTINCT b.booking_id) as total_bookings,
               MAX(b.booking_date) as last_booking
        FROM users u
        LEFT JOIN bookings b ON u.id = b.user_id
        WHERE u.role != 'admin'";

if (!empty($search)) {
    $sql .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.id LIKE ?)";
}

if ($role_filter !== 'all') {
    $sql .= " AND u.role = ?";
}

$sql .= " GROUP BY u.id ORDER BY u.created_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($search)) {
    $search_param = "%$search%";
    if ($role_filter !== 'all') {
        $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $role_filter);
    } else {
        $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    }
} elseif ($role_filter !== 'all') {
    $stmt->bind_param("s", $role_filter);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - TrainDekho Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .users-table th, .users-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .users-table th {
            background-color: #003366;
            color: white;
        }

        .role-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .role-user { background-color: #4CAF50; color: white; }
        .role-employee { background-color: #2196F3; color: white; }
        .role-admin { background-color: #f44336; color: white; }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-view { background-color: #003366; color: white; }
        .btn-activate { background-color: #4CAF50; color: white; }
        .btn-deactivate { background-color: #ff9800; color: white; }
        .btn-block { background-color: #f44336; color: white; }
        .btn-delete { background-color: #9e9e9e; color: white; }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            width: 70%;
            max-width: 800px;
            border-radius: 8px;
        }

        .analytics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .analytics-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>User Management</h1>
            <a href="admin_dashboard.php" class="btn btn-view">Back to Dashboard</a>
        </div>

        <!-- Analytics Section -->
        <div class="analytics">
            <div class="analytics-card">
                <h3>Total Users</h3>
                <p><?php echo $total_users; ?></p>
            </div>
            <div class="analytics-card">
                <h3>Active Users (Last 30 Days)</h3>
                <p><?php echo $active_users; ?></p>
            </div>
            <div class="analytics-card">
                <h3>New Users (This Month)</h3>
                <p><?php echo $new_users; ?></p>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="search-filters">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="role">
                    <option value="all" <?php echo $role_filter === 'all' ? 'selected' : ''; ?>>All Roles</option>
                    <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>User</option>
                    <option value="employee" <?php echo $role_filter === 'employee' ? 'selected' : ''; ?>>Employee</option>
                </select>
                <button type="submit" class="btn btn-view">Search</button>
            </form>
        </div>

        <!-- Users Table -->
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Total Bookings</th>
                    <th>Last Booking</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <span class="role-badge role-<?php echo $user['role']; ?>">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </td>
                    <td><?php echo $user['total_bookings']; ?></td>
                    <td><?php echo $user['last_booking'] ? date('Y-m-d', strtotime($user['last_booking'])) : 'Never'; ?></td>
                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                    <td class="action-buttons">
                        <button onclick="viewUser(<?php echo $user['id']; ?>)" class="btn btn-view">View</button>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- User Details Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="userDetails"></div>
        </div>
    </div>

    <script>
        // Modal functionality
        const modal = document.getElementById('userModal');
        const closeBtn = document.getElementsByClassName('close')[0];

        closeBtn.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        function viewUser(userId) {
            // Fetch user details using AJAX
            fetch(`get_user_details.php?user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('userDetails').innerHTML = `
                        <h2>User Details</h2>
                        <p><strong>Username:</strong> ${data.username}</p>
                        <p><strong>Email:</strong> ${data.email}</p>
                        <p><strong>Status:</strong> ${data.status}</p>
                        <p><strong>Registered:</strong> ${data.created_at}</p>
                        <h3>Booking History</h3>
                        <table>
                            <tr>
                                <th>Booking ID</th>
                                <th>Train</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                            ${data.bookings.map(booking => `
                                <tr>
                                    <td>${booking.booking_id}</td>
                                    <td>${booking.train_name}</td>
                                    <td>${booking.journey_date}</td>
                                    <td>${booking.status}</td>
                                </tr>
                            `).join('')}
                        </table>
                    `;
                    modal.style.display = "block";
                });
        }
    </script>
</body>
</html>

<?php
$conn->close();
?> 