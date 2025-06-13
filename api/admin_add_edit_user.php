<?php
include 'conn.php';

$id = $_POST['id'] ?? '';
$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];
$contact = $_POST['contact'];
$user_type = $_POST['user_type'];
$image = null;

if (isset($_FILES['profile_image']) && $_FILES['profile_image']['tmp_name']) {
    $image = addslashes(file_get_contents($_FILES['profile_image']['tmp_name']));
}

if ($id) {
    $sql = "UPDATE users SET name=?, email=?, contact=?, user_type=?";
    if (!empty($password)) {
        $sql .= ", password='" . password_hash($password, PASSWORD_DEFAULT) . "'";
    }
    if ($image) {
        $sql .= ", profile_image='$image'";
    }
    $sql .= " WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $name, $email, $contact, $user_type, $id);
} else {
    $hashed_pw = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, contact, user_type, profile_image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssb", $name, $email, $hashed_pw, $contact, $user_type, $image);
}

$stmt->execute();
