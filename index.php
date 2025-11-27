<?php
// Warranty Tracker CMS - Main Entry Point
// Redirect to the appropriate page based on user authentication status

require_once 'config/database.php';
require_once 'includes/functions.php';

startSession();

// Check if user is logged in
if (isLoggedIn()) {
    // Redirect to dashboard if logged in
    header('Location: pages/dashboard.php');
} else {
    // Redirect to login if not logged in
    header('Location: pages/login.php');
}

exit;
?>

