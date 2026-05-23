<?php
require_once 'config.php';

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT a.*, u.username, u.id as user_id FROM announcements a JOIN users u ON a.user_id = u.id WHERE a.id = ?");
$stmt->execute([$id]);
$announcement = $stmt->fetch();

if(!$announcement) {
    die("Annonce non trouvée");
}

$stmt = $pdo->prepare("SELECT p.* FROM profiles p WHERE p.user_id = ?");
$stmt->execute([$announcement['user_id']]);
$author_profile = $stmt->fetch();

$is_owner = isLoggedIn() && $_SESSION['user_id'] == $announcement['user_id'];
$is_chauffeur = isLoggedIn() && $_SESSION['user_type'] == 'chauffeur';

if($_SERVER['REQUEST_METHOD'] == 'POST' && $is_chauffeur && !$is_owner) {
    $message = $_POST['message'] ?? '';
    
    $stmt = $pdo->prepare("INSERT INTO applications (announcement_id, chauffeur_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$id, $_SESSION['user_id'], $message]);
    
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, announcement_id, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $announcement['user_id'], $id, "Candidature pour: " . $announcement['titre'] . ". Message: " . $message]);
    
    $success = "Candidature envoyée!";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($announcement['titre']) ?> - TransportConnect</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">🚚 TransportConnect</div>
        <div class="nav-links">
            <a href="index.php">Accueil</a>
            <a href="announcements.php">Annonces</a>
            <?php if(isLoggedIn()): ?>
                <a href="dashboard.php">Mon Espace</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <div class="announcement-detail">
            <a href="announcements.php" class="back-link">← Retour aux annonces</a>
            
            <h1><?= h($announcement['titre']) ?></h1>
            
            <div class="ann-price">
                <?= $announcement['prix'] ? h(number_format($announcement['prix'], 0, ',', ' ')) . ' XOF' : 'Prix à négocier' ?>
            </div>
            
            <div class="ann-route-large">
                <div class="city">
                    <span class="label">Départ</span>
                    <span class="value">📍 <?= h($announcement['depart']) ?></span>
                </div>
                <div class="arrow-large">➜</div>
                <div class="city">
                    <span class="label">Arrivée</span>
                    <span class="value">🏁 <?= h($announcement['arrivee']) ?></span>
                </div>
            </div>
            
            <div class="ann-info-grid">
                <div class="info-item">
                    <strong>Type de marchandise</strong>
                    <span><?= h($announcement['type_marchandise']) ?></span>
                </div>
                <div class="info-item">
                    <strong>Poids</strong>
                    <span><?= h($announcement['poids']) ?></span>
                </div>
                <div class="info-item">
                    <strong>Date de livraison</strong>
                    <span><?= $announcement['date_livraison'] ? h(date('d/m/Y', strtotime($announcement['date_livraison']))) : 'À définir' ?></span>
                </div>
                <div class="info-item">
                    <strong>Publiée par</strong>
                    <span><?= h($announcement['username']) ?></span>
                </div>
            </div>
            
            <div class="ann-description-full">
                <h3>Description</h3>
                <p><?= nl2br(h($announcement['description'])) ?></p>
            </div>
            
            <?php if($author_profile): ?>
            <div class="author-contact">
                <h3>Contact du demandeur</h3>
                <p>📞 <?= h($author_profile['telephone'] ?? 'Non disponible') ?></p>
                <?php if($author_profile['zone_service']): ?>
                <p>📍 Zone: <?= h($author_profile['zone_service']) ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if(isset($success)): ?>
                <div class="alert alert-success"><?= h($success) ?></div>
            <?php endif; ?>
            
            <?php if($is_chauffeur && !$is_owner): ?>
            <div class="apply-section">
                <h3>Postuler pour cette livraison</h3>
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="form-group">
                        <label for="message">Votre message (présentez-vous)</label>
                        <textarea id="message" name="message" rows="3" placeholder="Présentez-vous et indiquez votre disponibilité..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Envoyer ma candidature</button>
                </form>
            </div>
            <?php elseif(!isLoggedIn()): ?>
            <div class="apply-section">
                <a href="login.php?redirect=view_announcement.php?id=<?= (int)$id ?>" class="btn btn-primary">Se connecter pour postuler</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>