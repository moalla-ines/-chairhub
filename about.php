<?php
session_start();
$page_title = "À propos de nous";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?> - ChaiHub</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .about-section {
            padding: 80px 20px;
        background-color: #6c757d;
            text-align: center;
        }

        .about-section h2 {
            font-size: 2.8rem;
            margin-bottom: 30px;
            color: #222;
        }

        .about-section p {
            max-width: 800px;
            margin: 0 auto 25px auto;
            font-size: 1.1rem;
            line-height: 1.8;
            color: #444;
        }

        .values {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 40px;
            margin-top: 40px;
        }

        .value-box {
            background: white;
            border-radius: 12px;
            padding: 20px;
            width: 250px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .value-box i {
            font-size: 2rem;
            color: #ff6600;
            margin-bottom: 10px;
        }

        .value-box h4 {
            margin: 10px 0;
            font-size: 1.2rem;
            color: #333;
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
                    <li><a href="aboutus.php" class="active">À propos</a></li>
                    <li><a href="contact.php">Contact</a></li>
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

    <!-- ======= About Section ======= -->
    <section class="about-section">
        <h2>Bienvenue chez ChaiHub</h2>
        <p>
            Chez <strong>ChaiHub</strong>, nous allions design, confort et durabilité pour offrir à nos clients des chaises qui s'intègrent parfaitement à tous les espaces. Que ce soit pour le bureau, la maison ou les environnements professionnels, nous avons la chaise qu’il vous faut.
        </p>
        <p>
            Notre mission est de rendre chaque instant d’assise agréable, grâce à une sélection rigoureuse de matériaux de qualité et un service client à l’écoute.
        </p>

        <div class="values">
            <div class="value-box">
                <i class="fas fa-gem"></i>
                <h4>Qualité Premium</h4>
                <p>Des produits soigneusement choisis pour leur confort et leur longévité.</p>
            </div>
            <div class="value-box">
                <i class="fas fa-shipping-fast"></i>
                <h4>Livraison Rapide</h4>
                <p>Commandez aujourd’hui, recevez rapidement à votre porte.</p>
            </div>
            <div class="value-box">
                <i class="fas fa-headset"></i>
                <h4>Support Client</h4>
                <p>Une équipe dédiée pour répondre à toutes vos questions.</p>
            </div>
            <div class="value-box">
                <i class="fas fa-leaf"></i>
                <h4>Écoresponsable</h4>
                <p>Nous privilégions les matériaux durables et respectueux de l’environnement.</p>
            </div>
        </div>
    </section>

    <!-- ======= Footer ======= -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> ChaiHub. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>
