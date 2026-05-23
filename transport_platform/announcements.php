<?php
require_once 'config.php';

$filter = $_GET['filter'] ?? 'all';

$sql = "SELECT a.*, u.username, u.id as user_id FROM announcements a 
        JOIN users u ON a.user_id = u.id 
        WHERE a.status = 'active'";

if($filter == 'today') {
    $sql .= " AND DATE(a.created_at) = CURDATE()";
}

$sql .= " ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$announcements = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM announcements WHERE status = 'active'");
$stmt->execute();
$total = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annonces de Transport - TransportConnect</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">🚚 TransportConnect</div>
        <div class="nav-links">
            <a href="index.php">Accueil</a>
            <a href="chauffeurs.php">Chauffeurs</a>
            <?php if(isLoggedIn()): ?>
                <a href="dashboard.php">Mon Espace</a>
                <a href="create_announcement.php" class="btn-nav">Publier une annonce</a>
            <?php else: ?>
                <a href="login.php">Connexion</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <div class="announcements-page">
            <div class="page-header">
                <h1>📦 Annonces de Transport</h1>
                <p><?= h($total['total']) ?> annonces actives</p>
            </div>
            
            <div class="filters">
                <a href="announcements.php?filter=all" class="filter-btn <?= h($filter) == 'all' ? 'active' : '' ?>">Toutes</a>
                <a href="announcements.php?filter=today" class="filter-btn <?= h($filter) == 'today' ? 'active' : '' ?>">Aujourd'hui</a>
            </div>
            
            <?php if(empty($announcements)): ?>
                <div class="empty-state">
                    <p>Aucune annonce disponible pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="announcements-grid">
                    <?php foreach($announcements as $a): ?>
                        <div class="announcement-card">
                            <div class="ann-header">
                                <h3><?= h($a['titre']) ?></h3>
                                <span class="price"><?= $a['prix'] ? h(number_format($a['prix'], 0, ',', ' ')) . ' XOF' : 'Prix à négocier' ?></span>
                            </div>
                            <div class="ann-route">
                                <span class="depart">📍 <?= h($a['depart']) ?></span>
                                <span class="arrow">→</span>
                                <span class="arrivee">🏁 <?= h($a['arrivee']) ?></span>
                            </div>
                            <div class="ann-details">
                                <p><strong>Marchandise:</strong> <?= h($a['type_marchandise']) ?></p>
                                <p><strong>Poids:</strong> <?= h($a['poids']) ?></p>
                                <p><strong>Livraison:</strong> <?= $a['date_livraison'] ? h(date('d/m/Y', strtotime($a['date_livraison']))) : 'À définir' ?></p>
                            </div>
                            <p class="ann-description"><?= h(substr($a['description'], 0, 100)) ?>...</p>
                            <div class="ann-footer">
                                <span class="author">Par <?= h($a['username']) ?></span>
                                <a href="view_announcement.php?id=<?= (int)$a['id'] ?>" class="btn btn-small">Voir détails</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>