<?php
$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<header class="header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="dashboard.php">
                    <h2>🛡️ Warranty Tracker</h2>
                </a>
            </div>
            
            <nav class="nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="warranties.php" class="nav-link <?php echo $currentPage === 'warranties.php' ? 'active' : ''; ?>">
                            Warranties
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="warranty-add.php" class="nav-link <?php echo $currentPage === 'warranty-add.php' ? 'active' : ''; ?>">
                            Add Warranty
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="user-menu">
                <div class="user-info">
                    <span class="username"><?php echo htmlspecialchars($currentUser['username']); ?></span>
                    <div class="user-dropdown">
                        <button class="user-toggle" onclick="toggleUserMenu()">
                            <span class="user-avatar">👤</span>
                            <span class="dropdown-arrow">▼</span>
                        </button>
                        <div class="user-dropdown-menu" id="userDropdown">
                            <a href="profile.php" class="dropdown-item">Profile</a>
                            <a href="logout.php" class="dropdown-item">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mobile menu toggle -->
            <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
        
        <!-- Mobile navigation -->
        <nav class="mobile-nav" id="mobileNav">
            <ul class="mobile-nav-list">
                <li><a href="dashboard.php" class="mobile-nav-link">Dashboard</a></li>
                <li><a href="warranties.php" class="mobile-nav-link">Warranties</a></li>
                <li><a href="warranty-add.php" class="mobile-nav-link">Add Warranty</a></li>
                <li><a href="profile.php" class="mobile-nav-link">Profile</a></li>
                <li><a href="logout.php" class="mobile-nav-link">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<script>
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('show');
}

function toggleMobileMenu() {
    const mobileNav = document.getElementById('mobileNav');
    const toggle = document.querySelector('.mobile-menu-toggle');
    mobileNav.classList.toggle('show');
    toggle.classList.toggle('active');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const userMenu = document.querySelector('.user-dropdown');
    const dropdown = document.getElementById('userDropdown');
    
    if (!userMenu.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});
</script>

