<?php
include("conn.php");

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid user ID']);
    exit;
}

$stmt = $conn->prepare("SELECT name, email, contact, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if ($data) {
    echo json_encode($data);
} else {
    echo json_encode(['error' => 'User not found']);
}
