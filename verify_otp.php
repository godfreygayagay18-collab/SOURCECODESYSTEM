<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$user_otp = trim($data['otp'] ?? '');

$response = ['success' => false, 'message' => ''];

if (isset($_SESSION['otp']) && isset($_SESSION['otp_expiry'])) {
    
    if (time() > $_SESSION['otp_expiry']) {
        $response['message'] = 'The OTP has expired. Please request a new one.';
        unset($_SESSION['otp']);
        unset($_SESSION['otp_expiry']);
    } 
    elseif ($_SESSION['otp'] == $user_otp) {
        $_SESSION['otp_verified'] = true;
        $response['success'] = true;
        $response['message'] = 'Correct OTP!';
    } else {
        $response['message'] = 'Incorrect 6-digit OTP. Please try again.';
    }
} else {
    $response['message'] = 'No OTP request found. Please request an OTP first.';
}

echo json_encode($response);
?>