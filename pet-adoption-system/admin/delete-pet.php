<?php
// admin/delete-pet.php
require_once '../includes/session.php';
requireAdmin();
require_once '../config/database.php';
require_once '../includes/functions.php';

$animal_id = $_GET['id'] ?? '';

if (!$animal_id) {
    header('Location: manage-pets.php');
    exit();
}

// Get pet details
$stmt = $pdo->prepare("SELECT * FROM pets WHERE animal_id = ?");
$stmt->execute([$animal_id]);
$pet = $stmt->fetch();

if (!$pet) {
    $_SESSION['error'] = "Pet not found.";
    header('Location: manage-pets.php');
    exit();
}

// Check if confirmed deletion
if (isset($_POST['confirm_delete'])) {
    try {
        // Check if pet has applications
        $app_count = getApplicationCount($pdo, $animal_id);
        
        if ($app_count > 0 && !isset($_POST['force_delete'])) {
            $_SESSION['error'] = "Cannot delete this pet. It has {$app_count} application(s). Please handle applications first or use force delete.";
            header('Location: manage-pets.php');
            exit();
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        // If force delete, remove applications first
        if (isset($_POST['force_delete'])) {
            // Delete email logs for applications
            $stmt = $pdo->prepare("
                DELETE FROM email_logs 
                WHERE application_id IN (
                    SELECT application_id FROM adoption_applications WHERE animal_id = ?
                )
            ");
            $stmt->execute([$animal_id]);
            
            // Delete applications
            $stmt = $pdo->prepare("DELETE FROM adoption_applications WHERE animal_id = ?");
            $stmt->execute([$animal_id]);
        }
        
        // Delete pet
        $stmt = $pdo->prepare("DELETE FROM pets WHERE animal_id = ?");
        $stmt->execute([$animal_id]);
        
        // Delete photo file
        deletePetPhoto($pet['photo_path']);
        
        // Commit transaction
        $pdo->commit();
        
        $_SESSION['success'] = "Pet '{$pet['pet_name']}' has been deleted successfully.";
        header('Location: manage-pets.php');
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error deleting pet: " . $e->getMessage();
        header('Location: manage-pets.php');
        exit();
    }
}

// Get application count
$app_count = getApplicationCount($pdo, $animal_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Pet - <?= htmlspecialchars($pet['pet_name']) ?></title>
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

        /* Danger Card */
        .content-card {
            background: white;
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        .card-header-danger {
            background: #fee2e2;
            color: #b91c1c;
            padding: 1.5rem;
            font-weight: 800;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #fecaca;
        }
        
        .card-body-custom { padding: 2rem; }

        /* Buttons */
        .btn-back { color: var(--text-muted); font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; transition: all 0.3s; }
        .btn-back:hover { color: var(--primary-color); transform: translateX(-5px); }

        .btn-delete-confirm {
            background: #dc2626;
            color: white;
            border: none;
            font-weight: 700;
            padding: 12px 30px;
            border-radius: 10px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
            width: 100%;
        }
        .btn-delete-confirm:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(220, 38, 38, 0.4); background: #b91c1c; color: white; }

        .btn-cancel {
            background: #f3f4f6;
            color: var(--text-dark);
            border: none;
            font-weight: 700;
            padding: 12px 30px;
            border-radius: 10px;
            transition: all 0.3s;
            width: 100%;
            display: block;
            text-align: center;
            text-decoration: none;
        }
        .btn-cancel:hover { background: #e5e7eb; color: var(--text-dark); }
        
        .info-row {
            display: flex;
            border-bottom: 1px solid #f3f4f6;
            padding: 10px 0;
        }
        .info-label { font-weight: 700; color: var(--text-muted); width: 40%; }
        .info-val { font-weight: 600; color: var(--text-dark); }
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
                    <li class="nav-item"><a class="nav-link active" href="manage-pets.php">Manage Pets</a></li>
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
        <div class="row justify-content-center">
            <div class="col-lg-6">
                
                <div class="mb-4">
                    <a href="manage-pets.php" class="btn-back mb-2"><i class="fas fa-arrow-left me-2"></i> Back to Manage Pets</a>
                </div>

                <div class="content-card border-danger">
                    <div class="card-header-danger">
                        <i class="fas fa-exclamation-triangle me-3"></i> Confirm Deletion
                    </div>
                    <div class="card-body-custom">
                        <h5 class="fw-bold mb-3 text-center">Are you sure you want to delete this pet?</h5>
                        
                        <div class="bg-light p-4 rounded-3 mb-4">
                            <div class="info-row">
                                <span class="info-label">Animal ID</span>
                                <span class="info-val"><?= htmlspecialchars($pet['animal_id']) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Name</span>
                                <span class="info-val"><?= htmlspecialchars($pet['pet_name']) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Type/Breed</span>
                                <span class="info-val"><?= htmlspecialchars($pet['animal_type']) ?> / <?= htmlspecialchars($pet['breed']) ?></span>
                            </div>
                            <div class="info-row border-0">
                                <span class="info-label">Status</span>
                                <span class="info-val"><?= htmlspecialchars($pet['adoption_status']) ?></span>
                            </div>
                        </div>
                        
                        <?php if ($app_count > 0): ?>
                            <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-4">
                                <div class="d-flex">
                                    <i class="fas fa-file-alt fa-2x me-3 text-warning"></i>
                                    <div>
                                        <h5 class="alert-heading fw-bold">Applications Detected</h5>
                                        <p class="mb-0">This pet has <strong><?= $app_count ?></strong> active adoption application(s).</p>
                                    </div>
                                </div>
                                <hr>
                                <p class="mb-2 fw-bold">Recommended Action:</p>
                                <ul class="mb-0 small">
                                    <li>Go back and process applications first.</li>
                                    <li>Or use "Force Delete" below to remove the pet and all associated data immediately.</li>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                            <div class="d-flex">
                                <i class="fas fa-trash-alt fa-2x me-3"></i>
                                <div>
                                    <strong>This action cannot be undone!</strong>
                                    <ul class="mb-0 mt-2 small ps-3">
                                        <li>Pet record will be permanently removed.</li>
                                        <li>Photo file will be deleted.</li>
                                        <?php if ($app_count > 0): ?>
                                            <li><strong><?= $app_count ?> applications will be deleted.</strong></li>
                                            <li><strong>Associated email logs will be deleted.</strong></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="delete-pet.php?id=<?= urlencode($animal_id) ?>">
                            <?php if ($app_count > 0): ?>
                                <div class="form-check mb-4 p-3 bg-warning bg-opacity-10 border border-warning rounded-3">
                                    <input class="form-check-input" type="checkbox" name="force_delete" id="force_delete" value="1" required>
                                    <label class="form-check-label text-danger fw-bold" for="force_delete">
                                        I understand the consequences and want to force delete this pet and all its data.
                                    </label>
                                </div>
                            <?php endif; ?>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <button type="submit" name="confirm_delete" class="btn btn-delete-confirm">
                                        <i class="fas fa-trash me-2"></i> Yes, Delete
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <a href="manage-pets.php" class="btn btn-cancel">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>