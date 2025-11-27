<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

startSession();

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['username'] = sanitizeInput($_POST['username'] ?? '');
    $formData['email'] = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($formData['username'])) {
        $errors[] = 'Username is required.';
    } elseif (strlen($formData['username']) < 3) {
        $errors[] = 'Username must be at least 3 characters long.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $formData['username'])) {
        $errors[] = 'Username can only contain letters, numbers, and underscores.';
    }
    
    if (empty($formData['email'])) {
        $errors[] = 'Email is required.';
    } elseif (!isValidEmail($formData['email'])) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }
    
    // Check for existing username/email
    if (empty($errors)) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$formData['username'], $formData['email']]);
            
            if ($stmt->fetch()) {
                $errors[] = 'Username or email already exists.';
            }
        } catch (Exception $e) {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
    
    // Create user if no errors
    if (empty($errors)) {
        try {
            $passwordHash = hashPassword($password);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$formData['username'], $formData['email'], $passwordHash]);
            
            setFlashMessage('success', 'Registration successful! Please log in.');
            header('Location: login.php');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Warranty Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <div class="logo-icon">🛡️</div>
                    <h1>Warranty Tracker</h1>
                </div>
                <p>Create your account to get started</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username">
                        <span class="label-icon">👤</span>
                        Username
                    </label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($formData['username'] ?? ''); ?>"
                           placeholder="Enter your username">
                    <small>3+ characters, letters, numbers, and underscores only</small>
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <span class="label-icon">📧</span>
                        Email
                    </label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                           placeholder="Enter your email address">
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <span class="label-icon">🔒</span>
                        Password
                    </label>
                    <input type="password" id="password" name="password" required
                           placeholder="Enter your password">
                    <small>Minimum 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">
                        <span class="label-icon">🔐</span>
                        Confirm Password
                    </label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           placeholder="Enter your password again">
                </div>
                
                <button type="submit" class="btn btn-primary btn-full btn-login">
                    <span class="btn-icon">🚀</span>
                    Create Account
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign in here</a></p>
            </div>
        </div>
        
        <!-- Copyright Notice -->
        <div class="auth-copyright">
            <div class="text-center my-2">
                <div>
                    <span>© <?php echo date('Y'); ?> .  </span>
                    <span class="text- ">Developed by </span>
                    <a href="https://rivertheme.com" class="fw-bold text-decoration-none" target="_blank" rel="noopener">RiverTheme</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

