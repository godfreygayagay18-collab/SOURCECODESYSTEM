<?php
include('db.php');
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['code_id'])) {
    $code_id = intval($_GET['code_id']);
    $username = $_SESSION['user'];

    $gcash_ref = isset($_GET['gcash_ref']) ? mysqli_real_escape_string($conn, $_GET['gcash_ref']) : 'N/A';

    $check = mysqli_query($conn, "SELECT * FROM download_requests WHERE user_username = '$username' AND code_id = '$code_id'");
    
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($conn, "INSERT INTO download_requests (user_username, code_id, gcash_ref, status) VALUES ('$username', '$code_id', '$gcash_ref', 'Pending')");
    }
    
    header("Location: index.php?status=requested");
    exit();
}
?>