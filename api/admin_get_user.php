<?php
include("conn.php");

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'User ID is required']);
    exit;
}

$userId = $_GET['id'];

try {
    $stmt = $conn->prepare("SELECT id, name, email, contact, user_type, profile_image FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode($user);
    } else {
        echo json_encode(['error' => 'User not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Failed to fetch user: ' . $e->getMessage()]);
}
