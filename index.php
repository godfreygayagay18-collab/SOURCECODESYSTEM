<?php
include('db.php');
session_start();

$get_codes = mysqli_query($conn, "SELECT * FROM source_codes ORDER BY id DESC");
$is_logged_in = (isset($_SESSION['admin']) || isset($_SESSION['user']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Free Source Code Hub - Developer-Mr Freyy</title>
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

        .hero-section {
            position: relative;
            overflow: hidden;
            background-color: #111827;
            color: #ffffff;
            padding: 100px 0;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
        }
        .bg-video {
            position: absolute;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            z-index: 1;
            transform: translateX(-50%) translateY(-50%);
            object-fit: cover;
            opacity: 0.6;
        }
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.75);
            z-index: 2;
        }
        .hero-section .container {
            position: relative;
            z-index: 3;
        }
        
        .code-card { 
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            transition: transform 0.2s ease, box-shadow 0.2s ease; 
        }
        .code-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
            border-color: #6366f1;
        }

        .img-container-overflow {
            overflow: hidden;
            border-top-left-radius: 0.375rem;
            border-top-right-radius: 0.375rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .card-img-top {
            transition: transform 0.4s ease;
            cursor: pointer;
        }
        .code-card:hover .card-img-top {
            transform: scale(1.05);
        }

        .btn-black-custom {
            background-color: #6366f1;
            color: #ffffff;
            border: 1px solid #6366f1;
            font-weight: bold;
            transition: all 0.2s ease;
        }
        .btn-black-custom:hover {
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

        .qr-container {
            max-width: 200px;
            margin: 0 auto;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            background-color: #ffffff;
        }

        .card {
            background-color: #ffffff;
            color: #333333;
            border: 1px solid #e5e7eb;
        }

        .fb-chat-container {
            position: fixed;
            bottom: 0;
            right: 20px;
            width: 380px; 
            z-index: 1050;
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            background: #ffffff;
            border: 1px solid #dee2e6;
            overflow: hidden;
            display: none;
        }
        
        .fb-chat-header {
            background: #6366f1;
            color: white;
            padding: 12px 15px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #dee2e6;
        }
        .fb-chat-body {
            height: 450px; 
            background: #ffffff;
            display: flex;
            flex-direction: column;
        }
        .fb-messages-area {
            flex-grow: 1;
            overflow-y: auto;
            padding: 12px;
            background: #f9fafb;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .fb-msg-bubble {
            max-width: 85%;
            padding: 8px 12px;
            border-radius: 15px;
            font-size: 13px;
        }
        .fb-msg-admin {
            background: #e5e7eb;
            color: #1f2937;
            align-self: flex-start;
            border-bottom-left-radius: 3px;
        }
        .fb-msg-user {
            background: #6366f1;
            color: #ffffff;
            align-self: flex-end;
            border-bottom-right-radius: 3px;
        }
        .modal-content {
            background-color: #ffffff !important;
            color: #333333 !important;
            border: 1px solid #dee2e6 !important;
        }
        .modal-header, .modal-footer {
            border-color: #f1f3f5 !important;
        }
        .form-control {
            background-color: #f9fafb !important;
            border: 1px solid #d1d5db !important;
            color: #1f2937 !important;
        }
        .form-control:focus {
            background-color: #ffffff !important;
            color: #1f2937 !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15) !important;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light shadow-sm">
        <div class="container">
            <div>
                <a class="navbar-brand fw-bold" href="index.php"><span style="color: #6366f1;">CodeShare</span> PH</a>
                <span class="text-muted small d-block d-sm-inline ms-sm-2 fw-semibold">DEVELOPED BY GROUP</span>
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
                    <li class="nav-item"><a class="nav-link active fw-bold" style="color: #6366f1 !important;" href="index.php">Home</a></li>
                    
                    <li class="nav-item">
                        <?php if (isset($_SESSION['user']) || isset($_SESSION['admin'])): ?>
                            <?php 
                                $unread_count = 0;
                                if (isset($_SESSION['user'])) {
                                    $u_name = $_SESSION['user'];
                                    $u_q = mysqli_query($conn, "SELECT id FROM users WHERE username = '$u_name'");
                                    if ($u_q && mysqli_num_rows($u_q) > 0) {
                                        $u_d = mysqli_fetch_assoc($u_q);
                                        $my_id = $u_d['id'];
                                        if (!isset($_GET['open_chat'])) {
                                            $count_unread = mysqli_query($conn, "SELECT COUNT(*) as total FROM messages WHERE receiver_id = '$my_id' AND sender_id = 1");
                                            if ($count_unread) {
                                                $c_res = mysqli_fetch_assoc($count_unread);
                                                $unread_count = $c_res['total'];
                                            }
                                        }
                                    }
                                }
                            ?>
                            <a class="nav-link fw-bold position-relative" href="#" id="openUserChatBtn">
                                <i class="bi bi-chat-dots-fill"></i> Message
                                <?php if ($unread_count > 0): ?>
                                    <span class="position-absolute top-25 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 9px;">
                                        <?php echo $unread_count; ?>
                                        <span class="visually-hidden">unread messages</span>
                                    </span>
                                <?php endif; ?>
                            </a>
                        <?php else: ?>
                            <a class="nav-link fw-bold" href="#" data-bs-toggle="modal" data-bs-target="#loginRequiredModal"><i class="bi bi-chat-dots-fill"></i> Message</a>
                        <?php endif; ?>
                    </li>

                    <?php if (isset($_SESSION['user']) || isset($_SESSION['admin'])): ?>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" href="settings.php"><i class="bi bi-gear-fill"></i> Settings</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['admin'])): ?>
                        <li class="nav-item"><a class="nav-link fw-bold" href="system_files.php"><i class="bi bi-folder-fill"></i> System Files</a></li>
                        <li class="nav-item"><a class="nav-link fw-bold" href="admin_requests.php"><i class="bi bi-bell-fill"></i> Download Requests</a></li>
                    <?php endif; ?>
                </ul>
                
                <div class="d-flex gap-2 align-items-center">
                    <?php if (isset($_SESSION['admin'])): ?>
                        <span class="text-muted small me-2 fw-semibold"><i class="bi bi-shield-fill text-indigo"></i> Admin Mode</span>
                        <a href="admin_dashboard.php" class="btn btn-black-custom btn-sm fw-bold">Dashboard</a>
                        <a href="logout.php" class="btn btn-outline-custom btn-sm fw-bold">Log Out</a>
                    <?php elseif (isset($_SESSION['user'])): ?>
                        <span class="text-muted small me-2"><i class="bi bi-person-fill text-dark"></i> <?php echo htmlspecialchars($_SESSION['user']); ?></span>
                        <a href="logout.php" class="btn btn-outline-custom btn-sm fw-bold">Log Out</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-custom btn-sm fw-bold">Log In</a>
                        <a href="signup.php" class="btn btn-black-custom btn-sm fw-bold">Create Account</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <header class="hero-section">
        <video autoplay muted loop playsinline class="bg-video">
            <source src="background.mp4" type="video/mp4">
            Your browser does not support HTML5 video.
        </video>
        
        <div class="hero-overlay"></div>

        <div class="container">
            <h1 class="display-4 fw-bold text-white">SOURCE CODE <span style="color: #818cf8;">HUB</span></h1>
            <p class="lead text-white-50">Explore and download quality source codes seamlessly.</p>
            
            <div class="d-flex flex-column align-items-center gap-2 mt-4">
                <a href="#browse" class="btn btn-black-custom btn-lg fw-bold shadow-sm rounded-2 text-white" style="width: 220px;">CLICK ME!</a>
                <a href="https://www.facebook.com/gfrey.frey77" target="_blank" class="btn btn-outline-light btn-lg fw-bold d-flex align-items-center justify-content-center rounded-2" style="width: 220px;">
                    <i class="bi bi-facebook me-2"></i> Visit Facebook Page
                </a>
            </div>
        </div>
    </header>

    <main class="container my-5" id="browse">
        <?php if(isset($_GET['status']) && $_GET['status'] == 'requested'): ?>
            <div class="alert alert-success border border-success alert-dismissible fade show text-center fw-bold shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> Please message the developer by clicking the Facebook Page link at the top. Don't forget to send your reference number for confirmation. THANK YOU!!!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <h2 class="text-center mb-4 fw-bold text-dark"><i class="bi bi-calendar-event me-2"></i> Available Source Codes</h2>
        
        <div class="row g-4">
            <?php if (!$get_codes || mysqli_num_rows($get_codes) == 0): ?>
                <div class="col-12 text-center py-5 text-muted">
                    <i class="bi bi-folder-x display-1"></i>
                    <p class="mt-3 fs-5">No source codes uploaded at the moment.</p>
                </div>
            <?php else: ?>
                <?php while($row = mysqli_fetch_assoc($get_codes)): ?>
                <?php 
                    $price_value = (!empty($row['price'])) ? htmlspecialchars($row['price']) : 'Free';
                    $is_free = (strtolower($price_value) == 'free' || $price_value == '0');
                    
                    $img_src = "images/me.jpg";
                    if (!empty($row['image_path']) && file_exists($row['image_path'])) {
                        $img_src = $row['image_path'];
                    } elseif (!empty($row['image']) && file_exists("images/" . $row['image'])) {
                        $img_src = "images/" . $row['image'];
                    } elseif (!empty($row['image']) && file_exists("uploads/thumbnails/" . $row['image'])) {
                        $img_src = "uploads/thumbnails/" . $row['image'];
                    }
                ?>
                <div class="col-md-4">
                    <div class="card h-100 code-card shadow-sm">
                        <div class="img-container-overflow">
                            <img src="<?php echo $img_src; ?>" class="card-img-top preview-img" alt="<?php echo htmlspecialchars($row['title']); ?>" style="height: 160px; object-fit: cover;" data-bs-toggle="modal" data-bs-target="#imageLightboxModal" data-img="<?php echo $img_src; ?>" data-title="<?php echo htmlspecialchars($row['title']); ?>">
                        </div>
                        
                        <div class="card-body bg-white text-dark">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge text-white" style="background-color: #6366f1;"><?php echo htmlspecialchars($row['language']); ?></span>
                                <span class="badge bg-light border text-dark"><i class="bi bi-tags-fill me-1"></i> <?php echo $price_value; ?></span>
                            </div>

                            <h5 class="card-title fw-bold text-dark"><?php echo htmlspecialchars($row['title']); ?></h5>
                            <p class="card-text text-muted small"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                            <hr style="border-color: #e5e7eb;">
                            <p class="mb-0 text-muted" style="font-size: 11px;">Uploaded by: <strong class="text-dark">ADMIN</strong></p>
                        </div>
                        <div class="card-footer bg-white border-0 pb-3">
                            <?php if (isset($_SESSION['admin'])): ?>
                                <a href="uploads/<?php echo $row['zip_file']; ?>" class="btn btn-black-custom w-100 fw-bold rounded-2" download>
                                    <i class="bi bi-file-earmark-zip-fill"></i> Download Source Code (Admin)
                                </a>
                            <?php elseif (isset($_SESSION['user'])): ?>
                                <?php 
                                $current_user = $_SESSION['user'];
                                $code_id = $row['id'];
                                $req_check = mysqli_query($conn, "SELECT status FROM download_requests WHERE user_username = '$current_user' AND code_id = '$code_id'");
                                $req_data = mysqli_fetch_assoc($req_check);
                                ?>
                                <?php if (!$req_data): ?>
                                    <?php if ($is_free): ?>
                                        <a href="request_download.php?code_id=<?php echo $row['id']; ?>" class="btn btn-black-custom w-100 fw-bold rounded-2">
                                            <i class="bi bi-send-fill"></i> Request Admin to Confirm Download
                                        </a>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-black-custom w-100 fw-bold rounded-2" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#gcashPaymentModal"
                                                data-id="<?php echo $row['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($row['title']); ?>"
                                                data-price="<?php echo $price_value; ?>">
                                            <i class="bi bi-wallet2 me-1"></i> Pay via GCash & Request Download
                                        </button>
                                    <?php endif; ?>
                                <?php elseif ($req_data['status'] == 'Pending'): ?>
                                    <button class="btn btn-outline-secondary w-100 fw-bold rounded-2" disabled>
                                        <i class="bi bi-hourglass-split"></i> Waiting for Admin Approval...
                                    </button>
                                <?php elseif ($req_data['status'] == 'Approved'): ?>
                                    <a href="uploads/<?php echo $row['zip_file']; ?>" class="btn btn-success w-100 fw-bold rounded-2" download>
                                        <i class="bi bi-file-earmark-zip-fill"></i> Download Approved! Click Here
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <button type="button" class="btn btn-black-custom w-100 fw-bold rounded-2" data-bs-toggle="modal" data-bs-target="#loginRequiredModal">
                                    <i class="bi bi-lock-fill"></i> Download Source Code
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </main>

    <div class="modal fade" id="imageLightboxModal" tabindex="-1" aria-labelledby="lightboxModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-dark fw-bold" id="lightboxModalLabel">Project Output Screen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-3">
                    <img id="lightboxImage" src="" class="img-fluid rounded shadow-sm" alt="Zoomed Screenshot" style="max-height: 80vh; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>

    <div id="fbUserChatWidget" class="fb-chat-container">
        <div class="fb-chat-header">
            <span><i class="bi bi-chat-dots-fill me-1"></i> Chat with Admin</span>
            <button type="button" class="btn-close btn-close-white btn-sm" id="closeUserChatX" aria-label="Close"></button>
        </div>
        <div class="fb-chat-body">
            <div class="fb-messages-area" id="chatScrollArea">
            </div>
            
            <div class="p-2 bg-white border-top">
                <div id="imagePreviewContainer" class="px-2 pb-2 d-none">
                    <div class="position-relative d-inline-block">
                        <img id="previewThumb" src="" alt="Preview" class="rounded border" style="width: 55px; height: 55px; object-fit: cover;">
                        <button type="button" id="removeImageBtn" class="btn btn-danger btn-sm rounded-circle position-absolute top-0 start-100 translate-middle p-0 d-flex align-items-center justify-content-center shadow-sm" style="width: 20px; height: 20px; font-size: 10px;">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>

                <form id="userChatForm" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
                    <?php 
                    $current_user_id = 0;
                    if (isset($_SESSION['user'])) {
                        $username = $_SESSION['user'];
                        $user_q = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
                        if ($user_q && mysqli_num_rows($user_q) > 0) {
                            $u_res = mysqli_fetch_assoc($user_q);
                            $current_user_id = $u_res['id'];
                        }
                    }
                    ?>
                    <input type="hidden" name="receiver_id" value="1">
                    <input type="hidden" name="sender_id" value="<?php echo $current_user_id; ?>">
                    
                    <label for="chat_file_input" class="mb-0" style="cursor: pointer; font-size: 20px; color: #6366f1;" title="Attach an Image">
                        <i class="bi bi-plus-circle-fill"></i>
                    </label>
                    <input type="file" id="chat_file_input" name="chat_file" accept="image/*" style="display: none;">

                    <div class="flex-grow-1 bg-light rounded-pill px-3 py-1 border d-flex align-items-center">
                        <input type="text" id="userMessageInput" name="message" class="form-control form-control-sm border-0 bg-transparent shadow-none text-dark" placeholder="Aa" autocomplete="off" style="font-size: 13px;">
                    </div>

                    <button type="submit" class="btn btn-sm rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm" style="background-color: #6366f1; width: 34px; height: 34px;">
                        <i class="bi bi-send-fill" style="font-size: 12px;"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="gcashPaymentModal" tabindex="-1" aria-labelledby="gcashModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header text-dark border-bottom">
                    <h5 class="modal-title fw-bold" id="gcashModalTitle"><i class="bi bi-wallet2 me-2" style="color: #6366f1;"></i> GCash Payment Method</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="request_download.php" method="GET">
                    <div class="modal-body p-4 text-center">
                        <p class="text-muted small mb-3">To acquire this premium source code, please scan the QR code below or send your payment to the developer's account:</p>
                        <div class="bg-light rounded p-3 mb-4 text-start border">
                            <div class="small text-muted fw-bold text-uppercase">Selected Project:</div>
                            <div id="gcash-project-title" class="fw-bold text-dark fs-5">Project Title</div>
                            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                <span class="text-muted small fw-bold">Amount Due:</span>
                                <span id="gcash-project-price" class="badge text-white fs-6 fw-bold" style="background-color: #6366f1;">₱0.00</span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="qr-container shadow-sm p-2 mb-2">
                                <img src="gcash.jfif" alt="GCash QR Code" class="img-fluid rounded">
                            </div>
                            <small class="text-muted d-block" style="font-size: 11px;"><i class="bi bi-qr-code-scan"></i> Scan or screenshot this QR code using your GCash App to transfer payment.</small>
                        </div>
                        <div class="card border mb-4 bg-light" style="border-style: dashed;">
                            <div class="card-body py-3">
                                <div class="text-uppercase small fw-bold text-muted mb-1">Or Send to GCash Number:</div>
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <h3 class="fw-bold text-dark mb-0" id="gcashNumber">09945862744</h3> 
                                    <button type="button" class="btn btn-sm btn-outline-dark py-1 px-2 fw-bold" onclick="copyGcashNumber()"><i class="bi bi-clipboard"></i> Copy</button>
                                </div>
                                <div class="fw-semibold text-muted small mt-2">Account Name: MA*Y CE****E L.</div> 
                            </div>
                        </div>
                        <hr>
                        <div class="text-start mt-3">
                            <label class="form-label small fw-bold text-dark mb-1"><i class="bi bi-receipt"></i> Enter GCash Reference Number:</label>
                            <input type="text" name="gcash_ref" class="form-control form-control-lg text-center fw-bold" placeholder="13-Digit Ref No." required pattern="[0-9]{13,}" title="Please ensure your 13-digit GCash Reference Number is correct and complete">
                            <small class="text-muted d-block mt-1 text-center" style="font-size: 11px;">This will be used by the Admin to verify and approve your download request.</small>
                        </div>
                        <input type="hidden" name="code_id" id="gcash-project-id">
                    </div>
                    <div class="modal-footer border-top d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary fw-semibold btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-black-custom fw-bold btn-sm px-4">Submit Download Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="loginRequiredModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="modalTitle"><i class="bi bi-shield-exclamation text-warning me-2"></i> Authentication Required</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-person-fill-lock display-4 mb-3 d-block" style="color: #6366f1;"></i>
                    <h5 class="fw-bold text-dark mb-2">Login Required</h5>
                    <p class="text-muted px-3 small">Sorry, to download our premium zip source codes, you must first log in or create an account on CodeShare PH.</p>
                </div>
                <div class="modal-footer d-flex justify-content-center gap-2 border-top">
                    <a href="login.php" class="btn btn-outline-custom fw-bold px-4">Log In</a>
                    <a href="signup.php" class="btn btn-black-custom fw-bold px-4">Create Account</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-white text-dark text-center py-4 mt-5 border-top">
        <p class="mb-1">&copy; 2026 CodeShare PH. All Rights Reserved.</p>
        <p class="mb-0 text-muted small">Developed with ❤️ by <strong class="text-dark">Developer-Mr Freyy</strong></p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const userChatWidget = document.getElementById('fbUserChatWidget');
        const openUserBtn = document.getElementById('openUserChatBtn');
        const closeUserBtn = document.getElementById('closeUserChatX');
        const currentUserId = <?php echo $current_user_id; ?>;

        function loadUserMessages() {
            if (currentUserId > 0) {
                $.ajax({
                    url: 'get_messages.php',
                    type: 'GET',
                    data: { user_id: currentUserId },
                    success: function(data) {
                        $('#chatScrollArea').html(data);
                    }
                });
            }
        }

        let chatInterval;

        if (openUserBtn) {
            openUserBtn.addEventListener('click', function(e) {
                e.preventDefault();
                userChatWidget.style.display = 'block';
                loadUserMessages();
                chatInterval = setInterval(loadUserMessages, 1500);
                if (!window.location.search.includes('open_chat=true')) {
                    window.history.pushState({}, '', 'index.php?open_chat=true');
                }
                scrollToBottom();
            });
        }

        if (closeUserBtn) {
            closeUserBtn.addEventListener('click', function() {
                userChatWidget.style.display = 'none';
                clearInterval(chatInterval);
                window.history.pushState({}, '', 'index.php');
            });
        }

        function scrollToBottom() {
            var chatArea = document.getElementById('chatScrollArea');
            if(chatArea) {
                chatArea.scrollTop = chatArea.scrollHeight;
            }
        }

        const chatFileInput = document.getElementById('chat_file_input');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        const previewThumb = document.getElementById('previewThumb');
        const removeImageBtn = document.getElementById('removeImageBtn');

        chatFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewThumb.src = e.target.result;
                    imagePreviewContainer.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            }
        });

        removeImageBtn.addEventListener('click', function() {
            chatFileInput.value = '';
            imagePreviewContainer.classList.add('d-none');
            previewThumb.src = '';
        });

        $('#userChatForm').on('submit', function(e) {
            e.preventDefault();
            
            let messageText = $('#userMessageInput').val().trim();
            let hasFile = chatFileInput.files.length > 0;

            if (messageText === '' && !hasFile) {
                return;
            }

            let formData = new FormData(this);

            $.ajax({
                url: 'send_message.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    $('#userMessageInput').val('');
                    chatFileInput.value = '';
                    imagePreviewContainer.classList.add('d-none');
                    previewThumb.src = '';
                    
                    loadUserMessages();
                    setTimeout(scrollToBottom, 200);
                }
            });
        });

        <?php if (isset($_GET['open_chat']) && $_GET['open_chat'] == 'true'): ?>
        window.addEventListener('DOMContentLoaded', (event) => {
            if(userChatWidget) {
                userChatWidget.style.display = 'block';
                loadUserMessages();
                chatInterval = setInterval(loadUserMessages, 1500);
                setTimeout(scrollToBottom, 300);
            }
        });
        <?php endif; ?>

        const gcashPaymentModal = document.getElementById('gcashPaymentModal');
        if (gcashPaymentModal) {
            gcashPaymentModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget; 
                document.getElementById('gcash-project-id').value = button.getAttribute('data-id');
                document.getElementById('gcash-project-title').textContent = button.getAttribute('data-title');
                document.getElementById('gcash-project-price').textContent = button.getAttribute('data-price');
            });
        }

        const imageLightboxModal = document.getElementById('imageLightboxModal');
        if (imageLightboxModal) {
            imageLightboxModal.addEventListener('show.bs.modal', function (event) {
                const imgElement = event.relatedTarget;
                const imgSrc = imgElement.getAttribute('data-img');
                const imgTitle = imgElement.getAttribute('data-title');
                
                document.getElementById('lightboxImage').setAttribute('src', imgSrc);
                document.getElementById('lightboxModalLabel').textContent = imgTitle;
            });
        }

        function copyGcashNumber() {
            var gcashNum = document.getElementById("gcashNumber").innerText;
            navigator.clipboard.writeText(gcashNum).then(function() {
                alert("GCash Number copied successfully: " + gcashNum);
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
