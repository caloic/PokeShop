<?php
require_once 'config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupérer l'ID et le slug de l'article
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

// Récupérer les détails de l'article
$query = "
    SELECT articles.*, stocks.quantite 
    FROM articles 
    LEFT JOIN stocks ON articles.id = stocks.article_id 
    WHERE articles.id = ? AND articles.slug = ?
";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("is", $article_id, $slug);
$stmt->execute();
$result = $stmt->get_result();
$article = $result->fetch_assoc();

// Rediriger si l'article n'existe pas
if (!$article) {
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
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($article['nom']); ?></title>
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

        .product-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .product-image {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .product-info {
            padding: 20px;
        }

        .product-price {
            font-size: 24px;
            color: #2ecc71;
            font-weight: bold;
            margin: 20px 0;
        }

        .stock-status {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            margin: 10px 0;
        }

        .in-stock { background-color: #2ecc71; color: white; }
        .low-stock { background-color: #f1c40f; color: black; }
        .out-of-stock { background-color: #e74c3c; color: white; }

        .add-to-cart-form {
            margin-top: 20px;
        }

        .quantity-input {
            width: 60px;
            padding: 8px;
            margin-right: 10px;
        }

        .add-to-cart-btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .add-to-cart-btn:hover {
            background-color: #0056b3;
        }

        .add-to-cart-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
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

        .back-link {
            color: #007bff;
            text-decoration: none;
            margin-right: 20px;
        }
    </style>
</head>
<body>
<div class="header">
    <a href="index.php" class="back-link">← Retour aux articles</a>
    <a href="cart.php" class="cart-info">
        Panier <span class="cart-count"><?php echo $cart_count; ?></span>
    </a>
</div>

<div class="product-container">
    <div class="product-image-container">
        <img src="<?php echo htmlspecialchars($article['image_url']); ?>"
             alt="<?php echo htmlspecialchars($article['nom']); ?>"
             class="product-image">
    </div>

    <div class="product-info">
        <h1><?php echo htmlspecialchars($article['nom']); ?></h1>

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
            $stockStatus = 'Rupture de stock';
            $stockClass = 'out-of-stock';
        }
        ?>

        <div class="stock-status <?php echo $stockClass; ?>">
            <?php echo $stockStatus; ?>
        </div>

        <div class="product-price">
            <?php echo number_format($article['prix'], 2); ?> €
        </div>

        <p class="product-description">
            <?php echo nl2br(htmlspecialchars($article['description'])); ?>
        </p>

        <?php if ($article['quantite'] > 0): ?>
            <form action="add_to_cart.php" method="POST" class="add-to-cart-form">
                <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                <input type="number"
                       name="quantity"
                       value="1"
                       min="1"
                       max="<?php echo $article['quantite']; ?>"
                       class="quantity-input">
                <button type="submit" class="add-to-cart-btn">
                    Ajouter au panier
                </button>
            </form>
        <?php else: ?>
            <button class="add-to-cart-btn" disabled>
                Indisponible
            </button>
        <?php endif; ?>
    </div>
</div>
</body>
</html>