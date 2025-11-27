<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();

$warrantyId = (int)($_GET['id'] ?? 0);
$userId = getCurrentUserId();

if (!$warrantyId) {
    header('Location: warranties.php');
    exit;
}

// Get warranty data to verify ownership
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM warranties WHERE id = ? AND user_id = ?");
    $stmt->execute([$warrantyId, $userId]);
    $warranty = $stmt->fetch();
    
    if (!$warranty) {
        setFlashMessage('error', 'Warranty not found.');
        header('Location: warranties.php');
        exit;
    }
} catch (Exception $e) {
    setFlashMessage('error', 'Failed to load warranty.');
    header('Location: warranties.php');
    exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
        try {
            // Delete the warranty record
            $stmt = $pdo->prepare("DELETE FROM warranties WHERE id = ? AND user_id = ?");
            $stmt->execute([$warrantyId, $userId]);
            
            // Delete associated file if it exists
            if ($warranty['receipt_image']) {
                deleteUploadedFile($warranty['receipt_image']);
            }
            
            setFlashMessage('success', 'Warranty deleted successfully.');
            header('Location: warranties.php');
            exit;
        } catch (Exception $e) {
            setFlashMessage('error', 'Failed to delete warranty. Please try again.');
        }
    } else {
        // User cancelled deletion
        header('Location: warranty-view.php?id=' . $warrantyId);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Warranty - Warranty Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Delete Warranty</h1>
                <p>Are you sure you want to delete this warranty?</p>
            </div>
            
            <div class="delete-confirmation">
                <div class="warranty-summary">
                    <h3><?php echo htmlspecialchars($warranty['product_name']); ?></h3>
                    <?php if ($warranty['brand'] || $warranty['model']): ?>
                        <p class="warranty-model">
                            <?php echo htmlspecialchars(trim($warranty['brand'] . ' ' . $warranty['model'])); ?>
                        </p>
                    <?php endif; ?>
                    <div class="warranty-dates">
                        <p><strong>Purchase Date:</strong> <?php echo formatDate($warranty['purchase_date']); ?></p>
                        <p><strong>Expiry Date:</strong> <?php echo formatDate($warranty['warranty_expiry_date']); ?></p>
                    </div>
                    <?php if ($warranty['store_vendor']): ?>
                        <p><strong>Store:</strong> <?php echo htmlspecialchars($warranty['store_vendor']); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="warning-message">
                    <div class="warning-icon">⚠️</div>
                    <div class="warning-content">
                        <h4>Warning: This action cannot be undone</h4>
                        <p>Deleting this warranty will permanently remove all associated data, including any uploaded receipt or image files.</p>
                    </div>
                </div>
                
                <form method="POST" class="delete-form">
                    <div class="form-actions">
                        <button type="submit" name="confirm_delete" value="yes" class="btn btn-danger">
                            Yes, Delete Warranty
                        </button>
                        <a href="warranty-view.php?id=<?php echo $warranty['id']; ?>" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>

