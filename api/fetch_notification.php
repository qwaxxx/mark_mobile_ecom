<?php
include("conn.php");

// Retrieve user_id from the GET request
$user_id = $_GET['user_id'] ?? null; // Get user_id from query parameters

$limit   = 10;

if ($user_id === null) {
    echo json_encode(['result' => false, 'error' => 'User ID is required']);
    exit;
}

try {
    // 1) Get the unread count
    $countSql  = "SELECT COUNT(*) AS unread_count
                  FROM notifications
                  WHERE user_id = ? AND status = 'unread'";
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param("i", $user_id);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $countRow = $countResult->fetch_assoc();
    $unread_count = (int)$countRow['unread_count'];

    // 2) Fetch the notifications themselves
    $sql = "SELECT 
                n.id,
                n.addcart_id,
                n.message,
                n.status,
                n.created_at,
                p.prod_name,
                u.profile_image
            FROM notifications n
            LEFT JOIN products p ON n.addcart_id = p.prod_id
            LEFT JOIN users u ON n.sender_id = u.id
            WHERE n.user_id = ?
            ORDER BY n.created_at DESC
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id'            => $row['id'],
            'addcart_id'    => $row['addcart_id'],
            'message'       => $row['message'],
            'status'        => $row['status'],
            'created_at'    => $row['created_at'],
            'prod_name'     => $row['prod_name'] ?: 'Product Notification',
            'profile_image' => $row['profile_image'] ?: 'https://orchid-chinchilla-614427.hostingersite.com/jellygrace/img/Profile.jpg',
            'url'           => 'https://orchid-chinchilla-614427.hostingersite.com/jellygrace/seller_transaction.html?id=' . $row['addcart_id'] // optional
        ];
    }

    // 3) Return a successful response with unread_count and notifications
    echo json_encode([
        'result'        => true,
        'unread_count'  => $unread_count,
        'notifications' => $notifications
    ]);
} catch (Exception $e) {
    echo json_encode([
        'result' => false,
        'error'  => 'Server error: ' . $e->getMessage()
    ]);
}
