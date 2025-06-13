<?php
include 'conn.php';
$result = $conn->query("SELECT id, name, email, contact, user_type, create_on FROM users");

$users = array();
while ($row = $result->fetch_assoc()) {
    // Only show Delete button if user_type is NOT admin
    $row['can_delete'] = $row['user_type'] !== 'admin';
    $users[] = $row;
}

header('Content-Type: application/json');
echo json_encode($users);
