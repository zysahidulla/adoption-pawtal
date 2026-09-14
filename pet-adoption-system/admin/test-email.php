<?php
/**
 * Email Testing & Diagnostic Tool
 * Place this file in your admin/ folder
 * Access it at: http://yoursite.com/admin/test-email.php
 */

require_once '../includes/session.php';
requireAdmin();
require_once '../config/database.php';
require_once '../includes/email-functions.php';

// Handle test email sending
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $test_email = trim($_POST['test_email'] ?? '');
    
    if (!empty($test_email) && filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
        $subject = "Test Email from Pet Adoption System";
        $body = "This is a test email sent at " . date('Y-m-d H:i:s') . "\n\nIf you received this, your email configuration is working correctly!";
        
        // Attempt to send
        $result = sendGmailSMTP($test_email, $subject, $body);
        
        if ($result) {
            $success_msg = "Test email sent successfully to $test_email! Check the inbox (and spam folder).";
        } else {
            $error_msg = "Failed to send test email. Check the error log below for details.";
        }
    } else {
        $error_msg = "Please enter a valid email address.";
    }
}

// Check email logs
$recent_logs = [];
try {
    $stmt = $pdo->query("SELECT * FROM email_logs ORDER BY sent_at DESC LIMIT 10");
    $recent_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $db_error = "Error fetching email logs: " . $e->getMessage();
}

