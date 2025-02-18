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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h3 {
            margin: 0;
            color: #7f8c8d;
        }

        .stat-card .number {
            font-size: 2em;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .menu-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s;
        }

        .menu-card:hover {
            transform: translateY(-5px);
        }

        .menu-card a {
            text-decoration: none;
            color: inherit;
        }

        .menu-card h2 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .menu-card p {
            color: #7f8c8d;
            margin: 0;
        }

        .back-btn {
            background-color: #34495e;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
        }

        .back-btn:hover {
            background-color: #2c3e50;
        }
    </style>
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