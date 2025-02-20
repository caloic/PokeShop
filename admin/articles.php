<?php
require_once '../config.php';
require_once 'auth_admin.php';

// Récupérer tous les articles avec leur stock
$query = "
    SELECT articles.*, stocks.quantite 
    FROM articles 
    LEFT JOIN stocks ON articles.id = stocks.article_id 
    WHERE articles.is_deleted = FALSE
    ORDER BY articles.date_publication DESC
";
$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion des Articles - Administration</title>
    <link rel="stylesheet" href="../styles/admin.css">

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