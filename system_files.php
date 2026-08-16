<?php
include('db.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$message = "";

if (isset($_POST['upload'])) {
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $language = mysqli_real_escape_string($conn, trim($_POST['language']));
    
    $price = mysqli_real_escape_string($conn, trim($_POST['price']));
    if (empty($price)) {
        $price = "Free";
    }
    
    $uploader = $_SESSION['admin'];
    
    $file_name = $_FILES['zip_file']['name'];
    $file_tmp = $_FILES['zip_file']['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    $image_path = "";
    if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] == 0) {
        $img_name = $_FILES['project_image']['name'];
        $img_tmp = $_FILES['project_image']['tmp_name'];
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed_img = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'];

        if (in_array($img_ext, $allowed_img)) {
            $new_img_name = time() . "-" . basename($img_name);
            $img_dir = "uploads/thumbnails/";
            if (!is_dir($img_dir)) {
                mkdir($img_dir, 0777, true);
            }
            $target_img = $img_dir . $new_img_name;
            if (move_uploaded_file($img_tmp, $target_img)) {
                $image_path = $target_img;
            }
        }
    }
    
    if ($file_ext != "zip") {
        $message = "<div class='alert alert-danger text-center fw-bold shadow-sm'>⚠️ Sorry, only .ZIP files are allowed to be uploaded!</div>";
    } else {
        $new_file_name = time() . "-" . basename($file_name);
        $target_dir = "uploads/";
        
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $target_file = $target_dir . $new_file_name;
        
        if (move_uploaded_file($file_tmp, $target_file)) {
            $query = "INSERT INTO source_codes (title, description, language, price, zip_file, image_path, uploaded_by) 
                      VALUES ('$title', '$description', '$language', '$price', '$new_file_name', '$image_path', '$uploader')";
            
            if (mysqli_query($conn, $query)) {
                $message = "<div class='alert alert-success text-center fw-bold shadow-sm'>🎉 You have successfully uploaded your Source Code ZIP file with a price and image!</div>";
            } else {
                $message = "<div class='alert alert-danger text-center fw-bold shadow-sm'>Problem encountered while saving to the database.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger text-center fw-bold shadow-sm'>Error moving the file. Make sure the server has sufficient permissions.</div>";
        }
    }
}

if (isset($_POST['update_code'])) {
    $edit_id = intval($_POST['edit_id']);
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $language = mysqli_real_escape_string($conn, trim($_POST['language']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $price = mysqli_real_escape_string($conn, trim($_POST['price']));
    
    if (empty($price)) { $price = "Free"; }

    $image_update_sql = "";
    if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] == 0) {
        $img_name = $_FILES['project_image']['name'];
        $img_tmp = $_FILES['project_image']['tmp_name'];
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed_img = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'];

        if (in_array($img_ext, $allowed_img)) {
            $old_img_query = mysqli_query($conn, "SELECT image_path FROM source_codes WHERE id=$edit_id");
            if ($old_img_data = mysqli_fetch_assoc($old_img_query)) {
                if (!empty($old_img_data['image_path']) && file_exists($old_img_data['image_path'])) {
                    unlink($old_img_data['image_path']);
                }
            }

            $new_img_name = time() . "-" . basename($img_name);
            $img_dir = "uploads/thumbnails/";
            if (!is_dir($img_dir)) { mkdir($img_dir, 0777, true); }
            $target_img = $img_dir . $new_img_name;
            if (move_uploaded_file($img_tmp, $target_img)) {
                $image_update_sql = ", image_path='$target_img'";
            }
        }
    }

    if (!empty($_FILES['zip_file']['name'])) {
        $file_name = $_FILES['zip_file']['name'];
        $file_tmp = $_FILES['zip_file']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if ($file_ext != "zip") {
            $message = "<div class='alert alert-danger text-center fw-bold shadow-sm'>⚠️ File not updated: Only .ZIP files are allowed!</div>";
        } else {
            $old_file_query = mysqli_query($conn, "SELECT zip_file FROM source_codes WHERE id=$edit_id");
            if ($old_file_data = mysqli_fetch_assoc($old_file_query)) {
                $old_file_path = "uploads/" . $old_file_data['zip_file'];
                if (file_exists($old_file_path)) { unlink($old_file_path); }
            }

            $new_file_name = time() . "-" . basename($file_name);
            move_uploaded_file($file_tmp, "uploads/" . $new_file_name);

            $update_query = "UPDATE source_codes SET title='$title', language='$language', description='$description', price='$price', zip_file='$new_file_name' $image_update_sql WHERE id=$edit_id";
            mysqli_query($conn, $update_query);
            $message = "<div class='alert alert-success text-center fw-bold shadow-sm'>✨ Project information and ZIP file updated successfully!</div>";
        }
    } else {
        $update_query = "UPDATE source_codes SET title='$title', language='$language', description='$description', price='$price' $image_update_sql WHERE id=$edit_id";
        mysqli_query($conn, $update_query);
        $message = "<div class='alert alert-success text-center fw-bold shadow-sm'>✨ Project information updated successfully!</div>";
    }
}

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    $file_query = mysqli_query($conn, "SELECT zip_file, image_path FROM source_codes WHERE id=$delete_id");
    if ($file_data = mysqli_fetch_assoc($file_query)) {
        $file_to_delete = "uploads/" . $file_data['zip_file'];
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }

        if (!empty($file_data['image_path']) && file_exists($file_data['image_path'])) {
            unlink($file_data['image_path']);
        }
        
        $delete_query = "DELETE FROM source_codes WHERE id=$delete_id";
        if (mysqli_query($conn, $delete_query)) {
            $message = "<div class='alert alert-success text-center fw-bold shadow-sm'>🗑️ File, image, and its database record deleted successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger text-center fw-bold shadow-sm'>Problem encountered while deleting from the database.</div>";
        }
    }
}

