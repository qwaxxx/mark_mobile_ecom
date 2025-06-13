<?php
include("conn.php");
require '../vendor/autoload.php'; // ✅ Autoload PHPMailer and all dependencies

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $user_type = trim($_POST['user_type']);
    $password = $_POST['password'];
    $imageFileName = null;

    // ✅ Image Upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $target_dir = "../img/";
        $file_tmp = $_FILES["profile_image"]["tmp_name"];
        $original_name = basename($_FILES["profile_image"]["name"]);
        $imageFileType = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (getimagesize($file_tmp) && in_array($imageFileType, $allowed_types)) {
            $unique_name = uniqid('IMG_', true) . '.' . $imageFileType;
            $target_file = $target_dir . $unique_name;
            if (move_uploaded_file($file_tmp, $target_file)) {
                $imageFileName = $unique_name;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Image upload failed.']);
                exit;
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid image type.']);
            exit;
        }
    }

    // ✅ Check for duplicate
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'User already exists.']);
        exit;
    }

    // ✅ Generate OTP
    $otp = rand(1000, 9999);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // ✅ Store temporarily or send OTP directly
    // Save OTP & data in temp table if needed.

    // ✅ Send Email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'remoterouter71@gmail.com';
        $mail->Password = 'dspkvdhaakctmgdu';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('remoterouter71@gmail.com', 'E-commerce');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body = "Hello <strong>$name</strong>,<br>Your OTP is <strong>" . implode(' ', str_split($otp)) . "</strong>";

        $mail->send();

        echo json_encode([
            'status' => 'success',
            'message' => 'OTP sent.',
            'otp' => $otp, // 🔐 Don't send OTP in production.
            'data' => [
                'name' => $name,
                'email' => $email,
                'contact' => $contact,
                'user_type' => $user_type,
                'image' => $imageFileName,
                'password' => $hashed_password
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Mailer error: ' . $mail->ErrorInfo]);
    }
}
