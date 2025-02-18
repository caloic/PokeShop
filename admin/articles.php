<?php
require_once '../config.php';
require_once 'auth_admin.php';

// Récupérer tous les articles avec leur stock
$query = "
    SELECT articles.*, stocks.quantite 
    FROM articles 
    LEFT JOIN stocks ON articles.id = stocks.article_id 
    ORDER BY articles.date_publication DESC
";
$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion des Articles - Administration</title>
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

        .articles-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .articles-table th,
        .articles-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .articles-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .articles-table tr:hover {
            background-color: #f8f9fa;
        }

        .thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin: 0 4px;
        }

        .edit-btn {
            background-color: #3498db;
            color: white;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: white;
        }

        .add-btn {
            background-color: #2ecc71;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
        }

        .back-btn {
            background-color: #34495e;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
        }

        .stock-status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
        }

        .in-stock { background-color: #e8f5e9; color: #2e7d32; }
        .low-stock { background-color: #fff3e0; color: #f57c00; }
        .out-of-stock { background-color: #ffebee; color: #c62828; }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .success { background-color: #e8f5e9; color: #2e7d32; }
        .error { background-color: #ffebee; color: #c62828; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Gestion des Articles</h1>
        <div>
            <a href="index.php" class="back-btn">Retour au tableau de bord</a>
            <a href="edit_article.php" class="add-btn">Ajouter un article</a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success">
            <?php
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error">
            <?php
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <table class="articles-table">
        <thead>
        <tr>
            <th>Image</th>
            <th>Nom</th>
            <th>Prix</th>
            <th>Stock</th>
            <th>Date de publication</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($article = $result->fetch_assoc()):
            // Définir le statut du stock
            $stockStatus = '';
            $stockClass = '';
            if ($article['quantite'] > 10) {
                $stockStatus = 'En stock';
                $stockClass = 'in-stock';
            } elseif ($article['quantite'] > 0) {
                $stockStatus = 'Stock faible';
                $stockClass = 'low-stock';
            } else {
                $stockStatus = 'Rupture';
                $stockClass = 'out-of-stock';
            }
            ?>
            <tr>
                <td>
                    <img src="<?php echo htmlspecialchars($article['image_url']); ?>"
                         alt="<?php echo htmlspecialchars($article['nom']); ?>"
                         class="thumbnail">
                </td>
                <td><?php echo htmlspecialchars($article['nom']); ?></td>
                <td><?php echo number_format($article['prix'], 2); ?> €</td>
                <td>
                            <span class="stock-status <?php echo $stockClass; ?>">
                                <?php echo $article['quantite']; ?> - <?php echo $stockStatus; ?>
                            </span>
                </td>
                <td><?php echo date('d/m/Y H:i', strtotime($article['date_publication'])); ?></td>
                <td>
                    <a href="edit_article.php?id=<?php echo $article['id']; ?>" class="action-btn edit-btn">Modifier</a>
                    <a href="delete_article.php?id=<?php echo $article['id']; ?>"
                       class="action-btn delete-btn"
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')">
                        Supprimer
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>