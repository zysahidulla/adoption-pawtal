<?php
// public/index.php
require_once '../config/database.php';

// Get filters
$animal_type = $_GET['type'] ?? '';
$size = $_GET['size'] ?? '';
$sex = $_GET['sex'] ?? '';
$search = $_GET['search'] ?? '';

// Build query - only show Available pets to public
$sql = "SELECT * FROM pets WHERE adoption_status = 'Available'";
$params = [];

if ($animal_type) {
    $sql .= " AND animal_type = ?";
    $params[] = $animal_type;
}

if ($size) {
    $sql .= " AND pet_size = ?";
    $params[] = $size;
}

if ($sex) {
    $sql .= " AND sex = ?";
    $params[] = $sex;
}

if ($search) {
    $sql .= " AND (pet_name LIKE ? OR breed LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY intake_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pets = $stmt->fetchAll();

// Get unique types for filter
$types_stmt = $pdo->query("SELECT DISTINCT animal_type FROM pets WHERE adoption_status = 'Available' ORDER BY animal_type");
$animal_types = $types_stmt->fetchAll(PDO::FETCH_COLUMN);

// Get statistics
$total_available = $pdo->query("SELECT COUNT(*) FROM pets WHERE adoption_status = 'Available'")->fetchColumn();

// Function to calculate days in shelter
function daysInShelter($intake_date) {
    $intake = new DateTime($intake_date);
    $now = new DateTime();
    $diff = $now->diff($intake);
    return $diff->days;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adoption Pawtal - Find Your Perfect Companion</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --brand-gradient: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%); /* Coral/Peach */
            --secondary-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); /* Soft Blue */
            --primary-color: #FF6B6B; 
            --secondary-color: #FF8E53;
            --text-dark: #2d3436;
            --text-muted: #636e72;
            --white: #ffffff;
            --bg-soft: #fff9f5; /* Very light peach tint */
			--paw-pattern: url("data:image/svg+xml,%3Csvg width='50' height='70' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill='%23FF6B6B' fill-opacity='0.10' d='M22.6,30.1c-5.2,0-9.4-4.2-9.4-9.4s4.2-9.4,9.4-9.4s9.4,4.2,9.4,9.4S27.8,30.1,22.6,30.1z M48.9,21.4 c-5.2,0-9.4-4.2-9.4-9.4s4.2-9.4,9.4-9.4s9.4,4.2,9.4,9.4S54.1,21.4,48.9,21.4z M75.6,30.1c-5.2,0-9.4-4.2-9.4-9.4s4.2-9.4,9.4-9.4 s9.4,4.2,9.4,9.4S80.8,30.1,75.6,30.1z M49.2,33.9c-18.6,0-26.9,14.4-28.1,23.9c-1.4,11.1,13,19.8,28.1,19.8s29.4-8.7,28.1-19.8 C76.1,48.3,67.8,33.9,49.2,33.9z'/%3E%3C/svg%3E");        
			}
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--bg-soft);
            background-image: var(--paw-pattern);
            color: var(--text-dark);
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
        
        .nav-link:hover, .nav-link.active {
            color: var(--primary-color) !important;
        }

        .btn-admin {
            background-color: #f1f2f6;
            border-radius: 50px;
            padding: 8px 20px;
            color: var(--text-muted) !important;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.9) 0%, rgba(255, 142, 83, 0.85) 100%), 
                        url('Index banner.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: white;
            padding: 8rem 0 10rem; /* Increased padding */
            position: relative;
            overflow: hidden;
            border-bottom-left-radius: 50px;
            border-bottom-right-radius: 50px;
        }

        /* Decorative Circles in Hero */
        .hero-section::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            pointer-events: none;
        }
        
        .hero-title {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            letter-spacing: -1px;
        }
        
        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            opacity: 0.95;
            font-weight: 600;
        }

        .hero-quote {
            font-size: 1.1rem;
            font-style: italic;
            opacity: 0.9;
            margin-bottom: 3rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            position: relative;
        }

        .hero-quote::before, .hero-quote::after {
            content: '—';
            margin: 0 10px;
            opacity: 0.5;
        }

        /* Stats */
        .stats-container {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(5px);
            border-radius: 20px;
            padding: 25px 40px;
            display: inline-flex;
            gap: 60px;
            margin-top: 10px;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            color: rgba(255,255,255,0.9);
        }

        .hero-stat-number {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            opacity: 0.9;
        }

        /* Floating Filter Section */
        .filter-section {
            margin-top: -80px; /* Negative margin to create overlap */
            position: relative;
            z-index: 10;
            padding-bottom: 2rem;
        }

        .filter-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            background: white;
            padding: 2rem;
        }

        .form-label {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-select, .form-control {
            border: 2px solid #f1f2f6;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
        }

        .form-select:focus, .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 4px rgba(255, 142, 83, 0.1);
        }

        /* Pet Cards */
        .section-title {
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .pet-card { 
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
            background: white;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            height: 100%;
            position: relative;
        }
        
        .pet-card:hover { 
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(255, 107, 107, 0.15);
        }
        
        .pet-card-img {
            height: 280px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .pet-card:hover .pet-card-img {
            transform: scale(1.05);
        }

        .days-badge { 
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 0.8rem;
            background: rgba(255,255,255,0.95) !important;
            color: var(--text-dark);
            padding: 8px 15px;
            border-radius: 30px;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        .card-body {
            padding: 1.5rem;
        }

        .pet-name {
            color: var(--text-dark);
            font-weight: 800;
            font-size: 1.6rem;
            margin-bottom: 5px;
        }
        
        /* Badges */
        .badge-custom {
            padding: 8px 12px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        
        .badge-type { background-color: #e3f2fd; color: #2196f3; }
        .badge-size { background-color: #fff3e0; color: #ff9800; }
        
        .pet-details {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 15px;
            margin: 15px 0;
        }

        .detail-item {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .detail-item i {
            width: 20px;
            color: var(--primary-color);
        }

        /* Buttons */
        .btn-gradient {
            background: var(--brand-gradient);
            border: none;
            color: white;
            border-radius: 12px;
            padding: 12px 25px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 107, 0.4);
            color: white;
        }

        .btn-outline-custom {
            border: 2px solid #f1f2f6;
            color: var(--text-muted);
            border-radius: 12px;
            transition: all 0.3s;
        }
        
        .btn-outline-custom:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        
        .empty-state i {
            font-size: 4rem;
            background: var(--brand-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }

        /* Footer */
        footer {
            background: white;
            color: var(--text-dark);
            padding-top: 4rem;
            margin-top: 4rem;
            position: relative;
            border-top: 1px solid rgba(0,0,0,0.05);
        }

        .footer-title {
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: var(--brand-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .footer-links a {
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.3s;
            padding: 5px 0;
            display: inline-block;
        }

        .footer-links a:hover {
            color: var(--primary-color);
            transform: translateX(5px);
        }

        .copyright {
            background: #f8f9fa;
            padding: 1.5rem 0;
            margin-top: 3rem;
            font-size: 0.9rem;
            color: var(--text-muted);
        }
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
                        <a class="nav-link active" href="index.php">Browse Pets</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
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

    <section class="hero-section d-flex align-items-center">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h1 class="hero-title mt-4">MEET YOUR NEW BEST FRIEND!</h1>
                    <p class="hero-subtitle">— We connect loving families, one paw at a time —</p>
                   

                    <div class="stats-container">
                        <div class="stat-item">
                            <i class="fas fa-paw stat-icon"></i>
                            <div class="hero-stat-number"><?= $total_available ?></div>
                            <div class="stat-label">Available</div>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-heart stat-icon"></i>
                            <div class="hero-stat-number">100%</div>
                            <div class="stat-label">Love</div>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-headset stat-icon"></i>
                            <div class="hero-stat-number">24/7</div>
                            <div class="stat-label">Support</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="filter-section" id="pets">
        <div class="container">
            <div class="card filter-card">
                <h4 class="mb-4 text-center fw-bold" style="color: var(--text-dark);">Find Your Perfect Match</h4>
                <form method="GET" action="index.php" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Animal Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <?php foreach ($animal_types as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>" <?= $animal_type === $type ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Size</label>
                        <select name="size" class="form-select">
                            <option value="">All Sizes</option>
                            <option value="SMALL" <?= $size === 'SMALL' ? 'selected' : '' ?>>Small</option>
                            <option value="MED" <?= $size === 'MED' ? 'selected' : '' ?>>Medium</option>
                            <option value="LARGE" <?= $size === 'LARGE' ? 'selected' : '' ?>>Large</option>
                            <option value="X-LRG" <?= $size === 'X-LRG' ? 'selected' : '' ?>>Extra Large</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sex</label>
                        <select name="sex" class="form-select">
                            <option value="">Any</option>
                            <option value="M" <?= $sex === 'M' ? 'selected' : '' ?>>Male</option>
                            <option value="F" <?= $sex === 'F' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search Name/Breed</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="e.g. Golden Retriever" value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-gradient w-100">
                            Find
                        </button>
                        <?php if($animal_type || $size || $sex || $search): ?>
                            <a href="index.php" class="btn btn-outline-custom" title="Reset">
                                <i class="fas fa-undo"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <div class="container mb-5">
        <?php if (count($pets) > 0): ?>
            <div class="text-center mb-5">
				<h2 class="section-title">❤️</h2>
                <h2 class="section-title">PETS WAITING FOR YOU</h2>
                <p class="text-muted">Many furry friends are looking for their forever homes below!</p>
            </div>
            
            <div class="row g-4">
                <?php foreach ($pets as $pet): 
                    $days = daysInShelter($pet['intake_date']);
                ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card pet-card h-100">
                            <div class="position-relative">
                                <?php if ($pet['photo_path'] && file_exists('../' . $pet['photo_path'])): ?>
                                    <img src="../<?= htmlspecialchars($pet['photo_path']) ?>" class="card-img-top pet-card-img" alt="<?= htmlspecialchars($pet['pet_name']) ?>">
                                <?php else: ?>
                                    <div class="card-img-top pet-card-img d-flex align-items-center justify-content-center bg-light">
                                        <span style="font-size: 80px; opacity: 0.5;">
                                            <?php
                                            echo match($pet['animal_type']) {
                                                'DOG' => '🐕',
                                                'CAT' => '🐱',
                                                'BIRD' => '🦜',
                                                default => '🐾'
                                            };
                                            ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="days-badge">
                                    <i class="far fa-heart text-danger me-1"></i> <?= $days ?> days waiting
                                </div>
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="pet-name mb-0"><?= htmlspecialchars($pet['pet_name']) ?></h5>
                                    <div>
                                        <span class="badge-custom badge-type me-1">
                                            <?= htmlspecialchars($pet['animal_type']) ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="pet-details">
                                    <div class="row">
                                        <div class="col-6 detail-item">
                                            <i class="fas fa-dna"></i> <?= htmlspecialchars($pet['breed']) ?>
                                        </div>
                                        <div class="col-6 detail-item">
                                            <i class="fas fa-birthday-cake"></i> <?= htmlspecialchars($pet['pet_age']) ?>
                                        </div>
                                        <div class="col-6 detail-item">
                                            <i class="fas fa-ruler-vertical"></i> <?= ucfirst(strtolower($pet['pet_size'])) ?>
                                        </div>
                                        <div class="col-6 detail-item">
                                            <i class="fas fa-venus-mars"></i> 
                                            <?php
                                            $sex_display = match($pet['sex']) {
                                                'M' => 'Male',
                                                'F' => 'Female',
                                                'N' => 'Neutered',
                                                'S' => 'Spayed',
                                                default => $pet['sex']
                                            };
                                            echo htmlspecialchars($sex_display);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-auto">
                                    <a href="pet-details.php?id=<?= htmlspecialchars($pet['animal_id']) ?>" class="btn btn-gradient w-100">
                                        Adopt <?= htmlspecialchars($pet['pet_name']) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h3 class="mt-3 fw-bold">No Pets Found</h3>
                <p class="text-muted">We couldn't find any pets matching your search.</p>
                <a href="index.php" class="btn btn-gradient mt-3 px-4">
                    View All Pets
                </a>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4" id="about">
                    <h5 class="footer-title"><i class="fas fa-paw me-2"></i>Adoption Pawtal</h5>
                    <p class="text-muted">We are dedicated to connecting loving families with pets in need. Every adoption saves a life and brings joy to a family.</p>
                    <div class="mt-4">
                        <a href="#" class="text-muted me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-muted me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-twitter fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-4" id="contact">
                    <h5 class="footer-title">Get in Touch</h5>
                    <ul class="list-unstyled text-muted">
                        <li class="mb-2"><i class="fas fa-phone me-2 text-danger"></i> (123) 456-7890</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2 text-danger"></i> info@adoptionpawtal.com</li>
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-danger"></i> 123 Paws Lane, Pet City</li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="footer-title">Quick Links</h5>
                    <div class="footer-links d-flex flex-column">
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