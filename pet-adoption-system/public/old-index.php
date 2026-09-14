// — We connect loving families, one paw at a time —

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #dc3545;
            --accent-yellow: #ffc107;
            --accent-green: #28a745;
            --white: #ffffff;
        }
        
        /* Hero Section */
        .hero-section {
            background: var(--primary-color);
            color: white;
            padding: 4rem 0;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><text x="10" y="50" font-size="40" opacity="0.1">🐾</text></svg>');
            opacity: 0.1;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }
        
        .hero-stats {
            display: flex;
            gap: 3rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .hero-stat {
            text-align: center;
        }
        
        .hero-stat-number {
            font-size: 3rem;
            font-weight: 700;
            display: block;
        }
        
        .hero-stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        /* Navigation */
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            background-color: var(--white) !important;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color) !important;
        }
        
        /* Quote Section */
        .quote-section {
            background: var(--accent-yellow);
            padding: 3rem 0;
            border-top: 3px solid var(--secondary-color);
            border-bottom: 3px solid var(--secondary-color);
        }
        
        .quote-text {
            font-size: 1.8rem;
            font-style: italic;
            color: #495057;
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .quote-author {
            text-align: center;
            margin-top: 1rem;
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        /* Filter Section */
        .filter-section {
            background: var(--white);
            padding: 2rem 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        /* Pet Cards */
        .pet-card { 
            transition: all 0.3s ease;
            border: 2px solid var(--primary-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            height: 100%;
        }
        
        .pet-card:hover { 
            transform: translateY(-8px);
            box-shadow: 0 8px 16px rgba(13, 110, 253, 0.3);
            border-color: var(--secondary-color);
        }
        
        .pet-card-img {
            height: 250px;
            object-fit: cover;
            background: var(--primary-color);
        }
        
        .days-badge { 
            font-size: 0.85rem;
            background: var(--secondary-color) !important;
        }
        
        .pet-name {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.4rem;
        }
        
        /* Footer */
        footer {
            background: var(--accent-green);
            color: white;
            padding: 3rem 0 1rem;
            margin-top: 4rem;
        }
        
        .footer-links a {
            color: white;
            text-decoration: none;
            opacity: 0.9;
            transition: opacity 0.3s;
        }
        
        .footer-links a:hover {
            opacity: 1;
            text-decoration: underline;
        }
        
        /* Admin Link */
        .admin-link {
            color: var(--secondary-color);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .admin-link:hover {
            color: var(--primary-color);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }
        
        .empty-state i {
            font-size: 5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        /* Custom button colors */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0b5ed7;
        }

        .badge.bg-primary {
            background-color: var(--primary-color) !important;
        }

        .badge.bg-info {
            background-color: var(--accent-yellow) !important;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .text-danger {
            color: var(--secondary-color) !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
        <div class="container">
            <a class="navbar-brand text-primary" href="index.php">
                <i class="fas fa-paw me-2"></i>Adoption Pawtal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><i class="fas fa-home me-1"></i> Browse Pets</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about"><i class="fas fa-heart me-1"></i> About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact"><i class="fas fa-envelope me-1"></i> Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-link" href="../admin/login.php">
                            <i class="fas fa-user-shield me-1"></i> Admin Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container hero-content">
            <div class="text-center">
                <h1 class="hero-title">Find Your Perfect Companion</h1>
                <p class="hero-subtitle">Connecting families, one paw at a time.</p>
                <a href="#pets" class="btn btn-light btn-lg px-5 py-3 shadow">
                    <i class="fas fa-search me-2"></i>Start Your Search
                </a>
                
                <div class="hero-stats">
                    <div class="hero-stat">
                        <span class="hero-stat-number"><?= $total_available ?></span>
                        <span class="hero-stat-label">Pets Available</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-number"><i class="fas fa-heart"></i></span>
                        <span class="hero-stat-label">Waiting for Love</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-number"><i class="fas fa-home"></i></span>
                        <span class="hero-stat-label">New Beginnings</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quote Section -->
    <section class="quote-section">
        <div class="container">
            <blockquote class="quote-text">
                "Saving one animal won't change the world, but it will change the world for that one animal."
            </blockquote>
            <p class="quote-author">— Unknown</p>
        </div>
    </section>

    <!-- Filter Section -->
    <section class="filter-section" id="pets">
        <div class="container">
            <h2 class="text-center mb-4">
                <i class="fas fa-filter text-primary me-2"></i>Find Your Match
            </h2>
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" action="index.php" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label"><i class="fas fa-paw me-1"></i> Animal Type</label>
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
                            <label class="form-label"><i class="fas fa-ruler-vertical me-1"></i> Size</label>
                            <select name="size" class="form-select">
                                <option value="">All Sizes</option>
                                <option value="SMALL" <?= $size === 'SMALL' ? 'selected' : '' ?>>Small</option>
                                <option value="MED" <?= $size === 'MED' ? 'selected' : '' ?>>Medium</option>
                                <option value="LARGE" <?= $size === 'LARGE' ? 'selected' : '' ?>>Large</option>
                                <option value="X-LRG" <?= $size === 'X-LRG' ? 'selected' : '' ?>>Extra Large</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="fas fa-venus-mars me-1"></i> Sex</label>
                            <select name="sex" class="form-select">
                                <option value="">All</option>
                                <option value="M" <?= $sex === 'M' ? 'selected' : '' ?>>Male</option>
                                <option value="F" <?= $sex === 'F' ? 'selected' : '' ?>>Female</option>
                                <option value="N" <?= $sex === 'N' ? 'selected' : '' ?>>Neutered</option>
                                <option value="S" <?= $sex === 'S' ? 'selected' : '' ?>>Spayed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><i class="fas fa-search me-1"></i> Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Name or breed" value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary" title="Clear filters">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Pet Cards Section -->
    <div class="container my-5">
        <?php if (count($pets) > 0): ?>
            <h3 class="mb-4 text-center">
                <i class="fas fa-heart text-danger me-2"></i>
                <?= count($pets) ?> Pet<?= count($pets) != 1 ? 's' : '' ?> Ready for Adoption
            </h3>
            <div class="row g-4">
                <?php foreach ($pets as $pet): 
                    $days = daysInShelter($pet['intake_date']);
                ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card pet-card">
                            <?php if ($pet['photo_path'] && file_exists('../' . $pet['photo_path'])): ?>
                                <img src="../<?= htmlspecialchars($pet['photo_path']) ?>" class="card-img-top pet-card-img" alt="<?= htmlspecialchars($pet['pet_name']) ?>">
                            <?php else: ?>
                                <div class="card-img-top pet-card-img d-flex align-items-center justify-content-center">
                                    <span class="text-white" style="font-size: 100px;">
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
                            
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="pet-name mb-0"><?= htmlspecialchars($pet['pet_name']) ?></h5>
                                    <span class="badge days-badge">
                                        <i class="far fa-clock me-1"></i><?= $days ?> day<?= $days != 1 ? 's' : '' ?>
                                    </span>
                                </div>
                                
                                <div class="mb-3">
                                    <span class="badge bg-primary me-1">
                                        <i class="fas fa-paw me-1"></i><?= htmlspecialchars($pet['animal_type']) ?>
                                    </span>
                                    <span class="badge bg-info text-dark">
                                        <i class="fas fa-ruler-vertical me-1"></i><?= htmlspecialchars($pet['pet_size']) ?>
                                    </span>
                                </div>
                                
                                <div class="small text-muted mb-3">
                                    <div><i class="fas fa-dna me-2"></i><strong>Breed:</strong> <?= htmlspecialchars($pet['breed']) ?></div>
                                    <div><i class="fas fa-birthday-cake me-2"></i><strong>Age:</strong> <?= htmlspecialchars($pet['pet_age']) ?></div>
                                    <div><i class="fas fa-venus-mars me-2"></i><strong>Sex:</strong> 
                                        <?php
                                        $sex_display = match($pet['sex']) {
                                            'M' => 'Male',
                                            'F' => 'Female',
                                            'N' => 'Neutered',
                                            'S' => 'Spayed',
                                            'U' => 'Unknown',
                                            default => $pet['sex']
                                        };
                                        echo htmlspecialchars($sex_display);
                                        ?>
                                    </div>
                                </div>
                                
                                <a href="pet-details.php?id=<?= htmlspecialchars($pet['animal_id']) ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-info-circle me-2"></i>View Details & Apply
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h3 class="mt-3">No Pets Found</h3>
                <p class="text-muted">Try adjusting your filters or check back later for new arrivals!</p>
                <a href="index.php" class="btn btn-primary mt-3">
                    <i class="fas fa-redo me-2"></i>Clear Filters
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4" id="about">
                    <h5><i class="fas fa-paw me-2"></i>About Adoption Pawtal</h5>
                    <p>We're dedicated to connecting loving families with pets in need. Every adoption saves a life and brings joy to a family.</p>
                </div>
                <div class="col-md-4 mb-4" id="contact">
                    <h5><i class="fas fa-envelope me-2"></i>Contact Us</h5>
                    <p>
                        <i class="fas fa-phone me-2"></i>(123) 456-7890<br>
                        <i class="fas fa-envelope me-2"></i>info@adoptionpawtal.com<br>
                        <i class="fas fa-clock me-2"></i>Mon-Fri: 9:00 AM - 5:00 PM
                    </p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5><i class="fas fa-link me-2"></i>Quick Links</h5>
                    <div class="footer-links">
                        <a href="index.php" class="d-block mb-2"><i class="fas fa-home me-2"></i>Browse Pets</a>
                        <a href="../admin/login.php" class="d-block mb-2"><i class="fas fa-user-shield me-2"></i>Admin Portal</a>
                        <a href="#" class="d-block mb-2"><i class="fas fa-question-circle me-2"></i>Adoption Process</a>
                        <a href="#" class="d-block mb-2"><i class="fas fa-hands-helping me-2"></i>Volunteer</a>
                    </div>
                </div>
            </div>
            <hr class="bg-white opacity-25">
            <div class="text-center">
                <p class="mb-0">&copy; 2025 Adoption Pawtal. All rights reserved. Made with <i class="fas fa-heart text-danger"></i> for pets in need.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>