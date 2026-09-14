<?php
// admin/manage-pets.php
require_once '../includes/session.php';
requireAdmin();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Get filters
$type_filter = $_GET['type'] ?? '';
$status_filter = $_GET['status'] ?? '';
$intake_type_filter = $_GET['intake_type'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT * FROM pets WHERE 1=1";
$params = [];

if ($type_filter) {
    $sql .= " AND animal_type = ?";
    $params[] = $type_filter;
}

if ($status_filter) {
    $sql .= " AND adoption_status = ?";
    $params[] = $status_filter;
}

if ($intake_type_filter) {
    $sql .= " AND intake_type = ?";
    $params[] = $intake_type_filter;
}

if ($search) {
    $sql .= " AND (animal_id LIKE ? OR pet_name LIKE ? OR breed LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY intake_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pets = $stmt->fetchAll();

// Get unique values for filters
$animal_types = $pdo->query("SELECT DISTINCT animal_type FROM pets ORDER BY animal_type")->fetchAll(PDO::FETCH_COLUMN);
$intake_types = $pdo->query("SELECT DISTINCT intake_type FROM pets ORDER BY intake_type")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pets - Adoption Pawtal Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
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
            padding-top: 60px;
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
        .card-body { padding: 1.5rem; }
        .section-title { font-weight: 800; color: var(--text-dark); font-size: 1.8rem; }

        /* Buttons */
        .btn-gradient {
            background: var(--brand-gradient);
            color: white;
            border: none;
            font-weight: 700;
            padding: 10px 20px;
            border-radius: 10px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(255, 107, 107, 0.4); color: white; }
        
        .btn-filter { background: var(--primary-color); color: white; font-weight: 700; border: none; }
        .btn-filter:hover { background: var(--secondary-color); color: white; }

        /* Form Elements */
        .form-label { font-weight: 700; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control, .form-select {
            border: 2px solid #f1f2f6;
            border-radius: 10px;
            padding: 10px 15px;
            font-weight: 600;
            color: var(--text-dark);
        }
        .form-control:focus, .form-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(255, 107, 107, 0.1); }

        /* Table Styles */
        .table-responsive { overflow-x: auto; }
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
            font-size: 0.95rem;
            color: var(--text-dark);
            border-bottom: 1px solid #f1f2f6;
        }
        .pet-thumbnail { width: 50px; height: 50px; object-fit: cover; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .avatar-placeholder { width: 50px; height: 50px; background: #f1f2f6; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: var(--text-muted); }

        /* Status Badges */
        .badge-custom { padding: 6px 12px; border-radius: 30px; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; }
        .status-Available { background: #dcfce7; color: #15803d; }
        .status-Pending { background: #fef9c3; color: #a16207; }
        .status-Reserved { background: #dbeafe; color: #1e40af; }
        .status-Trial { background: #ccfbf1; color: #0f766e; }
        .status-Adopted { background: #f3f4f6; color: #374151; text-decoration: line-through; }

        /* Action Buttons */
        .btn-icon { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s; border: none; }
        .btn-edit { background: #e0f2fe; color: #0284c7; }
        .btn-edit:hover { background: #0284c7; color: white; }
        .btn-delete { background: #fee2e2; color: #dc2626; }
        .btn-delete:hover { background: #dc2626; color: white; }
        .btn-apps { background: #f3e8ff; color: #9333ea; width: auto; padding: 10 10px; font-size: 0.8rem; font-weight: 700; height: 32px; }
        .btn-apps:hover { background: #9333ea; color: white; }
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

    <div class="container-fluid px-4">
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
            <h1 class="section-title">🐾 MANAGE PETS</h1>
            <a href="add-pet.php" class="btn btn-gradient">
                <i class="fas fa-plus me-2"></i> Add New Pet
            </a>
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

        <!-- Filter Card -->
        <div class="content-card mb-4">
            <div class="card-body">
                <form method="GET" action="manage-pets.php" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Animal Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <?php foreach ($animal_types as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>" <?= $type_filter === $type ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="Available" <?= $status_filter === 'Available' ? 'selected' : '' ?>>Available</option>
                            <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Reserved" <?= $status_filter === 'Reserved' ? 'selected' : '' ?>>Reserved</option>
                            <option value="Trial" <?= $status_filter === 'Trial' ? 'selected' : '' ?>>Trial</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Intake Type</label>
                        <select name="intake_type" class="form-select">
                            <option value="">All Intake Types</option>
                            <?php foreach ($intake_types as $itype): ?>
                                <option value="<?= htmlspecialchars($itype) ?>" <?= $intake_type_filter === $itype ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($itype) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="ID, Name, Breed" value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-filter w-100">Filter</button>
                    </div>
                </form>
                <?php if($type_filter || $status_filter || $intake_type_filter || $search): ?>
                    <div class="mt-3">
                        <a href="manage-pets.php" class="text-muted text-decoration-none small">
                            <i class="fas fa-times me-1"></i> Clear Filters
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Table Card -->
        <div class="content-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="petsTable" class="table custom-table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="text-center">Photo</th>
                                <th>Details</th>
                                <th>Pet Name</th>
                                <th>Type / Breed</th>
                                <th>Intake</th>
                                <th>Status</th>
                                <th>Applications</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pets as $pet): ?>
                                <tr>
                                    <td class="text-center">
                                        <?php if ($pet['photo_path'] && file_exists('../' . $pet['photo_path'])): ?>
                                            <img src="../<?= htmlspecialchars($pet['photo_path']) ?>" class="pet-thumbnail" alt="Pet photo">
                                        <?php else: ?>
                                            <div class="avatar-placeholder mx-auto">
                                                <?= getAnimalIcon($pet['animal_type']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-dark">#<?= htmlspecialchars($pet['animal_id']) ?></span>
                                            <span class="small text-muted"><?= htmlspecialchars($pet['sex']) == 'M' ? 'Male' : 'Female' ?>, <?= htmlspecialchars($pet['pet_age']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold fs-6"><?= htmlspecialchars($pet['pet_name']) ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-dark"><?= htmlspecialchars($pet['animal_type']) ?></span>
                                            <span class="small text-muted"><?= htmlspecialchars($pet['breed']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span><?= date('M d, Y', strtotime($pet['intake_date'])) ?></span>
                                            <span class="small text-muted"><?= htmlspecialchars($pet['intake_type']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-custom status-<?= htmlspecialchars($pet['adoption_status']) ?>">
                                            <?= htmlspecialchars($pet['adoption_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php $appCount = getApplicationCount($pdo, $pet['animal_id']); ?>
                                        <?php if($appCount > 0): ?>
                                            <a href="manage-applications.php?pet=<?= urlencode($pet['animal_id']) ?>" class="btn btn-apps" title="View Applications">
                                                <i class="fas fa-file-alt me-1"></i> <?= $appCount ?> Apps
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">No Apps</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="edit-pet.php?id=<?= urlencode($pet['animal_id']) ?>" class="btn btn-icon btn-edit me-1" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <a href="delete-pet.php?id=<?= urlencode($pet['animal_id']) ?>" class="btn btn-icon btn-delete" onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($pet['pet_name']) ?>? This cannot be undone.')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (empty($pets)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-paw fa-3x text-muted opacity-25 mb-3"></i>
                        <h5 class="text-muted">No pets found matching your criteria.</h5>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#petsTable').DataTable({
                "pageLength": 25,
                "order": [], // Disable initial sort (let backend or HTML order prevail)
                "columnDefs": [
                    { "orderable": false, "targets": [0, 7] } // Disable sorting on Photo and Actions columns
                ],
                "language": {
                    "search": "",
                    "searchPlaceholder": "Quick filter..."
                },
                "dom": '<"d-flex justify-content-between align-items-center mb-3"f>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
            });
        });
    </script>
</body>
</html>