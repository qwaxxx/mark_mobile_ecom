<?php
include("conn.php");

// Fetch all orders regardless of session
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
ORDER BY ac.addcart_date DESC
";

$result = $conn->query($sql);
$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Query failed']);
    exit;
}

header('Content-Type: application/json');
echo json_encode($data);
