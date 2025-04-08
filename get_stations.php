<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "collegep";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get all unique stations
$sql = "SELECT DISTINCT source_station FROM trains WHERE status = 'active'
        UNION
        SELECT DISTINCT destination_station FROM trains WHERE status = 'active'
        ORDER BY source_station";

$result = $conn->query($sql);
$stations = array();

while($row = $result->fetch_assoc()) {
    $stations[] = $row['source_station'];
}

// Return stations as JSON
header('Content-Type: application/json');
echo json_encode($stations);

$conn->close();
?> 