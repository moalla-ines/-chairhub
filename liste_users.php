<?php
require_once 'config.php';

// Vérification de la connexion
if (!isset($db) || !($db instanceof mysqli)) {
    die("Erreur de connexion à la base de données");
}

// Récupération des utilisateurs
$users = [];
try {
    $result = $db->query("SELECT * FROM users ORDER BY iduser DESC");
    if ($result) {
        $users = $result->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    die("Erreur lors de la récupération : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>List of users</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1 {
            color: #2c3e50;
        }
        .success-message {
            color: green;
            padding: 10px;
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .add-user-btn {
            display: inline-block;
            background-color: #2c3e50;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .add-user-btn:hover {
            background-color: #1a252f;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            background-color: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #2c3e50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .action-links a {
            color: #3498db;
            text-decoration: none;
            margin: 0 5px;
        }
        .action-links a:hover {
            text-decoration: underline;
        }
        .delete-link {
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <h1>List of users</h1>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i> 
            <?= ($_GET['success'] === 'user_deleted') ? 'User deleted successfully!' : 'Mission accomplished!' ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <?php
            $errors = [
                'invalid_id' => 'Invalid user ID',
                'user_not_found' => 'User not found',
                'delete_failed' => 'Delete failed',
                'database_error' => 'Database error'
            ];
            echo $errors[$_GET['error']] ?? 'Unknown error';
            ?>
        </div>
    <?php endif; ?>
    
    <a href="create_user.php" class="add-user-btn">
        <i class="fas fa-user-plus"></i> Create User
    </a>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Country</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['iduser']) ?></td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($user['country'] ?? 'N/A') ?></td>
                    <td>
                        <span class="role-badge <?= strtolower($user['role']) ?>">
                            <?= htmlspecialchars($user['role']) ?>
                        </span>
                    </td>
                    <td class="action-links">
                       
                        <a href="edit_users.php?id=<?= $user['iduser'] ?>" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="delete_user.php?id=<?= $user['iduser'] ?>" 
                           class="delete-link" 
                           title="Delete"
                           onclick="return confirm('Do you really want to delete this user?')">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center;">No users found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        // Confirmation for deletion
        document.querySelectorAll('.delete-link').forEach(link => {
            link.addEventListener('click', (e) => {
                if (!confirm('Do you really want to delete this user?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>