<?php
require_once 'config.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT m.*, u.username as sender_name, a.titre as ann_title 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        LEFT JOIN announcements a ON m.announcement_id = a.id
        WHERE m.receiver_id = ? 
        ORDER BY m.created_at DESC");
$stmt->execute([$user_id]);
$received = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT m.*, u.username as receiver_name, a.titre as ann_title 
        FROM messages m 
        JOIN users u ON m.receiver_id = u.id 
        LEFT JOIN announcements a ON m.announcement_id = a.id
        WHERE m.sender_id = ? 
        ORDER BY m.created_at DESC");
$stmt->execute([$user_id]);
$sent = $stmt->fetchAll();

$stmt = $pdo->prepare("UPDATE messages SET lu = 1 WHERE receiver_id = ?");
$stmt->execute([$user_id]);

$conversations = [];
foreach(array_merge($received, $sent) as $msg) {
    $other_id = $msg['sender_id'] == $user_id ? $msg['receiver_id'] : $msg['sender_id'];
    $other_name = $msg['sender_id'] == $user_id ? $msg['receiver_name'] : $msg['sender_name'];
    
    if(!isset($conversations[$other_id])) {
        $conversations[$other_id] = [
            'name' => $other_name,
            'last_message' => $msg['message'],
            'date' => $msg['created_at'],
            'unread' => 0
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - TransportConnect</title>
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
        <h1>💬 Mes Messages</h1>
        
        <div class="messages-layout">
            <div class="conversations-list">
                <h3>Conversations</h3>
                <?php if(empty($conversations)): ?>
                    <p class="empty">Aucune conversation</p>
                <?php else: ?>
                    <?php foreach($conversations as $id => $conv): ?>
                        <a href="conversation.php?user=<?= (int)$id ?>" class="conversation-item">
                            <div class="conv-avatar"><?= h(strtoupper(substr($conv['name'], 0, 1))) ?></div>
                            <div class="conv-info">
                                <strong><?= h($conv['name']) ?></strong>
                                <span class="preview"><?= h(substr($conv['last_message'], 0, 40)) ?>...</span>
                                <small><?= h(date('d/m H:i', strtotime($conv['date']))) ?></small>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="messages-list">
                <h3>Messages reçus (<?= h(count($received)) ?>)</h3>
                <?php if(empty($received)): ?>
                    <p class="empty">Aucun message reçu</p>
                <?php else: ?>
                    <?php foreach($received as $msg): ?>
                        <div class="message-card <?= $msg['lu'] ? '' : 'unread' ?>">
                            <div class="msg-header">
                                <strong>De: <?= h($msg['sender_name']) ?></strong>
                                <span class="date"><?= h(date('d/m/Y H:i', strtotime($msg['created_at']))) ?></span>
                            </div>
                            <?php if($msg['ann_title']): ?>
                                <p class="ann-ref">📦 <?= h($msg['ann_title']) ?></p>
                            <?php endif; ?>
                            <p class="msg-content"><?= nl2br(h($msg['message'])) ?></p>
                            <a href="new_message.php?to=<?= (int)$msg['sender_id'] ?>" class="btn btn-small">Répondre</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <h3 style="margin-top: 30px;">Messages envoyés (<?= h(count($sent)) ?>)</h3>
                <?php if(empty($sent)): ?>
                    <p class="empty">Aucun message envoyé</p>
                <?php else: ?>
                    <?php foreach($sent as $msg): ?>
                        <div class="message-card sent">
                            <div class="msg-header">
                                <strong>À: <?= h($msg['receiver_name']) ?></strong>
                                <span class="date"><?= h(date('d/m/Y H:i', strtotime($msg['created_at']))) ?></span>
                            </div>
                            <?php if($msg['ann_title']): ?>
                                <p class="ann-ref">📦 <?= h($msg['ann_title']) ?></p>
                            <?php endif; ?>
                            <p class="msg-content"><?= nl2br(h($msg['message'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>