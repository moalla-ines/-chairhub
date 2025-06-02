<?php
session_start();

// Vérification admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once 'config.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("Database connection error");
}

if (!isset($_GET['id'])) {
    header("Location: liste_users.php");
    exit();
}

$id = $_GET['id'];

// Récupération de l'utilisateur existant
$stmt = $pdo->prepare("SELECT * FROM users WHERE iduser = ?");
$stmt ->bindParam(1, $id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Utilisateur non trouvé");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'] ?? null;
    $country = $_POST['country'] ?? null;
    $role = $_POST['role'];
    
    // Gestion du mot de passe (seulement si modifié)
    $password = $user['password'];
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }
    
    $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, password = ?, phone = ?, country = ?, role = ? WHERE iduser = ?");
    $stmt->bind_param("ssssssi", $name, $email, $password, $phone, $country, $role, $id);
    
    if ($stmt->execute()) {
        header("Location: liste_users.php?success=1");
        exit();
    } else {
        $error = "Erreur lors de la mise à jour : " .$stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #1a252f;
            --danger: #e74c3c;
            --danger-hover: #c0392b;
            --success: #2ecc71;
            --text: #333;
            --light-gray: #f5f5f5;
            --white: #fff;
            --border: #ddd;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-gray);
            color: var(--text);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        
        .dashboard-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        
        .sidebar {
            background: var(--primary);
            color: var(--white);
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
            color: var(--white);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .sidebar a:hover, .sidebar a.active {
            background-color: var(--secondary);
        }
        
        .main-content {
            padding: 30px;
            background: var(--light-gray);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }
        
        .logout-btn {
            background: var(--danger);
            color: var(--white);
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: var(--danger-hover);
        }
        
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background: var(--white);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(44, 62, 80, 0.2);
        }
        
        .btn {
            display: inline-block;
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: var(--secondary);
        }
        
        .btn-secondary {
            background: #7f8c8d;
            margin-left: 10px;
        }
        
        .btn-secondary:hover {
            background: #6c7a7d;
        }
        
        .error-message {
            color: var(--danger);
            background: #ffebee;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid var(--danger);
        }
        
        .success-message {
            color: var(--success);
            background: #e8f5e9;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid var(--success);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <h2>Comfort Chairs</h2>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="products.php"><i class="fas fa-chair"></i> Products</a></li>
                <li><a href="categories.php"><i class="fas fa-list"></i> Categories</a></li>
                <li><a href="users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Edit User</h1>
                <div>
                    <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?> </span>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST">
                    <div class="form-group">
                        <label>Full Name:</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>New Password (leave blank to keep current):</label>
                        <input type="password" name="password">
                    </div>
                    <div class="form-group">
                        <label>Phone:</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Country:</label>
                        <input type="text" name="country" value="<?= htmlspecialchars($user['country'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Role:</label>
                        <select name="role">
                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn">Update User</button>
                        <a href="liste_users.php" class="btn btn-secondary">Back to List</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>