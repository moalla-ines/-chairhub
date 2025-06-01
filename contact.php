<?php
session_start();
$page_title = "Contactez-nous";

$message_sent = false;
$error = "";

// Traitement du formulaire (simple, sans envoi mail ici)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$name || !$email || !$subject || !$message) {
        $error = "Merci de remplir tous les champs.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    } else {
        // Ici tu peux ajouter l'envoi mail ou stockage en base
        $message_sent = true;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?> - ChaiHub</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .contact-section {
            padding: 80px 20px;
            background-color: #f9f9f9;
            max-width: 700px;
            margin: 0 auto;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .contact-section h2 {
            text-align: center;
            font-size: 2.8rem;
            margin-bottom: 30px;
            color: #222;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        input[type="text"],
        input[type="email"],
        textarea {
            padding: 12px 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            resize: vertical;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        textarea:focus {
            border-color:rgb(41, 40, 40);
            outline: none;
        }

        textarea {
            min-height: 120px;
        }

        button {
            background-color:rgb(41, 40, 40);
            border: none;
            padding: 15px;
            font-size: 1.2rem;
            color: white;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: rgb(41, 40, 40);
        }

        .message-success {
            text-align: center;
            color: green;
            font-weight: 600;
            margin-bottom: 25px;
        }

        .message-error {
            text-align: center;
            color: red;
            font-weight: 600;
            margin-bottom: 25px;
        }

        .footer {
            margin-top: 80px;
        }

        .active {
            color: #ff6600;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- ======= Header ======= -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="index.php"><h1>ChaiHub</h1></a>
            </div>
            <nav class="nav">
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="products.php">Produits</a></li>
                    <li><a href="aboutus.php">À propos</a></li>
                    <li><a href="contact.php" class="active">Contact</a></li>
                    <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Panier</a></li>
                    <li>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                        <?php else: ?>
                            <a href="login.php"><i class="fas fa-user"></i> Connexion</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- ======= Contact Section ======= -->
    <section class="contact-section">
        <h2>Contactez-nous</h2>

        <?php if ($message_sent): ?>
            <p class="message-success">Merci pour votre message, nous vous répondrons très bientôt !</p>
        <?php else: ?>
            <?php if ($error): ?>
                <p class="message-error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="POST" action="contact.php" novalidate>
                <input type="text" name="name" placeholder="Votre nom" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                <input type="email" name="email" placeholder="Votre email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                <input type="text" name="subject" placeholder="Sujet" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" required>
                <textarea name="message" placeholder="Votre message" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                <button type="submit">Envoyer</button>
            </form>
        <?php endif; ?>
    </section>

    <!-- ======= Footer ======= -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> ChaiHub. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>
