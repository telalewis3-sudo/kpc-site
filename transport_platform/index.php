<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TransportConnect - Plateforme de Transport</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">🚚 TransportConnect</div>
        <div class="nav-links">
            <a href="index.php">Accueil</a>
            <a href="announcements.php">Annonces</a>
            <a href="chauffeurs.php">Chauffeurs</a>
            <?php if(isLoggedIn()): ?>
                <a href="dashboard.php">Mon Espace</a>
                <a href="logout.php">Déconnexion</a>
            <?php else: ?>
                <a href="login.php">Connexion</a>
                <a href="register.php" class="btn-nav">Inscription</a>
            <?php endif; ?>
        </div>
    </nav>

    <header class="hero">
        <div class="hero-content">
            <h1>Connectez Transitaires et Chauffeurs</h1>
            <p>La plateforme qui simplifie le transport de marchandise en Africa</p>
            <div class="hero-buttons">
                <a href="register.php?type=chauffeur" class="btn btn-primary">Je suis Chauffeur</a>
                <a href="register.php?type=transitaire" class="btn btn-secondary">Je suis Transitaire</a>
            </div>
        </div>
    </header>

    <section class="features">
        <div class="feature">
            <div class="icon">📦</div>
            <h3>Publier des Annonces</h3>
            <p>Les transitaires publient leurs besoins de transport</p>
        </div>
        <div class="feature">
            <div class="icon">🚛</div>
            <h3>Trouver des Chauffeurs</h3>
            <p>Les chauffeurs trouvent des livraisons près de chez eux</p>
        </div>
        <div class="feature">
            <div class="icon">💬</div>
            <h3>Communiquer</h3>
            <p>Messagerie intégrée pour discuter des détails</p>
        </div>
        <div class="feature">
            <div class="icon">⭐</div>
            <h3>Avis & Notes</h3>
            <p>Construisez votre réputation avec des avis vérifiés</p>
        </div>
    </section>

    <section class="stats">
        <div class="stat">
            <span class="number">500+</span>
            <span class="label">Chauffeurs</span>
        </div>
        <div class="stat">
            <span class="number">1200+</span>
            <span class="label">Livraisons</span>
        </div>
        <div class="stat">
            <span class="number">98%</span>
            <span class="label">Satisfaction</span>
        </div>
    </section>

    <footer>
        <p>&copy; 2026 TransportConnect. Tous droits réservés.</p>
    </footer>
</body>
</html>