<?php
include 'conn.php';

$data = json_decode(file_get_contents("php://input"));

$id = $data->id;
$name = $data->name;
$email = $data->email;
$contact = $data->contact;
$user_type = $data->user_type;
$password = $data->password ? password_hash($data->password, PASSWORD_DEFAULT) : null;
$profile_image = $data->profile_image;

$query = "UPDATE users SET name = ?, email = ?, contact = ?, user_type = ?, password = ?, profile_image = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$name, $email, $contact, $user_type, $password, $profile_image, $id]);

echo json_encode(['message' => 'User updated successfully']);
?>
