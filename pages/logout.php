<?php
require_once '../includes/functions.php';

startSession();

// Destroy session and redirect to login
session_destroy();
header('Location: login.php');
exit;
?>

