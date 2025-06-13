<?php
include("conn.php");

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // First verify the token is still valid
    $query = "SELECT * FROM password_resets WHERE email = ? AND token = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $response['message'] = "Invalid or expired reset link";
        echo json_encode($response);
        exit;
    }

    $row = $result->fetch_assoc();
    if ($row['expires'] < time()) {
        $response['message'] = "This reset link has expired";
        echo json_encode($response);
        exit;
    }

    if ($password !== $confirm_password) {
        $response['message'] = "Passwords do not match";
        echo json_encode($response);
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Update password
    $update_query = "UPDATE users SET password = ? WHERE email = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('ss', $hashed_password, $email);
    $update_result = $stmt->execute();

    if ($update_result) {
        // Delete used token
        $delete_query = "DELETE FROM password_resets WHERE email = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param('s', $email);
        $stmt->execute();

        $response['success'] = true;
        $response['message'] = "Password reset successful! You can now log in.";
    } else {
        $response['message'] = "Failed to update password";
    }
} else {
    $response['message'] = "Invalid request method";
}

echo json_encode($response);
