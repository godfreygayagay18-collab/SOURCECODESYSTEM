<?php
include('db.php');
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 'GODFREY') {
    header('Location: login.php');
    exit();
}

$admin_id = 1;

if (isset($_GET['approve_id'])) {
    $id = intval($_GET['approve_id']);
    mysqli_query($conn, "UPDATE users SET status='approved' WHERE id=$id");
    header('Location: admin_dashboard.php');
    exit();
}

if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM users WHERE id=$id AND username != 'GODFREY'");
    header('Location: admin_dashboard.php');
    exit();
}

if (isset($_GET['approve_download_id'])) {
    $req_id = intval($_GET['approve_download_id']);
    mysqli_query($conn, "UPDATE download_requests SET status='Approved' WHERE id=$req_id");
    header('Location: admin_dashboard.php?msg=approved');
    exit();
}

if (isset($_GET['delete_download_id'])) {
    $req_id = intval($_GET['delete_download_id']);
    mysqli_query($conn, "DELETE FROM download_requests WHERE id=$req_id");
    header('Location: admin_dashboard.php?msg=deleted');
    exit();
}

if (isset($_GET['delete_payment_id'])) {
    $pay_id = intval($_GET['delete_payment_id']);
    mysqli_query($conn, "DELETE FROM download_requests WHERE id=$pay_id");
    header('Location: admin_dashboard.php?msg=payment_deleted');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax_admin_send'])) {
    $receiver_id = intval($_POST['receiver_id']);
    $message = mysqli_real_escape_string($conn, $_POST['message'] ?? '');
    $file_path = NULL;

    if (isset($_FILES['chat_file']) && $_FILES['chat_file']['error'] == 0) {
        $target_dir = "uploads/chat_files/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . "_" . basename($_FILES["chat_file"]["name"]);
        $target_file = $target_dir . $file_name;
        
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'docx', 'zip'];

        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["chat_file"]["tmp_name"], $target_file)) {
                $file_path = $target_file;
            }
        }
    }

    if (!empty($message) || !empty($file_path)) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, file_path, is_read) VALUES (?, ?, ?, ?, 0)");
        $stmt->bind_param("iiss", $admin_id, $receiver_id, $message, $file_path);
        $stmt->execute();
        $stmt->close();
    }
    exit;
}

if (isset($_GET['fetch_admin_messages']) && isset($_GET['user_id'])) {
    $chat_user_id = intval($_GET['user_id']);
    
    $conn->query("UPDATE messages SET is_read = 1 WHERE sender_id = '$chat_user_id' AND receiver_id = '$admin_id' AND is_read = 0");

    $msgs = mysqli_query($conn, "SELECT * FROM messages WHERE (sender_id = '$admin_id' AND receiver_id = '$chat_user_id') OR (sender_id = '$chat_user_id' AND receiver_id = '$admin_id') ORDER BY created_at ASC");
    
    if ($msgs && mysqli_num_rows($msgs) > 0) {
        while ($m = mysqli_fetch_assoc($msgs)) {
            $is_admin_sender = (intval($m['sender_id']) === $admin_id);
            $bubble_class = $is_admin_sender ? 'fb-msg-admin' : 'fb-msg-user';
            
            echo '<div class="d-flex flex-column w-100 mb-2">';
            echo '<div class="fb-msg-bubble ' . $bubble_class . ' shadow-sm p-2 px-3">';
            echo '<span class="d-block">' . htmlspecialchars($m['message']) . '</span>';
            
            if (!empty($m['file_path'])) {
                $ext = pathinfo($m['file_path'], PATHINFO_EXTENSION);
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    echo "<a href='{$m['file_path']}' target='_blank'><img src='{$m['file_path']}' style='max-width: 150px; border-radius: 4px; margin-top: 5px;'></a><br>";
                } else {
                    echo "<a href='{$m['file_path']}' target='_blank' class='text-white text-decoration-underline'><i class='bi bi-file-earmark-arrow-down'></i> Download Attachment</a><br>";
                }
            }
            echo '</div>';

            if ($is_admin_sender) {
                $seen_status = (intval($m['is_read']) === 1) ? "<span style='font-size: 9px; color: #6b7280;' class='align-self-end mt-1'>✓ Seen</span>" : "<span style='font-size: 9px; color: #9ca3af;' class='align-self-end mt-1'>Sent</span>";
                echo $seen_status;
            }
            echo '</div>';
        }
    } else {
        echo '<div class="text-center text-muted my-auto small" style="font-size: 11px;">No messages in this conversation yet.</div>';
    }
    exit;
}

