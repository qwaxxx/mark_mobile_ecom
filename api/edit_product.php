<?php
include 'conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $prod_id = intval($_POST['prod_id']);
    $prod_name = $conn->real_escape_string($_POST['prod_name']);
    $prod_price = floatval($_POST['prod_price']);
    $prod_stock = intval($_POST['prod_stock']);
    $prod_description = $conn->real_escape_string($_POST['prod_description']);

    // First, get current product data
    $currentDataQuery = $conn->prepare("SELECT prod_picture FROM products WHERE prod_id = ?");
    $currentDataQuery->bind_param("i", $prod_id);
    $currentDataQuery->execute();
    $currentDataResult = $currentDataQuery->get_result();

    if ($currentDataResult->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
        exit;
    }

    $currentData = $currentDataResult->fetch_assoc();
    $currentImage = $currentData['prod_picture'];
    $currentDataQuery->close();

    // Handle file upload if a new image was provided
    $dbFilePath = $currentImage; // Default to current image

    if (!empty($_FILES["prod_picture"]["name"])) {
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

            // Delete old image file if it exists
            if ($currentImage && file_exists('../' . $currentImage)) {
                unlink('../' . $currentImage);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Sorry, there was an error uploading your file.']);
            exit;
        }
    }

    // Update product in database
    $stmt = $conn->prepare("UPDATE products SET prod_name = ?, prod_description = ?, prod_stock = ?, prod_price = ?, prod_picture = ? WHERE prod_id = ?");
    $stmt->bind_param("ssidsi", $prod_name, $prod_description, $prod_stock, $prod_price, $dbFilePath, $prod_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Product updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
