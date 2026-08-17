<?php
include('db.php');
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';



if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username']) && isset($_POST['email'])) {
   
    $username = trim($_POST['username']);
    $input_email = trim($_POST['email']);

    if (empty($input_email) || !filter_var($input_email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Gmail address format.']);
        exit;
    }

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ? AND mobile_email = ?");
    mysqli_stmt_bind_param($stmt, "ss", $username, $input_email);
    mysqli_stmt_execute($stmt);
    $query = mysqli_stmt_get_result($stmt);

    if ($query && mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);

        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        $update_stmt = mysqli_prepare($conn, "UPDATE users SET reset_otp = ?, otp_expiry = ? WHERE id = ?");
        mysqli_stmt_bind_param($update_stmt, "ssi", $otp, $expiry, $user['id']);
        mysqli_stmt_execute($update_stmt);

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'godfreygayagay18@gmail.com'; 
            $mail->Password   = 'gwuiyclpqjjpruyz';    
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('godfreygayagay18@gmail.com', 'CodeShare PH Support');
            $mail->addAddress($input_email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP - CodeShare PH';
            $mail->Body    = "<h3>Hello, {$user['username']}</h3>
                              <p>We received a request to reset your password.</p>
                              <p>Your One-Time Password (OTP) is:</p>
                              <h2 style='color: #6366f1;'>{$otp}</h2>
                              <p>This will expire in 10 minutes. Do not share this with anyone.</p>";

            $mail->send();
            echo json_encode(['status' => 'success', 'message' => 'OTP has been sent to your Gmail!']);
            exit;
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => "Mailer Error: " . $mail->ErrorInfo]);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Username and Gmail address do not match.']);
        exit;
    }
}
?>
