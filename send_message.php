<?php
include('db.php');
session_start();

$sender_id = 0;
$receiver_id = intval($_POST['receiver_id'] ?? 0);
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$file_path = NULL;

if (isset($_SESSION['admin']) && $_SESSION['admin'] == 'GODFREY') {
    $sender_id = 1; 
} elseif (isset($_SESSION['user_id'])) {
    $sender_id = intval($_SESSION['user_id']); 
} elseif (isset($_POST['sender_id'])) {
    $sender_id = intval($_POST['sender_id']); 
}

if ($sender_id <= 0) {
    exit('Unauthorized or Invalid Sender');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['chat_file']) && $_FILES['chat_file']['error'] == 0) {
        $target_dir = "uploads/chat_files/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_name = time() . "_" . basename($_FILES["chat_file"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["chat_file"]["tmp_name"], $target_file)) {
            $file_path = $target_file;
        }
    }

    if ($receiver_id > 0 && (!empty($message) || !empty($file_path))) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, file_path, is_read) VALUES (?, ?, ?, ?, 0)");
        $stmt->bind_param("iiss", $sender_id, $receiver_id, $message, $file_path);
        
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error_db";
        }
        $stmt->close();
    } else {
        echo "error_empty";
    }
}
?>
```[cite: 5]