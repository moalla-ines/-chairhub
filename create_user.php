<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'] ?? null;
    $country = $_POST['country'] ?? null;
    $role = $_POST['role'] ?? 'user';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, password, email, phone, country, role) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $password, $email, $phone, $country, $role]);
        
        header("Location: liste_users.php?success=1");
        exit();
    } catch (PDOException $e) {
        $error = "Erreur lors de la création : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un utilisateur</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --danger-color: #e74c3c;
            --success-color: #2ecc71;
            --light-gray: #f5f5f5;
            --dark-gray: #7f8c8d;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light-gray);
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary-color);
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        select:focus {
            border-color: var(--secondary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #1a252f;
        }
        
        .btn-secondary {
            background-color: var(--dark-gray);
            margin-left: 10px;
        }
        
        .btn-secondary:hover {
            background-color: #6c757d;
        }
        
        .action-buttons {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        
        @media (max-width: 600px) {
            .container {
                padding: 15px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .btn-secondary {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-user-plus"></i> Ajouter un utilisateur</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="name"><i class="fas fa-user"></i> Full name</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="phone"><i class="fas fa-phone"></i> phone</label>
                <input type="text" id="phone" name="phone">
            </div>
            
            <div class="form-group">
                <label for="country"><i class="fas fa-globe"></i> Countries</label>
                <input type="text" id="country" name="country">
            </div>
            
            <div class="form-group">
                <label for="role"><i class="fas fa-user-tag"></i> Rôle</label>
                <select id="role" name="role">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            <div class="action-buttons">
                <button type="submit" class="btn">
                    <i class="fas fa-save"></i> Create user
                </button>
                <a href="liste_users.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Return to list
                </a>
            </div>
        </form>
    </div>

    <script>
        // Amélioration UX : confirmation avant soumission
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            if (password.length < 8) {
                if (!confirm('Le mot de passe est court (moins de 8 caractères). Voulez-vous continuer ?')) {
                    e.preventDefault();
                }
            }
        });
    </script>
</body>
</html>