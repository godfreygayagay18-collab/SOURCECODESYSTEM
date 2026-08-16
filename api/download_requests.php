<?php
require_once __DIR__ . '/config.php';

$currentUser = api_require_user($conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT dr.id, dr.code_id, sc.title, dr.gcash_ref, dr.status, dr.created_at
         FROM download_requests dr
         LEFT JOIN source_codes sc ON sc.id = dr.code_id
         WHERE dr.user_username = ?
         ORDER BY dr.created_at DESC"
    );
    mysqli_stmt_bind_param($stmt, "s", $currentUser['username']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }

    api_response(true, $items);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = api_input();
    $code_id = intval($input['code_id'] ?? 0);
    $gcash_ref = trim($input['gcash_ref'] ?? 'N/A');

    if ($code_id <= 0) {
        api_error('code_id is required.');
    }

    $check_code = mysqli_prepare($conn, "SELECT id FROM source_codes WHERE id = ?");
    mysqli_stmt_bind_param($check_code, "i", $code_id);
    mysqli_stmt_execute($check_code);
    if (mysqli_num_rows(mysqli_stmt_get_result($check_code)) === 0) {
        api_error('Source code not found.', 404);
    }

    $check = mysqli_prepare($conn, "SELECT id FROM download_requests WHERE user_username = ? AND code_id = ?");
    mysqli_stmt_bind_param($check, "si", $currentUser['username'], $code_id);
    mysqli_stmt_execute($check);
    if (mysqli_num_rows(mysqli_stmt_get_result($check)) > 0) {
        api_error('You already requested this source code.', 409);
    }

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO download_requests (user_username, code_id, gcash_ref, status) VALUES (?, ?, ?, 'Pending')"
    );
    mysqli_stmt_bind_param($stmt, "sis", $currentUser['username'], $code_id, $gcash_ref);

    if (mysqli_stmt_execute($stmt)) {
        api_response(true, ['id' => mysqli_insert_id($conn)], 'Request submitted. Waiting for Admin approval.', 201);
    } else {
        api_error('Failed to submit request.', 500);
    }
}

api_error('Method not allowed. Use GET or POST.', 405);
?>
```[cite: 11]