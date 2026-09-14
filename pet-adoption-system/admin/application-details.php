<?php
// admin/application-details.php
require_once '../includes/session.php';
requireAdmin();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/email-functions.php';

$application_id = $_GET['id'] ?? '';

if (!$application_id) {
    header('Location: manage-applications.php');
    exit();
}

// Get application details with all related info
$stmt = $pdo->prepare("
    SELECT aa.*, 
           a.*,
           CONCAT(a.first_name, ' ', a.last_name) as adopter_name,
           p.*
    FROM adoption_applications aa
    JOIN adopters a ON aa.adopter_id = a.adopter_id
    JOIN pets p ON aa.animal_id = p.animal_id
    WHERE aa.application_id = ?
");
$stmt->execute([$application_id]);
$app = $stmt->fetch();

if (!$app) {
    $_SESSION['error'] = "Application not found.";
    header('Location: manage-applications.php');
    exit();
}

// Get email log
$email_logs = getEmailLog($pdo, $application_id);

// Get valid status transitions
$valid_transitions = getAvailableStatusTransitions($app['status']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application #<?= $application_id ?> - Adoption Pawtal Admin</title>
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
            padding-top: 80px;
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
        .info-group { margin-bottom: 1rem; }
        .info-label { font-size: 0.85rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; letter-spacing: 0.5px; display: block; margin-bottom: 4px; }
        .info-value { font-weight: 600; color: var(--text-dark); font-size: 1rem; }
        .info-value a { color: var(--primary-color); text-decoration: none; }

        /* Status Badge */
        .status-badge-lg {
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: inline-block;
        }
        
        /* Status Colors */
        .status-Review { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        .status-Interview { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
        .status-Accepted { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
        .status-Rejected { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; }

        /* Pet Image */
        .pet-hero-img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        /* Timelines & Logs */
        .timeline { border-left: 2px solid #f1f2f6; padding-left: 20px; margin-left: 5px; }
        .timeline-item { position: relative; margin-bottom: 1.5rem; }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -26px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--primary-color);
            border: 2px solid white;
            box-shadow: 0 0 0 2px #f1f2f6;
        }
        .log-time { font-size: 0.8rem; color: var(--text-muted); }
        .log-title { font-weight: 700; font-size: 0.95rem; }

        /* Buttons */
        .btn-back { color: var(--text-muted); font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; transition: all 0.3s; }
        .btn-back:hover { color: var(--primary-color); transform: translateX(-5px); }
        
        .btn-action { border-radius: 10px; font-weight: 700; padding: 10px 20px; border: none; transition: all 0.3s; }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        
        .btn-approve { background: #10b981; color: white; }
        .btn-reject { background: #ef4444; color: white; }
        .btn-interview { background: #3b82f6; color: white; }
        .btn-email { background: #f59e0b; color: white; }
        .btn-reopen { background: #6366f1; color: white; }

        .section-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }
        .app-id { color: var(--text-muted); font-size: 1rem; font-weight: 600; }
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
                    <li class="nav-item"><a class="nav-link active" href="manage-applications.php">Manage Applications</a></li>
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

    <div class="container-fluid px-4">
        
        <!-- Header Area -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <a href="manage-applications.php" class="btn-back mb-2"><i class="fas fa-arrow-left me-2"></i> Back to List</a>
                <h1 class="section-title">Application Details</h1>
                <span class="app-id">ID: #<?= htmlspecialchars($application_id) ?> • Submitted <?= formatDate($app['application_date']) ?></span>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3">
                <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3">
                <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left Column: Status & Main Info -->
            <div class="col-lg-8">
                
                <!-- 1. Status & Actions Card -->
                <div class="content-card">
                    <div class="card-header-custom">
                        <i class="fas fa-tasks"></i> Status & Actions
                    </div>
                    <div class="card-body-custom">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center text-md-start mb-3 mb-md-0">
                                <span class="info-label mb-2">Current Status</span>
                                <?php 
                                    // Fallback for status badge class if function returns simple string
                                    $statusClass = 'bg-light text-dark';
                                    if (strpos($app['status'], 'Review') !== false) $statusClass = 'status-Review';
                                    elseif (strpos($app['status'], 'Interview') !== false) $statusClass = 'status-Interview';
                                    elseif ($app['status'] == 'Accepted') $statusClass = 'status-Accepted';
                                    elseif ($app['status'] == 'Rejected') $statusClass = 'status-Rejected';
                                ?>
                                <span class="status-badge-lg <?= $statusClass ?>"><?= htmlspecialchars($app['status']) ?></span>
                            </div>
                            <div class="col-md-8">
                                <span class="info-label mb-2">Available Actions</span>
                                <div class="d-flex gap-2 flex-wrap">
                                    <?php foreach ($valid_transitions as $new_status): ?>
                                        <form method="POST" action="update-status.php">
                                            <input type="hidden" name="application_id" value="<?= $application_id ?>">
                                            <input type="hidden" name="new_status" value="<?= $new_status ?>">
                                            <?php
                                                $btnClass = 'btn-secondary';
                                                $icon = 'fa-arrow-right';
                                                if ($new_status == 'Accepted') { $btnClass = 'btn-approve'; $icon = 'fa-check'; }
                                                elseif ($new_status == 'Rejected') { $btnClass = 'btn-reject'; $icon = 'fa-times'; }
                                                elseif ($new_status == 'For Interview') { $btnClass = 'btn-interview'; $icon = 'fa-comments'; }
                                                elseif ($new_status == 'For Review') { $btnClass = 'btn-reopen'; $icon = 'fa-undo'; }
                                            ?>
                                            <button type="submit" class="btn btn-action <?= $btnClass ?>">
                                                <i class="fas <?= $icon ?> me-1"></i> <?= $new_status === 'For Review' ? 'Reopen' : $new_status ?>
                                            </button>
                                        </form>
                                    <?php endforeach; ?>
                                    
                                    <button type="button" class="btn btn-action btn-email" data-bs-toggle="modal" data-bs-target="#emailModal">
                                        <i class="fas fa-envelope me-1"></i> Send Email
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Pet Information Card -->
                <div class="content-card">
                    <div class="card-header-custom">
                        <i class="fas fa-paw"></i> Pet Information
                    </div>
                    <div class="card-body-custom">
                        <div class="row">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <?php if ($app['photo_path'] && file_exists('../' . $app['photo_path'])): ?>
                                    <img src="../<?= htmlspecialchars($app['photo_path']) ?>" class="pet-hero-img" alt="<?= htmlspecialchars($app['pet_name']) ?>">
                                <?php else: ?>
                                    <div class="bg-light rounded-4 d-flex align-items-center justify-content-center" style="height: 250px; font-size: 4rem;">
                                        <?= getAnimalIcon($app['animal_type']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <h3 class="fw-bold text-dark mb-3"><?= htmlspecialchars($app['pet_name']) ?></h3>
                                <div class="row g-3">
                                    <div class="col-6 col-md-4">
                                        <div class="info-group">
                                            <span class="info-label">Type</span>
                                            <span class="info-value"><?= htmlspecialchars($app['animal_type']) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="info-group">
                                            <span class="info-label">Breed</span>
                                            <span class="info-value"><?= htmlspecialchars($app['breed']) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="info-group">
                                            <span class="info-label">Age</span>
                                            <span class="info-value"><?= htmlspecialchars($app['pet_age']) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="info-group">
                                            <span class="info-label">Gender</span>
                                            <span class="info-value"><?= formatSex($app['sex']) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="info-group">
                                            <span class="info-label">Size</span>
                                            <span class="info-value"><?= htmlspecialchars($app['pet_size']) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="info-group">
                                            <span class="info-label">Pet Status</span>
                                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($app['adoption_status']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!empty($app['description'])): ?>
                                    <div class="mt-3 p-3 bg-light rounded-3">
                                        <span class="info-label">Description</span>
                                        <p class="mb-0 small text-muted"><?= nl2br(htmlspecialchars($app['description'])) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Applicant & Household Card -->
                <div class="content-card">
                    <div class="card-header-custom">
                        <i class="fas fa-user"></i> Applicant Details
                    </div>
                    <div class="card-body-custom">
                        <div class="row g-4">
                            <!-- Contact Info -->
                            <div class="col-md-6">
                                <h6 class="fw-bold text-primary mb-3">Contact Information</h6>
                                <div class="info-group">
                                    <span class="info-label">Full Name</span>
                                    <span class="info-value fs-5"><?= htmlspecialchars($app['adopter_name']) ?></span>
                                </div>
                                <div class="info-group">
                                    <span class="info-label">Email Address</span>
                                    <span class="info-value"><a href="mailto:<?= htmlspecialchars($app['email']) ?>"><?= htmlspecialchars($app['email']) ?></a></span>
                                </div>
                                <div class="info-group">
                                    <span class="info-label">Phone Number</span>
                                    <span class="info-value"><a href="tel:<?= htmlspecialchars($app['phone']) ?>"><?= formatPhone($app['phone']) ?></a></span>
                                </div>
                                <div class="info-group">
                                    <span class="info-label">Address</span>
                                    <span class="info-value">
                                        <?= htmlspecialchars($app['address']) ?><br>
                                        <?= htmlspecialchars($app['city']) ?>, <?= htmlspecialchars($app['postal_code']) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Household Info -->
                            <div class="col-md-6">
                                <h6 class="fw-bold text-primary mb-3">Household & Lifestyle</h6>
                                <div class="info-group">
                                    <span class="info-label">Home Type</span>
                                    <span class="info-value"><?= htmlspecialchars($app['household_type']) ?></span>
                                </div>
                                <div class="info-group">
                                    <span class="info-label">Other Pets</span>
                                    <span class="info-value"><?= $app['has_other_pets'] ? 'Yes' : 'No' ?></span>
                                    <?php if ($app['has_other_pets'] && !empty($app['other_pets_details'])): ?>
                                        <div class="small text-muted fst-italic mt-1">"<?= htmlspecialchars($app['other_pets_details']) ?>"</div>
                                    <?php endif; ?>
                                </div>
                                <div class="info-group">
                                    <span class="info-label">Children</span>
                                    <span class="info-value"><?= $app['has_children'] ? 'Yes' : 'No' ?></span>
                                    <?php if ($app['has_children'] && !empty($app['children_ages'])): ?>
                                        <div class="small text-muted fst-italic mt-1">Ages: <?= htmlspecialchars($app['children_ages']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="info-group">
                                    <span class="info-label">Experience</span>
                                    <span class="info-value"><?= htmlspecialchars($app['experience_level']) ?></span>
                                </div>
                            </div>

                            <!-- Reason -->
                            <div class="col-12">
                                <div class="p-4 bg-light rounded-4 border border-light">
                                    <span class="info-label mb-2"><i class="fas fa-quote-left me-2"></i>Reason for Adoption</span>
                                    <p class="mb-0" style="font-size: 1.05rem; line-height: 1.6;"><?= nl2br(htmlspecialchars($app['reason_for_adoption'])) ?></p>
                                </div>
                            </div>

                            <!-- References -->
                             <div class="col-12">
                                <h6 class="fw-bold text-primary mb-3 mt-2">References</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-white h-100">
                                            <div class="badge bg-light text-dark mb-2">Reference 1</div>
                                            <?php if (!empty($app['reference1_name'])): ?>
                                                <div class="fw-bold"><?= htmlspecialchars($app['reference1_name']) ?></div>
                                                <div class="small text-muted"><?= htmlspecialchars($app['reference1_relationship']) ?></div>
                                                <div class="mt-2"><a href="tel:<?= htmlspecialchars($app['reference1_phone']) ?>" class="text-decoration-none"><i class="fas fa-phone me-1"></i> <?= formatPhone($app['reference1_phone']) ?></a></div>
                                            <?php else: ?>
                                                <div class="text-muted fst-italic">Not provided</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-white h-100">
                                            <div class="badge bg-light text-dark mb-2">Reference 2</div>
                                            <?php if (!empty($app['reference2_name'])): ?>
                                                <div class="fw-bold"><?= htmlspecialchars($app['reference2_name']) ?></div>
                                                <div class="small text-muted"><?= htmlspecialchars($app['reference2_relationship']) ?></div>
                                                <div class="mt-2"><a href="tel:<?= htmlspecialchars($app['reference2_phone']) ?>" class="text-decoration-none"><i class="fas fa-phone me-1"></i> <?= formatPhone($app['reference2_phone']) ?></a></div>
                                            <?php else: ?>
                                                <div class="text-muted fst-italic">Not provided</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Notes & History -->
            <div class="col-lg-4">
                
                <!-- Internal Notes -->
                <div class="content-card">
                    <div class="card-header-custom">
                        <i class="fas fa-sticky-note"></i> Internal Notes
                    </div>
                    <div class="card-body-custom">
                        <form id="notesForm" method="POST" action="save-notes.php">
                            <input type="hidden" name="application_id" value="<?= $application_id ?>">
                            <textarea name="notes" id="notesTextarea" class="form-control mb-3" rows="6" placeholder="Add private notes about this application..."><?= htmlspecialchars($app['notes'] ?? '') ?></textarea>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted fst-italic">
                                    <?php if(!empty($app['updated_at'])): ?>
                                        Updated: <?= formatDateTime($app['updated_at']) ?>
                                    <?php endif; ?>
                                </small>
                                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Save Notes</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Email History -->
                <div class="content-card">
                    <div class="card-header-custom">
                        <i class="fas fa-history"></i> Communication Log
                    </div>
                    <div class="card-body-custom">
                        <?php if (!empty($email_logs)): ?>
                            <div class="timeline">
                                <?php foreach ($email_logs as $log): ?>
                                    <div class="timeline-item">
                                        <div class="log-time mb-1"><?= formatDateTime($log['sent_at']) ?></div>
                                        <div class="log-title mb-1">
                                            <?= formatEmailType($log['email_type']) ?>
                                            <span class="badge <?= $log['status'] === 'Sent' ? 'bg-success' : 'bg-danger' ?> ms-1" style="font-size: 0.6rem;"><?= $log['status'] ?></span>
                                        </div>
                                        <div class="small text-muted">Subject: <?= htmlspecialchars($log['subject']) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i>
                                <p class="small mb-0">No emails sent yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Email Modal -->
    <div class="modal fade" id="emailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <form method="POST" action="send-email.php">
                    <div class="modal-header bg-light border-bottom-0">
                        <h5 class="modal-title fw-bold">Compose Email</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-info d-flex align-items-center mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <div>To: <strong><?= htmlspecialchars($app['adopter_name']) ?></strong> &lt;<?= htmlspecialchars($app['email']) ?>&gt;</div>
                        </div>

                        <input type="hidden" name="application_id" value="<?= $application_id ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Template</label>
                            <select name="email_type" id="emailTemplate" class="form-select rounded-3" required>
                                <option value="">Select a template...</option>
                                <option value="interview_request">Interview Request</option>
                                <option value="acceptance">Acceptance Letter</option>
                                <option value="rejection">Rejection Notice</option>
                                <option value="custom">Custom Email</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Subject</label>
                            <input type="text" name="subject" id="emailSubject" class="form-control rounded-3" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Message Body</label>
                            <textarea name="message" id="emailMessage" class="form-control rounded-3" rows="8" required></textarea>
                            <div class="form-text mt-2"><i class="fas fa-magic me-1"></i> Supported tags: {pet_name}, {adopter_name}, {application_id}</div>
                        </div>

                        <div id="interviewDateDiv" class="mb-3 p-3 bg-light rounded-3 border" style="display:none;">
                            <label class="form-label fw-bold text-primary">Interview Date & Time</label>
                            <input type="datetime-local" name="interview_date" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-gradient rounded-pill px-4">
                            <i class="fas fa-paper-plane me-2"></i> Send Email
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Email template loader (assumes getEmailTemplate returns JSON safe strings)
        const templates = <?= json_encode([
            'interview_request' => getEmailTemplate('interview_request'),
            'acceptance' => getEmailTemplate('acceptance'),
            'rejection' => getEmailTemplate('rejection'),
            'custom' => getEmailTemplate('custom')
        ]) ?>;

        document.getElementById('emailTemplate').addEventListener('change', function() {
            const template = templates[this.value];
            if (template) {
                document.getElementById('emailSubject').value = template.subject;
                document.getElementById('emailMessage').value = template.body;
                
                // Show interview date field for interview requests
                document.getElementById('interviewDateDiv').style.display = 
                    this.value === 'interview_request' ? 'block' : 'none';
            }
        });
    </script>
</body>
</html>