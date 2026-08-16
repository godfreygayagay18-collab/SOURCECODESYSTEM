<?php
include('db.php');
session_start();

if (isset($_POST['reset_password_action'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $otp_code = mysqli_real_escape_string($conn, trim($_POST['otp_code']));
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($new_pass !== $confirm_pass) {
        echo "<script>alert('The new passwords do not match!'); window.history.back();</script>";
        exit;
    }

    $query = mysqli_query($conn, "SELECT * FROM users WHERE (username = '$username' OR mobile_email = '$username') AND reset_otp = '$otp_code'");
    
    if (mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);
        
        if (strtotime(date("Y-m-d H:i:s")) > strtotime($user['otp_expiry'])) {
            echo "<script>alert('This OTP has expired. Please request a new one.'); window.history.back();</script>";
            exit;
        }

        $final_password = md5($new_pass);
        mysqli_query($conn, "UPDATE users SET password = '$final_password', reset_otp = NULL, otp_expiry = NULL WHERE id = '{$user['id']}'");

        echo "<script>alert('Your password has been reset successfully! You can now log in.'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Invalid OTP code or username entered.'); window.history.back();</script>";
    }
}
?>