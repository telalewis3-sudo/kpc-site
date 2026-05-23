<?php
require_once 'config.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

$to_id = (int)($_GET['to'] ?? 0);

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$to_id]);
$recipient = $stmt->fetch();

if(!$recipient) {
    die("Utilisateur non trouvé");
}

$error = '';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die("Erreur de validation CSRF");
    }

    $message = trim($_POST['message']);
    
    if (empty($message)) {
        $error = "Le message ne peut pas être vide";
    } else {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $to_id, $message]);
        
        redirect('messages.php');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau Message - TransportConnect</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">🚚 TransportConnect</div>
        <div class="nav-links">
            <a href="messages.php">Messages</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-card">
            <h2>Nouveau message à <?= h($recipient['username']) ?></h2>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?= h($error) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <?= csrfField() ?>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="6" required placeholder="Écrivez votre message..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Envoyer</button>
                <a href="messages.php" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
</body>
</html>