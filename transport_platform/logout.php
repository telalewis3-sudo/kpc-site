<?php
require_once 'config.php';

$_SESSION = [];
setcookie(session_name(), '', [
    'expires' => time() - 3600,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_destroy();
redirect('index.php');
?>