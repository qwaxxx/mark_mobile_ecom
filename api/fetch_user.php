<?php
include "conn.php"; // assumes $conn = new mysqli(...)

$response = [];

if (isset($_GET['user_id'])) {
    $user_id = mysqli_real_escape_string($conn, $_GET['user_id']);

    $sql = "SELECT id, name, email, user_type, create_on, profile_image, contact FROM users WHERE id = '$user_id' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        $response['success'] = true;
        $response['name'] = $user['name'];
        $response['user'] = $user; // Optional: send full user data
    } else {
        $response['success'] = false;
        $response['error'] = "User not found.";
    }
} else {
    $response['success'] = false;
    $response['error'] = "Missing user_id.";
}

echo json_encode($response);
