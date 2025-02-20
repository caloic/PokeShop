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
    WHERE articles.id = ? AND articles.slug = ? AND articles.is_deleted = FALSE
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
    <link rel="stylesheet" href="styles/product_details.css">
    <link rel="icon" type="image/png" href="images/favicon.png">
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
                <?php if ($is_logged_in && ($article['user_id'] == $_SESSION['user_id'])): ?>
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