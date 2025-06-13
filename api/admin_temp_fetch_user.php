<?php
include 'conn.php';
$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT id, name, email, contact, user_type FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
echo json_encode($stmt->get_result()->fetch_assoc());
