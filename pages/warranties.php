<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();

$userId = getCurrentUserId();

// Get filter and search parameters
$filter = $_GET['filter'] ?? 'all';
$search = sanitizeInput($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$itemsPerPage = 10;

// Build WHERE clause based on filters
$whereConditions = ["user_id = ?"];
$params = [$userId];

if ($search) {
    $whereConditions[] = "(product_name LIKE ? OR brand LIKE ? OR model LIKE ? OR store_vendor LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

switch ($filter) {
    case 'active':
        $whereConditions[] = "warranty_expiry_date > CURDATE()";
        break;
    case 'expired':
        $whereConditions[] = "warranty_expiry_date <= CURDATE()";
        break;
    case 'expiring':
        $whereConditions[] = "warranty_expiry_date > CURDATE() AND warranty_expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        break;
}

$whereClause = "WHERE " . implode(" AND ", $whereConditions);

try {
    $pdo = getDBConnection();
    
    // Get total count for pagination
    $countSql = "SELECT COUNT(*) FROM warranties $whereClause";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $totalItems = $stmt->fetchColumn();
    
    // Calculate pagination
    $pagination = paginate($totalItems, $itemsPerPage, $page);
    
    // Get warranties for current page
    $sql = "SELECT * FROM warranties $whereClause ORDER BY warranty_expiry_date ASC LIMIT {$pagination['items_per_page']} OFFSET {$pagination['offset']}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $warranties = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = 'Failed to load warranties.';
    $warranties = [];
    $pagination = ['total_pages' => 0, 'current_page' => 1];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warranties - Warranty Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>My Warranties</h1>
                <div class="page-actions">
                    <a href="warranty-add.php" class="btn btn-primary">Add New Warranty</a>
                </div>
            </div>
            
            <?php foreach (getFlashMessages() as $message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo htmlspecialchars($message['message']); ?>
                </div>
            <?php endforeach; ?>
            
            <!-- Filters and Search -->
            <div class="filters-section">
                <div class="filter-tabs">
                    <a href="?filter=all<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                       class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                        All Warranties
                    </a>
                    <a href="?filter=active<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                       class="filter-tab <?php echo $filter === 'active' ? 'active' : ''; ?>">
                        Active
                    </a>
                    <a href="?filter=expiring<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                       class="filter-tab <?php echo $filter === 'expiring' ? 'active' : ''; ?>">
                        Expiring Soon
                    </a>
                    <a href="?filter=expired<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                       class="filter-tab <?php echo $filter === 'expired' ? 'active' : ''; ?>">
                        Expired
                    </a>
                </div>
                
                <form method="GET" class="search-form">
                    <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                    <div class="search-group">
                        <input type="text" name="search" placeholder="Search warranties..." 
                               value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                        <button type="submit" class="btn btn-secondary">Search</button>
                        <?php if ($search): ?>
                            <a href="?filter=<?php echo urlencode($filter); ?>" class="btn btn-outline">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- Warranties List -->
            <?php if (!empty($warranties)): ?>
                <div class="warranties-grid">
                    <?php foreach ($warranties as $warranty): ?>
                        <?php $status = getWarrantyStatus($warranty['warranty_expiry_date']); ?>
                        <div class="warranty-card warranty-<?php echo $status; ?>">
                            <div class="warranty-header">
                                <h3><?php echo htmlspecialchars($warranty['product_name']); ?></h3>
                                <div class="warranty-status status-<?php echo $status; ?>">
                                    <?php
                                    switch ($status) {
                                        case 'expired':
                                            echo '❌ Expired';
                                            break;
                                        case 'expiring-soon':
                                            echo '⚠️ Expiring Soon';
                                            break;
                                        default:
                                            echo '✅ Active';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="warranty-details">
                                <?php if ($warranty['brand'] || $warranty['model']): ?>
                                    <p class="warranty-model">
                                        <?php echo htmlspecialchars(trim($warranty['brand'] . ' ' . $warranty['model'])); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="warranty-dates">
                                    <div class="date-item">
                                        <span class="date-label">Purchased:</span>
                                        <span class="date-value"><?php echo formatDate($warranty['purchase_date']); ?></span>
                                    </div>
                                    <div class="date-item">
                                        <span class="date-label">Expires:</span>
                                        <span class="date-value"><?php echo formatDate($warranty['warranty_expiry_date']); ?></span>
                                    </div>
                                </div>
                                
                                <?php if ($warranty['store_vendor']): ?>
                                    <p class="warranty-store">
                                        <span class="label">Store:</span> <?php echo htmlspecialchars($warranty['store_vendor']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ($warranty['purchase_price'] > 0): ?>
                                    <p class="warranty-price">
                                        <span class="label">Price:</span> $<?php echo number_format($warranty['purchase_price'], 2); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ($status !== 'expired'): ?>
                                    <div class="warranty-countdown">
                                        <?php
                                        $daysLeft = daysUntilExpiry($warranty['warranty_expiry_date']);
                                        if ($daysLeft > 0) {
                                            echo "<span class='days-left'>$daysLeft days remaining</span>";
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="warranty-actions">
                                <a href="warranty-view.php?id=<?php echo $warranty['id']; ?>" class="btn btn-sm btn-outline">View</a>
                                <a href="warranty-edit.php?id=<?php echo $warranty['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                <a href="warranty-delete.php?id=<?php echo $warranty['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this warranty?')">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <div class="pagination">
                        <?php
                        $baseUrl = "?filter=" . urlencode($filter);
                        if ($search) $baseUrl .= "&search=" . urlencode($search);
                        ?>
                        
                        <?php if ($pagination['current_page'] > 1): ?>
                            <a href="<?php echo $baseUrl; ?>&page=<?php echo $pagination['current_page'] - 1; ?>" class="pagination-link">Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <?php if ($i === $pagination['current_page']): ?>
                                <span class="pagination-link active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="<?php echo $baseUrl; ?>&page=<?php echo $i; ?>" class="pagination-link"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                            <a href="<?php echo $baseUrl; ?>&page=<?php echo $pagination['current_page'] + 1; ?>" class="pagination-link">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📋</div>
                    <h3>No warranties found</h3>
                    <?php if ($search): ?>
                        <p>No warranties match your search criteria.</p>
                        <a href="?filter=<?php echo urlencode($filter); ?>" class="btn btn-secondary">Clear Search</a>
                    <?php else: ?>
                        <p>You haven't added any warranties yet.</p>
                        <a href="warranty-add.php" class="btn btn-primary">Add Your First Warranty</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>

