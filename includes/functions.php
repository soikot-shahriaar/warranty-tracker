<?php
// Common functions for the Warranty Tracker CMS

// Start session if not already started
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get current user ID
function getCurrentUserId() {
    startSession();
    return $_SESSION['user_id'] ?? null;
}

// Get current user info
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id, username, email, created_at, updated_at FROM users WHERE id = ?");
    $stmt->execute([getCurrentUserId()]);
    return $stmt->fetch();
}

// Get base path for URLs (handles project subdirectory)
function getBasePath() {
    if (defined('BASE_PATH')) {
        return BASE_PATH;
    }
    // Fallback: detect from script path
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    $basePath = str_replace('\\', '/', $scriptPath);
    return rtrim($basePath, '/');
}

// Generate URL with base path
function url($path = '') {
    $basePath = getBasePath();
    $path = ltrim($path, '/');
    if ($basePath) {
        return $basePath . '/' . $path;
    }
    return '/' . $path;
}

// Redirect with proper base path handling
// Uses relative paths to avoid port number issues
function redirect($path) {
    $url = url($path);
    // Use relative path to avoid port issues (e.g., localhost:8080)
    header('Location: ' . $url);
    exit;
}

// Redirect to login if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('pages/login.php');
    }
}

// Sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Validate email format
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Generate CSRF token
function generateCSRFToken() {
    startSession();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    startSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Format date for display
function formatDate($date) {
    if (empty($date) || $date === null) {
        return 'Not available';
    }
    return date('M j, Y', strtotime($date));
}

// Calculate days until expiry
function daysUntilExpiry($expiryDate) {
    if (empty($expiryDate) || $expiryDate === null) {
        return null;
    }
    
    try {
        $today = new DateTime();
        $expiry = new DateTime($expiryDate);
        $diff = $today->diff($expiry);
        
        if ($expiry < $today) {
            return -$diff->days; // Negative for expired
        }
        return $diff->days;
    } catch (Exception $e) {
        return null;
    }
}

// Get warranty status
function getWarrantyStatus($expiryDate) {
    $daysLeft = daysUntilExpiry($expiryDate);
    
    if ($daysLeft === null) {
        return 'unknown';
    } elseif ($daysLeft < 0) {
        return 'expired';
    } elseif ($daysLeft <= 30) {
        return 'expiring-soon';
    } else {
        return 'active';
    }
}

// Upload file (for receipts/images)
function uploadFile($file, $uploadDir = 'assets/uploads/') {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $uploadPath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $filename;
    }
    
    return false;
}

// Delete uploaded file
function deleteUploadedFile($filename, $uploadDir = 'assets/uploads/') {
    if (empty($filename)) {
        return true;
    }
    
    $filePath = $uploadDir . $filename;
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    
    return true;
}

// Display flash messages
function setFlashMessage($type, $message) {
    startSession();
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function getFlashMessages() {
    startSession();
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

// Pagination helper
function paginate($totalItems, $itemsPerPage, $currentPage) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'offset' => $offset,
        'items_per_page' => $itemsPerPage
    ];
}
?>

