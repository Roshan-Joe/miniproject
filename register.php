<?php
session_start(); // Start the session

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

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $full_name = trim($_POST["full_name"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $email = trim($_POST["email"]);
    $country_code = $_POST["country_code"];
    $mobile = trim($_POST["mobile"]);
    $captcha_input = $_POST["captcha_input"];

    // Dummy captcha validation
    $expected_captcha = "fTy4LP";  
    if ($captcha_input !== $expected_captcha) {
        die("Captcha verification failed!");
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        die("Passwords do not match!");
    }

    // Check if username already exists
    $check_username = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $check_username->bind_param("s", $username);
    $check_username->execute();
    $check_username->store_result();

    if ($check_username->num_rows > 0) {
        die("Error: Username already exists. Please choose a different username.");
    }
    $check_username->close();

    // Hash password securely
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insert data into database
    $stmt = $conn->prepare("INSERT INTO users (username, full_name, password_hash, email, country_code, mobile) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $full_name, $password_hash, $email, $country_code, $mobile);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Registration successful! Redirecting to login...";
        header("Location: login1.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IRCTC Account Creation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
        }

        .header {
            background-color: #003366;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            font-size: 14px;
        }

        .header a:hover {
            text-decoration: underline;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 20px;
        }

        .form-container {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-container h1 {
            font-size: 22px;
            color: #003366;
            margin-bottom: 10px;
        }

        .instructions {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
        }

        .instructions p {
            margin: 5px 0;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"] {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        select {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .captcha-container {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .captcha {
            background-color: #003366;
            color: white;
            font-size: 18px;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 5px;
            text-align: center;
        }

        .refresh-captcha {
            cursor: pointer;
        }

        .submit-btn {
            background-color: #ff6f00;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }

        .submit-btn:hover {
            opacity: 0.9;
        }

        .fraud-alert {
            margin-left: 20px;
        }

        .fraud-alert img {
            width: 300px;
            border-radius: 5px;
        }

        .form-group {
            margin-bottom: 15px;
            position: relative;
        }

        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }

        input:invalid, select:invalid {
            border-color: #dc3545;
        }

        input:valid, select:valid {
            border-color: #28a745;
        }

        .validation-pending {
            border-color: #ffc107;
        }

        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .requirement {
            margin: 2px 0;
        }

        .requirement.met {
            color: #28a745;
        }

        .requirement.unmet {
            color: #dc3545;
        }
    </style>
</head>
<body>
  

    <div class="container">
        <div class="form-container">
            <h1>Create Your IRCTC Account</h1>
            <div class="instructions">
                <p>1. Please use valid E-Mail ID, Mobile number, and correct personal details in the registration form. This may be required for verification purposes.</p>
                <p>2. Garbage / Junk values in profile may lead to deactivation of IRCTC account.</p>
            </div>
            <form action="register.php" method="POST" id="registrationForm" novalidate>
                <div class="form-group">
                    <input type="text" name="username" id="username" placeholder="User Name" 
                           pattern="^[a-zA-Z0-9_]{4,20}$" 
                           required>
                    <span class="error-message" id="usernameError"></span>
                </div>

                <div class="form-group">
                    <input type="text" name="full_name" id="fullName" placeholder="Full Name" 
                           pattern="^[a-zA-Z\s]{2,50}$" 
                           required>
                    <span class="error-message" id="fullNameError"></span>
                </div>

                <div class="form-group">
                    <input type="password" name="password" id="password" placeholder="Password" 
                           pattern="^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$" 
                           required>
                    <span class="error-message" id="passwordError"></span>
                </div>

                <div class="form-group">
                    <input type="password" name="confirm_password" id="confirmPassword" 
                           placeholder="Confirm Password" required>
                    <span class="error-message" id="confirmPasswordError"></span>
                </div>

                <div class="form-group">
                    <input type="email" name="email" id="email" placeholder="Email" required>
                    <span class="error-message" id="emailError"></span>
                </div>

                <div class="form-group">
                    <select name="country_code" id="countryCode" required>
                        <option value="">Select Country Code</option>
                        <option value="+91">+91 - India</option>
                        <option value="+1">+1 - USA</option>
                        <option value="+44">+44 - UK</option>
                    </select>
                    <span class="error-message" id="countryCodeError"></span>
                </div>

                <div class="form-group">
                    <input type="text" name="mobile" id="mobile" placeholder="Mobile" 
                           pattern="^[0-9]{10}$" 
                           required>
                    <span class="error-message" id="mobileError"></span>
                </div>

                <div class="captcha-container">
                    <div class="captcha">fTy4LP</div>
                    <input type="text" name="captcha_input" id="captcha" placeholder="Enter Captcha" required>
                    <span class="error-message" id="captchaError"></span>
                </div>

                <button type="submit" class="submit-btn">Submit</button>
            </form>

        </div>
        <div class="fraud-alert">
            <img src="images/reg.png" alt="Fraud Alert">
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registrationForm');
        const validationRules = {
            username: {
                pattern: /^[a-zA-Z0-9_]{4,20}$/,
                messages: {
                    pattern: "Username must be 4-20 characters long and can only contain letters, numbers, and underscores",
                    required: "Username is required"
                }
            },
            fullName: {
                pattern: /^[a-zA-Z\s]{2,50}$/,
                messages: {
                    pattern: "Full name must contain only letters and spaces (2-50 characters)",
                    required: "Full name is required"
                }
            },
            password: {
                pattern: /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/,
                messages: {
                    pattern: "Password must be at least 8 characters long and include letters, numbers, and special characters",
                    required: "Password is required"
                }
            },
            email: {
                pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                messages: {
                    pattern: "Please enter a valid email address",
                    required: "Email is required"
                }
            },
            mobile: {
                pattern: /^[0-9]{10}$/,
                messages: {
                    pattern: "Mobile number must be 10 digits",
                    required: "Mobile number is required"
                }
            }
        };

        // Live validation for all inputs
        form.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('input', function() {
                validateField(this);
            });

            input.addEventListener('blur', function() {
                validateField(this, true);
            });
        });

        // Special validation for confirm password
        const confirmPassword = document.getElementById('confirmPassword');
        confirmPassword.addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const errorElement = document.getElementById('confirmPasswordError');
            
            if (this.value !== password) {
                errorElement.textContent = "Passwords do not match";
                errorElement.style.display = 'block';
                this.setCustomValidity("Passwords do not match");
            } else {
                errorElement.style.display = 'none';
                this.setCustomValidity("");
            }
        });

        // Username availability check
        let usernameTimer;
        const username = document.getElementById('username');
        username.addEventListener('input', function() {
            clearTimeout(usernameTimer);
            this.classList.add('validation-pending');
            
            usernameTimer = setTimeout(() => {
                checkUsernameAvailability(this.value);
            }, 500);
        });

        function checkUsernameAvailability(username) {
            // Make an AJAX call to check username availability
            fetch(`check_username.php?username=${encodeURIComponent(username)}`)
                .then(response => response.json())
                .then(data => {
                    const errorElement = document.getElementById('usernameError');
                    if (data.available) {
                        errorElement.style.display = 'none';
                        document.getElementById('username').setCustomValidity("");
                    } else {
                        errorElement.textContent = "Username already taken";
                        errorElement.style.display = 'block';
                        document.getElementById('username').setCustomValidity("Username already taken");
                    }
                });
        }

        function validateField(input, showError = false) {
            const errorElement = document.getElementById(`${input.id}Error`);
            const rule = validationRules[input.id];

            if (!rule) return;

            if (!input.value && input.required) {
                errorElement.textContent = rule.messages.required;
                errorElement.style.display = showError ? 'block' : 'none';
                input.setCustomValidity(rule.messages.required);
                return;
            }

            if (input.value && !rule.pattern.test(input.value)) {
                errorElement.textContent = rule.messages.pattern;
                errorElement.style.display = showError ? 'block' : 'none';
                input.setCustomValidity(rule.messages.pattern);
                return;
            }

            errorElement.style.display = 'none';
            input.setCustomValidity("");
        }

        // Form submission
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate all fields
            form.querySelectorAll('input, select').forEach(input => {
                validateField(input, true);
                if (!input.checkValidity()) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    });
    </script>
</body>
</html>