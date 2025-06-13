<?php
include 'conn.php';
$response = [
    'users' => 0,
    'addcarts' => 0,
    'products' => 0,
    'notifications' => 0,
];

$response['users'] = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$response['addcarts'] = $conn->query("SELECT COUNT(*) as count FROM addcarts")->fetch_assoc()['count'];
$response['products'] = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$response['notifications'] = $conn->query("SELECT COUNT(*) as count FROM notifications")->fetch_assoc()['count'];

echo json_encode($response);
