<?php
// admin/manage-applications.php
require_once '../includes/session.php';
requireAdmin();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Get filters
$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? '';
$pet_filter = $_GET['pet'] ?? '';
$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// --- NEW: Get Application Statistics ---
$app_stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM adoption_applications")->fetchColumn(),
    'review' => $pdo->query("SELECT COUNT(*) FROM adoption_applications WHERE status = 'For Review'")->fetchColumn(),
    'interview' => $pdo->query("SELECT COUNT(*) FROM adoption_applications WHERE status = 'For Interview'")->fetchColumn(),
    'accepted' => $pdo->query("SELECT COUNT(*) FROM adoption_applications WHERE status = 'Accepted'")->fetchColumn(),
    'rejected' => $pdo->query("SELECT COUNT(*) FROM adoption_applications WHERE status = 'Rejected'")->fetchColumn(),
];

// Build query
$sql = "SELECT aa.*, 
        CONCAT(a.first_name, ' ', a.last_name) as adopter_name,
        a.email,
        a.phone,
        p.pet_name,
        p.animal_type,
        p.animal_id
    FROM adoption_applications aa
    JOIN adopters a ON aa.adopter_id = a.adopter_id
    JOIN pets p ON aa.animal_id = p.animal_id
    WHERE 1=1";

$params = [];

if ($status_filter) {
    $sql .= " AND aa.status = ?";
    $params[] = $status_filter;
}

if ($type_filter) {
    $sql .= " AND p.animal_type = ?";
    $params[] = $type_filter;
}

if ($pet_filter) {
    $sql .= " AND p.animal_id = ?";
    $params[] = $pet_filter;
}

if ($date_from) {
    $sql .= " AND aa.application_date >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $sql .= " AND aa.application_date <= ?";
    $params[] = $date_to;
}

