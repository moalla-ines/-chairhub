<?php
session_start();

// Vérification admin
if (!isset($_SESSION['user_id'], $_SESSION['role'], $_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once 'config.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("Database connection error");
}

// Initialisation des variables
$total_users = 0;
$total_products = 0;
$total_orders = 0;
$dashboard_error = null;
$low_stock = [];

try {
    // Nombre total d'utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $total_users = $stmt->fetchColumn() ?? 0;
    
    // Nombre total de produits
    $stmt = $pdo->query("SELECT COUNT(*) as total_products FROM products");
    $total_products = $stmt->fetchColumn() ?? 0;
    
    // Nombre total de commandes
    $stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders");
    $total_orders = $stmt->fetchColumn() ?? 0;

    // Produits en faible stock
    $stmt = $pdo->query("SELECT id, name, stock_quantity FROM products WHERE stock_quantity < 5 ORDER BY stock_quantity ASC");
    $low_stock = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $dashboard_error = "Erreur lors du chargement des données";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Comfort Chairs</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        
        .sidebar {
            background: #2c3e50;
            color: white;
            padding: 20px;
        }
        
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        
        .sidebar li {
            margin-bottom: 15px;
        }
        
        .sidebar a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .sidebar a:hover, .sidebar a.active {
            background-color: #1a252f;
        }
        
        .main-content {
            padding: 20px;
            background: #f5f5f5;
        }
        
        .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); /* Augmentation de la largeur minimale */
        gap: 25px; /* Espacement accru */
        margin-bottom: 40px; /* Marge augmentée */
    }
    
    .stat-card {
        background: white;
        padding: 30px; /* Padding augmenté */
        border-radius: 12px; /* Bordures plus arrondies */
        box-shadow: 0 4px 10px rgba(0,0,0,0.1); /* Ombre plus prononcée */
        transition: transform 0.3s ease, box-shadow 0.3s ease; /* Animation ajoutée */
        min-height: 180px; /* Hauteur minimale fixe */
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    
    .stat-card:hover {
        transform: translateY(-5px); /* Effet de levage au survol */
        box-shadow: 0 6px 15px rgba(0,0,0,0.15);
    }
    
    .stat-card h3 {
        margin-top: 0;
        color: #7f8c8d;
        font-size: 1.2rem; /* Taille de police augmentée */
        margin-bottom: 15px;
    }
    
    .stat-card .value {
        font-size: 2.8rem; /* Taille considérablement augmentée */
        font-weight: bold;
        color: #2c3e50;
        margin: 15px 0;
    }
    
    .stat-card a {
        align-self: flex-start;
        padding: 8px 16px;
        font-size: 0.9rem;
    }
        
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert.warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .logout-btn {
            background: #2c3e50;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .logout-btn:hover {
            background: #2c3e50;
        }
        
        .btn-sm {
            padding: 5px 10px;
            background: #3498db;
            color: white;
            border-radius: 3px;
            text-decoration: none;
            font-size: 0.8rem;
        }
        
        .btn-sm:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <h2>Comfort Chairs</h2>
            <ul>
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="products.php"><i class="fas fa-chair"></i> Products</a></li>
                <li><a href="categorie.php"><i class="fas fa-list"></i> Categories</a></li>
                <li><a href="liste_users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="orders_history.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Admin Dashboard</h1>
                <div>
                    <span>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <?php if(isset($dashboard_error)): ?>
                <div class="alert warning"><?= htmlspecialchars($dashboard_error) ?></div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <div class="value"><?= $total_users ?></div>
                    <a href="liste_users.php">View all users</a>
                </div>
                
                <div class="stat-card">
                    <h3>Total Products</h3>
                    <div class="value"><?= $total_products ?></div>
                    <a href="products.php">View all products</a>
                </div>
                
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <div class="value"><?= $total_orders ?></div>
                    <a href="orders_history.php">View all orders</a>
                </div>
            </div>
            
            <!-- Low Stock Alert -->
            <?php if(!empty($low_stock)): ?>
            <div class="table-container">
                <h2>Low Stock Alert</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Stock Quantity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($low_stock as $product): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['name'] ?? '') ?></td>
                            <td><?= $product['stock_quantity'] ?? 0 ?></td>
                            <td>
                                <a href="products.php?action=edit&id=<?= $product['id'] ?? '' ?>" class="btn-sm">Restock</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Scripts pour améliorer l'UX
        document.addEventListener('DOMContentLoaded', function() {
            // Confirmation avant les actions importantes
            const deleteButtons = document.querySelectorAll('.delete-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to delete this item?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>