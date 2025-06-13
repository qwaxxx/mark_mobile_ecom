<?php
include("conn.php");


// Get raw POST data
$input = json_decode(file_get_contents('php://input'), true);
// Retrieve user_id from the GET request
$user_id = $_GET['user_id'] ?? null; // Get user_id from query parameters
//$user_id = 9; // Get user_id from query parameters

if ($user_id === null) {
    echo json_encode(['result' => false, 'error' => 'User ID is required']);
    exit;
}

$sql = "
SELECT 
    b.billing_user_id,
    b.billing_street_village_purok,
    b.billing_baranggay,
    b.billing_city,
    b.billing_province,
    b.billing_country,
    b.billing_fname,
    b.billing_lname,
    ac.addcart_date AS order_date,
    ac.addcart_pcs AS total_quantity,
    (ac.addcart_pcs * p.prod_price) AS total_amount,
    ac.addcart_status,
    ac.addcart_id,
    p.prod_name, -- optional, show product name
    ac.addcart_id AS order_id
FROM addcarts ac
JOIN billing_orders b ON ac.addcart_user_id = b.billing_user_id
JOIN products p ON ac.addcart_prod_id = p.prod_id
WHERE ac.addcart_seller_id = ?
ORDER BY ac.addcart_date DESC
";


$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
