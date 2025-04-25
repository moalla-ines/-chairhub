<?php
session_start();

// Vérification admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once 'config.php';

if (!isset($db) || !($db instanceof mysqli)) {
    die("Database connection error");
}

// Initialisation des variables avec des valeurs par défaut
$total_users = 0;
$total_products = 0;
$recent_users = [];
$low_stock = []; // Initialisation cruciale
$dashboard_error = null;

try {
    // Nombre total d'utilisateurs
    $result = $db->query("SELECT COUNT(*) as total_users FROM users");
    $total_users = $result->fetch_assoc()['total_users'] ?? 0;
    
    // Nombre total de produits
    $result = $db->query("SELECT COUNT(*) as total_products FROM products");
    $total_products = $result->fetch_assoc()['total_products'] ?? 0;
    
    // Derniers utilisateurs inscrits
    $result = $db->query("SELECT iduser, name, email, created_at FROM users ORDER BY iduser DESC LIMIT 5");
    $recent_users = $result->fetch_all(MYSQLI_ASSOC) ?: [];
    
    // Produits avec faible stock
    $result = $db->query("SELECT id, name, stock_quantity FROM products WHERE stock_quantity < 5 ORDER BY stock_quantity ASC");
    $low_stock = $result->fetch_all(MYSQLI_ASSOC) ?: [];

} catch (Exception $e) {
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
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            margin-top: 0;
            color: #7f8c8d;
            font-size: 1rem;
        }
        
        .stat-card .value {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }
        
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            color: #2c3e50;
        }
        
        tr:hover {
            background-color: #f5f5f5;
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
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .logout-btn:hover {
            background: #c0392b;
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
                <li><a href="categories.php"><i class="fas fa-list"></i> Categories</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Admin Dashboard</h1>
                <div>
                    <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?> </span>
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
                    <h3>Low Stock Items</h3>
                    <div class="value"><?= count($low_stock) ?></div>
                    <a href="products.php?filter=low_stock">View items</a>
                </div>
            </div>
            
            <!-- Recent Users Table -->
            <div class="table-container">
                <h2>Recent Users</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <a href="users.php?action=view&id=<?= $user['id'] ?>" class="btn-sm">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= $product['stock_quantity'] ?></td>
                            <td>
                                <a href="products.php?action=edit&id=<?= $product['id'] ?>" class="btn-sm">Restock</a>
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