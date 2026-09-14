<?php
// admin/login.php
session_start();
require_once '../config/database.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // Get admin from database
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Login successful
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['full_name'];
            
            header('Location: dashboard.php');
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Adoption Pawtal</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --brand-gradient: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%);
            --primary-color: #FF6B6B;
            --text-dark: #2d3436;
            /* New background gradient for the entire page */
            --page-bg-gradient: linear-gradient(135deg, #fcc2d0 0%, #e6d9b0 100%);
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: var(--page-bg-gradient); /* Use the new gradient */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 100%;
            max-width: 900px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            min-height: 550px;
        }

        /* Left Side - Brand Area */
        .brand-side {
            flex: 1;
            background: var(--brand-gradient);
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* Decorative Circle */
        .brand-side::before {
            content: '';
            position: absolute;
            top: -50px;
            left: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        
        .brand-logo {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            text-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .brand-title {
            font-weight: 800;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .brand-text {
            font-size: 1rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        /* Right Side - Form Area */
        .form-side {
            flex: 1;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-title {
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }

        .login-subtitle {
            color: #6c757d;
            margin-bottom: 2rem;
        }

        /* Form Styling */
        .form-floating > .form-control {
            border-radius: 12px;
            border: 2px solid #f1f2f6;
            padding-left: 45px;
            font-weight: 600;
        }
        
        .form-floating > .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(255, 107, 107, 0.1);
        }

        .form-floating > label {
            padding-left: 45px;
        }

        .input-icon {
            position: absolute;
            top: 18px;
            left: 15px;
            z-index: 4;
            color: #adb5bd;
        }

        .form-control:focus + label + .input-icon,
        .form-control:focus ~ .input-icon {
            color: var(--primary-color);
        }

        .btn-login {
            background: var(--brand-gradient);
            border: none;
            color: white;
            font-weight: 700;
            padding: 12px;
            border-radius: 12px;
            font-size: 1.1rem;
            transition: all 0.3s;
            width: 100%;
            margin-top: 1rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 107, 107, 0.3);
            color: white;
        }

        .back-link {
            text-decoration: none;
            color: #6c757d;
            font-weight: 600;
            font-size: 0.9rem;
            transition: color 0.3s;
            display: inline-flex;
            align-items: center;
            margin-top: 2rem;
        }
        
        .back-link:hover { color: var(--primary-color); }

        /* Responsive */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 400px;
                margin: 20px;
            }
            .brand-side {
                padding: 2rem;
                min-height: 200px;
            }
            .form-side {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <!-- Left: Brand Visuals --><div class="brand-side">
            <i class="fas fa-shield-cat brand-logo"></i>
            <h2 class="brand-title">Admin Portal</h2>
            <p class="brand-text">Secure access for Adoption Pawtal staff and administrators.</p>
        </div>

        <!-- Right: Login Form --><div class="form-side">
            <h2 class="login-title">Welcome Back!</h2>
            <p class="login-subtitle">Please login to access the dashboard.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger rounded-3 shadow-sm border-0 mb-4">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                
                <div class="mb-3 position-relative">
                    <i class="fas fa-user input-icon"></i>
                    <div class="form-floating">
                        <input type="text" name="username" class="form-control" id="floatingUser" placeholder="Username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
                        <label for="floatingUser">Username</label>
                    </div>
                </div>

                <div class="mb-4 position-relative">
                    <i class="fas fa-lock input-icon"></i>
                    <div class="form-floating">
                        <input type="password" name="password" class="form-control" id="floatingPass" placeholder="Password" required>
                        <label for="floatingPass">Password</label>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-login">
                        Sign In <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>

            </form>

            <div class="text-center">
                <a href="../public/index.php" class="back-link">
                    <i class="fas fa-arrow-left me-2"></i> Return to Website
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>