<?php
require_once 'config.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

$stmt = $pdo->prepare("SELECT p.* FROM profiles p WHERE p.user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

if($user_type == 'chauffeur') {
    $stmt = $pdo->prepare("SELECT a.*, u.username as auteur FROM announcements a JOIN users u ON a.user_id = u.id WHERE a.status = 'active' ORDER BY a.created_at DESC LIMIT 10");
    $stmt->execute();
    $announcements = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM applications WHERE chauffeur_id = ? AND status = 'accepte'");
    $stmt->execute([$user_id]);
    $livraisons = $stmt->fetch();
} else {
    $stmt = $pdo->prepare("SELECT * FROM announcements WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $my_announcements = $stmt->fetchAll();
}

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM messages WHERE receiver_id = ? AND lu = 0");
$stmt->execute([$user_id]);
$unread = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TransportConnect</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">🚚 TransportConnect</div>
        <div class="nav-links">
            <a href="index.php">Accueil</a>
            <a href="announcements.php">Annonces</a>
            <a href="messages.php">Messages <?= $unread['total'] > 0 ? '(' . h($unread['total']) . ')' : '' ?></a>
            <a href="profile.php">Mon Profil</a>
            <a href="logout.php">Déconnexion</a>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard">
            <h1>Bienvenue, <?= h($_SESSION['username']) ?>!</h1>
            
            <?php if(!$profile || ($user_type == 'chauffeur' && (!$profile['nom'] || !$profile['telephone']))): ?>
                <div class="alert alert-warning">
                    ⚠️ Votre profil est incomplet. <a href="profile.php">Compléter votre profil</a>
                </div>
            <?php endif; ?>
            
            <div class="dashboard-grid">
                <?php if($user_type == 'chauffeur'): ?>
                    <div class="dashboard-card">
                        <h3>📋 Dernières Annonces</h3>
                        <?php if(empty($announcements)): ?>
                            <p>Aucune annonce disponible</p>
                        <?php else: ?>
                            <ul class="announcement-list">
                                <?php foreach($announcements as $a): ?>
                                    <li>
                                        <a href="view_announcement.php?id=<?= (int)$a['id'] ?>">
                                            <strong><?= h($a['titre']) ?></strong>
                                            <span><?= h($a['depart']) ?> → <?= h($a['arrivee']) ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <a href="announcements.php" class="btn btn-small">Voir toutes les annonces</a>
                    </div>
                    
                    <div class="dashboard-card">
                        <h3>🚚 Livraisons effectuées</h3>
                        <p class="stat-big"><?= $livraisons['total'] ?? 0 ?></p>
                    </div>
                    
                    <div class="dashboard-card">
                        <h3>💬 Messages non lus</h3>
                        <p class="stat-big"><?= $unread['total'] ?></p>
                        <a href="messages.php" class="btn btn-small">Voir les messages</a>
                    </div>
                <?php else: ?>
                    <div class="dashboard-card">
                        <h3>📦 Mes Annonces</h3>
                        <?php if(empty($my_announcements)): ?>
                            <p>Vous n'avez pas encore publié d'annonces</p>
                        <?php else: ?>
                            <ul class="announcement-list">
                                <?php foreach($my_announcements as $a): ?>
                                    <li>
                                        <a href="view_announcement.php?id=<?= (int)$a['id'] ?>">
                                            <strong><?= h($a['titre']) ?></strong>
                                            <span><?= h($a['depart']) ?> → <?= h($a['arrivee']) ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <a href="create_announcement.php" class="btn btn-primary">Nouvelle annonce</a>
                    </div>
                    
                    <div class="dashboard-card">
                        <h3>💬 Messages</h3>
                        <p class="stat-big"><?= $unread['total'] ?> non lus</p>
                        <a href="messages.php" class="btn btn-small">Voir les messages</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>