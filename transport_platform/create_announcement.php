<?php
require_once 'config.php';

if(!isLoggedIn() || $_SESSION['user_type'] != 'transitaire') {
    redirect('login.php');
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die("Erreur de validation CSRF");
    }

    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $depart = trim($_POST['depart']);
    $arrivee = trim($_POST['arrivee']);
    $type_marchandise = trim($_POST['type_marchandise']);
    $poids = trim($_POST['poids']);
    $date_livraison = $_POST['date_livraison'];
    $prix = $_POST['prix'] ? (float)$_POST['prix'] : null;
    
    if (empty($titre) || empty($depart) || empty($arrivee)) {
        $error = "Veuillez remplir tous les champs obligatoires";
    } else {
        $stmt = $pdo->prepare("INSERT INTO announcements (user_id, titre, description, depart, arrivee, type_marchandise, poids, date_livraison, prix) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $titre, $description, $depart, $arrivee, $type_marchandise, $poids, $date_livraison, $prix]);
        
        $success = "Annonce publiée avec succès!";
        redirect('announcements.php');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publier une Annonce - TransportConnect</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">🚚 TransportConnect</div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="announcements.php">Annonces</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-card">
            <h2>Publier une annonce de transport</h2>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?= h($error) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <?= csrfField() ?>
                <div class="form-group">
                    <label for="titre">Titre de l'annonce</label>
                    <input type="text" id="titre" name="titre" placeholder="Ex: Transport de cacao vers Abidjan" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="depart">Lieu de départ</label>
                        <input type="text" id="depart" name="depart" placeholder="Ex: Bouaké" required>
                    </div>
                    <div class="form-group">
                        <label for="arrivee">Lieu d'arrivée</label>
                        <input type="text" id="arrivee" name="arrivee" placeholder="Ex: Abidjan" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="type_marchandise">Type de marchandise</label>
                        <input type="text" id="type_marchandise" name="type_marchandise" placeholder="Ex: Marchandises diverses" required>
                    </div>
                    <div class="form-group">
                        <label for="poids">Poids (approx)</label>
                        <input type="text" id="poids" name="poids" placeholder="Ex: 5 tonnes" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="date_livraison">Date de livraison souhaitée</label>
                        <input type="date" id="date_livraison" name="date_livraison" required>
                    </div>
                    <div class="form-group">
                        <label for="prix">Prix proposé (XOF)</label>
                        <input type="number" id="prix" name="prix" placeholder="Laisser vide pour négocier">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description détaillée</label>
                    <textarea id="description" name="description" rows="5" placeholder="Décrivez les détails de la marchandise, conditions de transport, etc." required></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Publier l'annonce</button>
            </form>
        </div>
    </div>
</body>
</html>