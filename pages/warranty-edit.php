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

$errors = [];
$formData = $warranty; // Pre-populate with existing data

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $formData['product_name'] = sanitizeInput($_POST['product_name'] ?? '');
    $formData['brand'] = sanitizeInput($_POST['brand'] ?? '');
    $formData['model'] = sanitizeInput($_POST['model'] ?? '');
    $formData['purchase_date'] = sanitizeInput($_POST['purchase_date'] ?? '');
    $formData['warranty_period_months'] = (int)($_POST['warranty_period_months'] ?? 0);
    $formData['store_vendor'] = sanitizeInput($_POST['store_vendor'] ?? '');
    $formData['purchase_price'] = floatval($_POST['purchase_price'] ?? 0);
    $formData['notes'] = sanitizeInput($_POST['notes'] ?? '');
    
    // Validation
    if (empty($formData['product_name'])) {
        $errors[] = 'Product name is required.';
    }
    
    if (empty($formData['purchase_date'])) {
        $errors[] = 'Purchase date is required.';
    } elseif (!strtotime($formData['purchase_date'])) {
        $errors[] = 'Invalid purchase date format.';
    }
    
    if ($formData['warranty_period_months'] <= 0) {
        $errors[] = 'Warranty period must be greater than 0 months.';
    }
    
    // Calculate expiry date
    if (empty($errors)) {
        $purchaseDate = new DateTime($formData['purchase_date']);
        $expiryDate = clone $purchaseDate;
        $expiryDate->add(new DateInterval('P' . $formData['warranty_period_months'] . 'M'));
        $formData['warranty_expiry_date'] = $expiryDate->format('Y-m-d');
    }
    
    // Handle file upload
    $receiptFilename = $warranty['receipt_image']; // Keep existing file by default
    if (isset($_FILES['receipt_image']) && $_FILES['receipt_image']['error'] === UPLOAD_ERR_OK) {
        $newReceiptFilename = uploadFile($_FILES['receipt_image']);
        if ($newReceiptFilename) {
            // Delete old file if it exists
            if ($receiptFilename) {
                deleteUploadedFile($receiptFilename);
            }
            $receiptFilename = $newReceiptFilename;
        } else {
            $errors[] = 'Failed to upload receipt image. Please check file type and size.';
        }
    }
    
    // Handle file removal
    if (isset($_POST['remove_receipt']) && $_POST['remove_receipt'] === '1') {
        if ($receiptFilename) {
            deleteUploadedFile($receiptFilename);
        }
        $receiptFilename = '';
    }
    
    // Update warranty if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE warranties SET 
                    product_name = ?, brand = ?, model = ?, purchase_date = ?, 
                    warranty_period_months = ?, warranty_expiry_date = ?, store_vendor = ?, 
                    purchase_price = ?, receipt_image = ?, notes = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND user_id = ?
            ");
            
            $stmt->execute([
                $formData['product_name'],
                $formData['brand'],
                $formData['model'],
                $formData['purchase_date'],
                $formData['warranty_period_months'],
                $formData['warranty_expiry_date'],
                $formData['store_vendor'],
                $formData['purchase_price'],
                $receiptFilename,
                $formData['notes'],
                $warrantyId,
                $userId
            ]);
            
            setFlashMessage('success', 'Warranty updated successfully!');
            header('Location: warranty-view.php?id=' . $warrantyId);
            exit;
        } catch (Exception $e) {
            $errors[] = 'Failed to update warranty. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Warranty - Warranty Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Edit Warranty</h1>
                <p>Update the details of your product warranty</p>
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
            
            <form method="POST" enctype="multipart/form-data" class="warranty-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="product_name">Product Name *</label>
                        <input type="text" id="product_name" name="product_name" required 
                               value="<?php echo htmlspecialchars($formData['product_name']); ?>"
                               placeholder="e.g., Laptop Computer">
                    </div>
                    
                    <div class="form-group">
                        <label for="brand">Brand</label>
                        <input type="text" id="brand" name="brand" 
                               value="<?php echo htmlspecialchars($formData['brand']); ?>"
                               placeholder="e.g., Dell, Apple, Samsung">
                    </div>
                    
                    <div class="form-group">
                        <label for="model">Model</label>
                        <input type="text" id="model" name="model" 
                               value="<?php echo htmlspecialchars($formData['model']); ?>"
                               placeholder="e.g., XPS 13, iPhone 15">
                    </div>
                    
                    <div class="form-group">
                        <label for="purchase_date">Purchase Date *</label>
                        <input type="date" id="purchase_date" name="purchase_date" required 
                               value="<?php echo htmlspecialchars($formData['purchase_date']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="warranty_period_months">Warranty Period (Months) *</label>
                        <input type="number" id="warranty_period_months" name="warranty_period_months" 
                               min="1" max="120" required 
                               value="<?php echo htmlspecialchars($formData['warranty_period_months']); ?>"
                               placeholder="e.g., 12, 24, 36">
                    </div>
                    
                    <div class="form-group">
                        <label for="store_vendor">Store/Vendor</label>
                        <input type="text" id="store_vendor" name="store_vendor" 
                               value="<?php echo htmlspecialchars($formData['store_vendor']); ?>"
                               placeholder="e.g., Best Buy, Amazon, Apple Store">
                    </div>
                    
                    <div class="form-group">
                        <label for="purchase_price">Purchase Price ($)</label>
                        <input type="number" id="purchase_price" name="purchase_price" 
                               min="0" step="0.01" 
                               value="<?php echo htmlspecialchars($formData['purchase_price']); ?>"
                               placeholder="e.g., 999.99">
                    </div>
                    
                    <div class="form-group">
                        <label for="receipt_image">Receipt/Image</label>
                        <?php if ($formData['receipt_image']): ?>
                            <div class="current-file">
                                <p>Current file: <?php echo htmlspecialchars($formData['receipt_image']); ?></p>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="remove_receipt" value="1">
                                    Remove current file
                                </label>
                            </div>
                        <?php endif; ?>
                        <input type="file" id="receipt_image" name="receipt_image" 
                               accept="image/*,.pdf">
                        <small>Upload receipt or product image (JPG, PNG, GIF, PDF - Max 5MB)</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="4" 
                              placeholder="Additional notes about the warranty..."><?php echo htmlspecialchars($formData['notes']); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Warranty</button>
                    <a href="warranty-view.php?id=<?php echo $warrantyId; ?>" class="btn btn-secondary">Cancel</a>
                    <a href="warranties.php" class="btn btn-outline">Back to List</a>
                </div>
            </form>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Auto-calculate expiry date when purchase date or warranty period changes
        function calculateExpiryDate() {
            const purchaseDate = document.getElementById('purchase_date').value;
            const warrantyMonths = document.getElementById('warranty_period_months').value;
            
            if (purchaseDate && warrantyMonths) {
                const purchase = new Date(purchaseDate);
                const expiry = new Date(purchase);
                expiry.setMonth(expiry.getMonth() + parseInt(warrantyMonths));
                
                // Display calculated expiry date
                let expiryDisplay = document.getElementById('expiry_display');
                if (!expiryDisplay) {
                    expiryDisplay = document.createElement('div');
                    expiryDisplay.id = 'expiry_display';
                    expiryDisplay.className = 'expiry-preview';
                    document.querySelector('.warranty-form').insertBefore(expiryDisplay, document.querySelector('.form-actions'));
                }
                expiryDisplay.innerHTML = '<strong>Warranty expires on:</strong> ' + expiry.toLocaleDateString();
            }
        }
        
        document.getElementById('purchase_date').addEventListener('change', calculateExpiryDate);
        document.getElementById('warranty_period_months').addEventListener('input', calculateExpiryDate);
        
        // Calculate on page load
        calculateExpiryDate();
    </script>
</body>
</html>

