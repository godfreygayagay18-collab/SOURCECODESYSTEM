<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_error('Method not allowed. Use POST.', 405);
}

$input = api_input();
$username = strtoupper(trim($input['username'] ?? ''));
$password = trim($input['password'] ?? '');

if ($username === '' || $password === '') {
    api_error('Username and password are required.');
}

$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
    api_error('Username not found.', 404);
}

$user = mysqli_fetch_assoc($result);

if (md5($password) !== $user['password']) {
    api_error('Wrong password.', 401);
}

if ($user['username'] === 'GODFREY') {
    $_SESSION['admin'] = $user['username'];
    api_response(true, [
        'role' => 'admin',
        'username' => $user['username'],
    ], 'Logged in as admin.');
}

if (($user['status'] ?? '') === 'pending') {
    api_error('Your account is not yet approved by the Admin.', 403);
}

$_SESSION['user'] = $user['username'];
$_SESSION['user_id'] = (int) $user['id'];

api_response(true, [
    'role' => 'user',
    'id' => (int) $user['id'],
    'username' => $user['username'],
    'firstname' => $user['firstname'],
    'lastname' => $user['lastname'],
    'status' => $user['status'],
], 'Login successful.');
?>
