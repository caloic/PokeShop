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

// Récupérer tous les articles, triés par date de publication décroissante
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
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .cart-info {
            display: flex;
            align-items: center;
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border-radius: 20px;
            text-decoration: none;
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
        }

        .article-card:hover {
            transform: translateY(-5px);
        }

        .article-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 10px;
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
        }

        .in-stock {
            background-color: #2ecc71;
            color: white;
        }

        .low-stock {
            background-color: #f1c40f;
            color: black;
        }

        .out-of-stock {
            background-color: #e74c3c;
            color: white;
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

        .add-to-cart-btn {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .add-to-cart-btn:hover {
            background-color: #0056b3;
        }

        .add-to-cart-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }

        .error {
            background-color: #ffebee;
            color: #c62828;
        }

        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
    </style>
</head>
<body>
<?php if (isset($_SESSION['error'])): ?>
    <div class="message error">
        <?php
        echo $_SESSION['error'];
        unset($_SESSION['error']);
        ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="message success">
        <?php
        echo $_SESSION['success'];
        unset($_SESSION['success']);
        ?>
    </div>
<?php endif; ?>

<div class="header">
    <div class="user-info">
        <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <a href="logout.php" class="logout-btn">Déconnexion</a>
    </div>
    <a href="cart.php" class="cart-info">
        Panier <span class="cart-count"><?php echo $cart_count; ?></span>
    </a>
</div>

<h1>Articles en vente</h1>

<!-- Dans la div articles-grid, modifiez la card article comme ceci: -->
<div class="articles-grid">
    <?php while ($article = $result->fetch_assoc()): ?>
        <div class="article-card">
            <a href="product.php?id=<?php echo $article['id']; ?>&slug=<?php echo $article['slug']; ?>" style="text-decoration: none; color: inherit;">
                <?php
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

                <span class="stock-status <?php echo $stockClass; ?>">
                    <?php echo $stockStatus; ?>
                </span>

                <img src="<?php echo htmlspecialchars($article['image_url']); ?>"
                     alt="<?php echo htmlspecialchars($article['nom']); ?>"
                     class="article-image">

                <h3><?php echo htmlspecialchars($article['nom']); ?></h3>
                <p class="article-price"><?php echo number_format($article['prix'], 2); ?> €</p>
                <p><?php echo htmlspecialchars(substr($article['description'], 0, 100)) . '...'; ?></p>
            </a>

            <form action="add_to_cart.php" method="POST">
                <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                <button type="submit"
                        class="add-to-cart-btn"
                    <?php echo ($article['quantite'] <= 0) ? 'disabled' : ''; ?>>
                    <?php echo ($article['quantite'] > 0) ? 'Ajouter au panier' : 'Indisponible'; ?>
                </button>
            </form>
        </div>
    <?php endwhile; ?>
</div>

<?php if ($result->num_rows === 0): ?>
    <p>Aucun article disponible pour le moment.</p>
<?php endif; ?>
</body>
</html>