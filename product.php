<?php
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté (pour l'affichage conditionnel)
$is_logged_in = isset($_SESSION['user_id']);
$user_role = null;

if ($is_logged_in) {
    // Récupérer le rôle de l'utilisateur
    $role_query = "SELECT role FROM users WHERE id = ?";
    $role_stmt = $mysqli->prepare($role_query);
    $role_stmt->bind_param("i", $_SESSION['user_id']);
    $role_stmt->execute();
    $user_role = $role_stmt->get_result()->fetch_assoc()['role'];

    // Récupérer le nombre d'articles dans le panier
    $cart_query = "SELECT SUM(quantite) as total_items FROM carts WHERE user_id = ?";
    $cart_stmt = $mysqli->prepare($cart_query);
    $cart_stmt->bind_param("i", $_SESSION['user_id']);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();
    $cart_count = $cart_result->fetch_assoc()['total_items'] ?? 0;
}

// Récupérer l'ID et le slug de l'article
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

// Récupérer les détails de l'article
$query = "
    SELECT articles.*, stocks.quantite, users.username as author, users.id as author_id 
    FROM articles 
    LEFT JOIN stocks ON articles.id = stocks.article_id 
    LEFT JOIN users ON articles.user_id = users.id
    WHERE articles.id = ? AND articles.slug = ?
";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("is", $article_id, $slug);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();

// Rediriger si l'article n'existe pas
if (!$article) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($article['nom']); ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .product-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .product-image-container {
            position: relative;
        }

        .product-image {
            width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .product-info {
            display: flex;
            flex-direction: column;
        }

        .product-title {
            font-size: 2em;
            margin: 0 0 10px 0;
            color: #333;
        }

        .product-meta {
            margin-bottom: 20px;
            color: #666;
            font-size: 0.95em;
        }

        .product-author {
            display: inline;
        }

        .product-author a {
            color: #4169E1;
            text-decoration: none;
            font-weight: 500;
        }

        .product-author a:hover {
            text-decoration: underline;
        }

        .product-date {
            display: inline;
            margin-left: 5px;
            color: #666;
        }

        .product-price {
            font-size: 2em;
            font-weight: bold;
            color: #4169E1;
            margin: 20px 0;
        }

        .product-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
            white-space: pre-line;
        }

        .stock-status {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .in-stock { background-color: #e8f5e9; color: #2e7d32; }
        .low-stock { background-color: #fff3e0; color: #f57c00; }
        .out-of-stock { background-color: #ffebee; color: #c62828; }

        .action-button {
            display: inline-block;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            border: none;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s;
            color: white;
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .edit-button {
            background-color: #6c757d;
        }

        .add-cart-button {
            background-color: #4169E1;
        }

        .action-button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: #4169E1;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .back-link svg {
            margin-right: 8px;
            transition: transform 0.3s;
        }

        .back-link:hover svg {
            transform: translateX(-4px);
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .product-container {
                grid-template-columns: 1fr;
                padding: 20px;
            }

            .product-image {
                margin-bottom: 20px;
            }

            .product-title {
                font-size: 1.5em;
            }

            .product-price {
                font-size: 1.5em;
            }

            .action-button {
                width: 100%;
                margin-right: 0;
                text-align: center;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <a href="index.php" class="back-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        Retour aux articles
    </a>

    <div class="product-container">
        <div class="product-image-container">
            <img src="<?php echo htmlspecialchars($article['image_url']); ?>"
                 alt="<?php echo htmlspecialchars($article['nom']); ?>"
                 class="product-image">
        </div>

        <div class="product-info">
            <h1 class="product-title"><?php echo htmlspecialchars($article['nom']); ?></h1>

            <div class="product-meta">
                <p class="product-author">
                    Par <a href="account.php?id=<?php echo $article['author_id']; ?>">
                        <?php echo htmlspecialchars($article['author']); ?>
                    </a>
                </p>
                <p class="product-date">
                    • Publié le <?php echo date('d/m/Y', strtotime($article['date_publication'])); ?>
                </p>
            </div>

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

            <span class="stock-status <?php echo $stockClass; ?>">
                    <?php echo $stockStatus; ?>
                    <?php if ($article['quantite'] > 0) echo ' (' . $article['quantite'] . ' disponibles)'; ?>
                </span>

            <div class="product-price"><?php echo number_format($article['prix'], 2); ?> €</div>

            <p class="product-description"><?php echo htmlspecialchars($article['description']); ?></p>

            <div class="product-actions">
                <?php if ($is_logged_in && ($article['user_id'] == $_SESSION['user_id'] || $user_role === 'admin')): ?>
                    <a href="product/edit.php?id=<?php echo $article['id']; ?>" class="action-button edit-button">
                        Modifier l'article
                    </a>
                <?php endif; ?>

                <?php if ($is_logged_in): ?>
                    <form action="add_to_cart.php" method="POST" style="display: inline;">
                        <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                        <button type="submit"
                                class="action-button add-cart-button"
                            <?php echo ($article['quantite'] <= 0) ? 'disabled' : ''; ?>>
                            <?php echo ($article['quantite'] > 0) ? 'Ajouter au panier' : 'Indisponible'; ?>
                        </button>
                    </form>
                <?php else: ?>
                    <a href="login.php" class="action-button add-cart-button">Se connecter pour acheter</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>