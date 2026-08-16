<?php
include('db.php');
session_start();

if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
}
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 2; 
$admin_id = 1; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Simple Chat System - User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: Arial, sans-serif; background: #12121a; color: #fff; padding: 20px; }
        .chat-box { width: 400px; height: 300px; border: 1px solid #444; background: #1a1a2e; overflow-y: scroll; padding: 10px; margin-bottom: 10px; border-radius: 8px; display: flex; flex-direction: column; gap: 6px; }
        .fb-msg-bubble { max-width: 80%; padding: 8px 12px; border-radius: 15px; font-size: 13px; word-wrap: break-word; }
        .fb-msg-admin { background: #3e4042; color: #fff; align-self: flex-start; }
        .fb-msg-user { background: #0084ff; color: #fff; align-self: flex-end; }
        form { width: 400px; display: flex; gap: 5px; align-items: center; }
        input[type="text"] { flex-grow: 1; padding: 8px; border-radius: 4px; border: 1px solid #444; background: #000; color: #fff; }
        button { padding: 8px 15px; background: #0084ff; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .file-upload-btn { cursor: pointer; color: #0084ff; font-size: 20px; }
    </style>
</head>
<body>

    <h3>Chat with Admin</h3>
    <div class="chat-box" id="chatBox">
    </div>

    <form id="chatForm" enctype="multipart/form-data">
        <input type="hidden" id="sender_id" name="sender_id" value="<?php echo $user_id; ?>">
        <input type="hidden" id="receiver_id" name="receiver_id" value="<?php echo $admin_id; ?>">
        
        <label for="chat_file" class="file-upload-btn" title="Attach a File">
            <i class="bi bi-paperclip"></i>
        </label>
        <input type="file" id="chat_file" name="chat_file" style="display: none;">

        <input type="text" id="message" name="message" placeholder="Type a message..." autocomplete="off">
        <button type="submit">Send</button>
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const currentUserId = <?php echo $user_id; ?>;

        function loadMessages() {
            $.ajax({
                url: 'get_messages.php',
                type: 'GET',
                data: { user_id: currentUserId }, 
                success: function(data) {
                    $('#chatBox').html(data);
                }
            });
        }

        setInterval(loadMessages, 1500);
        loadMessages(); 

        $('#chatForm').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            $.ajax({
                url: 'send_message.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    $('#message').val(''); 
                    $('#chat_file').val(''); 
                    loadMessages(); 
                    let chatBoxElement = document.getElementById('chatBox');
                    chatBoxElement.scrollTop = chatBoxElement.scrollHeight;
                }
            });
        });
    </script>

</body>
</html>
