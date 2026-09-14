<?php
// public/submit-application.php
session_start();
require_once '../config/database.php';
require_once '../includes/email-functions.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

// Get and sanitize form data
$animal_id = $_POST['animal_id'] ?? '';
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$postal_code = trim($_POST['postal_code'] ?? '');
$household_type = $_POST['household_type'] ?? '';
$has_other_pets = $_POST['has_other_pets'] ?? '';
$other_pets_details = trim($_POST['other_pets_details'] ?? '');
$has_children = $_POST['has_children'] ?? '';
$children_ages = trim($_POST['children_ages'] ?? '');
$experience_level = $_POST['experience_level'] ?? '';
$reason_for_adoption = trim($_POST['reason_for_adoption'] ?? '');
$reference1_name = trim($_POST['reference1_name'] ?? '');
$reference1_phone = trim($_POST['reference1_phone'] ?? '');
$reference1_relationship = trim($_POST['reference1_relationship'] ?? '');
$reference2_name = trim($_POST['reference2_name'] ?? '');
$reference2_phone = trim($_POST['reference2_phone'] ?? '');
$reference2_relationship = trim($_POST['reference2_relationship'] ?? '');

// Validate required fields
$errors = [];

if (empty($animal_id)) $errors[] = "Pet selection is required.";
if (empty($first_name)) $errors[] = "First name is required.";
if (empty($last_name)) $errors[] = "Last name is required.";
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
if (empty($phone)) $errors[] = "Phone is required.";
if (empty($address)) $errors[] = "Address is required.";
if (empty($city)) $errors[] = "City is required.";
if (empty($postal_code)) $errors[] = "Postal code is required.";
if (empty($household_type)) $errors[] = "Household type is required.";
if ($has_other_pets === '') $errors[] = "Please indicate if you have other pets.";
if ($has_children === '') $errors[] = "Please indicate if you have children.";
if (empty($experience_level)) $errors[] = "Experience level is required.";
if (empty($reason_for_adoption)) $errors[] = "Reason for adoption is required.";

if ($has_other_pets == '1' && empty($other_pets_details)) {
    $errors[] = "Please describe your other pets.";
}

if ($has_children == '1' && empty($children_ages)) {
    $errors[] = "Please provide children ages.";
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    header('Location: apply.php?pet_id=' . urlencode($animal_id));
    exit();
}

try {
    // Verify pet exists and is available
    $stmt = $pdo->prepare("SELECT * FROM pets WHERE animal_id = ? AND adoption_status = 'Available'");
    $stmt->execute([$animal_id]);
    $pet = $stmt->fetch();
    
    if (!$pet) {
        $_SESSION['error'] = "The selected pet is no longer available for adoption.";
        header('Location: index.php');
        exit();
    }
    
    // Check if this email has already applied for this pet
    $check_stmt = $pdo->prepare("
        SELECT COUNT(*) FROM adoption_applications aa
        JOIN adopters a ON aa.adopter_id = a.adopter_id
        WHERE a.email = ? AND aa.animal_id = ?
    ");
    $check_stmt->execute([$email, $animal_id]);
    $already_applied = $check_stmt->fetchColumn();
    
    if ($already_applied > 0) {
        $_SESSION['error'] = "You have already submitted an application for this pet.";
        header('Location: pet-details.php?id=' . urlencode($animal_id));
        exit();
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Insert adopter
    $stmt = $pdo->prepare("
        INSERT INTO adopters (
            first_name, last_name, email, phone, address, city, postal_code,
            household_type, has_other_pets, other_pets_details, has_children, children_ages,
            experience_level, reason_for_adoption,
            reference1_name, reference1_phone, reference1_relationship,
            reference2_name, reference2_phone, reference2_relationship
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $first_name, $last_name, $email, $phone, $address, $city, $postal_code,
        $household_type, $has_other_pets, $other_pets_details, $has_children, $children_ages,
        $experience_level, $reason_for_adoption,
        $reference1_name, $reference1_phone, $reference1_relationship,
        $reference2_name, $reference2_phone, $reference2_relationship
    ]);
    
    $adopter_id = $pdo->lastInsertId();
    
    // Insert application
    $stmt = $pdo->prepare("
        INSERT INTO adoption_applications (adopter_id, animal_id, status)
        VALUES (?, ?, 'For Review')
    ");
    $stmt->execute([$adopter_id, $animal_id]);
    
    $application_id = $pdo->lastInsertId();
    
    // Commit transaction BEFORE sending email
    $pdo->commit();
    
    error_log("Application created successfully. ID: $application_id");
    error_log("Attempting to send confirmation email...");
    
    // Send confirmation email
    $email_result = sendApplicationEmail($pdo, $application_id, 'confirmation');
    
    error_log("Email result: " . json_encode($email_result));
    
    // Log the result (but don't fail the application if email fails)
    if (!$email_result['success']) {
        error_log("Confirmation email failed for application #{$application_id}: " . $email_result['message']);
        $_SESSION['email_warning'] = "Your application was submitted successfully, but there was an issue sending the confirmation email. You may not receive an email confirmation, but your application is in our system.";
    }
    
    // Redirect to success page
    header('Location: success.php?app_id=' . $application_id);
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Application submission error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $_SESSION['error'] = "An error occurred while submitting your application. Please try again.";
    header('Location: apply.php?pet_id=' . urlencode($animal_id));
    exit();
}