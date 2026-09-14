<?php
/**
 * Helper Functions
 * Pet Adoption Management System
 */

/**
 * Sanitize and format phone number
 */
function formatPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) == 10) {
        return '(' . substr($phone, 0, 3) . ') ' . substr($phone, 3, 3) . '-' . substr($phone, 6);
    }
    return $phone;
}

/**
 * Format sex value for display
 */
function formatSex($sex) {
    return match($sex) {
        'M' => 'Male',
        'F' => 'Female',
        'N' => 'Neutered Male',
        'S' => 'Spayed Female',
        'U' => 'Unknown',
        default => $sex
    };
}

/**
 * Get badge class for adoption status
 */
function getStatusBadgeClass($status) {
    return match($status) {
        'Available' => 'bg-success',
        'Pending' => 'bg-warning text-dark',
        'Reserved' => 'bg-info',
        'Trial' => 'bg-secondary',
        default => 'bg-secondary'
    };
}

/**
 * Get badge class for application status
 */
function getApplicationStatusBadge($status) {
    return match($status) {
        'For Review' => 'bg-warning text-dark',
        'For Interview' => 'bg-info',
        'Accepted' => 'bg-success',
        'Rejected' => 'bg-danger',
        default => 'bg-secondary'
    };
}

/**
 * Calculate days since a date
 */
function daysSince($date) {
    $start = new DateTime($date);
    $now = new DateTime();
    return $now->diff($start)->days;
}

/**
 * Validate image file upload
 */
function validateImageUpload($file) {
    $errors = [];
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "File upload error.";
        return $errors;
    }
    
    if (!in_array($file['type'], $allowed_types)) {
        $errors[] = "Photo must be JPG or PNG format.";
    }
    
    if ($file['size'] > $max_size) {
        $errors[] = "Photo must be less than 5MB.";
    }
    
    return $errors;
}

/**
 * Upload pet photo
 */
function uploadPetPhoto($file, $animal_id, $old_photo_path = null) {
    $upload_dir = '../uploads/pets/';
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $animal_id . '_' . time() . '.' . $extension;
    $upload_path = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // Delete old photo if it exists
        if ($old_photo_path && file_exists('../' . $old_photo_path)) {
            unlink('../' . $old_photo_path);
        }
        return 'uploads/pets/' . $filename;
    }
    
    return false;
}

/**
 * Delete pet photo from server
 */
function deletePetPhoto($photo_path) {
    if ($photo_path && file_exists('../' . $photo_path)) {
        return unlink('../' . $photo_path);
    }
    return true;
}

/**
 * Check if pet has applications
 */
function petHasApplications($pdo, $animal_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM adoption_applications WHERE animal_id = ?");
    $stmt->execute([$animal_id]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Get application count for pet
 */
function getApplicationCount($pdo, $animal_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM adoption_applications WHERE animal_id = ?");
    $stmt->execute([$animal_id]);
    return $stmt->fetchColumn();
}

/**
 * Log activity (optional - for audit trail)
 */
function logActivity($pdo, $admin_id, $action, $details) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (admin_id, action, details, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$admin_id, $action, $details]);
    } catch (PDOException $e) {
        // Silently fail - logging shouldn't break the app
        error_log("Activity log error: " . $e->getMessage());
    }
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'M j, Y') {
    if (!$date) return 'N/A';
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime, $format = 'M j, Y g:i A') {
    if (!$datetime) return 'N/A';
    return date($format, strtotime($datetime));
}

/**
 * Truncate text with ellipsis
 */
function truncate($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

/**
 * Get available status transitions
 */
function getAvailableStatusTransitions($current_status) {
    return match($current_status) {
        'For Review' => ['For Interview', 'Rejected'],
        'For Interview' => ['Accepted', 'Rejected'],
        'Accepted' => ['For Review'], // Reopen
        'Rejected' => ['For Review'], // Reopen
        default => []
    };
}

/**
 * Validate status transition
 */
function isValidStatusTransition($current_status, $new_status) {
    $valid_transitions = getAvailableStatusTransitions($current_status);
    return in_array($new_status, $valid_transitions);
}

/**
 * Get animal type icon
 */
function getAnimalIcon($animal_type) {
    return match($animal_type) {
        'DOG' => '🐕',
        'CAT' => '🐱',
        'BIRD' => '🦜',
        default => '🐾'
    };
}

/**
 * Generate unique animal ID (helper for auto-generation if needed)
 */
function generateAnimalId($pdo, $prefix = 'A') {
    do {
        $id = $prefix . rand(100000, 999999);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pets WHERE animal_id = ?");
        $stmt->execute([$id]);
        $exists = $stmt->fetchColumn() > 0;
    } while ($exists);
    
    return $id;
}