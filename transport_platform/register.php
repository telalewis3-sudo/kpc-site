<?php
require_once 'config.php';

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die("Erreur de validation CSRF");
    }

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = $_POST['user_type'];

    $allowed_types = ['chauffeur', 'transitaire'];
    if (!in_array($user_type, $allowed_types)) {
        $error = "Type de compte invalide";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide";
    } elseif (strlen($password) < 8) {
        $error = "Le mot de passe doit faire au moins 8 caractères";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, user_type) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password, $user_type]);
            
            $user_id = $pdo->lastInsertId();
            
            $stmt2 = $pdo->prepare("INSERT INTO profiles (user_id) VALUES (?)");
            $stmt2->execute([$user_id]);
            
            $success = "Compte créé avec succès!";
            redirect('login.php');
        } catch(PDOException $e) {
            if(strpos($e->getMessage(), 'Duplicate') !== false) {
                $error = "Ce nom d'utilisateur ou email existe déjà";
            } else {
                $error = "Erreur lors de l'inscription";
            }
        }
    }
}

$type = $_GET['type'] ?? 'transitaire';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - TransportConnect</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">🚚 TransportConnect</div>
        <div class="nav-links">
            <a href="index.php">Accueil</a>
            <a href="login.php">Connexion</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-card">
            <h2>Créer un compte</h2>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?= h($error) ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?= h($success) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <?= csrfField() ?>
                <div class="form-group">
                    <label>Type de compte</label>
                    <div class="type-selector">
                        <label class="type-option <?= $type == 'chauffeur' ? 'active' : '' ?>">
                            <input type="radio" name="user_type" value="chauffeur" <?= $type == 'chauffeur' ? 'checked' : '' ?>>
                            🚛 Chauffeur
                        </label>
                        <label class="type-option <?= $type == 'transitaire' ? 'active' : '' ?>">
                            <input type="radio" name="user_type" value="transitaire" <?= $type == 'transitaire' ? 'checked' : '' ?>>
                            📦 Transitaire
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username">Nom d'utilisateur (3-20 caractères, lettres et chiffres)</label>
                    <input type="text" id="username" name="username" pattern="[a-zA-Z0-9_]{3,20}" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe (8 caractères minimum)</label>
                    <input type="password" id="password" name="password" minlength="8" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">S'inscrire</button>
            </form>
            
            <p class="form-footer">Déjà un compte? <a href="login.php">Se connecter</a></p>
        </div>
    </div>
</body>
</html>