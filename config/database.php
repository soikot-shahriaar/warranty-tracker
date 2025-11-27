<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'warranty_tracker');
define('DB_USER', 'root');
define('DB_PASS', '');

// Base URL configuration
// Automatically detect the base path from the current script location
if (isset($_SERVER['SCRIPT_NAME'])) {
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    $basePath = str_replace('\\', '/', $scriptPath);
    $basePath = rtrim($basePath, '/');
    
    // Get the project directory name from the base path
    $pathParts = explode('/', trim($basePath, '/'));
    $projectName = end($pathParts);
    
    // Set base URL (without port, using standard HTTP port 80)
    // For localhost: http://localhost/project_name
    // For production: https://yourdomain.com
    define('BASE_URL', 'http://localhost' . ($projectName ? '/' . $projectName : ''));
    define('BASE_PATH', $basePath ? $basePath : '');
} else {
    // Fallback if SCRIPT_NAME is not available
    define('BASE_URL', 'http://localhost');
    define('BASE_PATH', '');
}

// Create database connection
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Test database connection
function testDBConnection() {
    try {
        $pdo = getDBConnection();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>

