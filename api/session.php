<?php

require_once __DIR__ . '/config.php';

if (isset($_SESSION['admin'])) {
    api_response(true, [
        'logged_in' => true,
        'role' => 'admin',
        'username' => $_SESSION['admin'],
    ]);
}

if (isset($_SESSION['user'])) {
    api_response(true, [
        'logged_in' => true,
        'role' => 'user',
        'username' => $_SESSION['user'],
        'id' => $_SESSION['user_id'] ?? null,
    ]);
}

api_response(true, ['logged_in' => false]);
