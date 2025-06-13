<?php
include 'conn.php';

$response = [
    'success' => false,
    'message' => ''
];

// Check if the form data is present
if (isset($_POST['prod_name'], $_POST['prod_price'], $_POST['prod_stock'], $_POST['prod_description'], $_FILES['prod_picture'])) {
    $name = $_POST['prod_name'];
    $price = $_POST['prod_price'];
    $stock = $_POST['prod_stock'];
    $description = $_POST['prod_description'];
    $user_id = $_POST['userId']; // Make sure to include this in your AJAX request

    // Handle image upload
    $image_name = $_FILES['prod_picture']['name'];
    $image_tmp = $_FILES['prod_picture']['tmp_name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image_name);

    if (move_uploaded_file($image_tmp, $target_file)) {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO products (prod_name, prod_stock, prod_description, prod_price, prod_picture, prod_user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisdss", $name, $stock, $description, $price, $target_file, $user_id);

        // Execute statement
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Product added successfully.';
        } else {
            $response['message'] = 'Database error: ' . $stmt->error;
        }

        $stmt->close();
    } else {
        $response['message'] = 'Image upload failed.';
    }
} else {
    $response['message'] = 'Invalid input data.';
}

echo json_encode($response);
$conn->close();
