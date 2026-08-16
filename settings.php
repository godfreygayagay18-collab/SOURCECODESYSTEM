<?php
include('db.php');
session_start();

if (!isset($_SESSION['user']) && !isset($_SESSION['admin'])) {
    header("location: login.php");
    exit();
}

$username = $_SESSION['user'] ?? $_SESSION['admin'];
$message = "";

$result = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
$user = mysqli_fetch_assoc($result);
$user_id = $user['id'] ?? 0;

if (isset($_POST['update_profile'])) {
    $new_username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $firstname = mysqli_real_escape_string($conn, trim($_POST['firstname']));
    $lastname = mysqli_real_escape_string($conn, trim($_POST['lastname']));
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));
    $school_attended = mysqli_real_escape_string($conn, trim($_POST['school_attended']));
    $mobile_email = mysqli_real_escape_string($conn, trim($_POST['mobile_email']));
    $security_question = mysqli_real_escape_string($conn, trim($_POST['security_question']));
    $security_answer = mysqli_real_escape_string($conn, trim($_POST['security_answer']));

    $check_user = mysqli_query($conn, "SELECT * FROM users WHERE username='$new_username' AND id != '$user_id'");
    if (mysqli_num_rows($check_user) > 0) {
        $message = "<div class='alert alert-danger text-center fw-bold py-2 mb-3' style='font-size: 13px;'>⚠️ Username is already taken by another user.</div>";
    } else {
        $update_query = "UPDATE users SET username='$new_username', firstname='$firstname', lastname='$lastname', address='$address', school_attended='$school_attended', mobile_email='$mobile_email', security_question='$security_question', security_answer='$security_answer' WHERE id='$user_id'";
        
        if (mysqli_query($conn, $update_query)) {
            if (isset($_SESSION['user'])) {
                $_SESSION['user'] = $new_username;
            } else {
                $_SESSION['admin'] = $new_username;
            }
            $username = $new_username;
            $message = "<div class='alert alert-success text-center fw-bold py-2 mb-3' style='font-size: 13px;'>🎉 Profile and Security Question updated successfully!</div>";
            
            $result = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
            $user = mysqli_fetch_assoc($result);
        } else {
            $message = "<div class='alert alert-danger text-center fw-bold py-2 mb-3' style='font-size: 13px;'>⚠️ Error updating profile. Please try again.</div>";
        }
    }
}

