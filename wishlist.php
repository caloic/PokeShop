<?php
require_once 'config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupérer le nombre d'articles dans le panier
$cart_query = "SELECT SUM(quantite) as total_items FROM carts WHERE user_id = ?";
$cart_stmt = $mysqli->prepare($cart_query);
$cart_stmt->bind_param("i", $_SESSION['user_id']);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();
$cart_count = $cart_result->fetch_assoc()['total_items'] ?? 0;

// Récupérer les articles en wishlist
$query = "
    SELECT articles.*, stocks.quantite, users.username as author, users.id as author_id, 
           wishlist.date_ajout as date_favori
    FROM wishlist 
    JOIN articles ON wishlist.article_id = articles.id
    LEFT JOIN stocks ON articles.id = stocks.article_id 
    LEFT JOIN users ON articles.user_id = users.id
    WHERE wishlist.user_id = ?
    ORDER BY wishlist.date_ajout DESC
";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ma Wishlist - PokéShop</title>
    <link rel="stylesheet" href="styles/wishlist.css">
    <link rel="icon" type="image/png" href="images/favicon.png">
</head>
<body>
<header class="main-header">
    <a href="index.php" class="logo">
        <img src="images/logo.png" alt="Logo PokéShop" class="logo-image">
    </a>
    <div class="header-actions">
        <a href="account.php" class="header-button">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <?php echo htmlspecialchars($_SESSION['username']); ?>
        </a>
        <a href="cart/cart.php" class="header-button cart-button">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <?php if ($cart_count > 0): ?>
                <span class="cart-count"><?php echo $cart_count; ?></span>
            <?php endif; ?>
        </a>
    </div>
</header>

<div class="container">
    <div class="wishlist-header">
        <h1 class="wishlist-title">Ma Wishlist</h1>
    </div>

    <div class="wishlist-grid">
        <?php while ($article = $result->fetch_assoc()): ?>
            <div class="wishlist-item" data-article-id="<?php echo $article['id']; ?>" data-article-slug="<?php echo $article['slug']; ?>">
                <button class="btn-remove remove-from-wishlist">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <img src="<?php echo htmlspecialchars($article['image_url']); ?>"
                     alt="<?php echo htmlspecialchars($article['nom']); ?>"
                     class="wishlist-item-image">

                <div class="wishlist-item-content">
                    <h2 class="wishlist-item-title"><?php echo htmlspecialchars($article['nom']); ?></h2>
                    <p class="wishlist-item-price"><?php echo number_format($article['prix'], 2); ?> €</p>
                    <p class="wishlist-item-description"><?php echo htmlspecialchars($article['description']); ?></p>
                    <p class="wishlist-item-date">
                        Ajouté le <?php echo date('d/m/Y', strtotime($article['date_favori'])); ?>
                    </p>
                </div>

                <div class="wishlist-item-actions">
                    <form action="add_to_cart.php" method="POST" style="width: 100%;">
                        <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                        <button type="submit"
                                class="btn-cart"
                            <?php echo ($article['quantite'] <= 0) ? 'disabled' : ''; ?>>
                            <?php echo ($article['quantite'] > 0) ? 'Ajouter au panier' : 'Indisponible'; ?>
                        </button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>

        <?php if ($result->num_rows === 0): ?>
            <div class="no-wishlist">
                <h2 class="no-wishlist-title">Votre wishlist est vide</h2>
                <p class="no-wishlist-description">
                    Explorez nos articles et ajoutez vos favoris !
                </p>
                <a href="index.php" class="btn-cart" style="display: inline-block; margin-top: 15px;">
                    Parcourir les articles
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion de la suppression des articles de la wishlist
        const removeButtons = document.querySelectorAll('.remove-from-wishlist');

        removeButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                // Empêcher la propagation pour ne pas déclencher le clic sur l'article
                event.stopPropagation();

                const wishlistItem = this.closest('.wishlist-item');
                const articleId = wishlistItem.dataset.articleId;

                fetch('toggle_wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `article_id=${articleId}&action=remove`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Supprimer l'article de la vue
                            wishlistItem.remove();

                            // Vérifier s'il reste des articles
                            const wishlistGrid = document.querySelector('.wishlist-grid');
                            const remainingItems = wishlistGrid.querySelectorAll('.wishlist-item').length;

                            if (remainingItems === 0) {
                                wishlistGrid.innerHTML = `
                                    <div class="no-wishlist">
                                        <h2 class="no-wishlist-title">Votre wishlist est vide</h2>
                                        <p class="no-wishlist-description">
                                            Explorez nos articles et ajoutez vos favoris !
                                        </p>
                                        <a href="index.php" class="btn-cart" style="display: inline-block; margin-top: 15px;">
                                            Parcourir les articles
                                        </a>
                                    </div>
                                `;
                            }
                        } else {
                            alert('Erreur : ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Une erreur est survenue');
                    });
            });
        });

        // Redirection vers la page de détail de l'article
        const wishlistItems = document.querySelectorAll('.wishlist-item');
        wishlistItems.forEach(item => {
            item.addEventListener('click', function() {
                const articleId = this.dataset.articleId;
                const articleSlug = this.dataset.articleSlug;
                window.location.href = `product.php?id=${articleId}&slug=${articleSlug}`;
            });
        });
    });
</script>
</body>
</html>