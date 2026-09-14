<?php
// public/pet-details.php
require_once '../config/database.php';

$animal_id = $_GET['id'] ?? '';

if (!$animal_id) {
    header('Location: index.php');
    exit();
}

// Get pet details
$stmt = $pdo->prepare("SELECT * FROM pets WHERE animal_id = ?");
$stmt->execute([$animal_id]);
$pet = $stmt->fetch();

if (!$pet) {
    header('Location: index.php');
    exit();
}

$is_available = $pet['adoption_status'] === 'Available';

function daysInShelter($intake_date) {
    $intake = new DateTime($intake_date);
    $now = new DateTime();
    $diff = $now->diff($intake);
    return $diff->days;
}

$days = daysInShelter($pet['intake_date']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pet['pet_name']) ?> - Adoption Pawtal</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- Shared Styles from Index.php --- */
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
            padding-top: 80px; /* Account for fixed header */
        }

        /* Navigation */
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
            letter-spacing: -0.5px;
        }
        
        .nav-link {
            font-weight: 700;
            color: var(--text-dark) !important;
            margin: 0 10px;
            transition: color 0.3s;
        }
        
        .nav-link:hover { color: var(--primary-color) !important; }
        .btn-admin { background-color: #f1f2f6; border-radius: 50px; padding: 8px 20px; color: var(--text-muted) !important; }

        /* --- Specific Pet Details Styles --- */
        
        .profile-card {
            background: white;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
            margin-top: 2rem;
            border: 1px solid rgba(0,0,0,0.02);
        }

        .pet-image-container {
            height: 100%;
            min-height: 500px;
            background-color: #f8f9fa;
            position: relative;
        }

        .pet-main-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pet-info-container {
            padding: 3rem;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .status-available { background: #d4edda; color: #155724; }
        .status-unavailable { background: #fff3cd; color: #856404; }

        .pet-title-name {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            line-height: 1;
            background: var(--brand-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .pet-meta-badges {
            display: flex;
            gap: 10px;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .meta-badge {
            background: #f1f2f6;
            padding: 8px 15px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        
        .meta-badge i { color: var(--primary-color); margin-right: 6px; }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin: 2rem 0;
            background: #fff9f5;
            padding: 20px;
            border-radius: 20px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: var(--text-muted);
            font-weight: 700;
            margin-bottom: 4px;
        }

        .info-value {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 1.1rem;
        }

        .description-box {
            color: var(--text-muted);
            line-height: 1.8;
            margin-bottom: 2rem;
            font-size: 1.05rem;
        }

        /* Buttons */
        .btn-gradient {
            background: var(--brand-gradient);
            border: none;
            color: white;
            border-radius: 15px;
            padding: 15px 30px;
            font-weight: 800;
            font-size: 1.1rem;
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.3);
            transition: all 0.3s ease;
            width: 100%;
            display: block; /* Ensures it behaves like a block button */
            text-align: center;
            text-decoration: none;
        }

        .btn-gradient:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(255, 107, 107, 0.4);
            color: white;
        }
        
        .back-link {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s;
        }
        
        .back-link:hover { color: var(--primary-color); transform: translateX(-5px); }

        /* Days Badge Floating on Image */
        .floating-days-badge {
            position: absolute;
            top: 30px;
            left: 30px;
            background: rgba(255,255,255,0.95);
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 700;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            z-index: 2;
        }

        /* Footer (Same as Index) */
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
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Browse Pets</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#contact">Contact</a>
                    </li>
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
        <div class="mt-4 mb-2">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left me-2"></i> Back to All Pets
            </a>
        </div>

        <div class="profile-card">
            <div class="row g-0">
                <div class="col-lg-6">
                    <div class="pet-image-container d-flex align-items-center justify-content-center">
                        <div class="floating-days-badge">
                            <i class="far fa-clock text-danger me-2"></i>Waiting <?= $days ?> days
                        </div>

                        <?php if ($pet['photo_path'] && file_exists('../' . $pet['photo_path'])): ?>
                            <img src="../<?= htmlspecialchars($pet['photo_path']) ?>" class="pet-main-img" alt="<?= htmlspecialchars($pet['pet_name']) ?>">
                        <?php else: ?>
                            <span style="font-size: 150px; opacity: 0.3;">
                                <?php
                                echo match($pet['animal_type']) {
                                    'DOG' => '🐕',
                                    'CAT' => '🐱',
                                    'BIRD' => '🦜',
                                    default => '🐾'
                                };
                                ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="pet-info-container h-100 d-flex flex-column">
                        
                        <div>
                            <?php if ($is_available): ?>
                                <span class="status-badge status-available">
                                    <i class="fas fa-check-circle me-2"></i>Available for Adoption
                                </span>
                            <?php else: ?>
                                <span class="status-badge status-unavailable">
                                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($pet['adoption_status']) ?>
                                </span>
                            <?php endif; ?>

                            <h1 class="pet-title-name"><?= htmlspecialchars($pet['pet_name']) ?></h1>
                            
                            <div class="pet-meta-badges">
                                <div class="meta-badge">
                                    <i class="fas fa-dna"></i> <?= htmlspecialchars($pet['breed']) ?>
                                </div>
                                <div class="meta-badge">
                                    <i class="fas fa-birthday-cake"></i> <?= htmlspecialchars($pet['pet_age']) ?>
                                </div>
                                <div class="meta-badge">
                                    <?php
                                    $sex_icon = match($pet['sex']) {
                                        'M', 'N' => 'fa-mars',
                                        'F', 'S' => 'fa-venus',
                                        default => 'fa-genderless'
                                    };
                                    ?>
                                    <i class="fas <?= $sex_icon ?>"></i>
                                    <?php
                                    $sex_display = match($pet['sex']) {
                                        'M' => 'Male',
                                        'F' => 'Female',
                                        'N' => 'Neutered Male',
                                        'S' => 'Spayed Female',
                                        'U' => 'Unknown',
                                        default => $pet['sex']
                                    };
                                    echo htmlspecialchars($sex_display);
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="description-box">
                            <?php if ($pet['description']): ?>
                                <p><?= nl2br(htmlspecialchars($pet['description'])) ?></p>
                            <?php else: ?>
                                <p class="fst-italic">No specific description provided, but this furry friend is eager to meet you!</p>
                            <?php endif; ?>
                        </div>

                        <div class="info-grid mt-auto">
                            <div class="info-item">
                                <span class="info-label">Type</span>
                                <span class="info-value"><?= ucfirst(strtolower($pet['animal_type'])) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Size</span>
                                <span class="info-value"><?= ucfirst(strtolower($pet['pet_size'])) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Color</span>
                                <span class="info-value"><?= htmlspecialchars($pet['color']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Intake Date</span>
                                <span class="info-value"><?= date('M j, Y', strtotime($pet['intake_date'])) ?></span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <?php if ($is_available): ?>
                                <a href="apply.php?pet_id=<?= htmlspecialchars($pet['animal_id']) ?>" class="btn btn-gradient">
                                    <i class="fas fa-heart me-2"></i> Adopt <?= htmlspecialchars($pet['pet_name']) ?>
                                </a>
                                <p class="text-center text-muted mt-3 small">
                                    <i class="fas fa-info-circle me-1"></i> You will be asked to fill out an application form.
                                </p>
                            <?php else: ?>
                                <div class="alert alert-warning border-0 shadow-sm">
                                    <strong>Not Available:</strong> This pet is currently not available.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-danger"></i> 123 Paws Lane, Pet City</li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="footer-title">Quick Links</h5>
                    <div class="footer-links">
                        <a href="index.php">Browse Pets</a>
                        <a href="#">Adoption Process</a>
                        <a href="#">Volunteer Opportunities</a>
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