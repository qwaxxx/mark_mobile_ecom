<?php
include("conn.php");
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'User ID required']);
    exit;
}

$user_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT name, email, contact, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $row['profile_image'] = $row['profile_image'] ? 'img/' . $row['profile_image'] : 'https://via.placeholder.com/150';
    echo json_encode(['success' => true, 'user' => $row]);
} else {
    echo json_encode(['success' => false, 'message' => 'User not found']);
}
