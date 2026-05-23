<?php
require_once 'config.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die("Erreur de validation CSRF");
    }

    $nom = trim($_POST['nom']);
    $telephone = trim($_POST['telephone']);
    $zone_service = trim($_POST['zone_service'] ?? '');
    $type_vehicule = $_POST['type_vehicule'] ?? '';
    $disponibilite = $_POST['disponibilite'] ?? 'disponible';
    $description = trim($_POST['description'] ?? '');
    
    $allowed_vehicules = ['', 'Camion benne', 'Camion plateau', 'Camion frigorifique', 'Semi-remorque', 'Fourgon'];
    $allowed_dispo = ['disponible', 'occupé', 'indisponible'];
    
    if (!in_array($type_vehicule, $allowed_vehicules)) {
        $error = "Type de véhicule invalide";
    } elseif (!in_array($disponibilite, $allowed_dispo)) {
        $error = "Disponibilité invalide";
    } else {
        $stmt = $pdo->prepare("UPDATE profiles SET nom = ?, telephone = ?, zone_service = ?, type_vehicule = ?, disponibilite = ?, description = ? WHERE user_id = ?");
        $stmt->execute([$nom, $telephone, $zone_service, $type_vehicule, $disponibilite, $description, $user_id]);
        
        $success = "Profil mis à jour avec succès!";
    }
}

