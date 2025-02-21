<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    // Stocker l'URL actuelle pour la redirection après connexion
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}
?>