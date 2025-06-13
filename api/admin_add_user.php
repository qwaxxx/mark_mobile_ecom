<?php
include 'conn.php';

$data = json_decode(file_get_contents("php://input"));

$name = $data->name;
$email = $data->email;
$contact = $data->contact;
$user_type = $data->user_type;
$password = password_hash($data->password, PASSWORD_DEFAULT);
$profile_image = 'default.jpg'; // Handle file upload separately

$query = "INSERT INTO users (name, email, contact, user_type, password, profile_image) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->execute([$name, $email, $contact, $user_type, $password, $profile_image]);

echo json_encode(['message' => 'User added successfully']);
?>
