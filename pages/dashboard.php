<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();

$user = getCurrentUser();
$userId = getCurrentUserId();

// Get warranty statistics
try {
    $pdo = getDBConnection();
    
    // Total warranties
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM warranties WHERE user_id = ?");
    $stmt->execute([$userId]);
    $totalWarranties = $stmt->fetchColumn();
    
    // Active warranties
    $stmt = $pdo->prepare("SELECT COUNT(*) as active FROM warranties WHERE user_id = ? AND warranty_expiry_date > CURDATE()");
    $stmt->execute([$userId]);
    $activeWarranties = $stmt->fetchColumn();
    
    // Expired warranties
    $stmt = $pdo->prepare("SELECT COUNT(*) as expired FROM warranties WHERE user_id = ? AND warranty_expiry_date <= CURDATE()");
    $stmt->execute([$userId]);
    $expiredWarranties = $stmt->fetchColumn();
    
    // Expiring soon (within 30 days)
    $stmt = $pdo->prepare("SELECT COUNT(*) as expiring FROM warranties WHERE user_id = ? AND warranty_expiry_date > CURDATE() AND warranty_expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
    $stmt->execute([$userId]);
    $expiringSoon = $stmt->fetchColumn();
    
    // Recent warranties (last 5)
    $stmt = $pdo->prepare("SELECT * FROM warranties WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$userId]);
    $recentWarranties = $stmt->fetchAll();
    
    // Warranties expiring soon
    $stmt = $pdo->prepare("SELECT * FROM warranties WHERE user_id = ? AND warranty_expiry_date > CURDATE() AND warranty_expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) ORDER BY warranty_expiry_date ASC");
    $stmt->execute([$userId]);
    $expiringWarranties = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = 'Failed to load dashboard data.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Warranty Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</p>
            </div>
            
            <?php foreach (getFlashMessages() as $message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo htmlspecialchars($message['message']); ?>
                </div>
            <?php endforeach; ?>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-content">
                        <h3><?php echo $totalWarranties; ?></h3>
                        <p>Total Warranties</p>
                    </div>
                </div>
                
                <div class="stat-card stat-active">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <h3><?php echo $activeWarranties; ?></h3>
                        <p>Active Warranties</p>
                    </div>
                </div>
                
                <div class="stat-card stat-warning">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-content">
                        <h3><?php echo $expiringSoon; ?></h3>
                        <p>Expiring Soon</p>
                    </div>
                </div>
                
                <div class="stat-card stat-expired">
                    <div class="stat-icon">❌</div>
                    <div class="stat-content">
                        <h3><?php echo $expiredWarranties; ?></h3>
                        <p>Expired</p>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <!-- Expiring Soon Section -->
                <?php if (!empty($expiringWarranties)): ?>
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>⚠️ Expiring Soon</h2>
                        <a href="warranties.php?filter=expiring" class="btn btn-sm">View All</a>
                    </div>
                    <div class="warranty-list">
                        <?php foreach ($expiringWarranties as $warranty): ?>
                            <div class="warranty-item warranty-expiring">
                                <div class="warranty-info">
                                    <h4><?php echo htmlspecialchars($warranty['product_name']); ?></h4>
                                    <p><?php echo htmlspecialchars($warranty['brand'] . ' ' . $warranty['model']); ?></p>
                                    <span class="warranty-expiry">Expires: <?php echo formatDate($warranty['warranty_expiry_date']); ?></span>
                                </div>
                                <div class="warranty-actions">
                                    <span class="days-left"><?php echo daysUntilExpiry($warranty['warranty_expiry_date']); ?> days</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Recent Warranties Section -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>📋 Recent Warranties</h2>
                        <a href="warranties.php" class="btn btn-sm">View All</a>
                    </div>
                    <?php if (!empty($recentWarranties)): ?>
                        <div class="warranty-list">
                            <?php foreach ($recentWarranties as $warranty): ?>
                                <div class="warranty-item warranty-<?php echo getWarrantyStatus($warranty['warranty_expiry_date']); ?>">
                                    <div class="warranty-info">
                                        <h4><?php echo htmlspecialchars($warranty['product_name']); ?></h4>
                                        <p><?php echo htmlspecialchars($warranty['brand'] . ' ' . $warranty['model']); ?></p>
                                        <span class="warranty-expiry">Expires: <?php echo formatDate($warranty['warranty_expiry_date']); ?></span>
                                    </div>
                                    <div class="warranty-actions">
                                        <a href="warranty-edit.php?id=<?php echo $warranty['id']; ?>" class="btn btn-sm">Edit</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No warranties found. <a href="warranty-add.php">Add your first warranty</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="warranty-add.php" class="btn btn-primary">Add New Warranty</a>
                <a href="warranties.php" class="btn btn-secondary">View All Warranties</a>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>

