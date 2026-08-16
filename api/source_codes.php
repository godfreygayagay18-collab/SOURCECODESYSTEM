<?php

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    api_error('Method not allowed. Use GET.', 405);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT id, title, description, price, language, uploaded_by, created_at, image_path FROM source_codes WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;

    if (!$row) {
        api_error('Source code not found.', 404);
    }
    api_response(true, $row);
}

$search = trim($_GET['search'] ?? '');
$language = trim($_GET['language'] ?? '');

$sql = "SELECT id, title, description, price, language, uploaded_by, created_at, image_path FROM source_codes WHERE 1=1";
$types = '';
$params = [];

if ($search !== '') {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
    $like = "%{$search}%";
    $types .= 'ss';
    $params[] = $like;
    $params[] = $like;
}

if ($language !== '') {
    $sql .= " AND language LIKE ?";
    $types .= 's';
    $params[] = "%{$language}%";
}

$sql .= " ORDER BY created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}

api_response(true, $items, '', 200);
