<?php
// admin/save-notes.php
require_once '../includes/session.php';
requireAdmin();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage-applications.php');
    exit();
}

$application_id = $_POST['application_id'] ?? '';
$notes = trim($_POST['notes'] ?? '');

if (!$application_id) {
    $_SESSION['error'] = "Invalid application ID.";
    header('Location: manage-applications.php');
    exit();
}

try {
    $stmt = $pdo->prepare("
        UPDATE adoption_applications 
        SET notes = ?, updated_at = NOW() 
        WHERE application_id = ?
    ");
    
    $stmt->execute([$notes, $application_id]);
    
    $_SESSION['success'] = "Notes saved successfully!";
    header('Location: application-details.php?id=' . urlencode($application_id));
    exit();
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Error saving notes: " . $e->getMessage();
    header('Location: application-details.php?id=' . urlencode($application_id));
    exit();
}