$get_files = mysqli_query($conn, "SELECT * FROM source_codes ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Files Manager - Admin Dashboard</title>
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
        .form-control {
            background-color: #fff;
            border: 1px solid var(--border-color);
            color: var(--text-main);
            font-size: 0.875rem;
            padding: 10px 12px;
        }
        .form-control:focus {
            border-color: var(--accent-purple);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        .btn-light-custom {
            background-color: var(--accent-purple);
            color: #ffffff;
            border: 1px solid var(--accent-purple);
            font-weight: 500;
            font-size: 0.875rem;
            border-radius: 8px;
            padding: 10px 16px;
        }
        .btn-light-custom:hover {
            background-color: #4f46e5;
            color: #ffffff;
        }
        .table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            background-color: #f8fafc !important;
            border-bottom: 1px solid var(--border-color) !important;
        }
        .table td {
            font-size: 0.875rem;
            color: var(--text-main);
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
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
            <li><a href="admin_profile.php" class="sidebar-link"><i class="bi bi-people-fill"></i> <span>User Accounts</span></a></li>
        </ul>

        <div class="sidebar-menu-category">System</div>
        <ul class="sidebar-nav">
            <li><a href="system_files.php" class="sidebar-link active"><i class="bi bi-folder-fill"></i> <span>System Files</span></a></li>
            <li><a href="admin_dashboard.php" class="sidebar-link"><i class="bi bi-chat-dots-fill"></i> <span>Messages</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1" style="font-size: 1.5rem;">System Files Manager</h2>
                <p class="text-muted small mb-0">Upload, manage, and monitor all downloadable source codes.</p>
            </div>
            <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Dashboard</a>
        </div>

        <?php if($message != "") echo $message; ?>

        <div class="row g-4">
            
            <div class="col-lg-4">
                <div class="card shadow-sm p-4">
                    <h5 class="fw-bold mb-1 text-dark fs-6"><i class="bi bi-cloud-arrow-up-fill me-2" style="color: var(--accent-purple);"></i> Upload Zip File</h5>
                    <p class="text-muted small mb-4">Add a new downloadable source code.</p>
                    
                    <form action="system_files.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Project Title</label>
                            <input type="text" name="title" class="form-control" placeholder="Ex: E-Commerce System" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Tech Stack / Language</label>
                            <input type="text" name="language" class="form-control" placeholder="Ex: PHP / MySQL" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Short Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Brief description..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Project Price</label>
                            <input type="text" name="price" class="form-control" placeholder="Ex: Free, ₱150, or ₱500" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Attach Thumbnail Image (JPG, PNG, JFIF, WEBP)</label>
                            <input type="file" name="project_image" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif">
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Select ZIP File</label>
                            <input type="file" name="zip_file" class="form-control" accept=".zip" required>
                        </div>

                        <button type="submit" name="upload" class="btn btn-light-custom w-100 shadow-sm">
                            <i class="bi bi-send-check me-1"></i> Upload and Publish
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark fs-6"><i class="bi bi-folder2-open me-2" style="color: var(--accent-purple);"></i> Uploaded Files</h5>
                            <p class="text-muted small mb-0">List of files available for download or deletion.</p>
                        </div>
                        <span class="badge bg-light text-dark border fs-6 py-2 px-3 fw-bold">Total Files: <?php echo mysqli_num_rows($get_files); ?></span>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Project Info</th>
                                    <th>Price</th>
                                    <th>File Name</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($get_files) == 0): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-folder-x display-5"></i><br>No uploaded system files found.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php while($row = mysqli_fetch_assoc($get_files)): ?>
                                        <tr>
                                            <td><strong>#<?php echo $row['id']; ?></strong></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <?php 
                                                        $img_display = "";
                                                        if(!empty($row['image_path']) && file_exists($row['image_path'])) {
                                                            $img_display = $row['image_path'];
                                                        } elseif(!empty($row['image']) && file_exists("uploads/thumbnails/" . $row['image'])) {
                                                            $img_display = "uploads/thumbnails/" . $row['image'];
                                                        }
                                                    ?>

                                                    <?php if($img_display != ""): ?>
                                                        <img src="<?php echo $img_display; ?>" alt="Thumbnail" class="rounded border shadow-sm" style="width: 45px; height: 45px; object-fit: cover; flex-shrink: 0;">
                                                    <?php else: ?>
                                                        <div class="rounded border bg-light d-flex align-items-center justify-content-center text-muted shadow-sm" style="width: 45px; height: 45px; font-size: 14px; flex-shrink: 0;">
                                                            <i class="bi bi-image"></i>
                                                        </div>
                                                    <?php endif; ?>

                                                    <div>
                                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['title']); ?></div>
                                                        <span class="badge bg-light text-dark border mb-1" style="font-size: 10px;"><?php echo htmlspecialchars($row['language']); ?></span>
                                                        <div class="text-muted small text-truncate" style="max-width: 180px;"><?php echo htmlspecialchars($row['description']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1"><i class="bi bi-tags-fill me-1"></i> <?php echo (!empty($row['price'])) ? htmlspecialchars($row['price']) : 'Free'; ?></span>
                                            </td>
                                            <td class="text-muted small">
                                                <i class="bi bi-file-earmark-zip text-danger"></i> <span class="d-inline-block text-truncate" style="max-width: 130px;"><?php echo htmlspecialchars($row['zip_file']); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group gap-1">
                                                    <button type="button" class="btn btn-outline-primary btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editModal"
                                                            data-id="<?php echo $row['id']; ?>"
                                                            data-title="<?php echo htmlspecialchars($row['title']); ?>"
                                                            data-language="<?php echo htmlspecialchars($row['language']); ?>"
                                                            data-description="<?php echo htmlspecialchars($row['description']); ?>"
                                                            data-price="<?php echo htmlspecialchars($row['price']); ?>"
                                                            title="Edit">
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </button>

                                                    <a href="uploads/<?php echo $row['zip_file']; ?>" class="btn btn-outline-success btn-sm" download title="Download">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                    
                                                    <a href="system_files.php?delete_id=<?php echo $row['id']; ?>" 
                                                       class="btn btn-outline-danger btn-sm" 
                                                       onclick="return confirm('Are you sure you want to delete this file?');"
                                                       title="Delete">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold fs-6 text-dark" id="editModalLabel"><i class="bi bi-pencil-square me-2" style="color: var(--accent-purple);"></i> Edit Source Code Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="system_files.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="modal-id">

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Project Title</label>
                            <input type="text" name="title" id="modal-title" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Tech Stack / Language</label>
                            <input type="text" name="language" id="modal-language" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Project Price</label>
                            <input type="text" name="price" id="modal-price" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Short Description</label>
                            <textarea name="description" id="modal-description" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Change Thumbnail Image <span class="text-danger fw-normal">(Optional)</span></label>
                            <input type="file" name="project_image" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif">
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">Leave empty if you don't want to replace the current image.</small>
                        </div>

                        <div class="mb-1">
                            <label class="form-label small fw-bold text-muted">Change ZIP File <span class="text-danger fw-normal">(Optional)</span></label>
                            <input type="file" name="zip_file" class="form-control" accept=".zip">
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">Leave empty if you don't want to replace the current ZIP file.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_code" class="btn btn-light-custom btn-sm px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const editModal = document.getElementById('editModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                
                document.getElementById('modal-id').value = button.getAttribute('data-id');
                document.getElementById('modal-title').value = button.getAttribute('data-title');
                document.getElementById('modal-language').value = button.getAttribute('data-language');
                document.getElementById('modal-description').value = button.getAttribute('data-description');
                document.getElementById('modal-price').value = button.getAttribute('data-price');
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
