<?php
include 'conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $prod_name = $conn->real_escape_string($_POST['prod_name']);
    $prod_price = intval($_POST['prod_price']);
    $prod_stock = intval($_POST['prod_stock']);
    $prod_description = $conn->real_escape_string($_POST['prod_description']);
    $prod_user_id = intval($_POST['prod_user_id']);

    // Handle file upload
    $uploadDir = '../uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES["prod_picture"]["name"]);
    $targetFile = $uploadDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if image file is a actual image
    $check = getimagesize($_FILES["prod_picture"]["tmp_name"]);
    if ($check === false) {
        echo json_encode(['status' => 'error', 'message' => 'File is not an image.']);
        exit;
    }

    // Check file size (5MB max)
    if ($_FILES["prod_picture"]["size"] > 5000000) {
        echo json_encode(['status' => 'error', 'message' => 'Sorry, your file is too large.']);
        exit;
    }

    // Allow certain file formats
    $allowedTypes = ['jpg', 'png', 'jpeg', 'gif'];
    if (!in_array($imageFileType, $allowedTypes)) {
        echo json_encode(['status' => 'error', 'message' => 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.']);
        exit;
    }

    // Check if file already exists and modify filename if needed
    $counter = 1;
    $originalFileName = pathinfo($fileName, PATHINFO_FILENAME);
    $extension = pathinfo($fileName, PATHINFO_EXTENSION);

    while (file_exists($targetFile)) {
        $fileName = $originalFileName . '_' . $counter . '.' . $extension;
        $targetFile = $uploadDir . $fileName;
        $counter++;
    }

    if (move_uploaded_file($_FILES["prod_picture"]["tmp_name"], $targetFile)) {
        // Store the path in the format you want (uploads/filename.ext)
        $dbFilePath = 'uploads/' . $fileName;

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO products (prod_name, prod_description, prod_stock, prod_price, prod_picture, prod_user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiisi", $prod_name, $prod_description, $prod_stock, $prod_price, $dbFilePath, $prod_user_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Product added successfully!']);
        } else {
            // Delete the uploaded file if database insert fails
            unlink($targetFile);
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Sorry, there was an error uploading your file.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
