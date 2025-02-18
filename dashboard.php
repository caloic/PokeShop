<?php
require_once 'config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Récupérer le nombre d'articles dans le panier
$cart_query = "SELECT SUM(quantite) as total_items FROM carts WHERE user_id = ?";
$cart_stmt = $mysqli->prepare($cart_query);
$cart_stmt->bind_param("i", $_SESSION['user_id']);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();
$cart_count = $cart_result->fetch_assoc()['total_items'] ?? 0;

// Récupérer le rôle de l'utilisateur
$role_query = "SELECT role FROM users WHERE id = ?";
$role_stmt = $mysqli->prepare($role_query);
$role_stmt->bind_param("i", $_SESSION['user_id']);
$role_stmt->execute();
$user_role = $role_stmt->get_result()->fetch_assoc()['role'];

// Récupérer tous les articles avec les informations de l'auteur
$query = "
    SELECT articles.*, stocks.quantite, users.username as author, users.id as author_id, articles.user_id 
    FROM articles 
    LEFT JOIN stocks ON articles.id = stocks.article_id 
    LEFT JOIN users ON articles.user_id = users.id
    ORDER BY articles.date_publication DESC
";
$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Articles en vente</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            width: calc(100% - 30px);
            max-width: 100%;
            box-sizing: border-box;
        }

        .user-info {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .profile-link {
            color: #333;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.2s;
        }

        .profile-link:hover {
            color: #3498db;
        }

        .cart-info {
            display: flex;
            align-items: center;
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border-radius: 20px;
            text-decoration: none;
            white-space: nowrap;
        }

        .cart-count {
            background-color: white;
            color: #007bff;
            padding: 2px 8px;
            border-radius: 50%;
            margin-left: 8px;
            font-weight: bold;
        }

        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }

        .article-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            position: relative;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            display: flex;
            flex-direction: column;
            height: 100%;
            box-sizing: border-box;
            max-width: 100%;
        }

        .article-card:hover {
            transform: translateY(-5px);
        }

        .article-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .article-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .article-title {
            font-size: 1.2em;
            margin: 10px 0;
            color: #333;
        }

        .article-price {
            font-weight: bold;
            color: #2ecc71;
            font-size: 1.2em;
            margin: 10px 0;
        }

        .stock-status {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            z-index: 1;
        }

        .in-stock { background-color: #2ecc71; color: white; }
        .low-stock { background-color: #f1c40f; color: black; }
        .out-of-stock { background-color: #e74c3c; color: white; }

        .admin-btn {
            padding: 8px 15px;
            background-color: #e67e22;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .admin-btn:hover {
            background-color: #d35400;
        }

        .create-product-btn {
            padding: 8px 15px;
            background-color: #2ecc71;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .create-product-btn:hover {
            background-color: #27ae60;
        }

        .logout-btn {
            padding: 8px 15px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

        .article-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 10px;
            width: 100%;
        }

        .button-container {
            width: 100%;
        }

        .add-to-cart-btn {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            text-align: center;
            box-sizing: border-box;
            display: inline-block;
        }

        .add-to-cart-btn:hover {
            background-color: #0056b3;
        }

        .add-to-cart-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .article-author {
            font-size: 0.9em;
            color: #666;
            margin: 5px 0;
        }

        .article-author a {
            color: #3498db;
            text-decoration: none;
        }

        .article-author a:hover {
            text-decoration: underline;
        }

        .article-description {
            color: #666;
            margin: 10px 0;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        .deprecated-error {
            display: none;
        }
    </style>
</head>
<body>
<div class="header">
    <div class="user-info">
        <span>Bienvenue, <a href="account.php" class="profile-link"><?php echo htmlspecialchars($_SESSION['username']); ?></a></span>
        <a href="product/create.php" class="create-product-btn">Créer un article</a>
        <?php if ($user_role === 'admin'): ?>
            <a href="admin/index.php" class="admin-btn">Administration</a>
        <?php endif; ?>
        <a href="logout.php" class="logout-btn">Déconnexion</a>
    </div>
    <a href="cart.php" class="cart-info">
        Panier <span class="cart-count"><?php echo $cart_count; ?></span>
    </a>
</div>

<h1>Articles en vente</h1>

<div class="articles-grid">
    <?php while ($article = $result->fetch_assoc()): ?>
        <div class="article-card">
            <?php
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

            <span class="stock-status <?php echo $stockClass; ?>">
                <?php echo $stockStatus; ?>
            </span>

            <div class="article-content">
                <a href="product.php?id=<?php echo $article['id']; ?>&slug=<?php echo $article['slug']; ?>">
                    <img src="<?php echo htmlspecialchars($article['image_url']); ?>"
                         alt="<?php echo htmlspecialchars($article['nom']); ?>"
                         class="article-image">
                </a>

                <h3 class="article-title"><?php echo htmlspecialchars($article['nom']); ?></h3>
                <?php if ($article['author']): ?>
                    <p class="article-author">
                        Par <a href="account.php?id=<?php echo $article['author_id']; ?>">
                            <?php echo htmlspecialchars($article['author']); ?>
                        </a>
                    </p>
                <?php endif; ?>
                <p class="article-price"><?php echo number_format($article['prix'], 2); ?> €</p>
                <p class="article-description"><?php echo htmlspecialchars($article['description']); ?></p>
            </div>

            <div class="article-actions">
                <?php if ($article['user_id'] == $_SESSION['user_id'] || $user_role === 'admin'): ?>
                    <div class="button-container">
                        <a href="product/edit.php?id=<?php echo $article['id']; ?>" class="add-to-cart-btn">
                            Modifier l'article
                        </a>
                    </div>
                <?php endif; ?>

                <div class="button-container">
                    <form action="add_to_cart.php" method="POST">
                        <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                        <button type="submit"
                                class="add-to-cart-btn"
                            <?php echo ($article['quantite'] <= 0) ? 'disabled' : ''; ?>>
                            <?php echo ($article['quantite'] > 0) ? 'Ajouter au panier' : 'Indisponible'; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<?php if ($result->num_rows === 0): ?>
    <p>Aucun article disponible pour le moment.</p>
<?php endif; ?>
</body>
</html>