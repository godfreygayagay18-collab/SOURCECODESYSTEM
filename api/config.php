<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json; charset=utf-8');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

require_once __DIR__ . '/../db.php';

function api_response(bool $success, $data = null, string $message = '', int $httpCode = 200): void
{
    http_response_code($httpCode);
    $payload = ['success' => $success];
    if ($message !== '') {
        $payload['message'] = $message;
    }
    if ($data !== null) {
        $payload['data'] = $data;
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

function api_error(string $message, int $httpCode = 400): void
{
    api_response(false, null, $message, $httpCode);
}

function api_input(): array
{
    $raw = file_get_contents('php://input');
    if (!empty($raw)) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return array_merge($_GET, $_POST, $decoded);
        }
    }
    return array_merge($_GET, $_POST);
}

function api_require_user($conn): array
{
    if (!isset($_SESSION['user'])) {
        api_error('Unauthorized. Please log in first.', 401);
    }

    if (!isset($_SESSION['user_id'])) {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $_SESSION['user']);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        if (!$row) {
            api_error('Session user not found.', 401);
        }
        $_SESSION['user_id'] = (int) $row['id'];
    }

    return [
        'username' => $_SESSION['user'],
        'id' => (int) $_SESSION['user_id'],
    ];
}

function api_require_admin(): string
{
    if (!isset($_SESSION['admin'])) {
        api_error('Unauthorized. Admin access only.', 401);
    }
    return $_SESSION['admin'];
}
?>
