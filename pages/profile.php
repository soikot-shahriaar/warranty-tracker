<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();

$user = getCurrentUser();
$userId = getCurrentUserId();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $username = sanitizeInput($_POST['username'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        
        // Validation
        if (empty($username)) {
            $errors[] = 'Username is required.';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters long.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, and underscores.';
        }
        
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!isValidEmail($email)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        // Check for existing username/email (excluding current user)
        if (empty($errors)) {
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
                $stmt->execute([$username, $email, $userId]);
                
                if ($stmt->fetch()) {
                    $errors[] = 'Username or email already exists.';
                }
            } catch (Exception $e) {
                $errors[] = 'Profile update failed. Please try again.';
            }
        }
        
        // Update profile if no errors
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$username, $email, $userId]);
                
                // Update session
                $_SESSION['username'] = $username;
                
                // Refresh user data
                $user = getCurrentUser();
                
                setFlashMessage('success', 'Profile updated successfully!');
                $success = true;
            } catch (Exception $e) {
                $errors[] = 'Profile update failed. Please try again.';
            }
        }
    } elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($currentPassword)) {
            $errors[] = 'Current password is required.';
        } else {
            // Verify current password
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $userPassword = $stmt->fetchColumn();
                
                if (!verifyPassword($currentPassword, $userPassword)) {
                    $errors[] = 'Current password is incorrect.';
                }
            } catch (Exception $e) {
                $errors[] = 'Password verification failed. Please try again.';
            }
        }
        
        if (empty($newPassword)) {
            $errors[] = 'New password is required.';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters long.';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'New passwords do not match.';
        }
        
        // Update password if no errors
        if (empty($errors)) {
            try {
                $passwordHash = hashPassword($newPassword);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$passwordHash, $userId]);
                
                setFlashMessage('success', 'Password changed successfully!');
                $success = true;
            } catch (Exception $e) {
                $errors[] = 'Password update failed. Please try again.';
            }
        }
    }
}

// Get user statistics
try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM warranties WHERE user_id = ?");
    $stmt->execute([$userId]);
    $totalWarranties = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT MIN(created_at) as first_warranty FROM warranties WHERE user_id = ?");
    $stmt->execute([$userId]);
    $firstWarranty = $stmt->fetchColumn();
    
} catch (Exception $e) {
    $totalWarranties = 0;
    $firstWarranty = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Warranty Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>My Profile</h1>
                <p>Manage your account settings and information</p>
            </div>
            
            <?php foreach (getFlashMessages() as $message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo htmlspecialchars($message['message']); ?>
                </div>
            <?php endforeach; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="profile-grid">
                <!-- Profile Information -->
                <div class="profile-section">
                    <div class="section-header">
                        <h2>Profile Information</h2>
                    </div>
                    
                    <form method="POST" class="profile-form">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required 
                                   value="<?php echo htmlspecialchars($user['username']); ?>">
                            <small>3+ characters, letters, numbers, and underscores only</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required 
                                   value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
                
                <!-- Change Password -->
                <div class="profile-section">
                    <div class="section-header">
                        <h2>Change Password</h2>
                    </div>
                    
                    <form method="POST" class="profile-form">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required>
                            <small>Minimum 6 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </div>
                    </form>
                </div>
                
                <!-- Account Statistics -->
                <div class="profile-section">
                    <div class="section-header">
                        <h2>Account Statistics</h2>
                    </div>
                    
                    <div class="stats-list">
                        <div class="stat-item">
                            <span class="stat-label">Total Warranties:</span>
                            <span class="stat-value"><?php echo $totalWarranties; ?></span>
                        </div>
                        
                        <div class="stat-item">
                            <span class="stat-label">Member Since:</span>
                            <span class="stat-value"><?php echo formatDate($user['created_at']); ?></span>
                        </div>
                        
                        <?php if ($firstWarranty): ?>
                        <div class="stat-item">
                            <span class="stat-label">First Warranty Added:</span>
                            <span class="stat-value"><?php echo formatDate($firstWarranty); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="stat-item">
                            <span class="stat-label">Last Updated:</span>
                            <span class="stat-value"><?php echo formatDate($user['updated_at']); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Account Actions -->
                <div class="profile-section">
                    <div class="section-header">
                        <h2>Account Actions</h2>
                    </div>
                    
                    <div class="action-list">
                        <a href="warranties.php" class="action-item">
                            <span class="action-icon">📋</span>
                            <div class="action-content">
                                <h4>View All Warranties</h4>
                                <p>Manage your warranty records</p>
                            </div>
                        </a>
                        
                        <a href="warranty-add.php" class="action-item">
                            <span class="action-icon">➕</span>
                            <div class="action-content">
                                <h4>Add New Warranty</h4>
                                <p>Register a new product warranty</p>
                            </div>
                        </a>
                        
                        <a href="dashboard.php" class="action-item">
                            <span class="action-icon">🏠</span>
                            <div class="action-content">
                                <h4>Dashboard</h4>
                                <p>View warranty overview and alerts</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <style>
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .profile-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .section-header {
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .section-header h2 {
            margin: 0;
            color: #1a202c;
            font-size: 1.25rem;
        }
        
        .profile-form .form-group {
            margin-bottom: 1.5rem;
        }
        
        .stats-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 6px;
        }
        
        .stat-label {
            font-weight: 500;
            color: #64748b;
        }
        
        .stat-value {
            font-weight: 600;
            color: #1a202c;
        }
        
        .action-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .action-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 8px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
        }
        
        .action-item:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }
        
        .action-icon {
            font-size: 1.5rem;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 8px;
            flex-shrink: 0;
        }
        
        .action-content h4 {
            margin: 0 0 0.25rem 0;
            color: #1a202c;
        }
        
        .action-content p {
            margin: 0;
            color: #64748b;
            font-size: 0.875rem;
        }
        
        @media (max-width: 768px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-section {
                padding: 1.5rem;
            }
        }
    </style>
</body>
</html>

