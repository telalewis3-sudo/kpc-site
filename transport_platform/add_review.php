<?php
require_once 'config.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die("Erreur de validation CSRF");
    }

    $reviewed_id = (int)($_POST['reviewed_id'] ?? 0);
    $note = (int)($_POST['note'] ?? 0);
    $commentaire = trim($_POST['commentaire'] ?? '');

    if ($reviewed_id <= 0) {
        die("ID utilisateur invalide");
    }

    if ($note < 1 || $note > 5) {
        die("Note invalide (1-5)");
    }

    if ($reviewed_id == $_SESSION['user_id']) {
        die("Vous ne pouvez pas vous noter vous-même");
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$reviewed_id]);
    if (!$stmt->fetch()) {
        die("Utilisateur non trouvé");
    }

    $stmt = $pdo->prepare("INSERT INTO reviews (reviewer_id, reviewed_id, note, commentaire) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $reviewed_id, $note, $commentaire]);

    redirect('view_profile.php?user=' . $reviewed_id);
}
?>
