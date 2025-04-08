<?php
session_start();
require('fpdf/fpdf.php');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "collegep";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$pnr = $_GET['pnr'];

// Fetch booking details
$sql = "SELECT b.*, t.*, u.email, u.mobile 
        FROM bookings b 
        JOIN trains t ON b.train_id = t.train_id 
        JOIN users u ON b.user_id = u.id 
        WHERE b.pnr = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $pnr);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

// Fetch passengers
$sql = "SELECT * FROM passengers WHERE booking_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking['id']);
$stmt->execute();
$passengers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Generate PDF
$pdf = generateTicketPDF($booking, $passengers);

// Output PDF
$pdf->Output('TrainDekho_Ticket_' . $pnr . '.pdf', 'D');

$conn->close();
?> 