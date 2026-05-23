<?php
require_once 'config.php';

$user_id = $_GET['user'] ?? 0;

$stmt = $pdo->prepare("SELECT u.username, u.user_type, p.* FROM users u LEFT JOIN profiles p ON u.id = p.user_id WHERE u.id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if(!$user) {
    die("Utilisateur non trouvé");
}

$stmt = $pdo->prepare("SELECT AVG(note) as moyenne, COUNT(*) as total FROM reviews WHERE reviewed_id = ?");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

$stmt = $pdo->prepare("SELECT r.*, u.username as reviewer_name FROM reviews r JOIN users u ON r.reviewer_id = u.id WHERE r.reviewed_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$user_id]);
$reviews = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de <?= h($user['username']) ?> - TransportConnect</title>
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
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <div class="public-profile">
            <div class="profile-card">
                <div class="profile-avatar"><?= h(strtoupper(substr($user['username'], 0, 1))) ?></div>
                <h1><?= h($user['username']) ?></h1>
                <p class="badge-large"><?= $user['user_type'] == 'chauffeur' ? '🚛 Chauffeur' : '📦 Transitaire' ?></p>
                
                <?php if($stats['moyenne']): ?>
                    <div class="rating-large">
                        <span class="stars"><?= str_repeat('⭐', (int)round($stats['moyenne'])) ?></span>
                        <span><?= h(number_format($stats['moyenne'], 1)) ?>/5 (<?= h($stats['total']) ?> avis)</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if($user['user_type'] == 'chauffeur'): ?>
            <div class="profile-details">
                <h3>Informations du chauffeur</h3>
                <div class="detail-grid">
                    <?php if($user['zone_service']): ?>
                    <div class="detail-item">
                        <strong>Zone de service</strong>
                        <span>📍 <?= h($user['zone_service']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if($user['type_vehicule']): ?>
                    <div class="detail-item">
                        <strong>Véhicule</strong>
                        <span>🚛 <?= h($user['type_vehicule']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if($user['disponibilite']): ?>
                    <div class="detail-item">
                        <strong>Disponibilité</strong>
                        <span class="status-<?= $user['disponibilite'] ?>">
                            <?php if($user['disponibilite'] == 'disponible'): ?>✅ Disponible
                            <?php elseif($user['disponibilite'] == 'occupé'): ?>🔄 Occupé
                            <?php else: ?>❌ Indisponible<?php endif; ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    <?php if($user['telephone']): ?>
                    <div class="detail-item">
                        <strong>Téléphone</strong>
                        <span>📞 <?= h($user['telephone']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if($user['description']): ?>
                <p class="description"><?= h($user['description']) ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="reviews-list">
                <h3>Avis sur ce <?= h($user['user_type']) == 'chauffeur' ? 'chauffeur' : 'transitaire' ?></h3>
                
                <?php if(empty($reviews)): ?>
                    <p class="empty">Aucun avis pour le moment</p>
                <?php else: ?>
                    <?php foreach($reviews as $r): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <strong><?= h($r['reviewer_name']) ?></strong>
                                <span class="stars"><?= str_repeat('⭐', (int)$r['note']) ?></span>
                            </div>
                            <p><?= h($r['commentaire']) ?></p>
                            <small><?= h(date('d/m/Y', strtotime($r['created_at']))) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <?php if(isLoggedIn() && $_SESSION['user_id'] != $user_id): ?>
            <div class="leave-review">
                <h3>Laisser un avis</h3>
                <form method="POST" action="add_review.php">
                    <?= csrfField() ?>
                    <input type="hidden" name="reviewed_id" value="<?= (int)$user_id ?>">
                    <div class="form-group">
                        <label>Note</label>
                        <div class="rating-input">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                            <label>
                                <input type="radio" name="note" value="<?= $i ?>" required>
                                <span><?= $i ?> ⭐</span>
                            </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="commentaire">Commentaire</label>
                        <textarea id="commentaire" name="commentaire" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Soumettre l'avis</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>