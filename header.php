<?php
// Vérifier si le panier existe et est un tableau avant de compter
$cart_count = (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) ? count($_SESSION['cart']) : 0;
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="main-nav">
    <ul class="nav-list">
        <li class="nav-item">
            <a href="index.php" class="nav-link <?= ($current_page === 'index.php') ? 'active' : '' ?>">Home</a>
        </li>
        <li class="nav-item">
            <a href="products.php" class="nav-link <?= ($current_page === 'products.php') ? 'active' : '' ?>">Our Chairs</a>
        </li>
        <li class="nav-item">
            <a href="about.php" class="nav-link <?= ($current_page === 'about.php') ? 'active' : '' ?>">About Us</a>
        </li>
        <li class="nav-item">
            <a href="contact.php" class="nav-link <?= ($current_page === 'contact.php') ? 'active' : '' ?>">Contact</a>
        </li>
        <li class="nav-item cart-item">
            <a href="cart.php" class="nav-link cart-link">
                <i class="fas fa-shopping-cart"></i> Cart
                <?php if ($cart_count > 0): ?>
                    <span class="cart-count"><?= $cart_count ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item auth-item">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="dashboard.php" class="nav-link admin-link">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                <?php endif; ?>

                <a href="logout.php" class="nav-link logout-link" 
                   onclick="return confirm('Do you really want to log out?')">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            <?php else: ?>
                <a href="login.php" class="nav-link login-link">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            <?php endif; ?>
        </li>
    </ul>
</nav>

<style>
    .main-nav {
        background-color: #f8f9fa;
        padding: 1rem 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .nav-list {
        display: flex;
        justify-content: center;
        align-items: center;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: 1.5rem;
    }
    
    .nav-link {
        color: #333;
        text-decoration: none;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: color 0.3s ease;
    }
    
    .nav-link:hover {
        color: #0d6efd;
    }
    
    .nav-link.active {
        color: #0d6efd;
        font-weight: 600;
    }
    
    .cart-count {
        background-color: #0d6efd;
        color: white;
        border-radius: 50%;
        padding: 0.2rem 0.5rem;
        font-size: 0.8rem;
        margin-left: 0.3rem;
    }
    
    .admin-link {
        color: #dc3545;
    }
    
    .logout-link {
        color: #6c757d;
    }
    
    .auth-item {
        display: flex;
        gap: 1rem;
    }
</style>