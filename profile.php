<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
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

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    $update_stmt = $conn->prepare("UPDATE users SET email = ?, mobile = ? WHERE username = ?");
    $update_stmt->bind_param("sss", $email, $phone, $_SESSION['username']);
    
    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = "Profile updated successfully!";
        header("Location: profile.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error updating profile!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - TrainDekho</title>
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
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo {
            height: 50px;
        }

        .brand-name {
            font-size: 24px;
            font-weight: bold;
        }

        .back-btn {
            background-color: #ff5722;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
        }

        .profile-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background-color: #003366;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            margin: 0 auto 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-item {
            padding: 15px;
            background-color: #f8f8f8;
            border-radius: 8px;
        }

        .info-item label {
            font-weight: bold;
            color: #666;
            display: block;
            margin-bottom: 5px;
        }

        .edit-form {
            display: none;
        }

        .edit-form.active {
            display: block;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-edit {
            background-color: #ff5722;
            color: white;
        }

        .btn-save {
            background-color: #4CAF50;
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
        <div class="logo-container">
            <img src="images/TRAIN MANIA YOU TUBE CHANNEL LOGO.png" alt="TrainDekho Logo" class="logo">
            <span class="brand-name">TrainDekho</span>
        </div>
        <a href="<?php echo $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'rose.php'; ?>" class="back-btn">Back to Dashboard</a>
    </div>

    <div class="profile-container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="message success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="message error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
            </div>
            <h2><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
        </div>

        <div class="info-grid" id="profile-info">
            <div class="info-item">
                <label>Username</label>
                <div><?php echo htmlspecialchars($user['username']); ?></div>
            </div>
            <div class="info-item">
                <label>Email</label>
                <div><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            <div class="info-item">
                <label>Phone Number</label>
                <div><?php echo htmlspecialchars($user['mobile']); ?></div>
            </div>
            <div class="info-item">
                <label>Account Type</label>
                <div><?php echo ucfirst(htmlspecialchars($user['role'])); ?></div>
            </div>
        </div>

        <button class="btn btn-edit" onclick="toggleEditForm()">Edit Profile</button>

        <form class="edit-form" id="edit-form" method="POST" action="profile.php">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['mobile']); ?>" required>
            </div>
            <button type="submit" name="update_profile" class="btn btn-save">Save Changes</button>
        </form>
    </div>

    <script>
        function toggleEditForm() {
            const profileInfo = document.getElementById('profile-info');
            const editForm = document.getElementById('edit-form');
            const editBtn = document.querySelector('.btn-edit');

            if (editForm.classList.contains('active')) {
                editForm.classList.remove('active');
                profileInfo.style.display = 'grid';
                editBtn.textContent = 'Edit Profile';
            } else {
                editForm.classList.add('active');
                profileInfo.style.display = 'none';
                editBtn.textContent = 'Cancel';
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?> 