<?php
// admin/dashboard.php
require_once '../includes/session.php';
requireAdmin();
require_once '../config/database.php';

// Get statistics
$stats = [];

// Total pets by status
$stmt = $pdo->query("SELECT adoption_status, COUNT(*) as count FROM pets GROUP BY adoption_status");
while ($row = $stmt->fetch()) {
    $stats[$row['adoption_status']] = $row['count'];
}

$stats['total_pets'] = $pdo->query("SELECT COUNT(*) FROM pets")->fetchColumn();
$stats['available'] = $stats['Available'] ?? 0;
$stats['pending'] = $stats['Pending'] ?? 0;
$stats['reserved'] = $stats['Reserved'] ?? 0;
$stats['trial'] = $stats['Trial'] ?? 0;

// Application statistics
$stats['total_apps'] = $pdo->query("SELECT COUNT(*) FROM adoption_applications")->fetchColumn();
$stats['for_review'] = $pdo->query("SELECT COUNT(*) FROM adoption_applications WHERE status = 'For Review'")->fetchColumn();
$stats['for_interview'] = $pdo->query("SELECT COUNT(*) FROM adoption_applications WHERE status = 'For Interview'")->fetchColumn();
$stats['accepted'] = $pdo->query("SELECT COUNT(*) FROM adoption_applications WHERE status = 'Accepted'")->fetchColumn();
$stats['rejected'] = $pdo->query("SELECT COUNT(*) FROM adoption_applications WHERE status = 'Rejected'")->fetchColumn();

