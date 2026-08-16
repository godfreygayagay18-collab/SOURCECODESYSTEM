<?php
include('db.php');
session_start();

$admin_id = 1; 
$target_user_id = 0;

if (isset($_GET['user_id']) && intval($_GET['user_id']) > 0) {
    $target_user_id = intval($_GET['user_id']);
    
    $conn->query("UPDATE messages SET is_read = 1 WHERE sender_id = '$target_user_id' AND receiver_id = '$admin_id' AND is_read = 0");

} elseif (isset($_SESSION['user_id']) && intval($_SESSION['user_id']) > 0) {
    $target_user_id = intval($_SESSION['user_id']);
    
    $conn->query("UPDATE messages SET is_read = 1 WHERE sender_id = '$admin_id' AND receiver_id = '$target_user_id' AND is_read = 0");
}

if ($target_user_id <= 0) {
    exit('<div class="text-center text-muted small" style="font-size: 11px;">No session or user found.</div>');
}

$query = "SELECT * FROM messages 
          WHERE (sender_id = '$admin_id' AND receiver_id = '$target_user_id') 
             OR (sender_id = '$target_user_id' AND receiver_id = '$admin_id') 
          ORDER BY created_at ASC";

$msgs = mysqli_query($conn, $query);

if ($msgs && mysqli_num_rows($msgs) > 0) {
    while ($m = mysqli_fetch_assoc($msgs)) {
        $is_admin_sender = (intval($m['sender_id']) === $admin_id);
        
        $bubble_class = $is_admin_sender ? 'fb-msg-admin' : 'fb-msg-user';
        
        echo '<div class="d-flex flex-column w-100 mb-2">';
        echo '<div class="fb-msg-bubble ' . $bubble_class . ' shadow-sm p-2 px-3">';
        
        if (!empty($m['message'])) {
            echo '<span class="d-block">' . htmlspecialchars($m['message']) . '</span>';
        }
        
        if (!empty($m['file_path'])) {
            $ext = pathinfo($m['file_path'], PATHINFO_EXTENSION);
            if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])) {
                echo "<a href='{$m['file_path']}' target='_blank'><img src='{$m['file_path']}' style='max-width: 150px; border-radius: 4px; margin-top: 5px; display: block;'></a>";
            } else {
                echo "<a href='{$m['file_path']}' target='_blank' class='text-info'><i class='bi bi-file-earmark-arrow-down'></i> Download Attachment</a>";
            }
        }
        
        echo '</div>';

        if ($is_admin_sender) {
            echo (intval($m['is_read']) === 1) ? "<span style='font-size: 9px; color: #00bcd4;' class='align-self-end mt-1'>✓ Seen</span>" : "<span style='font-size: 9px; color: #bbb;' class='align-self-end mt-1'>Sent</span>";
        }
        echo '</div>';
    }
} else {
    echo '<div class="text-center text-muted my-auto small" style="font-size: 11px;">No messages in this conversation yet.</div>';
}
?>