
<?php
include 'conn.php';

// Get search and price filter
$search = $_POST['search'] ?? '';
$price = $_POST['price'] ?? '';
$page = $_POST['page'] ?? 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM products WHERE 1";

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $sql .= " AND (prod_name LIKE '%$search%' OR prod_description LIKE '%$search%')";
}

if (!empty($price)) {
    [$min, $max] = explode('-', $price);
    $sql .= " AND prod_price BETWEEN $min AND $max";
}

// Count total for pagination
$countSql = str_replace("SELECT *", "SELECT COUNT(*) as total", $sql);
$countResult = $conn->query($countSql);
$totalProducts = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $limit);

// Add pagination limit
$sql .= " LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);

$products = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'id' => $row['prod_id'],
            'picture' => htmlspecialchars($row['prod_picture']),
            'stock' => $row['prod_stock'],
            'name' => htmlspecialchars($row['prod_name']),
            'description' => htmlspecialchars($row['prod_description']),
            'price' => number_format($row['prod_price'], 2)
        ];
    }
}

$response = [
    'products' => $products,
    'total' => $totalProducts,
    'totalPages' => $totalPages,
    'currentPage' => $page
];

header('Content-Type: application/json');
echo json_encode($response);
$conn->close();
