<?php
include("conn.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid user ID']);
    exit;
}

// Validate required fields
$name = trim($_POST['name'] ?? '');
$contact = trim($_POST['contact'] ?? '');
$email = trim($_POST['email'] ?? '');

if (empty($name) || empty($contact) || empty($email)) {
    http_response_code(400);
    echo json_encode(['error' => 'Name, contact, and email are required']);
    exit;
}

$password = $_POST['password'] ?? '';
$imageFileName = null;

// Handle file upload
if (!empty($_FILES['profile_image']['tmp_name'])) {
    $target_dir = "../img/";
    $originalName = basename($_FILES["profile_image"]["name"]);
    $uniqueName = time() . '_' . $originalName;
    $target_file = $target_dir . $uniqueName;

    // Check if image file is a actual image
    $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
    if ($check === false) {
        echo json_encode(['error' => 'File is not an image']);
        exit;
    }

    // Try to upload file
    if (!move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
        echo json_encode(['error' => 'Failed to upload image']);
        exit;
    }

    $imageFileName = $uniqueName;
}

// Prepare SQL query
$params = [$name, $email, $contact];
$types = "sss";
$query = "UPDATE users SET name = ?, email = ?, contact = ?";

if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $query .= ", password = ?";
    $params[] = $hashed_password;
    $types .= "s";
}

if ($imageFileName) {
    $query .= ", profile_image = ?";
    $params[] = $imageFileName;
    $types .= "s";
}

$query .= " WHERE id = ?";
$params[] = $user_id;
$types .= "i";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['error' => 'Database preparation error: ' . $conn->error]);
    exit;
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['error' => 'Failed to update profile: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