if (isset($_GET['check_admin_notifications'])) {
    $admin_unread_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM messages WHERE receiver_id = '$admin_id' AND is_read = 0");
    $admin_unread_res = mysqli_fetch_assoc($admin_unread_q);
    
    $user_counts = [];
    $uc_q = mysqli_query($conn, "SELECT sender_id, COUNT(*) as cnt FROM messages WHERE receiver_id = '$admin_id' AND is_read = 0 GROUP BY sender_id");
    while($uc_row = mysqli_fetch_assoc($uc_q)){
        $user_counts[$uc_row['sender_id']] = $uc_row['cnt'];
    }

    echo json_encode([
        'total_unread' => intval($admin_unread_res['total']),
        'user_counts' => $user_counts
    ]);
    exit;
}

$count_all = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"));
$count_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE status='pending'"));
$count_approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE status='approved'"));
$count_dl_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM download_requests WHERE status='Pending'"));

$pending_users = mysqli_query($conn, "SELECT * FROM users WHERE status='pending' ORDER BY id DESC");

$active_users = mysqli_query($conn, "SELECT * FROM users WHERE status='approved' AND username != 'GODFREY' ORDER BY id DESC");

$get_download_requests = mysqli_query($conn, "SELECT r.id, r.user_username, r.status, r.gcash_ref, c.title FROM download_requests r JOIN source_codes c ON r.code_id = c.id WHERE r.status = 'Pending' ORDER BY r.id DESC");

