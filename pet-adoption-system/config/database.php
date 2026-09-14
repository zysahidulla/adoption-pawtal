<?php
/**
 * Database Configuration and Connection
 * Pet Adoption Management System
 */

// Database credentials
$host = 'localhost';        // Database host (usually 'localhost' for XAMPP/WAMP)
$dbname = 'pet_adoption_system';  // Database name
$username = 'root';         // Database username (default 'root' for XAMPP/WAMP)
$password = '';             // Database password (default empty for XAMPP/WAMP)

// Character set
$charset = 'utf8mb4';

// DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// PDO options for better error handling and security
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Fetch associative arrays by default
    PDO::ATTR_EMULATE_PREPARES   => false,                   // Use real prepared statements
    PDO::ATTR_PERSISTENT         => false,                   // Don't use persistent connections
];

// Create PDO connection
try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please check your configuration or contact the administrator.");
}

// Optional: Set timezone (adjust to your timezone)
date_default_timezone_set('Asia/Manila'); // Philippines timezone

?>