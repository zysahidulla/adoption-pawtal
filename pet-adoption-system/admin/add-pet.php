<?php
// admin/add-pet.php
require_once '../includes/session.php';
requireAdmin();
require_once '../config/database.php';

$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $animal_id = trim($_POST['animal_id'] ?? '');
    $intake_type = $_POST['intake_type'] ?? '';
    $intake_date = $_POST['intake_date'] ?? '';
    $pet_name = trim($_POST['pet_name'] ?? '');
    $animal_type = $_POST['animal_type'] ?? '';
    $pet_age = trim($_POST['pet_age'] ?? '');
    $pet_size = $_POST['pet_size'] ?? '';
    $color = trim($_POST['color'] ?? '');
    $breed = trim($_POST['breed'] ?? '');
    $sex = $_POST['sex'] ?? '';
    $adoption_status = $_POST['adoption_status'] ?? 'Available';
    $description = trim($_POST['description'] ?? '');
    
    // Validate required fields
    if (empty($animal_id)) $errors[] = "Animal ID is required.";
    if (empty($intake_type)) $errors[] = "Intake Type is required.";
    if (empty($intake_date)) $errors[] = "Intake Date is required.";
    if (empty($pet_name)) $errors[] = "Pet Name is required.";
    if (empty($animal_type)) $errors[] = "Animal Type is required.";
    if (empty($pet_age)) $errors[] = "Pet Age is required.";
    if (empty($pet_size)) $errors[] = "Pet Size is required.";
    if (empty($color)) $errors[] = "Color is required.";
    if (empty($breed)) $errors[] = "Breed is required.";
    if (empty($sex)) $errors[] = "Sex is required.";
    
    // Check if animal_id already exists
    if (!empty($animal_id)) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM pets WHERE animal_id = ?");
        $check->execute([$animal_id]);
        if ($check->fetchColumn() > 0) {
            $errors[] = "Animal ID already exists. Please use a unique ID.";
        }
    }
    
    // Handle photo upload
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['photo']['type'], $allowed_types)) {
            $errors[] = "Photo must be JPG or PNG format.";
        } elseif ($_FILES['photo']['size'] > $max_size) {
            $errors[] = "Photo must be less than 5MB.";
        } else {
            $upload_dir = '../uploads/pets/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = $animal_id . '_' . time() . '.' . $extension;
            $upload_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                $photo_path = 'uploads/pets/' . $filename;
            } else {
                $errors[] = "Failed to upload photo.";
            }
        }
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO pets (animal_id, intake_type, intake_date, pet_name, animal_type, 
                                  pet_age, pet_size, color, breed, sex, adoption_status, photo_path, description)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $animal_id, $intake_type, $intake_date, $pet_name, $animal_type,
                $pet_age, $pet_size, $color, $breed, $sex, $adoption_status, $photo_path, $description
            ]);
            
            $success = "Pet added successfully!";
            // Clear form
            $_POST = [];
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
    <title>Add New Pet - Adoption Pawtal Admin</title>
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
                    <h1 class="page-title">🏠 ADD NEW PET</h1>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3 mb-4">
                        <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success) ?>
                        <a href="manage-pets.php" class="alert-link ms-2">View all pets</a>
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
                        <form method="POST" action="add-pet.php" enctype="multipart/form-data">
                            
                            <!-- Basic Information Section -->
                            <h4 class="section-title"><i class="fas fa-info-circle"></i> Basic Information</h4>
                            
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Animal ID <span class="required">*</span></label>
                                    <input type="text" name="animal_id" class="form-control" value="<?= htmlspecialchars($_POST['animal_id'] ?? '') ?>" placeholder="e.g. A537184" required>
                                    <div class="form-text">Must be unique</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Pet Name <span class="required">*</span></label>
                                    <input type="text" name="pet_name" class="form-control" value="<?= htmlspecialchars($_POST['pet_name'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Intake Type <span class="required">*</span></label>
                                    <select name="intake_type" class="form-select" required>
                                        <option value="">Select...</option>
                                        <option value="OWNER SUR" <?= ($_POST['intake_type'] ?? '') === 'OWNER SUR' ? 'selected' : '' ?>>Owner Surrender</option>
                                        <option value="STRAY" <?= ($_POST['intake_type'] ?? '') === 'STRAY' ? 'selected' : '' ?>>Stray</option>
                                        <option value="RETURN" <?= ($_POST['intake_type'] ?? '') === 'RETURN' ? 'selected' : '' ?>>Return</option>
                                        <option value="CONFISCATE" <?= ($_POST['intake_type'] ?? '') === 'CONFISCATE' ? 'selected' : '' ?>>Confiscate</option>
                                        <option value="WILDLIFE" <?= ($_POST['intake_type'] ?? '') === 'WILDLIFE' ? 'selected' : '' ?>>Wildlife</option>
                                        <option value="EUTH REQ" <?= ($_POST['intake_type'] ?? '') === 'EUTH REQ' ? 'selected' : '' ?>>Euthanasia Request</option>
                                        <option value="FOSTER" <?= ($_POST['intake_type'] ?? '') === 'FOSTER' ? 'selected' : '' ?>>Foster</option>
                                        <option value="BOARDING" <?= ($_POST['intake_type'] ?? '') === 'BOARDING' ? 'selected' : '' ?>>Boarding</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Intake Date <span class="required">*</span></label>
                                    <input type="date" name="intake_date" class="form-control" value="<?= htmlspecialchars($_POST['intake_date'] ?? '') ?>" required>
                                </div>
                            </div>

                            <!-- Animal Details Section -->
                            <h4 class="section-title"><i class="fas fa-paw"></i> Animal Details</h4>

                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Animal Type <span class="required">*</span></label>
                                    <select name="animal_type" class="form-select" required>
                                        <option value="">Select...</option>
                                        <option value="CAT" <?= ($_POST['animal_type'] ?? '') === 'CAT' ? 'selected' : '' ?>>Cat</option>
                                        <option value="DOG" <?= ($_POST['animal_type'] ?? '') === 'DOG' ? 'selected' : '' ?>>Dog</option>
                                        <option value="BIRD" <?= ($_POST['animal_type'] ?? '') === 'BIRD' ? 'selected' : '' ?>>Bird</option>
                                        <option value="OTHER" <?= ($_POST['animal_type'] ?? '') === 'OTHER' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Breed <span class="required">*</span></label>
                                    <input type="text" name="breed" class="form-control" value="<?= htmlspecialchars($_POST['breed'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Pet Age <span class="required">*</span></label>
                                    <input type="text" name="pet_age" class="form-control" value="<?= htmlspecialchars($_POST['pet_age'] ?? '') ?>" placeholder="e.g. 2 YEARS" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Pet Size <span class="required">*</span></label>
                                    <select name="pet_size" class="form-select" required>
                                        <option value="">Select...</option>
                                        <option value="SMALL" <?= ($_POST['pet_size'] ?? '') === 'SMALL' ? 'selected' : '' ?>>Small</option>
                                        <option value="MED" <?= ($_POST['pet_size'] ?? '') === 'MED' ? 'selected' : '' ?>>Medium</option>
                                        <option value="LARGE" <?= ($_POST['pet_size'] ?? '') === 'LARGE' ? 'selected' : '' ?>>Large</option>
                                        <option value="X-LRG" <?= ($_POST['pet_size'] ?? '') === 'X-LRG' ? 'selected' : '' ?>>Extra Large</option>
                                        <option value="KITTE" <?= ($_POST['pet_size'] ?? '') === 'KITTE' ? 'selected' : '' ?>>Kitten</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Sex <span class="required">*</span></label>
                                    <select name="sex" class="form-select" required>
                                        <option value="">Select...</option>
                                        <option value="M" <?= ($_POST['sex'] ?? '') === 'M' ? 'selected' : '' ?>>Male</option>
                                        <option value="F" <?= ($_POST['sex'] ?? '') === 'F' ? 'selected' : '' ?>>Female</option>
                                        <option value="N" <?= ($_POST['sex'] ?? '') === 'N' ? 'selected' : '' ?>>Neutered</option>
                                        <option value="S" <?= ($_POST['sex'] ?? '') === 'S' ? 'selected' : '' ?>>Spayed</option>
                                        <option value="U" <?= ($_POST['sex'] ?? '') === 'U' ? 'selected' : '' ?>>Unknown</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Color <span class="required">*</span></label>
                                    <input type="text" name="color" class="form-control" value="<?= htmlspecialchars($_POST['color'] ?? '') ?>" required>
                                </div>
                            </div>

                            <!-- Status & Media Section -->
                            <h4 class="section-title"><i class="fas fa-images"></i> Status & Media</h4>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Adoption Status</label>
                                        <select name="adoption_status" class="form-select">
                                            <option value="Available" <?= ($_POST['adoption_status'] ?? 'Available') === 'Available' ? 'selected' : '' ?>>Available</option>
                                            <option value="Pending" <?= ($_POST['adoption_status'] ?? '') === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="Reserved" <?= ($_POST['adoption_status'] ?? '') === 'Reserved' ? 'selected' : '' ?>>Reserved</option>
                                            <option value="Trial" <?= ($_POST['adoption_status'] ?? '') === 'Trial' ? 'selected' : '' ?>>Trial</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Photo Upload</label>
                                        <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/jpg">
                                        <div class="form-text">Accepts JPG, PNG (Max 5MB).</div>
                                    </div>
                                </div>
                                <div class="col-12 mt-2">
                                    <label class="form-label">Description (Optional)</label>
                                    <textarea name="description" class="form-control" rows="5" placeholder="Behavioral notes, medical information, personality traits..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <div class="row g-3 pt-3 border-top">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-gradient btn-lg">
                                        <i class="fas fa-plus-circle me-2"></i>Add Pet
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