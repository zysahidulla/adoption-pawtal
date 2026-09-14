<?php
// public/success.php
session_start();
require_once '../config/database.php';

$app_id = $_GET['app_id'] ?? '';

if (!$app_id) {
    header('Location: index.php');
    exit();
}

// Get application details
$stmt = $pdo->prepare("
    SELECT aa.*, 
           CONCAT(a.first_name, ' ', a.last_name) as adopter_name,
           a.email,
           p.pet_name,
           p.animal_type,
           p.photo_path,
           p.breed
    FROM adoption_applications aa
    JOIN adopters a ON aa.adopter_id = a.adopter_id
    JOIN pets p ON aa.animal_id = p.animal_id
    WHERE aa.application_id = ?
");
$stmt->execute([$app_id]);
$application = $stmt->fetch();

if (!$application) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted! - Adoption Pawtal</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- Shared Branding --- */
        :root {
            --brand-gradient: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%);
            --secondary-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --primary-color: #FF6B6B; 
            --secondary-color: #FF8E53;
            --text-dark: #2d3436;
            --text-muted: #636e72;
            --bg-soft: #fff9f5;
            --paw-pattern: url("data:image/svg+xml,%3Csvg width='50' height='70' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill='%23FF6B6B' fill-opacity='0.10' d='M22.6,30.1c-5.2,0-9.4-4.2-9.4-9.4s4.2-9.4,9.4-9.4s9.4,4.2,9.4,9.4S27.8,30.1,22.6,30.1z M48.9,21.4 c-5.2,0-9.4-4.2-9.4-9.4s4.2-9.4,9.4-9.4s9.4,4.2,9.4,9.4S54.1,21.4,48.9,21.4z M75.6,30.1c-5.2,0-9.4-4.2-9.4-9.4s4.2-9.4,9.4-9.4 s9.4,4.2,9.4,9.4S80.8,30.1,75.6,30.1z M49.2,33.9c-18.6,0-26.9,14.4-28.1,23.9c-1.4,11.1,13,19.8,28.1,19.8s29.4-8.7,28.1-19.8 C76.1,48.3,67.8,33.9,49.2,33.9z'/%3E%3C/svg%3E");
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--bg-soft);
            background-image: var(--paw-pattern);
            color: var(--text-dark);
            padding-top: 80px;
        }

        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            padding: 1rem 0;
        }
        .navbar-brand {
            font-weight: 800;
            font-size: 1.8rem;
            background: var(--brand-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .nav-link { font-weight: 700; color: var(--text-dark) !important; margin: 0 10px; transition: color 0.3s; }
        .nav-link:hover { color: var(--primary-color) !important; }
        .btn-admin { background-color: #f1f2f6; border-radius: 50px; padding: 8px 20px; color: var(--text-muted) !important; }

        /* Success Specific Styles */
        .success-header {
            text-align: center;
            margin-bottom: 3rem;
            margin-top: 2rem;
        }

        .success-icon-circle {
            width: 120px;
            height: 120px;
            background: #d4edda;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.2);
            animation: popIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .success-icon {
            font-size: 3.5rem;
            color: #28a745;
        }

        @keyframes popIn {
            0% { transform: scale(0); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        .content-card {
            background: white;
            border-radius: 25px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.05);
            padding: 2.5rem;
            border: 1px solid rgba(0,0,0,0.02);
            margin-bottom: 2rem;
            height: 100%;
        }

        .card-title-custom {
            color: var(--text-dark);
            font-weight: 800;
            margin-bottom: 1.5rem;
            font-size: 1.4rem;
            border-bottom: 2px solid #f1f2f6;
            padding-bottom: 10px;
        }

        .pet-thumb {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #f1f2f6;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: var(--text-muted); font-weight: 600; }
        .detail-value { font-weight: 700; color: var(--text-dark); text-align: right; }

        .next-steps-list li {
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
        }
        .step-icon {
            background: #e3f2fd;
            color: #2196f3;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
            font-size: 0.9rem;
        }

        .info-box {
            background: #e0f7fa;
            border-radius: 15px;
            padding: 1.5rem;
            border: 1px solid #b2ebf2;
        }

        /* Buttons */
        .btn-gradient {
            background: var(--brand-gradient);
            border: none;
            color: white;
            border-radius: 15px;
            padding: 12px 30px;
            font-weight: 800;
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.3);
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-gradient:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(255, 107, 107, 0.4); color: white; }

        .btn-outline {
            border: 2px solid #f1f2f6;
            background: white;
            color: var(--text-muted);
            border-radius: 15px;
            padding: 12px 30px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
        }
        .btn-outline:hover { border-color: var(--text-dark); color: var(--text-dark); }

        /* Footer */
        footer {
            background: white;
            color: var(--text-dark);
            padding-top: 4rem;
            margin-top: 5rem;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        .footer-title {
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: var(--brand-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .footer-links a { color: var(--text-muted); text-decoration: none; margin-bottom: 5px; display: block; }
        .copyright { background: #f8f9fa; padding: 1.5rem 0; margin-top: 3rem; color: var(--text-muted); }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-paw me-2"></i>Adoption Pawtal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="index.php">Browse Pets</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#contact">Contact</a></li>
                    <li class="nav-item ms-lg-3">
                        <a class="nav-link btn-admin" href="../admin/login.php">
                            <small><i class="fas fa-lock me-1"></i> Admin</small>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        
        <!-- Success Header -->
        <div class="success-header">
            <div class="success-icon-circle">
                <i class="fas fa-check success-icon"></i>
            </div>
            <h1 class="fw-bolder mb-2">Application Submitted!</h1>
            <p class="text-muted fs-5">Thank you for taking the first step to adopt <?= htmlspecialchars($application['pet_name']) ?>.</p>
        </div>

        <!-- Warning Alert (Email issue) -->
        <?php if (isset($_SESSION['email_warning'])): ?>
            <div class="alert alert-warning alert-dismissible fade show shadow-sm rounded-4 border-0 mb-4" role="alert">
                <div class="d-flex">
                    <i class="fas fa-exclamation-triangle me-3 mt-1"></i>
                    <div>
                        <strong>Note about Email:</strong> <?= htmlspecialchars($_SESSION['email_warning']) ?>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['email_warning']); ?>
        <?php endif; ?>

        <div class="row g-4 justify-content-center">
            
            <!-- Column 1: Application Details -->
            <div class="col-lg-6">
                <div class="content-card">
                    <h4 class="card-title-custom"><i class="fas fa-file-alt me-2 text-primary"></i> Application Details</h4>
                    
                    <div class="row mb-4 align-items-center">
                        <div class="col-md-5 mb-3 mb-md-0">
                            <?php if ($application['photo_path'] && file_exists('../' . $application['photo_path'])): ?>
                                <img src="../<?= htmlspecialchars($application['photo_path']) ?>" class="pet-thumb" alt="<?= htmlspecialchars($application['pet_name']) ?>">
                            <?php else: ?>
                                <div class="bg-light rounded-4 d-flex align-items-center justify-content-center" style="height: 150px; font-size: 4rem;">
                                    🐾
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-7">
                            <h3 class="fw-bold mb-1"><?= htmlspecialchars($application['pet_name']) ?></h3>
                            <p class="text-muted mb-3"><?= htmlspecialchars($application['breed']) ?></p>
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                                <i class="fas fa-clock me-1"></i> Status: For Review
                            </span>
                        </div>
                    </div>

                    <div class="bg-light p-3 rounded-4">
                        <div class="detail-row">
                            <span class="detail-label">Application ID</span>
                            <span class="detail-value">#<?= htmlspecialchars($app_id) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Applicant Name</span>
                            <span class="detail-value"><?= htmlspecialchars($application['adopter_name']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email</span>
                            <span class="detail-value"><?= htmlspecialchars($application['email']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Submitted On</span>
                            <span class="detail-value"><?= date('M j, Y, g:i A', strtotime($application['application_date'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column 2: Next Steps & Info -->
            <div class="col-lg-5">
                <div class="content-card d-flex flex-column h-100">
                    <h4 class="card-title-custom"><i class="fas fa-list-ol me-2 text-primary"></i> What Happens Next?</h4>
                    
                    <ul class="list-unstyled next-steps-list mb-4">
                        <li>
                            <div class="step-icon"><i class="fas fa-envelope"></i></div>
                            <div>
                                <strong>Confirmation Email</strong>
                                <div class="text-muted small">Check your inbox (and spam) for a confirmation receipt.</div>
                            </div>
                        </li>
                        <li>
                            <div class="step-icon"><i class="fas fa-search"></i></div>
                            <div>
                                <strong>Team Review</strong>
                                <div class="text-muted small">Our team will review your application details within 3-5 business days.</div>
                            </div>
                        </li>
                        <li>
                            <div class="step-icon"><i class="fas fa-phone-alt"></i></div>
                            <div>
                                <strong>Contact & Interview</strong>
                                <div class="text-muted small">If selected, we will contact you to schedule a meet-and-greet or interview.</div>
                            </div>
                        </li>
                        <li>
                            <div class="step-icon"><i class="fas fa-home"></i></div>
                            <div>
                                <strong>Final Decision</strong>
                                <div class="text-muted small">We'll notify you of the final decision shortly after the interview.</div>
                            </div>
                        </li>
                    </ul>

                    <div class="info-box mt-auto">
                        <h6 class="fw-bold text-info mb-2"><i class="fas fa-info-circle me-2"></i>Keep in touch!</h6>
                        <p class="small mb-0 text-muted">
                            Please save your ID <strong>#<?= htmlspecialchars($app_id) ?></strong>. If you have questions, contact us at <strong class="text-dark">info@adoptionpawtal.com</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5 mb-5">
            <a href="index.php" class="btn-gradient me-3">
                <i class="fas fa-search me-2"></i> Browse More Pets
            </a>
            <a href="index.php" class="btn-outline">
                <i class="fas fa-home me-2"></i> Return Home
            </a>
        </div>

    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="footer-title"><i class="fas fa-paw me-2"></i>Adoption Pawtal</h5>
                    <p class="text-muted">We are dedicated to connecting loving families with pets in need. Every adoption saves a life and brings joy to a family.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="footer-title">Get in Touch</h5>
                    <ul class="list-unstyled text-muted">
                        <li class="mb-2"><i class="fas fa-phone me-2 text-danger"></i> (123) 456-7890</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2 text-danger"></i> info@adoptionpawtal.com</li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="footer-title">Quick Links</h5>
                    <div class="footer-links">
                        <a href="index.php">Browse Pets</a>
                        <a href="#">Adoption Process</a>
                        <a href="#">Donate</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="copyright text-center">
            <div class="container">
                <p class="mb-0">&copy; 2025 Adoption Pawtal. Made with <i class="fas fa-heart text-danger mx-1"></i> for pets.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>