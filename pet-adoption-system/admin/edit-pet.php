<?php
// admin/edit-pet.php
require_once '../includes/session.php';
requireAdmin();
require_once '../config/database.php';
require_once '../includes/functions.php';

$animal_id = $_GET['id'] ?? '';
$success = '';
$errors = [];

if (!$animal_id) {
    header('Location: manage-pets.php');
    exit();
}

// Get pet details
$stmt = $pdo->prepare("SELECT * FROM pets WHERE animal_id = ?");
$stmt->execute([$animal_id]);
$pet = $stmt->fetch();

if (!$pet) {
    header('Location: manage-pets.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $intake_type = $_POST['intake_type'] ?? '';
    $intake_date = $_POST['intake_date'] ?? '';
    $pet_name = trim($_POST['pet_name'] ?? '');
    $animal_type = $_POST['animal_type'] ?? '';
    $pet_age = trim($_POST['pet_age'] ?? '');
    $pet_size = $_POST['pet_size'] ?? '';
    $color = trim($_POST['color'] ?? '');
    $breed = trim($_POST['breed'] ?? '');
    $sex = $_POST['sex'] ?? '';
    $adoption_status = $_POST['adoption_status'] ?? '';
    $description = trim($_POST['description'] ?? '');
    
    // Validate required fields
    if (empty($intake_type)) $errors[] = "Intake Type is required.";
    if (empty($intake_date)) $errors[] = "Intake Date is required.";
    if (empty($pet_name)) $errors[] = "Pet Name is required.";
    if (empty($animal_type)) $errors[] = "Animal Type is required.";
    if (empty($pet_age)) $errors[] = "Pet Age is required.";
    if (empty($pet_size)) $errors[] = "Pet Size is required.";
    if (empty($color)) $errors[] = "Color is required.";
    if (empty($breed)) $errors[] = "Breed is required.";
    if (empty($sex)) $errors[] = "Sex is required.";
    if (empty($adoption_status)) $errors[] = "Adoption Status is required.";
    
    // Handle photo upload
    $photo_path = $pet['photo_path']; // Keep existing photo by default
    
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $validation_errors = validateImageUpload($_FILES['photo']);
        
        if (!empty($validation_errors)) {
            $errors = array_merge($errors, $validation_errors);
        } else {
            $new_photo = uploadPetPhoto($_FILES['photo'], $animal_id, $pet['photo_path']);
            if ($new_photo) {
                $photo_path = $new_photo;
            } else {
                $errors[] = "Failed to upload photo.";
            }
        }
    }
    
    // If no errors, update database
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE pets SET
                    intake_type = ?,
                    intake_date = ?,
                    pet_name = ?,
                    animal_type = ?,
                    pet_age = ?,
                    pet_size = ?,
                    color = ?,
                    breed = ?,
                    sex = ?,
                    adoption_status = ?,
                    photo_path = ?,
                    description = ?
                WHERE animal_id = ?
            ");
            
            $stmt->execute([
                $intake_type, $intake_date, $pet_name, $animal_type,
                $pet_age, $pet_size, $color, $breed, $sex,
                $adoption_status, $photo_path, $description, $animal_id
            ]);
            
            $success = "Pet updated successfully!";
            
            // Refresh pet data
            $stmt = $pdo->prepare("SELECT * FROM pets WHERE animal_id = ?");
            $stmt->execute([$animal_id]);
            $pet = $stmt->fetch();
            
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EDIT PET- <?= htmlspecialchars($pet['pet_name']) ?></title>
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

        /* Content Card */
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
        }
        .card-body-custom { padding: 2rem; }

        /* Form Styles */
        .section-title {
            font-size: 1rem;
            font-weight: 800;
            color: var(--primary-color);
            text-transform: uppercase;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f1f2f6;
            display: flex;
            align-items: center;
        }
        .section-title i { margin-right: 10px; }
        
        .form-label { font-weight: 700; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem; }
        .required { color: #dc3545; margin-left: 3px; }
        
        .form-control, .form-select {
            border: 2px solid #f1f2f6;
            border-radius: 10px;
            padding: 12px 15px;
            font-weight: 600;
            color: var(--text-dark);
            transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(255, 107, 107, 0.1); }
        .form-control:disabled { background-color: #f8f9fa; color: #6c757d; }

        /* Image Preview */
        .current-photo-wrapper {
            width: 150px;
            height: 150px;
            border-radius: 15px;
            overflow: hidden;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
            background-color: #f1f2f6;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .current-photo { width: 100%; height: 100%; object-fit: cover; }
        .no-photo-icon { font-size: 3rem; color: #cbd5e1; }

        /* Buttons */
        .btn-back { color: var(--text-muted); font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; transition: all 0.3s; }
        .btn-back:hover { color: var(--primary-color); transform: translateX(-5px); }

        .btn-gradient {
            background: var(--brand-gradient);
            color: white;
            border: none;
            font-weight: 700;
            padding: 12px 30px;
            border-radius: 10px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
            width: 100%;
        }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(255, 107, 107, 0.4); color: white; }

        .btn-outline-cancel {
            border: 2px solid #f1f2f6;
            color: var(--text-muted);
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 700;
            background: transparent;
            width: 100%;
            display: block;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-outline-cancel:hover { background: #f1f2f6; color: var(--text-dark); }

        .page-header { margin-bottom: 2rem; }
        .page-title { font-weight: 800; color: var(--text-dark); font-size: 2rem; }
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
            <div class="col-lg-10">
                
                <div class="page-header">
                    <a href="manage-pets.php" class="btn-back mb-2"><i class="fas fa-arrow-left me-2"></i> Back to Manage Pets</a>
                    <h1 class="page-title">✏️ EDIT PET: <?= htmlspecialchars($pet['pet_name']) ?></h1>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3 mb-4">
                        <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3 mb-4">
                        <strong><i class="fas fa-exclamation-triangle me-2"></i> Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="content-card">
                    <div class="card-body-custom">
                        <form method="POST" action="edit-pet.php?id=<?= urlencode($animal_id) ?>" enctype="multipart/form-data">
                            
                            <!-- Basic Information Section -->
                            <h4 class="section-title"><i class="fas fa-info-circle"></i> Basic Information</h4>
                            
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Animal ID</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($pet['animal_id']) ?>" disabled>
                                    <div class="form-text">System generated ID (Cannot be changed)</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Pet Name <span class="required">*</span></label>
                                    <input type="text" name="pet_name" class="form-control" value="<?= htmlspecialchars($pet['pet_name']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Intake Type <span class="required">*</span></label>
                                    <select name="intake_type" class="form-select" required>
                                        <option value="">Select...</option>
                                        <option value="OWNER SUR" <?= $pet['intake_type'] === 'OWNER SUR' ? 'selected' : '' ?>>Owner Surrender</option>
                                        <option value="STRAY" <?= $pet['intake_type'] === 'STRAY' ? 'selected' : '' ?>>Stray</option>
                                        <option value="RETURN" <?= $pet['intake_type'] === 'RETURN' ? 'selected' : '' ?>>Return</option>
                                        <option value="CONFISCATE" <?= $pet['intake_type'] === 'CONFISCATE' ? 'selected' : '' ?>>Confiscate</option>
                                        <option value="WILDLIFE" <?= $pet['intake_type'] === 'WILDLIFE' ? 'selected' : '' ?>>Wildlife</option>
                                        <option value="EUTH REQ" <?= $pet['intake_type'] === 'EUTH REQ' ? 'selected' : '' ?>>Euthanasia Request</option>
                                        <option value="FOSTER" <?= $pet['intake_type'] === 'FOSTER' ? 'selected' : '' ?>>Foster</option>
                                        <option value="BOARDING" <?= $pet['intake_type'] === 'BOARDING' ? 'selected' : '' ?>>Boarding</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Intake Date <span class="required">*</span></label>
                                    <input type="date" name="intake_date" class="form-control" value="<?= htmlspecialchars($pet['intake_date']) ?>" required>
                                </div>
                            </div>

                            <!-- Animal Details Section -->
                            <h4 class="section-title"><i class="fas fa-paw"></i> Animal Details</h4>

                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Animal Type <span class="required">*</span></label>
                                    <select name="animal_type" class="form-select" required>
                                        <option value="">Select...</option>
                                        <option value="CAT" <?= $pet['animal_type'] === 'CAT' ? 'selected' : '' ?>>Cat</option>
                                        <option value="DOG" <?= $pet['animal_type'] === 'DOG' ? 'selected' : '' ?>>Dog</option>
                                        <option value="BIRD" <?= $pet['animal_type'] === 'BIRD' ? 'selected' : '' ?>>Bird</option>
                                        <option value="OTHER" <?= $pet['animal_type'] === 'OTHER' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Breed <span class="required">*</span></label>
                                    <input type="text" name="breed" class="form-control" value="<?= htmlspecialchars($pet['breed']) ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Pet Age <span class="required">*</span></label>
                                    <input type="text" name="pet_age" class="form-control" value="<?= htmlspecialchars($pet['pet_age']) ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Pet Size <span class="required">*</span></label>
                                    <select name="pet_size" class="form-select" required>
                                        <option value="">Select...</option>
                                        <option value="SMALL" <?= $pet['pet_size'] === 'SMALL' ? 'selected' : '' ?>>Small</option>
                                        <option value="MED" <?= $pet['pet_size'] === 'MED' ? 'selected' : '' ?>>Medium</option>
                                        <option value="LARGE" <?= $pet['pet_size'] === 'LARGE' ? 'selected' : '' ?>>Large</option>
                                        <option value="X-LRG" <?= $pet['pet_size'] === 'X-LRG' ? 'selected' : '' ?>>Extra Large</option>
                                        <option value="KITTE" <?= $pet['pet_size'] === 'KITTE' ? 'selected' : '' ?>>Kitten</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Sex <span class="required">*</span></label>
                                    <select name="sex" class="form-select" required>
                                        <option value="">Select...</option>
                                        <option value="M" <?= $pet['sex'] === 'M' ? 'selected' : '' ?>>Male</option>
                                        <option value="F" <?= $pet['sex'] === 'F' ? 'selected' : '' ?>>Female</option>
                                        <option value="N" <?= $pet['sex'] === 'N' ? 'selected' : '' ?>>Neutered</option>
                                        <option value="S" <?= $pet['sex'] === 'S' ? 'selected' : '' ?>>Spayed</option>
                                        <option value="U" <?= $pet['sex'] === 'U' ? 'selected' : '' ?>>Unknown</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Color <span class="required">*</span></label>
                                    <input type="text" name="color" class="form-control" value="<?= htmlspecialchars($pet['color']) ?>" required>
                                </div>
                            </div>

                            <!-- Status & Media Section -->
                            <h4 class="section-title"><i class="fas fa-images"></i> Status & Media</h4>

                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Current Photo</label>
                                    <div class="current-photo-wrapper">
                                        <?php if ($pet['photo_path'] && file_exists('../' . $pet['photo_path'])): ?>
                                            <img src="../<?= htmlspecialchars($pet['photo_path']) ?>" class="current-photo" alt="Current photo">
                                        <?php else: ?>
                                            <div class="text-center">
                                                <i class="fas fa-camera no-photo-icon"></i>
                                                <div class="small text-muted mt-2">No Image</div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">Adoption Status <span class="required">*</span></label>
                                        <select name="adoption_status" class="form-select" required>
                                            <option value="Available" <?= $pet['adoption_status'] === 'Available' ? 'selected' : '' ?>>Available</option>
                                            <option value="Pending" <?= $pet['adoption_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="Reserved" <?= $pet['adoption_status'] === 'Reserved' ? 'selected' : '' ?>>Reserved</option>
                                            <option value="Trial" <?= $pet['adoption_status'] === 'Trial' ? 'selected' : '' ?>>Trial</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Upload New Photo (Optional)</label>
                                        <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/jpg, image/webp">
                                        <div class="form-text">Leave empty to keep current photo.</div>
                                    </div>
                                </div>
                                <div class="col-12 mt-2">
                                    <label class="form-label">Description (Optional)</label>
                                    <textarea name="description" class="form-control" rows="5" placeholder="Tell us about this pet..."><?= htmlspecialchars($pet['description']) ?></textarea>
                                </div>
                            </div>

                            <div class="row g-3 pt-3 border-top">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-gradient btn-lg">
                                        <i class="fas fa-save me-2"></i>Update Pet
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <a href="manage-pets.php" class="btn btn-outline-cancel btn-lg">Cancel</a>
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