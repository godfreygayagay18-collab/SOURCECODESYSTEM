<?php
include('db.php');
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 'GODFREY') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = strtoupper(mysqli_real_escape_string($conn, trim($_POST['username'] ?? '')));
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!empty($new_username)) {
        $update_username = mysqli_query($conn, "UPDATE users SET username = '$new_username' WHERE username = 'GODFREY'");
        if ($update_username) {
            $_SESSION['admin'] = $new_username; 
        }
    }

    if (!empty($new_password)) {
        if ($new_password === $confirm_password) {
            $hashed_password = md5($new_password);
            
            $target_user = !empty($new_username) ? $new_username : 'GODFREY';
            
            $update_password = mysqli_query($conn, "UPDATE users SET password = '$hashed_password' WHERE username = '$target_user'");
            
            if ($update_password) {
                header('Location: admin_profile.php?msg=success');
                exit();
            } else {
                header('Location: admin_profile.php?error=db_error');
                exit();
            }
        } else {
            header('Location: admin_profile.php?error=password_mismatch');
            exit();
        }
    }

    header('Location: admin_profile.php?msg=success');
    exit();
} else {
    header('Location: admin_profile.php');
    exit();
}
?>
