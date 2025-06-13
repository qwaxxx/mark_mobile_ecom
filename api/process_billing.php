<?php
include 'conn.php';
// Validate required fields

if (!empty($missing)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields', 'fields' => $missing]);
    exit();
}

$user_id = $_POST['user_id'] ?? null;
//$user_id = 16;
$temp_id = $_POST['temp_id'] ?? null;
$cart = json_decode($_POST['cart'], true);

if (is_null($user_id) && !is_null($temp_id)) {
    // temp_id exists but no user_id → require login
    echo json_encode([
        'status' => 'redirect',
        'location' => 'login_page.html', // adjust this URL as needed
        'message' => 'Please log in to continue with checkout.'
    ]);
    exit();
}

if (is_null($user_id)) {
    // no user_id and no temp_id → unauthorized
    http_response_code(401);
    echo json_encode([
        'error' => 'Unauthorized: user_id or temp_id required.'
    ]);
    exit();
}

// If neither user_id nor temp_id provided, reject
if (!$user_id && !$temp_id) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized: user_id or temp_id required.']);
    exit();
}

$id_field = $user_id ? "user_id" : "temp_id";
$id_value = $user_id ?? $temp_id;
$batch_id = $id_value . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

// Billing info
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];
$house = $_POST['house_address'];
$barangay = $_POST['baranggay'];
$city = $_POST['city'];
$province = $_POST['province'];
$country = $_POST['country'];
$zip = $_POST['zip'];

// Check for existing billing
$stmt = $conn->prepare("SELECT * FROM billing_orders WHERE billing_$id_field = ?");
$stmt->bind_param("s", $id_value);
$stmt->execute();
$result = $stmt->get_result();

if ($existing = $result->fetch_assoc()) {
    // Update if any data changed
    if (
        $existing['billing_fname'] !== $first_name ||
        $existing['billing_lname'] !== $last_name ||
        $existing['billing_email'] !== $email ||
        $existing['billing_street_village_purok'] !== $house ||
        $existing['billing_baranggay'] !== $barangay ||
        $existing['billing_city'] !== $city ||
        $existing['billing_province'] !== $province ||
        $existing['billing_country'] !== $country ||
        $existing['billing_postal'] !== $zip
    ) {
        $update = $conn->prepare("UPDATE billing_orders SET billing_fname=?, billing_lname=?, billing_email=?, billing_street_village_purok=?, billing_baranggay=?, billing_city=?, billing_province=?, billing_country=?, billing_postal=? WHERE billing_$id_field=?");
        $update->bind_param("ssssssssss", $first_name, $last_name, $email, $house, $barangay, $city, $province, $country, $zip, $id_value);
        $update->execute();
    }
} else {
    // Insert billing
    $insert = $conn->prepare("INSERT INTO billing_orders (billing_fname, billing_lname, billing_email, billing_street_village_purok, billing_baranggay, billing_city, billing_province, billing_country, billing_postal, billing_$id_field) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insert->bind_param("ssssssssss", $first_name, $last_name, $email, $house, $barangay, $city, $province, $country, $zip, $id_value);
    $insert->execute();
}

// Process each cart item
foreach ($cart as $prod_id => $qty) {
    $stmt = $conn->prepare("SELECT prod_price, prod_user_id, prod_name FROM products WHERE prod_id = ?");
    $stmt->bind_param("i", $prod_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $prod_price = $row['prod_price'];
        $prod_seller_id = $row['prod_user_id'];
        $prod_name = $row['prod_name'];
        $status = "pending";

        $insertCart = $conn->prepare("INSERT INTO addcarts (addcart_batch_id, addcart_user_id, addcart_seller_id, addcart_prod_id, addcart_pcs, addcart_price, addcart_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertCart->bind_param("siiiids", $batch_id, $user_id, $prod_seller_id, $prod_id, $qty, $prod_price, $status);
        $insertCart->execute();

        $addcart_id = $insertCart->insert_id;

        $notif_sql = "INSERT INTO notifications (user_id, sender_id, addcart_id, message, type) VALUES (?, ?, ?, ?, 'purchase')";
        $stmt1 = $conn->prepare($notif_sql);
        $msg = "Someone purchased your product: $prod_name / $qty pcs / Price: $prod_price";
        $stmt1->bind_param("iiis", $prod_seller_id, $user_id, $addcart_id, $msg);
        $stmt1->execute();
    }
}

// Send success response
echo json_encode([
    'status' => 'success',
    'message' => 'Billing and order processed.',
    'batch_id' => $batch_id
]);
