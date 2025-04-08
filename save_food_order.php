<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "collegep";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => "Connection failed: " . $conn->connect_error]));
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['orders']) && is_array($data['orders'])) {
    try {
        $conn->begin_transaction();
        
        $sql = "INSERT INTO food_order (pnr_number, food_item, quantity, price, total_price) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        foreach ($data['orders'] as $order) {
            $stmt->bind_param("ssids", 
                $order['pnr_number'],
                $order['food_item'],
                $order['quantity'],
                $order['price'],
                $order['total_price']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Error saving food order: " . $stmt->error);
            }
        }
        
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid data format']);
}

$conn->close();
?> 