if ($search) {
    $sql .= " AND (a.first_name LIKE ? OR a.last_name LIKE ? OR a.email LIKE ? OR p.pet_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY aa.application_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();

// Get unique types for filter
$types = $pdo->query("SELECT DISTINCT animal_type FROM pets ORDER BY animal_type")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Applications - Adoption Pawtal Admin</title>
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
        .card-body { padding: 1.5rem; }
        .section-title { font-weight: 800; color: var(--text-dark); font-size: 1.8rem; }

        /* --- NEW: Stat Cards Styles (Same as Dashboard) --- */
        .stat-card {
            background: white;
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            overflow: hidden;
            position: relative;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }
        .stat-card-body {
            padding: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stat-icon-wrapper {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }
        .stat-text h3 {
            font-weight: 800;
            font-size: 1.8rem;
            margin-bottom: 0;
            color: var(--text-dark);
            line-height: 1;
        }
        .stat-text p {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-bottom: 0;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        /* Color Variants */
        .stat-purple .stat-icon-wrapper { background: #f3e8ff; color: #9333ea; }
        .stat-orange .stat-icon-wrapper { background: #ffedd5; color: #ea580c; }
        .stat-blue .stat-icon-wrapper { background: #dbeafe; color: #2563eb; }
        .stat-green .stat-icon-wrapper { background: #dcfce7; color: #16a34a; }
        .stat-red .stat-icon-wrapper { background: #fee2e2; color: #dc2626; }

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

        /* Status Badges */
        .badge-custom { padding: 6px 12px; border-radius: 30px; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; }
        .status-Review { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        .status-Interview { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
        .status-Accepted { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
        .status-Rejected { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; }

        /* Action Buttons */
        .btn-view { background: #e0f2fe; color: #0284c7; border: none; width: 35px; height: 35px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s; }
        .btn-view:hover { background: #0284c7; color: white; }
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
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
            <h1 class="section-title">🐾 MANAGE APPLICATIONS</h1>
        </div>

        <!-- --- NEW: Statistics Cards --- -->
        <div class="row g-4 mb-4 row-cols-1 row-cols-md-5">
            <!-- Total -->
            <div class="col">
                <div class="stat-card stat-purple">
                    <div class="stat-card-body">
                        <div class="stat-text">
                            <h3><?= $app_stats['total'] ?></h3>
                            <p>Total</p>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-folder"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- For Review -->
            <div class="col">
                <div class="stat-card stat-orange">
                    <div class="stat-card-body">
                        <div class="stat-text">
                            <h3><?= $app_stats['review'] ?></h3>
                            <p>For Review</p>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- For Interview -->
            <div class="col">
                <div class="stat-card stat-blue">
                    <div class="stat-card-body">
                        <div class="stat-text">
                            <h3><?= $app_stats['interview'] ?></h3>
                            <p>Interview</p>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-comments"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accepted -->
            <div class="col">
                <div class="stat-card stat-green">
                    <div class="stat-card-body">
                        <div class="stat-text">
                            <h3><?= $app_stats['accepted'] ?></h3>
                            <p>Accepted</p>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rejected -->
            <div class="col">
                <div class="stat-card stat-red">
                    <div class="stat-card-body">
                        <div class="stat-text">
                            <h3><?= $app_stats['rejected'] ?></h3>
                            <p>Rejected</p>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
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

        <!-- Filter Card -->
        <div class="content-card mb-4">
            <div class="card-body">
                <form method="GET" action="manage-applications.php" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="For Review" <?= $status_filter === 'For Review' ? 'selected' : '' ?>>For Review</option>
                            <option value="For Interview" <?= $status_filter === 'For Interview' ? 'selected' : '' ?>>For Interview</option>
                            <option value="Accepted" <?= $status_filter === 'Accepted' ? 'selected' : '' ?>>Accepted</option>
                            <option value="Rejected" <?= $status_filter === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Animal Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <?php foreach ($types as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>" <?= $type_filter === $type ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($date_from) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($date_to) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Name, Email, Pet" value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-filter w-100">Filter</button>
                    </div>
                </form>
                <?php if($status_filter || $type_filter || $date_from || $date_to || $search): ?>
                    <div class="mt-3">
                        <a href="manage-applications.php" class="text-muted text-decoration-none small">
                            <i class="fas fa-times me-1"></i> Clear Filters
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Applications Table -->
        <div class="content-card">
            <div class="card-body">
                    <table id="applicationsTable" class="table custom-table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>App ID</th>
                                <th>Date Submitted</th>
                                <th>Applicant Name</th>
                                <th>Contact</th>
                                <th>Pet Details</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td><strong>#<?= $app['application_id'] ?></strong></td>
                                    <td><?= date('M d, Y', strtotime($app['application_date'])) ?> <br> <small class="text-muted"><?= date('h:i A', strtotime($app['application_date'])) ?></small></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($app['adopter_name']) ?></div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column small">
                                            <span><i class="fas fa-envelope me-1 text-muted"></i> <?= htmlspecialchars($app['email']) ?></span>
                                            <span><i class="fas fa-phone me-1 text-muted"></i> <?= formatPhone($app['phone']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-dark"><?= htmlspecialchars($app['pet_name']) ?></span>
                                            <span class="small text-muted"><?= htmlspecialchars($app['animal_type']) ?> (#<?= $app['animal_id'] ?>)</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $statusKey = $app['status'];
                                            // Normalize status for CSS class if needed
                                            if (strpos($statusKey, 'Review') !== false) $statusKey = 'Review';
                                            elseif (strpos($statusKey, 'Interview') !== false) $statusKey = 'Interview';
                                        ?>
                                        <span class="badge-custom status-<?= $statusKey ?>">
                                            <?= htmlspecialchars($app['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="application-details.php?id=<?= $app['application_id'] ?>" class="btn btn-view" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (empty($applications)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted opacity-25 mb-3"></i>
                        <h5 class="text-muted">No applications found matching your criteria.</h5>
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
            $('#applicationsTable').DataTable({
                "pageLength": 25,
                "order": [], // Disable initial sort
                "columnDefs": [
                    { "orderable": false, "targets": [6] } // Disable sorting on Actions
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