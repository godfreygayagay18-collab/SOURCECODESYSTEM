<?php
include('db.php');
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 'GODFREY') {
    header('Location: login.php');
    exit();
}

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = strtoupper(trim($_POST['username'] ?? ''));
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!empty($new_username)) {
        $update_query = "UPDATE users SET username = '$new_username' WHERE username = 'GODFREY'";
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['admin'] = $new_username;
            $success_msg = "Account details updated successfully!";
        } else {
            $error_msg = "Error updating username: " . mysqli_error($conn);
        }
    }

    if (!empty($new_password)) {
        if ($new_password === $confirm_password) {
            $hashed_password = md5($new_password);
            $pass_update = "UPDATE users SET password = '$hashed_password' WHERE username = 'GODFREY'";
            if (mysqli_query($conn, $pass_update)) {
                $success_msg = "Account details and password updated successfully!";
            } else {
                $error_msg = "Error updating password.";
            }
        } else {
            $error_msg = "New passwords do not match!";
        }
    }
}

$admin_query = mysqli_query($conn, "SELECT * FROM users WHERE username = 'GODFREY' LIMIT 1");
$admin_data = mysqli_fetch_assoc($admin_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Account Settings - CodeShare PH</title>
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
        .main-content {
            margin-left: 260px;
            padding: 24px 32px;
        }
        .card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
        }
        .btn-light-custom {
            background-color: var(--accent-purple);
            color: #ffffff;
            border: 1px solid var(--accent-purple);
            font-weight: 500;
            font-size: 0.8125rem;
            border-radius: 6px;
            padding: 8px 16px;
        }
        .btn-light-custom:hover {
            background-color: #4f46e5;
            color: #ffffff;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <a class="sidebar-brand" href="admin_dashboard.php">
            <i class="bi bi-code-square fs-5" style="color: var(--accent-purple);"></i>
            <span>CodeShare PH</span>
        </a>

        <div class="sidebar-menu-category">Management</div>
        <ul class="sidebar-nav">
            <li><a href="admin_dashboard.php" class="sidebar-link"><i class="bi bi-grid-1x2-fill"></i> <span>Dashboard</span></a></li>
            <li><a href="admin_profile.php" class="sidebar-link active"><i class="bi bi-people-fill"></i> <span>User Accounts</span></a></li>
        </ul>

        <div class="sidebar-menu-category">System</div>
        <ul class="sidebar-nav">
            <li><a href="system_files.php" class="sidebar-link"><i class="bi bi-folder-fill"></i> <span>System Files</span></a></li>
            <li><a href="admin_dashboard.php" class="sidebar-link"><i class="bi bi-chat-dots-fill"></i> <span>Messages</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1" style="font-size: 1.5rem;">Admin User Account Settings</h2>
                <p class="text-muted small mb-0">Manage your master credentials, username, and security password.</p>
            </div>
            <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Dashboard</a>
        </div>

        <?php if(!empty($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show small fw-bold" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if(!empty($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show small fw-bold" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm p-4">
                    <form method="POST" action="">
                        <h5 class="fw-bold mb-3 text-dark fs-6"><i class="bi bi-person-gear me-2" style="color: var(--accent-purple);"></i> Update Profile Credentials</h5>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Admin Username / Name</label>
                            <input type="text" name="username" class="form-control form-control-sm" value="<?php echo htmlspecialchars($admin_data['username'] ?? 'GODFREY'); ?>" required>
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        <h5 class="fw-bold mb-3 text-dark fs-6"><i class="bi bi-shield-lock-fill me-2" style="color: var(--accent-purple);"></i> Change Password</h5>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">New Password (LEAVE BLANK IF YOU DONT WANT TO CHANGE)</label>
                            <div class="input-group input-group-sm">
                                <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Enter new password">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password', 'toggleIcon1')">
                                    <i class="bi bi-eye-slash" id="toggleIcon1"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Confirm New Password</label>
                            <div class="input-group input-group-sm">
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm New Password">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                                    <i class="bi bi-eye-slash" id="toggleIcon2"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-light-custom w-100 mt-2 shadow-sm">
                            <i class="bi bi-save2 me-1"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(fieldId, iconId) {
            const passwordField = document.getElementById(fieldId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.classList.remove("bi-eye-slash");
                toggleIcon.classList.add("bi-eye");
            } else {
                passwordField.type = "password";
                toggleIcon.classList.remove("bi-eye");
                toggleIcon.classList.add("bi-eye-slash");
            }
        }
    </script>
</body>
</html>