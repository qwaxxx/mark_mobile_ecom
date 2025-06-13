<?php
include 'conn.php'; // adjust as needed

// Read JSON payload from JavaScript
$input = json_decode(file_get_contents('php://input'), true);

$cart = $input['cart'] ?? [];

$response = ['success' => false, 'items' => [], 'total' => 0];

if (!empty($cart)) {
    $total = 0;

    foreach ($cart as $productId => $qty) {
        $stmt = $conn->prepare("SELECT prod_id, prod_name, prod_description, prod_price FROM products WHERE prod_id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $subtotal = $row['prod_price'] * $qty;
            $total += $subtotal;

            $response['items'][] = [
                'id' => $row['prod_id'],
                'name' => $row['prod_name'],
                'description' => $row['prod_description'],
                'price' => $row['prod_price'],
                'quantity' => $qty,
                'subtotal' => $subtotal
            ];
        }

        $stmt->close();
    }

    $response['success'] = true;
    $response['total'] = $total;
} else {
    $response['message'] = "Cart is empty.";
}

$conn->close();
echo json_encode($response);