$stmt = $pdo->prepare("SELECT * FROM profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

$stmt = $pdo->prepare("SELECT AVG(note) as moyenne, COUNT(*) as total FROM reviews WHERE reviewed_id = ?");
$stmt->execute([$user_id]);
$reviews = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - TransportConnect</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">🚚 TransportConnect</div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Déconnexion</a>
        </div>
    </nav>

    <div class="container">
        <div class="profile-page">
            <h1>Mon Profil</h1>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?= h($error) ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?= h($success) ?></div>
            <?php endif; ?>
            
            <div class="profile-header">
                <div class="profile-info">
                    <h2><?= h($_SESSION['username']) ?></h2>
                    <p class="badge"><?= $user_type == 'chauffeur' ? '🚛 Chauffeur' : '📦 Transitaire' ?></p>
                    
                    <?php if($reviews['moyenne']): ?>
                        <div class="rating">
                            <span>Note: <?= h(number_format($reviews['moyenne'], 1)) ?>/5</span>
                            <span>(<?= h($reviews['total']) ?> avis)</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-card">
                <h3>Informations du profil</h3>
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="form-group">
                        <label for="nom">Nom complet</label>
                        <input type="text" id="nom" name="nom" value="<?= h($profile['nom'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" value="<?= h($profile['telephone'] ?? '') ?>" required>
                    </div>
                    
                    <?php if($user_type == 'chauffeur'): ?>
                    <div class="form-group">
                        <label for="zone_service">Zone de service</label>
                        <input type="text" id="zone_service" name="zone_service" value="<?= h($profile['zone_service'] ?? '') ?>" placeholder="Ex:Abidjan, Yamoussoukro">
                    </div>
                    
                    <div class="form-group">
                        <label for="type_vehicule">Type de véhicule</label>
                        <select id="type_vehicule" name="type_vehicule">
                            <option value="">Sélectionner...</option>
                            <option value="Camion benne" <?= ($profile['type_vehicule'] ?? '') == 'Camion benne' ? 'selected' : '' ?>>Camion benne</option>
                            <option value="Camion plateau" <?= ($profile['type_vehicule'] ?? '') == 'Camion plateau' ? 'selected' : '' ?>>Camion plateau</option>
                            <option value="Camion frigorifique" <?= ($profile['type_vehicule'] ?? '') == 'Camion frigorifique' ? 'selected' : '' ?>>Camion frigorifique</option>
                            <option value="Semi-remorque" <?= ($profile['type_vehicule'] ?? '') == 'Semi-remorque' ? 'selected' : '' ?>>Semi-remorque</option>
                            <option value="Fourgon" <?= ($profile['type_vehicule'] ?? '') == 'Fourgon' ? 'selected' : '' ?>>Fourgon</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="disponibilite">Disponibilité</label>
                        <select id="disponibilite" name="disponibilite">
                            <option value="disponible" <?= ($profile['disponibilite'] ?? 'disponible') == 'disponible' ? 'selected' : '' ?>>✅ Disponible</option>
                            <option value="occupé" <?= ($profile['disponibilite'] ?? '') == 'occupé' ? 'selected' : '' ?>>🔄 Occupé</option>
                            <option value="indisponible" <?= ($profile['disponibilite'] ?? '') == 'indisponible' ? 'selected' : '' ?>>❌ Indisponible</option>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?= h($profile['description'] ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </form>
            </div>
            
            <div class="reviews-section">
                <h3>Mes Avis</h3>
                <?php
                $stmt = $pdo->prepare("SELECT r.*, u.username FROM reviews r JOIN users u ON r.reviewer_id = u.id WHERE r.reviewed_id = ? ORDER BY r.created_at DESC");
                $stmt->execute([$user_id]);
                $user_reviews = $stmt->fetchAll();
                ?>
                
                <?php if(empty($user_reviews)): ?>
                    <p>Aucun avis pour le moment</p>
                <?php else: ?>
                    <?php foreach($user_reviews as $r): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <strong><?= h($r['username']) ?></strong>
                                <span class="stars"><?= str_repeat('⭐', (int)$r['note']) ?></span>
                            </div>
                            <p><?= h($r['commentaire']) ?></p>
                            <small><?= h(date('d/m/Y', strtotime($r['created_at']))) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <div class="profile-header">
                <div class="profile-info">
                    <h2><?= $_SESSION['username'] ?></h2>
                    <p class="badge"><?= $user_type == 'chauffeur' ? '🚛 Chauffeur' : '📦 Transitaire' ?></p>
                    
                    <?php if($reviews['moyenne']): ?>
                        <div class="rating">
                            <span>Note: <?= number_format($reviews['moyenne'], 1) ?>/5</span>
                            <span>(<?= $reviews['total'] ?> avis)</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-card">
                <h3>Informations du profil</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="nom">Nom complet</label>
                        <input type="text" id="nom" name="nom" value="<?= $profile['nom'] ?? '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" value="<?= $profile['telephone'] ?? '' ?>" required>
                    </div>
                    
                    <?php if($user_type == 'chauffeur'): ?>
                    <div class="form-group">
                        <label for="zone_service">Zone de service</label>
                        <input type="text" id="zone_service" name="zone_service" value="<?= $profile['zone_service'] ?? '' ?>" placeholder="Ex:Abidjan, Yamoussoukro">
                    </div>
                    
                    <div class="form-group">
                        <label for="type_vehicule">Type de véhicule</label>
                        <select id="type_vehicule" name="type_vehicule">
                            <option value="">Sélectionner...</option>
                            <option value="Camion benne" <?= ($profile['type_vehicule'] ?? '') == 'Camion benne' ? 'selected' : '' ?>>Camion benne</option>
                            <option value="Camion plateau" <?= ($profile['type_vehicule'] ?? '') == 'Camion plateau' ? 'selected' : '' ?>>Camion plateau</option>
                            <option value="Camion frigorifique" <?= ($profile['type_vehicule'] ?? '') == 'Camion frigorifique' ? 'selected' : '' ?>>Camion frigorifique</option>
                            <option value="Semi-remorque" <?= ($profile['type_vehicule'] ?? '') == 'Semi-remorque' ? 'selected' : '' ?>>Semi-remorque</option>
                            <option value="Fourgon" <?= ($profile['type_vehicule'] ?? '') == 'Fourgon' ? 'selected' : '' ?>>Fourgon</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="disponibilite">Disponibilité</label>
                        <select id="disponibilite" name="disponibilite">
                            <option value="disponible" <?= ($profile['disponibilite'] ?? 'disponible') == 'disponible' ? 'selected' : '' ?>>✅ Disponible</option>
                            <option value="occupé" <?= ($profile['disponibilite'] ?? '') == 'occupé' ? 'selected' : '' ?>>🔄 Occupé</option>
                            <option value="indisponible" <?= ($profile['disponibilite'] ?? '') == 'indisponible' ? 'selected' : '' ?>>❌ Indisponible</option>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?= $profile['description'] ?? '' ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </form>
            </div>
            
            <div class="reviews-section">
                <h3>Mes Avis</h3>
                <?php
                $stmt = $pdo->prepare("SELECT r.*, u.username FROM reviews r JOIN users u ON r.reviewer_id = u.id WHERE r.reviewed_id = ? ORDER BY r.created_at DESC");
                $stmt->execute([$user_id]);
                $user_reviews = $stmt->fetchAll();
                ?>
                
                <?php if(empty($user_reviews)): ?>
                    <p>Aucun avis pour le moment</p>
                <?php else: ?>
                    <?php foreach($user_reviews as $r): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <strong><?= $r['username'] ?></strong>
                                <span class="stars"><?= str_repeat('⭐', $r['note']) ?></span>
                            </div>
                            <p><?= $r['commentaire'] ?></p>
                            <small><?= date('d/m/Y', strtotime($r['created_at'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>