$payment_history_query = mysqli_query($conn, "SELECT r.id, r.user_username, r.status, r.gcash_ref, r.created_at, c.title FROM download_requests r JOIN source_codes c ON r.code_id = c.id WHERE r.gcash_ref IS NOT NULL AND r.gcash_ref != '' AND r.gcash_ref != 'N/A' ORDER BY r.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CodeShare PH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --bg-main: #f8fafc;
            --bg-sidebar: #ffffff;
            --bg-card: #ffffff;
            --border-color: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --accent-purple: #6366f1;
            --accent-light: #eef2ff;
        }

        body {
            background: var(--bg-main);
            color: var(--text-main);
            min-height: 100vh;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            overflow-x: hidden;
        }

        .sidebar {
            width: 260px;
            background-color: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
            padding: 24px 20px;
        }

        .sidebar-brand {
            font-weight: 700;
            font-size: 1.05rem;
            color: var(--text-main);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
        }

        .sidebar-menu-category {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            margin-bottom: 8px;
            font-weight: 700;
            padding-left: 12px;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0 0 20px 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.15s ease;
        }

        .sidebar-link:hover {
            background-color: #f1f5f9;
            color: var(--text-main);
        }

        .sidebar-link.active {
            background-color: var(--accent-purple);
            color: #ffffff;
        }

        .sidebar-footer-card {
            margin-top: auto;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            border: none;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }

        .main-content {
            margin-left: 260px;
            padding: 24px 32px;
        }

        .top-navbar {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .search-box {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 6px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            width: 320px;
            color: var(--text-muted);
        }

        .search-box input {
            background: transparent;
            border: none;
            color: var(--text-main);
            outline: none;
            width: 100%;
            font-size: 0.85rem;
        }

        .search-box input::placeholder {
            color: #94a3b8;
        }

        .card {
            background-color: var(--bg-card);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .stat-card-top {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }
        .stat-card-top:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04);
        }

        .table {
            color: var(--text-main) !important;
            margin-bottom: 0;
            font-size: 0.875rem;
        }
        .table td, .table th {
            background-color: var(--bg-card) !important;
            border-color: var(--border-color) !important;
            color: var(--text-main) !important;
            vertical-align: middle;
            padding: 12px 16px;
        }
        .table-light th {
            background-color: #f8fafc !important;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--text-muted) !important;
            border-bottom: 1px solid var(--border-color) !important;
        }
        .table-hover tbody tr:hover td {
            background-color: #f8fafc !important;
        }

        .btn-light-custom {
            background-color: var(--accent-purple);
            color: #ffffff;
            border: 1px solid var(--accent-purple);
            font-weight: 500;
            font-size: 0.8125rem;
            border-radius: 6px;
            padding: 6px 12px;
            transition: all 0.15s ease;
        }
        .btn-light-custom:hover {
            background-color: #4f46e5;
            border-color: #4f46e5;
            color: #ffffff;
        }

        .btn-dark-action {
            background-color: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            font-weight: 500;
            font-size: 0.8125rem;
            border-radius: 6px;
            padding: 6px 12px;
            transition: all 0.15s ease;
        }
        .btn-dark-action:hover {
            background-color: #e2e8f0;
            color: #0f172a;
            border-color: #94a3b8;
        }

        .fb-chat-container {
            position: fixed;
            bottom: 0;
            right: 20px;
            width: 380px;
            z-index: 1050;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            background: var(--bg-card);
            overflow: hidden;
            display: none;
            border: 1px solid var(--border-color);
        }
        .fb-chat-header {
            background: #ffffff;
            color: var(--text-main);
            padding: 12px 16px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
        }
        .fb-chat-body {
            height: 450px;
            background: var(--bg-card);
            display: flex;
        }
        .fb-chat-sidebar {
            width: 130px;
            border-right: 1px solid var(--border-color);
            overflow-y: auto;
            background: #f8fafc;
        }
        .fb-chat-main {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            height: 450px;
        }
        .fb-messages-area {
            flex-grow: 1;
            overflow-y: auto;
            padding: 12px;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .fb-msg-bubble {
            max-width: 85%;
            padding: 8px 12px;
            border-radius: 12px;
            font-size: 13px;
            word-wrap: break-word;
        }
        .fb-msg-admin {
            background: var(--accent-purple) !important;
            color: #ffffff !important;
            font-weight: 500;
            align-self: flex-end !important;
            border-bottom-right-radius: 4px !important;
        }
        .fb-msg-user {
            background: #f1f5f9 !important;
            color: var(--text-main) !important;
            align-self: flex-start !important;
            border-bottom-left-radius: 4px !important;
        }

        @media (max-width: 992px) {
            .sidebar { width: 70px; padding: 20px 10px; }
            .sidebar-brand span, .sidebar-menu-category, .sidebar-link span, .sidebar-footer-card { display: none; }
            .main-content { margin-left: 70px; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <a class="sidebar-brand" href="#">
            <i class="bi bi-code-square fs-5 text-indigo" style="color: var(--accent-purple);"></i>
            <span>CodeShare PH</span>
        </a>

        <div class="sidebar-menu-category">Management</div>
        <ul class="sidebar-nav">
            <li><a href="admin_dashboard.php" class="sidebar-link active"><i class="bi bi-grid-1x2-fill"></i> <span>Dashboard</span></a></li>
           <li><a href="admin_profile.php" class="sidebar-link"><i class="bi bi-people-fill"></i> <span>User Accounts</span></a></li>
        </ul>

        <div class="sidebar-menu-category">System</div>
        <ul class="sidebar-nav">
            <li><a href="system_files.php" class="sidebar-link"><i class="bi bi-folder-fill"></i> <span>System Files</span></a></li>
            <li><a href="#" id="openChatBubbleSidebar" class="sidebar-link"><i class="bi bi-chat-dots-fill"></i> <span>Messages</span></a></li>
            <li><a href="#" class="sidebar-link" data-bs-toggle="modal" data-bs-target="#paymentHistoryModal"><i class="bi bi-wallet2"></i> <span>Payments</span></a></li>
        </ul>

        <div class="sidebar-footer-card">
            <i class="bi bi-cloud-arrow-up fs-4 text-white mb-1 d-block"></i>
            <h6 class="text-white small fw-bold mb-1" style="font-size: 0.75rem;">Store. Share. Collaborate.</h6>
            <p class="text-white-50" style="font-size: 9px; margin-bottom: 0;">CodeShare PH makes it easy to upload, manage and share source codes securely.</p>
        </div>
    </div>

    <div class="main-content">
        
        <div class="top-navbar">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Search anything...">
            </div>

            <div class="d-flex align-items-center gap-3">
                <button type="button" id="openChatBubbleBtn" class="btn btn-sm btn-dark-action position-relative">
                    <i class="bi bi-chat-dots-fill"></i>
                    <span id="adminTopBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 8px; display: none;">0</span>
                </button>
                
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center gap-2 text-decoration-none dropdown-toggle text-dark" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 36px; height: 36px; font-size: 13px; background-color: var(--accent-purple);">MG</div>
                        <div class="d-none d-md-block text-start" style="line-height: 1.2;">
                            <span class="d-block fw-bold" style="font-size: 0.8125rem;">Master <?php echo $_SESSION['admin']; ?></span>
                            <span class="text-muted" style="font-size: 0.6875rem;">Super Admin</span>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border" style="border-color: var(--border-color) !important;">
                        <li><a class="dropdown-item small py-2" href="system_files.php"><i class="bi bi-folder-fill me-2 text-muted"></i> System Files</a></li>
                        <li><a class="dropdown-item small py-2" href="#" data-bs-toggle="modal" data-bs-target="#paymentHistoryModal"><i class="bi bi-wallet2 me-2 text-muted"></i> Payment History</a></li>
                        <li><hr class="dropdown-divider my-1" style="border-color: var(--border-color);"></li>
                        <li><a class="dropdown-item small py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Log Out</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1" style="font-size: 1.5rem;">Dashboard Overview</h2>
                <p class="text-muted small mb-0">Welcome back, Master <?php echo $_SESSION['admin']; ?>! Here's what's happening.</p>
            </div>
            <button class="btn btn-light-custom shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#paymentHistoryModal">
                <i class="bi bi-wallet2 me-1"></i> Payment History
            </button>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'approved'): ?>
            <div class="alert alert-success border alert-dismissible fade show fw-bold shadow-sm small py-2" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> Download request approved successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-danger border alert-dismissible fade show fw-bold shadow-sm small py-2" role="alert">
                <i class="bi bi-trash-fill me-2"></i> Download request has been denied and removed.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif(isset($_GET['msg']) && $_GET['msg'] == 'payment_deleted'): ?>
            
        <?php endif; ?>
        
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card-top shadow-sm">
                    <div>
                        <span class="text-muted d-block small fw-bold text-uppercase mb-1" style="font-size: 0.65rem; letter-spacing: 0.05em;">Total Accounts</span>
                        <h3 class="fw-bold m-0 text-dark"><?php echo $count_all['total']; ?></h3>
                        <span class="text-muted" style="font-size: 0.75rem;">All registered accounts</span>
                    </div>
                    <div class="p-3 rounded-circle" style="background-color: var(--accent-light); color: var(--accent-purple);">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-top shadow-sm">
                    <div>
                        <span class="text-muted d-block small fw-bold text-uppercase mb-1" style="font-size: 0.65rem; letter-spacing: 0.05em;">Pending Approvals</span>
                        <h3 class="fw-bold m-0 text-dark"><?php echo $count_pending['total']; ?></h3>
                        <span class="text-muted" style="font-size: 0.75rem;">Accounts for review</span>
                    </div>
                    <div class="p-3 rounded-circle text-warning" style="background-color: #fef3c7;">
                        <i class="bi bi-hourglass-split fs-5"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-top shadow-sm">
                    <div>
                        <span class="text-muted d-block small fw-bold text-uppercase mb-1" style="font-size: 0.65rem; letter-spacing: 0.05em;">Pending Downloads</span>
                        <h3 class="fw-bold m-0 text-dark"><?php echo $count_dl_pending['total']; ?></h3>
                        <span class="text-muted" style="font-size: 0.75rem;">Files for approval</span>
                    </div>
                    <div class="p-3 rounded-circle text-info" style="background-color: #e0f2fe;">
                        <i class="bi bi-cloud-arrow-down-fill fs-5"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-top shadow-sm">
                    <div>
                        <span class="text-muted d-block small fw-bold text-uppercase mb-1" style="font-size: 0.65rem; letter-spacing: 0.05em;">Approved Users</span>
                        <h3 class="fw-bold m-0 text-dark"><?php echo max(0, $count_approved['total'] - 1); ?></h3>
                        <span class="text-muted" style="font-size: 0.75rem;">Active & verified users</span>
                    </div>
                    <div class="p-3 rounded-circle text-success" style="background-color: #dcfce7;">
                        <i class="bi bi-person-check-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-bold py-3 bg-transparent border-bottom d-flex justify-content-between align-items-center" style="border-color: var(--border-color) !important;">
                        <span><i class="bi bi-hourglass-split me-2 text-warning"></i> Accounts Waiting for Confirmation</span>
                        <span class="badge bg-light border text-dark"><?php echo $count_pending['total']; ?></span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (mysqli_num_rows($pending_users) == 0): ?>
                            <div class="text-center py-5 text-muted small">
                                <i class="bi bi-check-circle-fill text-success display-6 d-block mb-2"></i>
                                No pending accounts at the moment. List is clean!
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">User Information</th>
                                            <th class="text-center pe-3">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = mysqli_fetch_assoc($pending_users)): ?>
                                        <tr>
                                            <td class="ps-3 py-3">
                                                <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                                                    <?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?>
                                                </div>
                                                <div class="small mt-1 text-muted" style="font-size: 0.775rem;">
                                                    Username: <span class="text-dark fw-semibold">@<?php echo htmlspecialchars($row['username']); ?></span><br>
                                                    Contact: <span class="text-dark"><?php echo htmlspecialchars($row['mobile_email']); ?></span><br>
                                                    School: <span class="text-dark"><?php echo htmlspecialchars($row['school_attended']); ?></span>
                                                </div>
                                            </td>
                                            <td class="text-center align-middle pe-3">
                                                <a href="admin_dashboard.php?approve_id=<?php echo $row['id']; ?>" class="btn btn-light-custom btn-sm px-3 shadow-sm">
                                                    <i class="bi bi-check-lg"></i> Approve
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-bold py-3 bg-transparent border-bottom d-flex justify-content-between align-items-center" style="border-color: var(--border-color) !important;">
                        <span><i class="bi bi-people me-2 text-success"></i> Active Approved Users</span>
                        <span class="text-muted small" style="cursor: pointer;">View all</span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (mysqli_num_rows($active_users) == 0): ?>
                            <div class="text-center py-5 text-muted small">
                                <i class="bi bi-person-x d-block display-6 text-muted opacity-50 mb-2"></i>
                                No other registered and approved members yet.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">User Information</th>
                                            <th class="text-center pe-3">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = mysqli_fetch_assoc($active_users)): ?>
                                        <tr>
                                            <td class="ps-3 py-3">
                                                <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                                                    <?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?>
                                                </div>
                                                <div class="small mt-1 text-muted" style="font-size: 0.775rem;">
                                                    Username: <span class="text-dark fw-semibold">@<?php echo htmlspecialchars($row['username']); ?></span><br>
                                                    ID: <span class="text-dark">#<?php echo $row['id']; ?></span>
                                                </div>
                                            </td>
                                            <td class="text-center align-middle pe-3">
                                                <button type="button" class="btn btn-dark-action btn-sm px-3 shadow-sm" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?>')">
                                                    <i class="bi bi-trash3-fill"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header fw-bold py-3 d-flex justify-content-between align-items-center bg-transparent border-bottom" style="border-color: var(--border-color) !important;">
                <span><i class="bi bi-cloud-arrow-down-fill me-2 text-info"></i> File Download Confirmation Requests</span>
                <span class="badge bg-light text-dark border fw-bold"><?php echo $count_dl_pending['total']; ?> Pending</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Requester</th>
                                <th>Requested System File</th>
                                <th>GCash Reference No.</th>
                                <th>Status</th>
                                <th class="text-center pe-3">Action Panel</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($get_download_requests) == 0): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted small">
                                        <i class="bi bi-cloud-check display-6 d-block text-muted opacity-50 mb-2"></i> Clean list! No pending download requests at the moment.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php while($row_req = mysqli_fetch_assoc($get_download_requests)): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold text-dark">
                                            @<?php echo htmlspecialchars($row_req['user_username']); ?>
                                        </td>
                                        <td class="text-dark fw-medium">
                                            <?php echo htmlspecialchars($row_req['title']); ?>
                                        </td>
                                        <td>
                                            <?php if(!empty($row_req['gcash_ref']) && $row_req['gcash_ref'] != 'N/A'): ?>
                                                <span class="badge font-monospace bg-light border text-dark px-2 py-1">
                                                    <?php echo htmlspecialchars($row_req['gcash_ref']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">No Reference (Free)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border px-2 py-1"><?php echo $row_req['status']; ?></span>
                                        </td>
                                        <td class="text-center pe-3">
                                            <a href="admin_dashboard.php?approve_download_id=<?php echo $row_req['id']; ?>" class="btn btn-light-custom btn-sm px-2 me-1 shadow-sm">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </a>
                                            <button type="button" class="btn btn-dark-action btn-sm px-2 shadow-sm" onclick="confirmDenyDownload(<?php echo $row_req['id']; ?>, '<?php echo htmlspecialchars($row_req['user_username']); ?>')">
                                                <i class="bi bi-x-circle"></i> Deny
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <footer class="text-center text-muted py-3 small border-top mt-5" style="border-color: var(--border-color) !important;">
            &copy; 2026 CodeShare PH. All rights reserved.
        </footer>
    </div>

    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-main); border: 1px solid var(--border-color);">
                <div class="modal-header border-bottom" style="border-color: var(--border-color) !important;">
                    <h5 class="modal-title fw-bold text-danger" id="deleteConfirmModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Account Removal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="mb-1 text-dark">Are you sure you want to delete or block this account (<strong id="deleteUserName"></strong>)?</p>
                    <p class="text-muted small mb-0">This action cannot be undone and will permanently remove user access.</p>
                </div>
                <div class="modal-footer border-top" style="border-color: var(--border-color) !important;">
                    <button type="button" class="btn btn-dark-action btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger btn-sm px-3 fw-bold">Delete Account</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="denyDownloadModal" tabindex="-1" aria-labelledby="denyDownloadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-main); border: 1px solid var(--border-color);">
                <div class="modal-header border-bottom" style="border-color: var(--border-color) !important;">
                    <h5 class="modal-title fw-bold text-danger" id="denyDownloadModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Request Denial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="mb-1 text-dark">Are you sure you want to deny and remove the download request for <strong id="denyRequesterName"></strong>?</p>
                    <p class="text-muted small mb-0">This action cannot be undone.</p>
                </div>
                <div class="modal-footer border-top" style="border-color: var(--border-color) !important;">
                    <button type="button" class="btn btn-dark-action btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDenyBtn" class="btn btn-danger btn-sm px-3 fw-bold">Deny Request</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentHistoryModal" tabindex="-1" aria-labelledby="paymentHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-main); border: 1px solid var(--border-color);">
                <div class="modal-header border-bottom" style="border-color: var(--border-color) !important;">
                    <h5 class="modal-title fw-bold" id="paymentHistoryModalLabel"><i class="bi bi-receipt me-2" style="color: var(--accent-purple);"></i> GCash Payment History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Date / Time</th>
                                    <th>User Name</th>
                                    <th>System Item</th>
                                    <th>GCash Ref No.</th>
                                    <th>Status</th>
                                    <th class="text-center pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($payment_history_query) == 0): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted small">
                                            <i class="bi bi-wallet display-6 d-block opacity-50 mb-2"></i> No GCash payment history recorded yet.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php while($pay = mysqli_fetch_assoc($payment_history_query)): ?>
                                        <tr>
                                            <td class="ps-3 small text-muted">
                                                <?php echo isset($pay['created_at']) ? date('M d, Y - h:i A', strtotime($pay['created_at'])) : 'N/A'; ?>
                                            </td>
                                            <td class="fw-bold text-dark">
                                                @<?php echo htmlspecialchars($pay['user_username']); ?>
                                            </td>
                                            <td class="text-dark">
                                                <?php echo htmlspecialchars($pay['title']); ?>
                                            </td>
                                            <td>
                                                <span class="badge font-monospace bg-light border text-dark px-2 py-1">
                                                    <?php echo htmlspecialchars($pay['gcash_ref']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border px-2 py-1"><?php echo $pay['status']; ?></span>
                                            </td>
                                            <td class="text-center pe-3">
                                                <button type="button" class="btn btn-dark-action btn-sm px-2 shadow-sm" onclick="confirmDeletePayment(<?php echo $pay['id']; ?>, '<?php echo htmlspecialchars($pay['user_username']); ?>')">
                                                    <i class="bi bi-trash3-fill"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top" style="border-color: var(--border-color) !important;">
                    <button type="button" class="btn btn-dark-action btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deletePaymentModal" tabindex="-1" aria-labelledby="deletePaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-main); border: 1px solid var(--border-color);">
                <div class="modal-header border-bottom" style="border-color: var(--border-color) !important;">
                    <h5 class="modal-title fw-bold text-danger" id="deletePaymentModalLabel">Confirm Payment Record Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="mb-1 text-dark">Are you sure you want to delete the payment record for <strong id="deletePaymentUser"></strong>?</p>
                    <p class="text-muted small mb-0">This action cannot be undone.</p>
                </div>
                <div class="modal-footer border-top" style="border-color: var(--border-color) !important;">
                    <button type="button" class="btn btn-dark-action btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeletePaymentBtn" class="btn btn-danger btn-sm px-3 fw-bold">Delete Record</a>
                </div>
            </div>
        </div>
    </div>

    <div id="fbChatWidget" class="fb-chat-container">
        <div class="fb-chat-header">
            <span><i class="bi bi-chat-dots-fill me-1 text-indigo" style="color: var(--accent-purple);"></i> Admin Messenger</span>
            <div>
                <button type="button" class="btn-close btn-sm" id="closeChatX" aria-label="Close"></button>
            </div>
        </div>
        <div class="fb-chat-body">
            <div class="fb-chat-sidebar">
                <div class="list-group list-group-flush small" id="adminUserSidebarList">
                    <?php 
                    $users_query = mysqli_query($conn, "SELECT * FROM users WHERE username != 'GODFREY'");
                    if ($users_query && mysqli_num_rows($users_query) > 0) {
                        while ($usr = mysqli_fetch_assoc($users_query)) {
                            echo '<a href="#" onclick="selectChatUser(' . $usr['id'] . ', \'' . htmlspecialchars($usr['username']) . '\'); return false;" class="list-group-item list-group-item-action text-truncate py-2 px-2 text-dark bg-transparent border-bottom user-sidebar-item" id="sidebarUser_' . $usr['id'] . '" style="border-color: var(--border-color) !important;">';
                            echo '<i class="bi bi-person-circle text-muted"></i> <span style="font-size: 11px;" class="text-dark fw-bold">' . htmlspecialchars($usr['username']) . '</span>';
                            echo '<span class="badge bg-danger text-white rounded-pill float-end user-badge" id="badge_' . $usr['id'] . '" style="font-size: 8px; display: none;">0</span>';
                            echo '</a>';
                        }
                    } else {
                        echo '<div class="p-2 text-muted text-center" style="font-size: 10px;">No users found.</div>';
                    }
                    ?>
                </div>
            </div>

            <div class="fb-chat-main">
                <div class="fb-messages-area" id="chatScrollArea">
                    <div class="text-center text-muted my-auto small" style="font-size: 11px;">Select a user on the left to start a conversation.</div>
                </div>

                <div class="p-2 border-top bg-white" style="border-color: var(--border-color) !important;">
                    <div id="adminImagePreviewContainer" class="px-2 pb-2 d-none">
                        <div class="position-relative d-inline-block">
                            <img id="adminPreviewThumb" src="" alt="Preview" class="rounded border" style="width: 50px; height: 50px; object-fit: cover;">
                            <button type="button" id="adminRemoveImageBtn" class="btn btn-danger btn-sm rounded-circle position-absolute top-0 start-100 translate-middle p-0 d-flex align-items-center justify-content-center shadow-sm" style="width: 18px; height: 18px; font-size: 9px;">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>

                    <form id="adminChatForm" class="d-flex align-items-center gap-2" enctype="multipart/form-data" style="display: none;">
                        <input type="hidden" id="activeReceiverId" name="receiver_id" value="">
                        
                        <label for="admin_chat_file" class="mb-0" style="cursor: pointer; font-size: 20px; color: var(--accent-purple);" title="Attach an Image">
                            <i class="bi bi-plus-circle-fill"></i>
                        </label>
                        <input type="file" id="admin_chat_file" name="chat_file" accept="image/*" style="display: none;">

                        <div class="flex-grow-1 bg-light rounded-pill px-3 py-1 border d-flex align-items-center">
                            <input type="text" id="adminMessageInput" name="message" class="form-control form-control-sm border-0 bg-transparent shadow-none text-dark" placeholder="Aa" autocomplete="off" style="font-size: 13px;">
                        </div>

                        <button type="submit" class="btn btn-sm rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm" style="background-color: var(--accent-purple); width: 34px; height: 34px;">
                            <i class="bi bi-send-fill" style="font-size: 11px;"></i>
                        </button>
                    </form>
                    <div id="noUserSelectedText" class="text-center text-muted small py-1" style="font-size: 11px;">No chat partner selected.</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(userId, userName) {
            document.getElementById('deleteUserName').innerText = userName;
            document.getElementById('confirmDeleteBtn').setAttribute('href', 'admin_dashboard.php?delete_id=' + userId);
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            deleteModal.show();
        }

        function confirmDenyDownload(reqId, username) {
            document.getElementById('denyRequesterName').innerText = '@' + username;
            document.getElementById('confirmDenyBtn').setAttribute('href', 'admin_dashboard.php?delete_download_id=' + reqId);
            var denyModal = new bootstrap.Modal(document.getElementById('denyDownloadModal'));
            denyModal.show();
        }

        function confirmDeletePayment(payId, username) {
            document.getElementById('deletePaymentUser').innerText = '@' + username;
            document.getElementById('confirmDeletePaymentBtn').setAttribute('href', 'admin_dashboard.php?delete_payment_id=' + payId);
            var payModal = new bootstrap.Modal(document.getElementById('deletePaymentModal'));
            payModal.show();
        }

        const chatWidget = document.getElementById('fbChatWidget');
        const openBtn = document.getElementById('openChatBubbleBtn');
        const openBtnSidebar = document.getElementById('openChatBubbleSidebar');
        const closeBtn = document.getElementById('closeChatX');
        const chatBox = document.getElementById('chatScrollArea');
        const adminChatForm = document.getElementById('adminChatForm');
        const adminMessageInput = document.getElementById('adminMessageInput');
        const adminChatFile = document.getElementById('admin_chat_file');
        const activeReceiverIdInput = document.getElementById('activeReceiverId');
        const noUserSelectedText = document.getElementById('noUserSelectedText');

        const adminImagePreviewContainer = document.getElementById('adminImagePreviewContainer');
        const adminPreviewThumb = document.getElementById('adminPreviewThumb');
        const adminRemoveImageBtn = document.getElementById('adminRemoveImageBtn');

        let currentActiveUserId = null;
        let isUserAtBottom = true;

        chatBox.addEventListener('scroll', function() {
            isUserAtBottom = chatBox.scrollTop + chatBox.clientHeight >= chatBox.scrollHeight - 30;
        });

        function toggleChat(e) {
            e.preventDefault();
            chatWidget.style.display = 'block';
            if(currentActiveUserId) {
                loadAdminMessages();
            }
        }

        openBtn.addEventListener('click', toggleChat);
        if(openBtnSidebar) openBtnSidebar.addEventListener('click', toggleChat);

        closeBtn.addEventListener('click', function() {
            chatWidget.style.display = 'none';
        });

        adminChatFile.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    adminPreviewThumb.src = e.target.result;
                    adminImagePreviewContainer.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            }
        });

        adminRemoveImageBtn.addEventListener('click', function() {
            adminChatFile.value = '';
            adminImagePreviewContainer.classList.add('d-none');
            adminPreviewThumb.src = '';
        });

        function selectChatUser(userId, username) {
            currentActiveUserId = userId;
            activeReceiverIdInput.value = userId;
            
            adminChatForm.style.display = 'flex';
            noUserSelectedText.style.display = 'none';

            document.querySelectorAll('.user-sidebar-item').forEach(el => el.classList.remove('active', 'bg-light'));
            const selectedItem = document.getElementById('sidebarUser_' + userId);
            if(selectedItem) {
                selectedItem.classList.add('active', 'bg-light');
            }

            const badge = document.getElementById('badge_' + userId);
            if(badge) badge.style.display = 'none';

            loadAdminMessages();
        }

        function loadAdminMessages() {
            if (!currentActiveUserId) return;

            fetch('admin_dashboard.php?fetch_admin_messages=true&user_id=' + currentActiveUserId)
                .then(response => response.text())
                .then(data => {
                    chatBox.innerHTML = data;
                    if (isUserAtBottom) {
                        chatBox.scrollTop = chatBox.scrollHeight;
                    }
                });
        }

        adminChatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if(!currentActiveUserId) return;

            const formData = new FormData(this);
            formData.append('ajax_admin_send', '1');

            fetch('admin_dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(() => {
                adminMessageInput.value = '';
                adminChatFile.value = '';
                adminImagePreviewContainer.classList.add('d-none');
                adminPreviewThumb.src = '';
                loadAdminMessages();
                chatBox.scrollTop = chatBox.scrollHeight;
                isUserAtBottom = true;
            })
            .catch(error => console.error('Error:', error));
        });

        setInterval(() => {
            fetch('admin_dashboard.php?check_admin_notifications=true')
                .then(response => response.json())
                .then(data => {
                    const topBadge = document.getElementById('adminTopBadge');
                    if (data.total_unread > 0) {
                        topBadge.style.display = 'inline-block';
                        topBadge.innerText = data.total_unread;
                    } else {
                        topBadge.style.display = 'none';
                    }

                    for (const [uId, count] of Object.entries(data.user_counts)) {
                        const uBadge = document.getElementById('badge_' + uId);
                        if (uBadge && parseInt(count) > 0 && uId != currentActiveUserId) {
                            uBadge.style.display = 'inline-block';
                            uBadge.innerText = count;
                        } else if(uBadge) {
                            uBadge.style.display = 'none';
                        }
                    }
                });

            if (chatWidget.style.display === 'block' && currentActiveUserId) {
                loadAdminMessages();
            }
        }, 1500);
    </script>
</body>
</html>