<?php
include("conn.php");

$data = json_decode(file_get_contents("php://input"), true);

if (
    isset(
        $data['name'],
        $data['email'],
        $data['password'],
        $data['contact'],
        $data['user_type']
    )
) {
    $name     = $data['name'];
    $email    = $data['email'];
    $password = $data['password'];
    $contact  = $data['contact'];
    $userType = $data['user_type'];
    $image    = $data['image'] ?? null;
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, contact, user_type, profile_image, create_on) 
                            VALUES (?, ?, ?, ?, ?, ?, NOW())");

    $stmt->bind_param("ssssss", $name, $email, $password, $contact, $userType, $image);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User registered successfully']);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to register user',
            'error' => $stmt->error
        ]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
}
