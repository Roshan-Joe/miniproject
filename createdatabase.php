<?php
// Define database connection details
$servername = "localhost"; // Change if necessary
$username = "root"; // Default username for XAMPP
$password = ""; // Default password is empty in XAMPP

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE collegep";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully";
} else {
    echo "Error creating database: " . $conn->error;
}

// Close connection
$conn->close();
?>
