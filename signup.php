<?php
include('db.php');
session_start();
$message = "";

if (isset($_POST['signup'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $firstname = mysqli_real_escape_string($conn, trim($_POST['firstname']));
    $lastname = mysqli_real_escape_string($conn, trim($_POST['lastname']));
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));
    $school_attended = mysqli_real_escape_string($conn, trim($_POST['school_attended']));
    $mobile_email = mysqli_real_escape_string($conn, trim($_POST['mobile_email']));
    $password = trim($_POST['password']);
    
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);
    $special   = preg_match('@[^\w]@', $password);

    if(!$uppercase || !$lowercase || !$number || !$special || strlen($password) < 8) {
        $message = "<div class='alert alert-danger text-center fw-bold small py-2'>⚠️ Password must have uppercase, lowercase, number, special char, & 8+ chars.</div>";
    } else {
        $check_user = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
        
        if (mysqli_num_rows($check_user) > 0) {
            $message = "<div class='alert alert-danger text-center fw-bold small py-2'>⚠️ This username is already taken!</div>";
        } else {
            $encrypted_password = md5($password);
            $query = "INSERT INTO users (username, password, firstname, lastname, address, school_attended, mobile_email, status) VALUES ('$username', '$encrypted_password', '$firstname', '$lastname', '$address', '$school_attended', '$mobile_email', 'pending')";
            
            if (mysqli_query($conn, $query)) {
                $message = "<div class='alert alert-success text-center fw-bold small py-2'>
                                🎉 Registered successfully!<br>
                                <span class='fw-normal' style='font-size: 11px;'>Please wait for Admin approval.</span>
                            </div>";
            } else {
                $message = "<div class='alert alert-danger text-center fw-bold small py-2'>An error occurred while saving.</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - CodeShare PH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body, html {
            height: 100vh;
            margin: 0;
            background-color: #ffffff;
            color: #212529;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        body::-webkit-scrollbar, html::-webkit-scrollbar, .form-side::-webkit-scrollbar {
            display: none;
        }

        .split-screen {
            display: flex;
            height: 100vh;
            width: 100vw;
        }

        .form-side {
            flex: 1;
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-y: auto;
            height: 100vh;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .signup-card {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .video-side {
            flex: 1.2;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .bg-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 1;
        }
        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2;
        }
        .video-content {
            position: relative;
            z-index: 3;
            text-align: center;
            padding: 20px;
        }

        .form-control {
            background-color: #f8f9fa;
            border-color: #ced4da;
            color: #212529;
            font-size: 13.5px;
        }
        .form-control:focus {
            background-color: #ffffff;
            border-color: #6366f1;
            color: #212529;
            box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25);
        }
        .form-control::placeholder {
            color: #6c757d !important;
            opacity: 1;
        }

        .btn-primary-custom {
            background-color: #6366f1;
            color: #ffffff;
            font-weight: bold;
            border: none;
        }
        .btn-primary-custom:hover {
            background-color: #4f46e5;
            color: #ffffff;
        }

        .clear-helper-text {
            color: #dc3545 !important;
            font-size: 11px !important;
            font-weight: 700;
            margin-top: 4px;
        }

        @media (max-width: 992px) {
            .split-screen {
                flex-direction: column;
                overflow-y: auto;
            }
            .video-side {
                display: none;
            }
            .form-side {
                height: auto;
                min-height: 100vh;
            }
        }
    </style>
</head>
<body>

    <div class="split-screen">
        <div class="form-side">
            <div class="signup-card">
                <div class="mb-3 text-start">
                    <h3 class="fw-bold text-dark mb-1">Create Account</h3>
                    <p class="text-muted small">Enter your credentials to register your account.</p>
                </div>

                <?php echo $message; ?>

                <form action="signup.php" method="POST">
                    <div class="mb-2">
                        <label for="username" class="form-label text-dark small fw-semibold">Username</label>
                        <input type="text" id="username" name="username" class="form-control form-control-sm" placeholder="Enter desired username" required autocomplete="off">
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-dark small fw-semibold">Name</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="text" name="firstname" class="form-control form-control-sm" placeholder="First name" required autocomplete="off">
                            </div>
                            <div class="col-6">
                                <input type="text" name="lastname" class="form-control form-control-sm" placeholder="Last name" required autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="address" class="form-label text-dark small fw-semibold">Address</label>
                        <input type="text" id="address" name="address" class="form-control form-control-sm" placeholder="Enter your address" required autocomplete="off">
                    </div>

                    <div class="mb-2">
                        <label for="school_attended" class="form-label text-dark small fw-semibold">School Attended</label>
                        <input type="text" id="school_attended" name="school_attended" class="form-control form-control-sm" placeholder="Enter school attended" required autocomplete="off">
                    </div>

                    <div class="mb-2">
                        <label for="mobile_email" class="form-label text-dark small fw-semibold">Mobile number or email</label>
                        <input type="text" id="mobile_email" name="mobile_email" class="form-control form-control-sm" placeholder="Mobile number or email" required autocomplete="off">
                        <div class="clear-helper-text">WAIT FOR THE CONFIRMATION OF ADMIN TO LOG-IN</div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label text-dark small fw-semibold">Password</label>
                        <div class="input-group input-group-sm">
                            <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                            <button class="btn btn-outline-secondary text-dark" type="button" id="togglePassword" style="border-color: #ced4da; background-color: #f8f9fa;">
                                <i class="bi bi-eye-slash" id="eyeIcon"></i>
                            </button>
                        </div>
                        <div class="form-text text-muted" style="font-size: 10.5px;">
                            Must be 8+ chars with uppercase, number, & special char.
                        </div>
                    </div>
                    
                    <button type="submit" name="signup" class="btn btn-primary-custom w-100 fw-bold py-2 shadow-sm" style="font-size: 13.5px;">Register Account</button>
                </form>

                <div class="text-center mt-3" style="font-size: 13.5px;">
                    <p class="mb-1 text-muted">Already have an account? <a href="login.php" class="text-dark fw-bold text-decoration-underline">Log In</a></p>
                    <p class="mb-0"><a href="index.php" class="text-muted text-decoration-none">← Return to Home</a></p>
                </div>
            </div>
        </div>

        <div class="video-side">
            <video autoplay muted loop class="bg-video">
                <source src="121.mp4" type="video/mp4">
                Your browser does not support HTML5 video.
            </video>
            <div class="video-overlay"></div>
            <div class="video-content">
                <h1 class="display-5 fw-bold text-white mb-2" style="text-shadow: 0 4px 15px rgba(0,0,0,0.8);">CodeShare PH</h1>
                <p class="text-light fs-6 fw-light mb-0" style="text-shadow: 0 2px 10px rgba(0,0,0,0.8);">Explore, download, and share source codes seamlessly.</p>
            </div>
        </div>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            if (type === 'text') {
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            } else {
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