// Read recent error log entries
$error_log_path = __DIR__ . '/../error.log';
$error_log_lines = [];
if (file_exists($error_log_path)) {
    $lines = file($error_log_path);
    $error_log_lines = array_slice($lines, -20); // Last 20 lines
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Diagnostics - Adoption Pawtal Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- Shared Branding --- */
        :root {
            --brand-gradient: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%);
            --primary-color: #FF6B6B; 
            --secondary-color: #FF8E53;
            --text-dark: #2d3436;
            --text-muted: #636e72;
            --bg-soft: #fff9f5;
            --paw-pattern: url("data:image/svg+xml,%3Csvg width='50' height='70' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill='%23FF6B6B' fill-opacity='0.05' d='M22.6,30.1c-5.2,0-9.4-4.2-9.4-9.4s4.2-9.4,9.4-9.4s9.4,4.2,9.4,9.4S27.8,30.1,22.6,30.1z M48.9,21.4 c-5.2,0-9.4-4.2-9.4-9.4s4.2-9.4,9.4-9.4s9.4,4.2,9.4,9.4S54.1,21.4,48.9,21.4z M75.6,30.1c-5.2,0-9.4-4.2-9.4-9.4s4.2-9.4,9.4-9.4 s9.4,4.2,9.4,9.4S80.8,30.1,75.6,30.1z M49.2,33.9c-18.6,0-26.9,14.4-28.1,23.9c-1.4,11.1,13,19.8,28.1,19.8s29.4-8.7,28.1-19.8 C76.1,48.3,67.8,33.9,49.2,33.9z'/%3E%3C/svg%3E");
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--bg-soft);
            background-image: var(--paw-pattern);
            color: var(--text-dark);
            padding-top: 100px;
            padding-bottom: 40px;
        }

        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            padding: 0.8rem 0;
        }
        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            background: var(--brand-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .nav-link { font-weight: 700; color: var(--text-muted) !important; transition: color 0.3s; margin: 0 10px; }
        .nav-link:hover, .nav-link.active { color: var(--primary-color) !important; }
        .btn-logout { background: #fff0f0; color: #dc3545; border-radius: 50px; padding: 8px 20px; font-weight: 700; text-decoration: none; font-size: 0.9rem; transition: all 0.3s; }
        .btn-logout:hover { background: #dc3545; color: white; }

        /* Content Cards */
        .content-card {
            background: white;
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .card-header-custom {
            background: #fff;
            border-bottom: 1px solid #f1f2f6;
            padding: 1.2rem 1.5rem;
            font-weight: 800;
            color: var(--text-dark);
            font-size: 1.1rem;
            display: flex;
            align-items: center;
        }
        .card-header-custom i { margin-right: 10px; color: var(--primary-color); }
        .card-body-custom { padding: 1.5rem; }

        /* Typography & Labels */
        .section-title { font-weight: 800; color: var(--text-dark); font-size: 1.8rem; }
        .info-label { font-weight: 700; color: var(--text-muted); font-size: 0.9rem; }

        /* Buttons */
        .btn-back { color: var(--text-muted); font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; transition: all 0.3s; }
        .btn-back:hover { color: var(--primary-color); transform: translateX(-5px); }

        .btn-gradient {
            background: var(--brand-gradient);
            color: white;
            border: none;
            font-weight: 700;
            padding: 10px 25px;
            border-radius: 10px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(255, 107, 107, 0.4); color: white; }

        /* Table Styles */
        .custom-table thead th {
            background: #f8fafc;
            color: var(--text-muted);
            font-weight: 800;
            text-transform: uppercase;
            font-size: 0.75rem;
            padding: 15px;
            border-bottom: 2px solid #f1f2f6;
            letter-spacing: 0.5px;
        }
        .custom-table tbody td {
            padding: 15px;
            vertical-align: middle;
            font-size: 0.9rem;
            color: var(--text-dark);
            border-bottom: 1px solid #f1f2f6;
        }

        /* Diagnostics Specific */
        .config-table tr td:first-child { background: #f9fafb; font-weight: 700; width: 30%; color: var(--text-muted); }
        .log-entry { font-family: 'Courier New', monospace; font-size: 0.85rem; color: #333; }
        .error-log-container { background: #1e293b; color: #e2e8f0; padding: 1.5rem; border-radius: 10px; max-height: 400px; overflow-y: auto; font-family: monospace; font-size: 0.85rem; }
        .log-line { margin-bottom: 4px; border-bottom: 1px solid #334155; padding-bottom: 2px; }
        
        .badge-sent { background: #dcfce7; color: #166534; padding: 5px 10px; border-radius: 50px; font-weight: 700; font-size: 0.75rem; }
        .badge-failed { background: #fee2e2; color: #991b1b; padding: 5px 10px; border-radius: 50px; font-weight: 700; font-size: 0.75rem; }
    </style>
</head>
<body>
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-shield-cat me-2"></i>Admin Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage-pets.php">Manage Pets</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage-applications.php">Manage Applications</a></li>
                </ul>
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item me-3">
                        <span class="text-muted fw-bold"><i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars(getAdminName()) ?></span>
                    </li>
                    <li class="nav-item"><a class="btn-logout" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container px-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
            <div>
                <a href="dashboard.php" class="btn-back mb-2"><i class="fas fa-arrow-left me-2"></i> Back to Dashboard</a>
                <h1 class="section-title">Email Diagnostics</h1>
            </div>
        </div>

        <?php if (isset($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3">
                <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3">
                <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($db_error)): ?>
            <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0 rounded-3">
                <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($db_error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left Column: Config & Test -->
            <div class="col-lg-6">
                
                <!-- Configuration Check -->
                <div class="content-card">
                    <div class="card-header-custom">
                        <i class="fas fa-cogs"></i> Configuration Check
                    </div>
                    <div class="card-body-custom">
                        <div class="table-responsive">
                            <table class="table table-bordered config-table mb-0">
                                <tr>
                                    <td>SMTP Host</td>
                                    <td>smtp.gmail.com</td>
                                </tr>
                                <tr>
                                    <td>SMTP Port</td>
                                    <td>587 (STARTTLS)</td>
                                </tr>
                                <tr>
                                    <td>Username</td>
                                    <td><?= EMAIL_USERNAME ?></td>
                                </tr>
                                <tr>
                                    <td>Password</td>
                                    <td>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span><?= str_repeat('•', 12) ?></span>
                                            <span class="badge bg-light text-dark border"><?= strlen(EMAIL_PASSWORD) ?> chars</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>From Address</td>
                                    <td><?= EMAIL_FROM_ADDRESS ?></td>
                                </tr>
                                <tr>
                                    <td>From Name</td>
                                    <td><?= EMAIL_FROM_NAME ?></td>
                                </tr>
                                <tr>
                                    <td>Debug Mode</td>
                                    <td><?= DEBUG_MODE ? '<span class="badge bg-warning text-dark">ENABLED</span>' : '<span class="badge bg-secondary">Disabled</span>' ?></td>
                                </tr>
                                <tr>
                                    <td>PHPMailer</td>
                                    <td><?php 
                                        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                                            echo '<span class="badge bg-success"><i class="fas fa-check me-1"></i> Loaded</span>';
                                        } else {
                                            echo '<span class="badge bg-danger"><i class="fas fa-times me-1"></i> Not Found</span>';
                                        }
                                    ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Send Test Email -->
                <div class="content-card">
                    <div class="card-header-custom">
                        <i class="fas fa-paper-plane"></i> Send Test Email
                    </div>
                    <div class="card-body-custom">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Recipient Address</label>
                                <input type="email" name="test_email" class="form-control" placeholder="your-email@example.com" required>
                                <div class="form-text">Enter an email address to receive a test message.</div>
                            </div>
                            <button type="submit" class="btn btn-gradient w-100">
                                <i class="fas fa-envelope me-2"></i> Send Test
                            </button>
                        </form>
                    </div>
                </div>

            </div>

            <!-- Right Column: Logs -->
            <div class="col-lg-6">
                
                <!-- Recent Logs -->
                <div class="content-card">
                    <div class="card-header-custom">
                        <i class="fas fa-history"></i> Recent Email Logs (Last 10)
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($recent_logs)): ?>
                            <div class="table-responsive">
                                <table class="table custom-table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>To</th>
                                            <th>Subject</th>
                                            <th>Status</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_logs as $log): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($log['sent_to']) ?></td>
                                                <td><?= htmlspecialchars($log['subject']) ?></td>
                                                <td>
                                                    <span class="<?= $log['status'] === 'Sent' ? 'badge-sent' : 'badge-failed' ?>">
                                                        <?= $log['status'] ?>
                                                    </span>
                                                </td>
                                                <td class="text-muted small"><?= date('M j, H:i', strtotime($log['sent_at'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i>
                                <p>No email logs found.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Error Log -->
                <div class="content-card">
                    <div class="card-header-custom text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i> System Error Log
                    </div>
                    <div class="card-body-custom">
                        <?php if (!empty($error_log_lines)): ?>
                            <div class="error-log-container">
                                <?php foreach (array_reverse($error_log_lines) as $line): ?>
                                    <div class="log-line"><?= htmlspecialchars($line) ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-3 text-success">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <p class="mb-0">No errors found in log file.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>