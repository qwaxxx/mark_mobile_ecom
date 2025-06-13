<?php
include("conn.php");

// Retrieve user_id from the GET request
$user_id = $_GET['user_id'] ?? null; // Get user_id from query parameters
$limit   = isset($_GET['all']) ? 1000 : 10;
//$user_id = 7;
$notif_id = $_GET['notifId'] ?? null;;
if ($user_id === null) {
    echo json_encode(['result' => false, 'error' => 'User ID is required']);
    exit;
}
// 4) Verify this notification belongs to the user
$check = $conn->prepare("SELECT user_id FROM notifications WHERE id = ?");
$check->bind_param("i", $notif_id);
$check->execute();
$res = $check->get_result();
if ($res->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Notification not found']);
    exit;
}
$row = $res->fetch_assoc();
if ($row['user_id'] != $user_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

// 5) Perform the update
$update = $conn->prepare("UPDATE notifications SET status = 'read' WHERE id = ?");
$update->bind_param("i", $notif_id);
if ($update->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $update->error
    ]);
}
