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
    <title>Ma Wishlist - MonSite</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .wishlist-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .wishlist-title {
            font-size: 2em;
            color: #4169E1;
            font-weight: 700;
        }

        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .wishlist-item {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            cursor: pointer;
        }

        .wishlist-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }

        .wishlist-item-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .wishlist-item-content {
            padding: 15px;
        }

        .wishlist-item-title {
            font-size: 1.2em;
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .wishlist-item-price {
            font-size: 1.4em;
            color: #4169E1;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .wishlist-item-description {
            color: #666;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .wishlist-item-date {
            color: #888;
            font-size: 0.8em;
            margin-bottom: 10px;
        }

        .btn-remove {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255,255,255,0.8);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
            z-index: 10;
        }

        .btn-remove:hover {
            background: rgba(255,100,100,0.2);
        }

        .btn-remove svg {
            color: #ff4444;
            width: 20px;
            height: 20px;
        }

        .wishlist-item-actions {
            display: flex;
            gap: 10px;
            padding: 15px;
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }

        .btn-cart {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 6px;
            background-color: #2ecc71;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-cart:hover {
            background-color: #27ae60;
        }

        .btn-cart:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .no-wishlist {
            text-align: center;
            background-color: white;
            padding: 50px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            grid-column: 1 / -1;
        }

        .no-wishlist-title {
            color: #4169E1;
            margin-bottom: 15px;
            font-size: 1.5em;
        }

        .no-wishlist-description {
            color: #666;
        }

        /* Header Styles (reste inchangé) */
        .main-header {
            background-color: #4169E1;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: white;
            text-decoration: none;
            font-size: 24px;
            font-weight: bold;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-button {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cart-button {
            position: relative;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #ff4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
    </style>
</head>
<body>
<header class="main-header">
    <a href="index.php" class="logo">MonSite</a>
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