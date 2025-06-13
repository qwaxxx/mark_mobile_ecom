<?php
include 'conn.php';

if (!isset($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID not provided']);
    exit;
}

$product_id = (int) $_POST['product_id'];
$sql = "SELECT * FROM products WHERE prod_id = $product_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Return product data as JSON
    echo json_encode(['success' => true, 'product' => $row]);
} else {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
}

$conn->close();