// Recent applications
$recent_apps = $pdo->query("
    SELECT aa.application_id, aa.application_date, aa.status,
           CONCAT(a.first_name, ' ', a.last_name) as applicant_name,
           p.pet_name, p.animal_type
    FROM adoption_applications aa
    JOIN adopters a ON aa.adopter_id = a.adopter_id
    JOIN pets p ON aa.animal_id = p.animal_id
    ORDER BY aa.application_date DESC
    LIMIT 10
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Adoption Pawtal</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
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
            min-height: 100vh;
        }

        /* Navbar Styling */
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

        .nav-link {
            font-weight: 700;
            color: var(--text-muted) !important;
            transition: color 0.3s;
            margin: 0 10px;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary-color) !important;
        }

        .btn-logout {
            background: #fff0f0;
            color: #dc3545;
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 700;
            transition: all 0.3s;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn-logout:hover {
            background: #dc3545;
            color: white;
        }

        /* Page Content */
        .dashboard-header {
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-weight: 800;
            color: var(--text-dark);
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary-color);
            padding-left: 15px;
        }

        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }

        .stat-card-body {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            flex-shrink: 0;
        }

        .stat-text h3 {
            font-weight: 800;
            font-size: 2rem;
            margin-bottom: 0;
            color: var(--text-dark);
        }

        .stat-text p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 0;
            font-weight: 600;
        }

        /* Color Variants for Stats */
        .stat-purple .stat-icon-wrapper { background: #f3e8ff; color: #9333ea; }
        .stat-green .stat-icon-wrapper { background: #dcfce7; color: #16a34a; }
        .stat-orange .stat-icon-wrapper { background: #ffedd5; color: #ea580c; }
        .stat-blue .stat-icon-wrapper { background: #dbeafe; color: #2563eb; }
        .stat-red .stat-icon-wrapper { background: #fee2e2; color: #dc2626; }
        .stat-yellow .stat-icon-wrapper { background: #fef9c3; color: #ca8a04; }
        .stat-teal .stat-icon-wrapper { background: #ccfbf1; color: #0d9488; }
        .stat-gray .stat-icon-wrapper { background: #f3f4f6; color: #4b5563; }


        /* Recent Applications Table */
        .table-card {
            background: white;
            border-radius: 25px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            overflow: hidden;
            padding: 0;
        }

        .table-responsive {
            padding: 0;
            margin: 0;
        }

        .custom-table {
            margin-bottom: 0;
            width: 100%;
        }

        .custom-table thead th {
            background: #f8fafc;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            padding: 1.2rem 1.5rem;
            border-bottom: 2px solid #f1f2f6;
        }

        .custom-table tbody td {
            padding: 1.2rem 1.5rem;
            vertical-align: middle;
            color: var(--text-dark);
            font-weight: 600;
            border-bottom: 1px solid #f1f2f6;
        }

        .custom-table tr:last-child td {
            border-bottom: none;
        }

        .custom-table tr:hover td {
            background-color: #fcfcfc;
        }

        /* Status Badges */
        .status-badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        .status-review { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        .status-interview { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
        .status-accepted { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
        .status-rejected { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; }

        .btn-view {
            background: var(--brand-gradient);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 6px 15px;
            font-size: 0.85rem;
            font-weight: 700;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(255, 107, 107, 0.2);
        }

        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 107, 107, 0.3);
            color: white;
        }

        .view-all-link {
            display: block;
            text-align: center;
            padding: 1.5rem;
            background: #f8fafc;
            color: var(--primary-color);
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .view-all-link:hover {
            background: #f1f5f9;
            color: var(--secondary-color);
        }
    </style>
</head>
<body>
    
    <!-- Modern Navbar -->
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
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage-pets.php">Manage Pets</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage-applications.php">Manage Applications</a>
                    </li>
                </ul>
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item me-3">
                        <span class="text-muted fw-bold">
                            <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars(getAdminName()) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn-logout" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content with padding for fixed navbar -->
    <div class="container-fluid px-4" style="padding-top: 80px; padding-bottom: 40px;">
        
        <div class="dashboard-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="page-title">🐾 DASHBOARD OVERVIEW</h2>
                <p class="text-muted mb-0">Welcome back, here's what's happening today!</p>
            </div>
            <a href="add-pet.php" class="btn btn-view py-2 px-4">
                <i class="fas fa-plus me-2"></i> Add New Pet
            </a>
        </div>

        <!-- 1. Pet Statistics -->
        <h5 class="section-title"><i class="fas fa-paw me-2"></i> Pet Inventory</h5>
        <div class="row g-4 mb-5">
            <!-- Total Pets -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="stat-card stat-purple">
                    <div class="stat-card-body">
                        <div class="stat-text">
                            <h3><?= $stats['total_pets'] ?></h3>
                            <p>Total Pets</p>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-paw"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Available -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="stat-card stat-green">
                    <div class="stat-card-body">
                        <div class="stat-text">
                            <h3><?= $stats['available'] ?></h3>
                            <p>Available</p>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="stat-card stat-yellow">
                    <div class="stat-card-body">
                        <div class="stat-text">
                            <h3><?= $stats['pending'] ?></h3>
                            <p>Pending</p>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reserved -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="stat-card stat-blue">
                    <div class="stat-card-body">
                        <div class="stat-text">
                            <h3><?= $stats['reserved'] ?></h3>
                            <p>Reserved</p>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-bookmark"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trial -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="stat-card stat-teal">
                    <div class="stat-card-body">
                        <div class="stat-text">
                            <h3><?= $stats['trial'] ?></h3>
                            <p>On Trial</p>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-home"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Adopted/Other (Optional Filler) -->
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="stat-card stat-gray">
                    <div class="stat-card-body">
                        <div class="stat-text">
                            <h3><?= $stats['total_pets'] - $stats['available'] - $stats['pending'] - $stats['reserved'] - $stats['trial'] ?></h3>
                            <p>Adopted/Other</p>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-heart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Application Statistics -->
        <h5 class="section-title"><i class="fas fa-file-alt me-2"></i> Application Pipeline</h5>
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="stat-card stat-gray">
                    <div class="stat-card-body">
                        <div class="stat-text">
                            <h3><?= $stats['total_apps'] ?></h3>
                            <p>All Time</p>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-folder"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-orange">
                    <div class="stat-card-body">
                        <div class="stat-text">
                            <h3><?= $stats['for_review'] ?></h3>
                            <p>For Review</p>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-blue">
                    <div class="stat-card-body">
                        <div class="stat-text">
                            <h3><?= $stats['for_interview'] ?></h3>
                            <p>Interview</p>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-comments"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-green">
                    <div class="stat-card-body">
                        <div class="stat-text">
                            <h3><?= $stats['accepted'] ?></h3>
                            <p>Accepted</p>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-thumbs-up"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Recent Applications Table -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="section-title mb-0"><i class="fas fa-clock me-2"></i> Recent Applications</h5>
                    <a href="manage-applications.php" class="text-decoration-none fw-bold" style="color: var(--primary-color);">View All <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="table-card">
                    <div class="table-responsive">
                        <table class="table custom-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date Submitted</th>
                                    <th>Applicant</th>
                                    <th>Pet Details</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($recent_apps) > 0): ?>
                                    <?php foreach ($recent_apps as $app): ?>
                                        <tr>
                                            <td><span class="text-muted">#<?= $app['application_id'] ?></span></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="far fa-calendar-alt me-2 text-muted"></i>
                                                    <?= date('M j, Y', strtotime($app['application_date'])) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($app['applicant_name']) ?></div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold text-dark"><?= htmlspecialchars($app['pet_name']) ?></span>
                                                    <span class="small text-muted"><?= htmlspecialchars($app['animal_type']) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = 'bg-light text-dark';
                                                if ($app['status'] == 'For Review') $statusClass = 'status-review';
                                                elseif ($app['status'] == 'For Interview') $statusClass = 'status-interview';
                                                elseif ($app['status'] == 'Accepted') $statusClass = 'status-accepted';
                                                elseif ($app['status'] == 'Rejected') $statusClass = 'status-rejected';
                                                ?>
                                                <span class="status-badge <?= $statusClass ?>">
                                                    <?= htmlspecialchars($app['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="application-details.php?id=<?= $app['application_id'] ?>" class="btn btn-view">
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
                                            <p>No applications found.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="manage-applications.php" class="view-all-link">
                        See All Applications
                    </a>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>