<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

startSession();

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && verifyPassword($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                setFlashMessage('success', 'Welcome back, ' . $user['username'] . '!');
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid username/email or password.';
            }
        } catch (Exception $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Warranty Tracker</title>
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
                <p>Welcome back! Please sign in to your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <div class="alert-icon">⚠️</div>
                    <div class="alert-content">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username">
                        <span class="label-icon">👤</span>
                        Username or Email
                    </label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($username ?? ''); ?>"
                           placeholder="Enter your username or email">
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <span class="label-icon">🔒</span>
                        Password
                    </label>
                    <input type="password" id="password" name="password" required
                           placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-full btn-login">
                    <span class="btn-icon">🚀</span>
                    Sign In
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Sign up here</a></p>
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

