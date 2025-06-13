<?php
include 'conn.php';

$users = $conn->query("SELECT id, name, email, contact, profile_image, user_type, password FROM users");

$data = [];

while ($row = $users->fetch_assoc()) {
    $row['profile_image'] = $row['profile_image'] ? "img/" . $row['profile_image'] : "https://via.placeholder.com/50";
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
