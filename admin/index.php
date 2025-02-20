<?php
require_once '../config.php';
require_once 'auth_admin.php';

// Récupérer les statistiques
$stats = [
    'users' => $mysqli->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'articles' => $mysqli->query("SELECT COUNT(*) as count FROM articles")->fetch_assoc()['count'],
    'orders' => $mysqli->query("SELECT COUNT(*) as count FROM commandes")->fetch_assoc()['count'],
    'stock_low' => $mysqli->query("SELECT COUNT(*) as count FROM stocks WHERE quantite < 10")->fetch_assoc()['count']
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Administration</title>
    <link rel="stylesheet" href="../styles/admin.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Panneau d'administration</h1>
        <a href="../index.php" class="back-btn">Retour au site</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Utilisateurs</h3>
            <div class="number"><?php echo $stats['users']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Articles</h3>
            <div class="number"><?php echo $stats['articles']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Commandes</h3>
            <div class="number"><?php echo $stats['orders']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Stock Faible</h3>
            <div class="number"><?php echo $stats['stock_low']; ?></div>
        </div>
    </div>

    <div class="menu-grid">
        <div class="menu-card">
            <a href="articles.php">
                <h2>Gestion des Articles</h2>
                <p>Ajouter, modifier ou supprimer des articles</p>
            </a>
        </div>
        <div class="menu-card">
            <a href="users.php">
                <h2>Gestion des Utilisateurs</h2>
                <p>Gérer les comptes utilisateurs</p>
            </a>
        </div>
    </div>
</div>
</body>
</html>