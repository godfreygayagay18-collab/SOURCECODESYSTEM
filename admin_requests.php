<?php
include('db.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['approve_id'])) {
    $id = intval($_GET['approve_id']);
    mysqli_query($conn, "UPDATE download_requests SET status = 'Approved' WHERE id = '$id'");
    header("Location: admin_requests.php");
    exit();
}

$requests = mysqli_query($conn, "SELECT r.id, r.user_username, r.status, r.gcash_ref, c.title FROM download_requests r JOIN source_codes c ON r.code_id = c.id WHERE r.status = 'Pending' ORDER BY r.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Download Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark"><i class="bi bi-shield-lock-fill text-danger"></i> Pending Download Requests</h2>
            <a href="index.php" class="btn btn-secondary fw-bold"><i class="bi bi-arrow-left"></i> Back to Home</a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="p-3">Username</th>
                            <th class="p-3">Requested System/Code File</th>
                            <th class="p-3">GCash Reference No.</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($requests) == 0): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted fw-bold">
                                    <i class="bi bi-emoji-smile display-6 d-block mb-2"></i> No pending download requests. Queue is clean!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while($row = mysqli_fetch_assoc($requests)): ?>
                                <tr>
                                    <td class="p-3 fw-bold text-primary">@<?php echo htmlspecialchars($row['user_username']); ?></td>
                                    <td class="p-3 text-dark fw-semibold"><?php echo htmlspecialchars($row['title']); ?></td>
                                    
                                    <td class="p-3">
                                        <?php if(!empty($row['gcash_ref']) && $row['gcash_ref'] != 'N/A'): ?>
                                            <span class="badge bg-primary fs-6 font-monospace px-3 py-2 shadow-sm">
                                                <i class="bi bi-receipt me-1"></i> <?php echo htmlspecialchars($row['gcash_ref']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small italic">No Reference (Free)</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="p-3"><span class="badge bg-warning text-dark px-3 py-2"><?php echo $row['status']; ?></span></td>
                                    <td class="p-3 text-center">
                                        <a href="admin_requests.php?approve_id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm fw-bold px-3">
                                            <i class="bi bi-check-lg"></i> Approve Download
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>