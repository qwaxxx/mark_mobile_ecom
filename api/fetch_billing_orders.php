<?php
include 'conn.php';

$user_id = $_GET['user_id'] ?? null;
$temp_id = $_GET['temp_id'] ?? null;

$response = [];

if ($user_id || $temp_id) {
    $id_field = $user_id ? "user_id" : "temp_id";
    $id_value = $user_id ?? $temp_id;

    $stmt = $conn->prepare("SELECT * FROM billing_orders WHERE billing_$id_field = ?");
    $stmt->bind_param("s", $id_value);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($billing = $result->fetch_assoc()) {
        $response = $billing;
    }

    $stmt->close();
}

$conn->close();
echo json_encode($response);
