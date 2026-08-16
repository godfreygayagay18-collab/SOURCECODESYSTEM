<?php

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_error('Method not allowed. Use POST.', 405);
}

$input = api_input();

$username        = strtoupper(trim($input['username'] ?? ''));
$firstname       = trim($input['firstname'] ?? '');
$lastname        = trim($input['lastname'] ?? '');
$address         = trim($input['address'] ?? '');
$school_attended = trim($input['school_attended'] ?? '');
$mobile_email    = trim($input['mobile_email'] ?? '');
$password        = trim($input['password'] ?? '');

if ($username === '' || $password === '') {
    api_error('Username and password are required.');
}

$uppercase = preg_match('@[A-Z]@', $password);
$lowercase = preg_match('@[a-z]@', $password);
$number    = preg_match('@[0-9]@', $password);
$special   = preg_match('@[^\w]@', $password);

if (!$uppercase || !$lowercase || !$number || !$special || strlen($password) < 8) {
    api_error('Password must have uppercase, lowercase, number, special char, & be 8+ characters.');
}

$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$existing = mysqli_stmt_get_result($stmt);

if ($existing && mysqli_num_rows($existing) > 0) {
    api_error('This username is already taken!', 409);
}

$encrypted_password = md5($password);

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO users (username, password, firstname, lastname, address, school_attended, mobile_email, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')"
);
mysqli_stmt_bind_param(
    $stmt,
    "sssssss",
    $username, $encrypted_password, $firstname, $lastname, $address, $school_attended, $mobile_email
);

if (mysqli_stmt_execute($stmt)) {
    api_response(true, null, 'Registered successfully! Please wait for Admin approval.', 201);
} else {
    api_error('An error occurred while saving.', 500);
}
