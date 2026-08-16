<?php

require_once __DIR__ . '/config.php';

const ADMIN_ID = 1;

$isAdmin = isset($_SESSION['admin']);
$currentUserId = 0;

if ($isAdmin) {
    $currentUserId = ADMIN_ID;
} elseif (isset($_SESSION['user'])) {
    $currentUser = api_require_user($conn);
    $currentUserId = $currentUser['id'];
} else {
    api_error('Unauthorized. Please log in first.', 401);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($isAdmin) {
        $targetUserId = intval($_GET['user_id'] ?? 0);
        if ($targetUserId <= 0) {
            api_error('user_id is required for admin requests.');
        }
    } else {
        $targetUserId = $currentUserId;
    }

    $otherId = $isAdmin ? $targetUserId : ADMIN_ID;

    // Markahan bilang read ang mga papasok na mensahe
    $mark = mysqli_prepare(
        $conn,
        "UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0"
    );
    mysqli_stmt_bind_param($mark, "ii", $otherId, $currentUserId);
    mysqli_stmt_execute($mark);

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, sender_id, receiver_id, message, file_path, created_at, is_read
         FROM messages
         WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
         ORDER BY created_at ASC"
    );
    mysqli_stmt_bind_param($stmt, "iiii", $currentUserId, $otherId, $otherId, $currentUserId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['id'] = (int) $row['id'];
        $row['sender_id'] = (int) $row['sender_id'];
        $row['receiver_id'] = (int) $row['receiver_id'];
        $row['is_read'] = (int) $row['is_read'];
        $items[] = $row;
    }

    api_response(true, $items);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = api_input();
    $receiver_id = intval($input['receiver_id'] ?? ($isAdmin ? 0 : ADMIN_ID));
    $message = trim($input['message'] ?? '');
    $file_path = null;

    if ($receiver_id <= 0) {
        api_error('receiver_id is required.');
    }

    if (isset($_FILES['chat_file']) && $_FILES['chat_file']['error'] === UPLOAD_ERR_OK) {
        $target_dir = __DIR__ . '/../uploads/chat_files/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES['chat_file']['name']);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['chat_file']['tmp_name'], $target_file)) {
            $file_path = 'uploads/chat_files/' . $file_name;
        }
    }

    if ($message === '' && empty($file_path)) {
        api_error('Message or attachment is required.');
    }

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO messages (sender_id, receiver_id, message, file_path, is_read) VALUES (?, ?, ?, ?, 0)"
    );
    mysqli_stmt_bind_param($stmt, "iiss", $currentUserId, $receiver_id, $message, $file_path);

    if (mysqli_stmt_execute($stmt)) {
        api_response(true, [
            'id' => mysqli_insert_id($conn),
            'sender_id' => $currentUserId,
            'receiver_id' => $receiver_id,
            'message' => $message,
            'file_path' => $file_path,
        ], 'Message sent.', 201);
    } else {
        api_error('Failed to send message.', 500);
    }
}

api_error('Method not allowed. Use GET or POST.', 405);
