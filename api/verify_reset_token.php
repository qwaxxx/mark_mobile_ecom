<?php
include("conn.php");
$response = ['valid' => false, 'message' => ''];

if (!isset($_GET['token']) || !isset($_GET['email'])) {
    $response['message'] = "Token and email are required";
    echo json_encode($response);
    exit;
}

$token = htmlspecialchars($_GET['token']);
$email = htmlspecialchars($_GET['email']);

$query = "SELECT * FROM password_resets WHERE email = ? AND token = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    $response['message'] = "Database error";
    echo json_encode($response);
    exit;
}

$stmt->bind_param('ss', $email, $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response['message'] = "Invalid reset link";
} else {
    $row = $result->fetch_assoc();
    if ($row['expires'] < time()) {
        $response['message'] = "This reset link has expired";
    } else {
        $response['valid'] = true;
    }
}

echo json_encode($response);
