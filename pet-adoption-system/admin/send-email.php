<?php
// admin/send-email.php
require_once '../includes/session.php';
requireAdmin();
require_once '../config/database.php';
require_once '../includes/email-functions.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage-applications.php');
    exit();
}

$application_id = $_POST['application_id'] ?? '';
$email_type = $_POST['email_type'] ?? '';

if (!$application_id || !$email_type) {
    $_SESSION['error'] = "Application ID and email type are required.";
    header('Location: manage-applications.php');
    exit();
}

error_log("Send email request - App ID: $application_id, Type: $email_type");

try {
    // Handle different email types
    if ($email_type === 'custom') {
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        if (empty($subject) || empty($message)) {
            $_SESSION['error'] = "Subject and message are required for custom emails.";
            header("Location: application-details.php?id=" . urlencode($application_id));
            exit();
        }
        
        error_log("Sending custom email - Subject: $subject");
        $result = sendApplicationEmail($pdo, $application_id, 'custom', $subject, $message);
    } else {
        // Send template email
        $interview_date = $_POST['interview_date'] ?? null;
        error_log("Sending template email - Type: $email_type");
        $result = sendApplicationEmail($pdo, $application_id, $email_type, null, null, $interview_date);
    }
    
    error_log("Email send result: " . json_encode($result));
    
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = "An error occurred: " . $e->getMessage();
    error_log("Send email exception: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
}

// Redirect back to application details page
header("Location: application-details.php?id=" . urlencode($application_id));
exit();