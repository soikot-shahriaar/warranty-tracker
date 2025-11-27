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

// Get warranty data
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

$status = getWarrantyStatus($warranty['warranty_expiry_date']);
$daysLeft = daysUntilExpiry($warranty['warranty_expiry_date']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($warranty['product_name']); ?> - Warranty Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><?php echo htmlspecialchars($warranty['product_name']); ?></h1>
                <div class="page-actions">
                    <a href="warranty-edit.php?id=<?php echo $warranty['id']; ?>" class="btn btn-primary">Edit</a>
                    <a href="warranty-delete.php?id=<?php echo $warranty['id']; ?>" 
                       class="btn btn-danger" 
                       onclick="return confirm('Are you sure you want to delete this warranty?')">Delete</a>
                </div>
            </div>
            
            <?php foreach (getFlashMessages() as $message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo htmlspecialchars($message['message']); ?>
                </div>
            <?php endforeach; ?>
            
            <div class="warranty-detail-card">
                <!-- Status Banner -->
                <div class="warranty-status-banner status-<?php echo $status; ?>">
                    <div class="status-info">
                        <h2 class="status-title">
                            <?php
                            switch ($status) {
                                case 'expired':
                                    echo '❌ Warranty Expired';
                                    break;
                                case 'expiring-soon':
                                    echo '⚠️ Warranty Expiring Soon';
                                    break;
                                default:
                                    echo '✅ Warranty Active';
                            }
                            ?>
                        </h2>
                        <p class="status-description">
                            <?php if ($status === 'expired'): ?>
                                This warranty expired <?php echo abs($daysLeft); ?> days ago.
                            <?php elseif ($status === 'expiring-soon'): ?>
                                This warranty expires in <?php echo $daysLeft; ?> days.
                            <?php else: ?>
                                This warranty is active for <?php echo $daysLeft; ?> more days.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <!-- Product Information -->
                <div class="detail-section">
                    <h3>Product Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Product Name</label>
                            <value><?php echo htmlspecialchars($warranty['product_name']); ?></value>
                        </div>
                        
                        <?php if ($warranty['brand']): ?>
                        <div class="detail-item">
                            <label>Brand</label>
                            <value><?php echo htmlspecialchars($warranty['brand']); ?></value>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($warranty['model']): ?>
                        <div class="detail-item">
                            <label>Model</label>
                            <value><?php echo htmlspecialchars($warranty['model']); ?></value>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Purchase Information -->
                <div class="detail-section">
                    <h3>Purchase Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Purchase Date</label>
                            <value><?php echo formatDate($warranty['purchase_date']); ?></value>
                        </div>
                        
                        <?php if ($warranty['store_vendor']): ?>
                        <div class="detail-item">
                            <label>Store/Vendor</label>
                            <value><?php echo htmlspecialchars($warranty['store_vendor']); ?></value>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($warranty['purchase_price'] > 0): ?>
                        <div class="detail-item">
                            <label>Purchase Price</label>
                            <value>$<?php echo number_format($warranty['purchase_price'], 2); ?></value>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Warranty Information -->
                <div class="detail-section">
                    <h3>Warranty Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Warranty Period</label>
                            <value><?php echo $warranty['warranty_period_months']; ?> months</value>
                        </div>
                        
                        <div class="detail-item">
                            <label>Expiry Date</label>
                            <value><?php echo formatDate($warranty['warranty_expiry_date']); ?></value>
                        </div>
                        
                        <div class="detail-item">
                            <label>Days Remaining</label>
                            <value>
                                <?php if ($daysLeft < 0): ?>
                                    Expired <?php echo abs($daysLeft); ?> days ago
                                <?php else: ?>
                                    <?php echo $daysLeft; ?> days
                                <?php endif; ?>
                            </value>
                        </div>
                    </div>
                </div>
                
                <!-- Receipt/Image -->
                <?php if ($warranty['receipt_image']): ?>
                <div class="detail-section">
                    <h3>Receipt/Image</h3>
                    <div class="receipt-display">
                        <?php
                        $filePath = '../assets/uploads/' . $warranty['receipt_image'];
                        $fileExtension = strtolower(pathinfo($warranty['receipt_image'], PATHINFO_EXTENSION));
                        ?>
                        
                        <?php if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                            <img src="<?php echo htmlspecialchars($filePath); ?>" 
                                 alt="Receipt/Product Image" 
                                 class="receipt-image"
                                 onclick="openImageModal(this.src)">
                        <?php else: ?>
                            <div class="file-download">
                                <span class="file-icon">📄</span>
                                <a href="<?php echo htmlspecialchars($filePath); ?>" 
                                   target="_blank" 
                                   class="btn btn-outline">
                                    View <?php echo strtoupper($fileExtension); ?> File
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Notes -->
                <?php if ($warranty['notes']): ?>
                <div class="detail-section">
                    <h3>Notes</h3>
                    <div class="notes-content">
                        <?php echo nl2br(htmlspecialchars($warranty['notes'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Metadata -->
                <div class="detail-section">
                    <h3>Record Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Created</label>
                            <value><?php echo formatDate($warranty['created_at']); ?></value>
                        </div>
                        
                        <div class="detail-item">
                            <label>Last Updated</label>
                            <value><?php echo formatDate($warranty['updated_at']); ?></value>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="detail-actions">
                <a href="warranty-edit.php?id=<?php echo $warranty['id']; ?>" class="btn btn-primary">Edit Warranty</a>
                <a href="warranties.php" class="btn btn-secondary">Back to List</a>
                <a href="warranty-delete.php?id=<?php echo $warranty['id']; ?>" 
                   class="btn btn-danger" 
                   onclick="return confirm('Are you sure you want to delete this warranty?')">Delete</a>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <!-- Image Modal -->
    <div id="imageModal" class="modal" onclick="closeImageModal()">
        <div class="modal-content">
            <span class="modal-close" onclick="closeImageModal()">&times;</span>
            <img id="modalImage" src="" alt="Receipt/Product Image">
        </div>
    </div>
    
    <script>
        function openImageModal(src) {
            document.getElementById('imageModal').style.display = 'block';
            document.getElementById('modalImage').src = src;
            document.body.style.overflow = 'hidden';
        }
        
        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeImageModal();
            }
        });
    </script>
</body>
</html>

