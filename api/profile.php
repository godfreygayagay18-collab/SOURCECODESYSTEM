<?php

require_once __DIR__ . '/config.php';

$currentUser = api_require_user($conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, username, firstname, lastname, address, school_attended, mobile_email, contact, status, created_at
         FROM users WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $currentUser['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;

    if (!$row) {
        api_error('User not found.', 404);
    }
    api_response(true, $row);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = api_input();

    $firstname       = trim($input['firstname'] ?? '');
    $lastname        = trim($input['lastname'] ?? '');
    $address         = trim($input['address'] ?? '');
    $school_attended = trim($input['school_attended'] ?? '');
    $mobile_email    = trim($input['mobile_email'] ?? '');
    $new_password    = trim($input['new_password'] ?? '');
    $confirm_password = trim($input['confirm_password'] ?? '');

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE users SET firstname = ?, lastname = ?, address = ?, school_attended = ?, mobile_email = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "sssssi", $firstname, $lastname, $address, $school_attended, $mobile_email, $currentUser['id']);

    if (!mysqli_stmt_execute($stmt)) {
        api_error('Failed to update profile.', 500);
    }

    if ($new_password !== '') {
        if ($new_password !== $confirm_password) {
            api_error('New password and confirm password do not match.');
        }
        $hashed = md5($new_password);
        $pwStmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($pwStmt, "si", $hashed, $currentUser['id']);
        if (!mysqli_stmt_execute($pwStmt)) {
            api_error('Profile updated, but failed to update password.', 500);
        }
    }

    api_response(true, null, 'Profile updated successfully.');
}

api_error('Method not allowed. Use GET, POST, or PUT.', 405);