if (isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    $db_password = $user['password'] ?? '';
    $is_match = false;

    if (md5($current_pass) === $db_password || $db_password === $current_pass || password_verify($current_pass, $db_password)) {
        $is_match = true;
    }

    if (!$is_match) {
        $message = "<div class='alert alert-danger text-center fw-bold py-2 mb-3' style='font-size: 13px;'>⚠️ Incorrect current password entered!</div>";
    } elseif ($new_pass !== $confirm_pass) {
        $message = "<div class='alert alert-danger text-center fw-bold py-2 mb-3' style='font-size: 13px;'>⚠️ New password and confirmation do not match!</div>";
    } else {
        $final_pass = md5($new_pass);
        $pass_update = mysqli_query($conn, "UPDATE users SET password='$final_pass' WHERE id='$user_id'");
        
        if ($pass_update) {
            $message = "<div class='alert alert-success text-center fw-bold py-2 mb-3' style='font-size: 13px;'>🔒 Password successfully reset and updated!</div>";
        } else {
            $message = "<div class='alert alert-danger text-center fw-bold py-2 mb-3' style='font-size: 13px;'>⚠️ Error updating password.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings & Downloads - CodeShare PH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            color: #333333;
            font-family: Arial, sans-serif;
        }
        .navbar {
            background-color: #ffffff !important;
            border-bottom: 1px solid #dee2e6;
        }
        .navbar-brand, .nav-link {
            color: #1f2937 !important;
        }
        .nav-link:hover {
            color: #6366f1 !important;
        }
        .settings-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            height: 100%;
            color: #333333;
        }
        label, .form-label {
            color: #374151 !important;
            font-weight: 600;
        }
        p, .text-muted {
            color: #6b7280 !important;
        }
        .form-control, .form-select {
            background-color: #f9fafb !important;
            border: 1px solid #d1d5db !important;
            color: #1f2937 !important;
            padding: 10px 12px;
            font-size: 14px;
        }
        .form-control:focus, .form-select:focus {
            background-color: #ffffff !important;
            border-color: #6366f1 !important;
            color: #1f2937 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15) !important;
        }
        .btn-primary-custom {
            background-color: #6366f1;
            color: #ffffff;
            border: 1px solid #6366f1;
            font-weight: bold;
            transition: all 0.2s ease;
        }
        .btn-primary-custom:hover {
            background-color: #4f46e5;
            color: #ffffff;
        }
        .btn-outline-custom {
            background-color: transparent;
            color: #6366f1;
            border: 1px solid #d1d5db;
            font-weight: bold;
            transition: all 0.2s ease;
        }
        .btn-outline-custom:hover {
            border-color: #6366f1;
            color: #4f46e5;
            background-color: #f3f4f6;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light px-4 shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php"><span style="color: #6366f1;">CodeShare</span> PH</a>
            <div class="ms-auto d-flex align-items-center gap-3">
                <a href="index.php" class="nav-link fw-bold">Home</a>
                <a href="settings.php" class="nav-link fw-bold active" style="color: #6366f1 !important;"><i class="bi bi-gear-fill"></i> Settings</a>
                <a href="logout.php" class="btn btn-outline-custom btn-sm fw-bold">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <h2 class="fw-bold mb-4 text-dark text-center"><i class="bi bi-gear-fill" style="color: #6366f1;"></i> Account Settings & Download Hub</h2>
        
        <?php echo $message; ?>

        <div class="row g-4">
            
            <div class="col-lg-6">
                <div class="settings-card mb-4">
                    <h4 class="fw-bold mb-3 text-dark"><i class="bi bi-person-lines-fill"></i> Edit Profile & Security Question</h4>
                    <p class="small">Update your personal info and password recovery security question below.</p>
                    <hr style="border-color: #e5e7eb;">

                    <form action="settings.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label small">Username</label>
                            <input type="text" name="username" class="form-control rounded-2" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small">First Name</label>
                                <input type="text" name="firstname" class="form-control rounded-2" value="<?php echo htmlspecialchars($user['firstname'] ?? ''); ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Last Name</label>
                                <input type="text" name="lastname" class="form-control rounded-2" value="<?php echo htmlspecialchars($user['lastname'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">Address</label>
                            <input type="text" name="address" class="form-control rounded-2" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">School Attended</label>
                            <input type="text" name="school_attended" class="form-control rounded-2" value="<?php echo htmlspecialchars($user['school_attended'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">Mobile Number / Email</label>
                            <input type="text" name="mobile_email" class="form-control rounded-2" value="<?php echo htmlspecialchars($user['mobile_email'] ?? ''); ?>" required>
                        </div>

                        <button type="submit" name="update_profile" class="btn btn-primary-custom w-100 fw-bold py-2 rounded-2">Save Profile & Security Changes</button>
                    </form>
                </div>

                <div class="settings-card">
                    <h4 class="fw-bold mb-3 text-dark"><i class="bi bi-shield-lock-fill"></i> Reset / Change Password</h4>
                    <p class="small">Secure your account by updating your password (saved using MD5 encryption).</p>
                    <hr style="border-color: #e5e7eb;">

                    <form action="settings.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label small">Current Password</label>
                            <input type="password" name="current_password" class="form-control rounded-2" placeholder="Enter current password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">New Password</label>
                            <input type="password" name="new_password" class="form-control rounded-2" placeholder="Enter new password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control rounded-2" placeholder="Confirm new password" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary-custom w-100 fw-bold py-2 rounded-2">Update Password</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="settings-card">
                    <h4 class="fw-bold mb-3 text-dark"><i class="bi bi-download"></i> My Purchased & Downloaded Systems</h4>
                    <p class="small">View all systems and zip files you requested or purchased. Once approved by the admin, you can download them directly here.</p>
                    <hr style="border-color: #e5e7eb;">

                    <div class="table-responsive">
                        <table class="table table-hover align-middle small">
                            <thead>
                                <tr style="color: #6366f1;">
                                    <th>Project Title</th>
                                    <th>Reference Info</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $current_username = $user['username'];
                                $req_query = mysqli_query($conn, "SELECT dr.*, sc.title as code_title, sc.zip_file FROM download_requests dr LEFT JOIN source_codes sc ON dr.code_id = sc.id WHERE dr.user_username = '$current_username' ORDER BY dr.id DESC");
                                
                                if ($req_query && mysqli_num_rows($req_query) > 0) {
                                    while ($row = mysqli_fetch_assoc($req_query)) {
                                        $proj_name = htmlspecialchars($row['code_title'] ?? 'Source Code File');
                                        $ref_no = htmlspecialchars($row['gcash_ref'] ?? 'N/A');
                                        $status = $row['status']; 
                                        $zip_filename = $row['zip_file'] ?? '';

                                        echo "<tr>";
                                        echo "<td><i class='bi bi-file-earmark-zip-fill text-warning fs-6'></i> <strong class='text-dark'>{$proj_name}</strong></td>";
                                        echo "<td><span class='text-muted' style='font-size: 11px;'>Ref: {$ref_no}</span></td>";
                                        
                                        if ($status == 'Approved') {
                                            echo "<td><span class='badge bg-success'>Approved</span></td>";
                                            if (!empty($zip_filename) && file_exists("uploads/" . $zip_filename)) {
                                                echo "<td><a href='uploads/{$zip_filename}' class='btn btn-sm btn-success fw-bold rounded-2' download><i class='bi bi-download'></i> Download</a></td>";
                                            } else {
                                                echo "<td><span class='text-danger small'>File Missing</span></td>";
                                            }
                                        } else {
                                            echo "<td><span class='badge bg-warning text-dark'>Pending</span></td>";
                                            echo "<td><span class='text-muted small'>Waiting Admin</span></td>";
                                        }
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center text-muted py-4'>You haven't requested or purchased any system files yet. Browse the home page to make a request!</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="alert alert-light border mt-4 small text-center text-dark fw-semibold shadow-sm">
                        <strong>Note:</strong> If your download status is <em>Pending</em>, please wait for the administrator to review and verify your GCash payment reference number.
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>