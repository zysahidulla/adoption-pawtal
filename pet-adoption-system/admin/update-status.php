<?php
// admin/update-status.php
require_once '../includes/session.php';
requireAdmin();
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage-applications.php');
    exit();
}

$application_id = $_POST['application_id'] ?? '';
$new_status = $_POST['new_status'] ?? '';

if (!$application_id || !$new_status) {
    $_SESSION['error'] = "Invalid request.";
    header('Location: manage-applications.php');
    exit();
}

try {
    // Get current application status
    $stmt = $pdo->prepare("SELECT * FROM adoption_applications WHERE application_id = ?");
    $stmt->execute([$application_id]);
    $app = $stmt->fetch();
    
    if (!$app) {
        $_SESSION['error'] = "Application not found.";
        header('Location: manage-applications.php');
        exit();
    }
    
    // Validate status transition
    if (!isValidStatusTransition($app['status'], $new_status)) {
        $_SESSION['error'] = "Invalid status transition from '{$app['status']}' to '{$new_status}'.";
        header('Location: application-details.php?id=' . urlencode($application_id));
        exit();
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Update application status
    $stmt = $pdo->prepare("
        UPDATE adoption_applications 
        SET status = ?, updated_at = NOW() 
        WHERE application_id = ?
    ");
    $stmt->execute([$new_status, $application_id]);
    
    // Update pet status based on application status
    if ($new_status === 'Accepted') {
        // Set pet to Pending when application is accepted
        $stmt = $pdo->prepare("
            UPDATE pets 
            SET adoption_status = 'Pending' 
            WHERE animal_id = ?
        ");
        $stmt->execute([$app['animal_id']]);
        
        // Reject all other pending applications for this pet
        $stmt = $pdo->prepare("
            UPDATE adoption_applications 
            SET status = 'Rejected', 
                notes = CONCAT(COALESCE(notes, ''), '\n[Auto-rejected: Another application was accepted]'),
                updated_at = NOW()
            WHERE animal_id = ? 
            AND application_id != ? 
            AND status IN ('For Review', 'For Interview')
        ");
        $stmt->execute([$app['animal_id'], $application_id]);
    }
    
    // If reopening from Accepted, set pet back to Available
    if ($app['status'] === 'Accepted' && $new_status === 'For Review') {
        $stmt = $pdo->prepare("
            UPDATE pets 
            SET adoption_status = 'Available' 
            WHERE animal_id = ?
        ");
        $stmt->execute([$app['animal_id']]);
    }
    
    // Commit transaction
    $pdo->commit();
    
    $_SESSION['success'] = "Application status updated to '{$new_status}' successfully!";
    
    // Add note about the auto-email reminder
    if ($new_status === 'For Interview') {
        $_SESSION['success'] .= " Don't forget to send an interview request email.";
    } elseif ($new_status === 'Accepted') {
        $_SESSION['success'] .= " Don't forget to send an acceptance email.";
    } elseif ($new_status === 'Rejected') {
        $_SESSION['success'] .= " Consider sending a rejection notice email.";
    }
    
    header('Location: application-details.php?id=' . urlencode($application_id));
    exit();
    
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Error updating status: " . $e->getMessage();
    header('Location: application-details.php?id=' . urlencode($application_id));
    exit();
}