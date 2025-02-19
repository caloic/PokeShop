<?php
require_once 'config.php';
session_start();

// Vérifie si l'utilisateur est connecté (pour l'affichage conditionnel)
$is_logged_in = isset($_SESSION['user_id']);

// Si l'utilisateur est connecté, récupérer ses informations
if ($is_logged_in) {
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

    // Récupérer les articles en wishlist de l'utilisateur
    $wishlist_query = "SELECT article_id FROM wishlist WHERE user_id = ?";
    $wishlist_stmt = $mysqli->prepare($wishlist_query);
    $wishlist_stmt->bind_param("i", $_SESSION['user_id']);
    $wishlist_stmt->execute();
    $wishlist_result = $wishlist_stmt->get_result();
    $wishlist_articles = [];
    while ($row = $wishlist_result->fetch_assoc()) {
        $wishlist_articles[] = $row['article_id'];
    }
}

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
        <title>MonSite - Articles en vente</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                padding-top: 80px;
                background-color: #f5f5f5;
                line-height: 1.6;
            }

            /* Header Styles */
            .main-header {
                background-color: #4169E1;
                padding: 15px 30px;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 1000;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }

            .header-content {
                max-width: 1400px;
                margin: 0 auto;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 20px;
            }

            .logo {
                color: white;
                text-decoration: none;
                font-size: 24px;
                font-weight: bold;
                white-space: nowrap;
            }

            .search-bar {
                flex: 1;
                max-width: 600px;
                position: relative;
            }

            .search-input {
                width: 100%;
                padding: 12px 20px;
                border: none;
                border-radius: 25px;
                font-size: 16px;
                background-color: white;
                transition: box-shadow 0.3s;
            }

            .search-input:focus {
                outline: none;
                box-shadow: 0 0 0 2px rgba(255,255,255,0.5);
            }

            .search-button {
                position: absolute;
                right: 10px;
                top: 50%;
                transform: translateY(-50%);
                background: none;
                border: none;
                color: #4169E1;
                cursor: pointer;
                padding: 8px;
            }

            .header-actions {
                display: flex;
                align-items: center;
                gap: 20px;
            }

            .header-button {
                display: flex;
                align-items: center;
                gap: 8px;
                color: white;
                text-decoration: none;
                font-weight: 500;
                padding: 8px 16px;
                border-radius: 20px;
                transition: all 0.3s;
            }

            .header-button:hover {
                background-color: rgba(255, 255, 255, 0.1);
                transform: translateY(-1px);
            }

            .cart-button {
                background-color: white;
                color: #4169E1;
            }

            .cart-button:hover {
                background-color: #f0f0f0;
            }

            .cart-count {
                background-color: #ff4444;
                color: white;
                padding: 2px 6px;
                border-radius: 10px;
                font-size: 12px;
                font-weight: bold;
            }

            /* Main Content Styles */
            .main-container {
                max-width: 1400px;
                margin: 0 auto;
                padding: 20px;
            }

            .page-title {
                font-size: 28px;
                margin: 20px 0;
                color: #333;
            }

            .articles-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 25px;
                padding: 20px 0;
            }

            .article-card {
                background: white;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                transition: transform 0.3s, box-shadow 0.3s;
                display: flex;
                flex-direction: column;
                position: relative;
            }

            .article-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            }

            .article-image {
                width: 100%;
                height: 200px;
                object-fit: cover;
            }

            .article-content {
                padding: 20px;
                flex: 1;
                display: flex;
                flex-direction: column;
            }

            .article-title {
                font-size: 1.2em;
                margin: 0 0 10px 0;
                color: #333;
                text-decoration: none;
            }

            .article-author {
                font-size: 0.9em;
                color: #666;
                margin-bottom: 10px;
            }

            .article-price {
                font-size: 1.4em;
                font-weight: bold;
                color: #4169E1;
                margin: 10px 0;
            }

            .article-description {
                color: #666;
                margin-bottom: 15px;
                overflow: hidden;
                text-overflow: ellipsis;
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
            }

            .stock-status {
                position: absolute;
                top: 10px;
                right: 10px;
                padding: 5px 12px;
                border-radius: 20px;
                font-size: 0.8em;
                font-weight: 500;
                z-index: 1;
            }

            .in-stock { background-color: #4CAF50; color: white; }
            .low-stock { background-color: #FFC107; color: #333; }
            .out-of-stock { background-color: #F44336; color: white; }

            .article-actions {
                padding: 20px;
                background-color: #f8f9fa;
                border-top: 1px solid #eee;
            }

            .action-button {
                width: 100%;
                padding: 10px;
                border: none;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                text-align: center;
                text-decoration: none;
                transition: background-color 0.3s;
                display: inline-block;
                margin-bottom: 8px;
                background-color: #4169E1;
                color: white;
            }

            .action-button:last-child {
                margin-bottom: 0;
            }

            .action-button:hover {
                opacity: 0.9;
            }

            .action-button:disabled {
                background-color: #ccc;
                cursor: not-allowed;
            }

            .edit-button {
                background-color: #6c757d;
            }

            .hidden {
                display: none;
            }

            .no-articles {
                text-align: center;
                padding: 40px;
                background: white;
                border-radius: 8px;
                margin: 20px 0;
                grid-column: 1 / -1;
            }

            .no-articles h2 {
                color: #4169E1;
                margin-bottom: 10px;
            }

            .no-articles p {
                color: #666;
            }

            .profile-dropdown {
                position: relative;
            }

            .dropdown-menu {
                position: absolute;
                top: 100%;
                right: 0;
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                min-width: 200px;
                opacity: 0;
                visibility: hidden;
                transform: translateY(-10px);
                transition: all 0.3s ease;
                margin-top: 5px;
                z-index: 1000;
            }

            .profile-dropdown:hover .dropdown-menu {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
            }

            .dropdown-item {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 12px 16px;
                color: #333;
                text-decoration: none;
                transition: background-color 0.2s;
            }

            .dropdown-item:first-child {
                border-radius: 8px 8px 0 0;
            }

            .dropdown-item:last-child {
                border-radius: 0 0 8px 8px;
            }

            .dropdown-item:hover {
                background-color: #f8f9fa;
            }

            .auth-buttons {
                display: flex;
                gap: 10px;
            }

            .header-actions {
                display: flex;
                align-items: center;
                gap: 20px;
            }

            .create-button {
                background-color: #2ecc71;
                color: white;
                display: flex;
                align-items: center;
                gap: 8px;
                transition: background-color 0.3s;
            }

            .create-button:hover {
                background-color: #27ae60;
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .header-content {
                    flex-direction: column;
                    gap: 10px;
                }

                .search-bar {
                    width: 100%;
                    max-width: none;
                }

                .header-actions {
                    width: 100%;
                    justify-content: center;
                }

                .articles-grid {
                    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                }
            }

            .admin-actions {
                position: fixed;
                bottom: 20px;
                right: 20px;
                display: flex;
                gap: 10px;
                z-index: 100;
            }

            .admin-button {
                padding: 12px 24px;
                border-radius: 30px;
                font-weight: 500;
                text-decoration: none;
                color: white;
                background-color: #4169E1;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                transition: transform 0.3s, box-shadow 0.3s;
                display: flex;
                align-items: center;
            }

            .admin-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            }

            .create-button {
                background-color: #2ecc71;
            }

            .create-button:hover {
                background-color: #27ae60;
            }

            /* Styles pour le bouton de favoris */
            .favorite-button {
                position: absolute;
                top: 10px;
                left: 10px;
                background: none;
                border: none;
                cursor: pointer;
                z-index: 10;
                transition: transform 0.2s;
                background-color: rgba(255,255,255,0.7);
                border-radius: 50%;
                padding: 5px;
            }

            .favorite-button:hover {
                transform: scale(1.1);
            }

            .favorite-button svg {
                width: 24px;
                height: 24px;
                stroke: #ff4444;
                fill: none;
                transition: fill 0.2s, stroke 0.2s;
            }

            .favorite-button.active svg {
                fill: #ff4444;
                stroke: #ff4444;
            }

            .sort-container {
                margin-bottom: 20px;
                display: flex;
                justify-content: flex-end;
            }

            .sort-select {
                padding: 8px 12px;
                border: 1px solid #ddd;
                border-radius: 4px;
                background-color: white;
                font-size: 14px;
                cursor: pointer;
                min-width: 200px;
            }

            .sort-select:focus {
                outline: none;
                border-color: #4169E1;
                box-shadow: 0 0 0 2px rgba(65,105,225,0.1);
            }
        </style>
    </head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-content">
            <a href="index.php" class="logo">MonSite</a>

            <form class="search-bar" onsubmit="return false;">
                <input type="text"
                       id="search-input"
                       class="search-input"
                       placeholder="Qu'est-ce qui vous ferait plaisir ?"
                       autocomplete="off">
                <button type="button" class="search-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
            </form>

            <div class="header-actions">
                <?php if ($is_logged_in): ?>
                    <div class="profile-dropdown">
                        <a href="#" class="header-button" id="profile-button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <div class="dropdown-menu">
                            <a href="account.php" class="dropdown-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                Mon compte
                            </a>
                            <a href="wishlist.php" class="dropdown-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                Ma wishlist
                            </a>
                            <?php if ($user_role === 'admin'): ?>
                                <a href="admin/index.php" class="dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v16m8-8H4"></path></svg>
                                    Administration
                                </a>
                            <?php endif; ?>
                            <a href="logout.php" class="dropdown-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                Déconnexion
                            </a>
                        </div>
                    </div>
                    <?php if ($is_logged_in): ?>
                        <div class="admin-actions">
                            <a href="product/create.php" class="admin-button create-button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                Créer un article
                            </a>
                        </div>
                    <?php endif; ?>
                    <a href="cart/cart.php" class="header-button cart-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="login.php" class="header-button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                            Connexion / Inscription
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Content -->
<main class="main-container">
    <h1 class="page-title">Articles en vente</h1>

    <div class="sort-container">
        <select id="sort-select" class="sort-select">
            <option value="date_desc">Plus récents</option>
            <option value="date_asc">Plus anciens</option>
            <option value="price_asc">Prix croissant</option>
            <option value="price_desc">Prix décroissant</option>
            <option value="name_asc">Nom A-Z</option>
            <option value="name_desc">Nom Z-A</option>
        </select>
    </div>

    <div class="articles-grid">
<?php while ($article = $result->fetch_assoc()): ?>
    <?php
    // Calculer la wishlist AVANT le bloc de statut du stock
    $is_in_wishlist = $is_logged_in && in_array($article['id'], $wishlist_articles);

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
    <div class="article-card" data-date="<?php echo $article['date_publication']; ?>">
    <?php if ($is_logged_in): ?>
        <button class="favorite-button <?php echo $is_in_wishlist ? 'active' : ''; ?>"
                data-article-id="<?php echo $article['id']; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
        </button>
    <?php endif; ?>

    <span class="stock-status <?php echo $stockClass; ?>">
            <?php echo $stockStatus; ?>
        </span>

    <a href="product.php?id=<?php echo $article['id']; ?>&slug=<?php echo $article['slug']; ?>">
        <img src="<?php echo htmlspecialchars($article['image_url']); ?>"
             alt="<?php echo htmlspecialchars($article['nom']); ?>"
             class="article-image">
    </a>

    <div class="article-content">
        <h3 class="article-title">
            <a href="product.php?id=<?php echo $article['id']; ?>&slug=<?php echo $article['slug']; ?>" class="article-title">
                <?php echo htmlspecialchars($article['nom']); ?>
            </a>
        </h3>

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
    <?php if ($is_logged_in): ?>
        <?php if ($article['user_id'] == $_SESSION['user_id'] || $user_role === 'admin'): ?>
            <a href="product/edit.php?id=<?php echo $article['id']; ?>" class="action-button edit-button">
                Modifier l'article
            </a>
        <?php endif; ?>

        <form action="add_to_cart.php" method="POST">
        <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
    <button type="submit"
            class="action-button"
        <?php echo ($article['quantite'] <= 0) ? 'disabled' : ''; ?>>
        <?php echo ($article['quantite'] > 0) ? 'Ajouter au panier' : 'Indisponible'; ?>
    </button>
        </form>
    <?php else: ?>
        <a href="login.php" class="action-button">Se connecter pour acheter</a>
    <?php endif; ?>
    </div>
    </div>
<?php endwhile; ?>
    </div>

    <?php if ($result->num_rows === 0): ?>
        <div class="no-articles">
            <h2>Aucun article disponible</h2>
            <p>Revenez plus tard pour voir les nouveaux articles.</p>
        </div>
    <?php endif; ?>
</main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Éléments de recherche
            const searchInput = document.getElementById('search-input');
            const articles = document.querySelectorAll('.article-card');
            const articlesGrid = document.querySelector('.articles-grid');

            // Fonction de recherche
            function filterArticles() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                let hasResults = false;

                articles.forEach(article => {
                    const title = article.querySelector('.article-title').textContent.toLowerCase();

                    if (title.includes(searchTerm)) {
                        article.classList.remove('hidden');
                        hasResults = true;
                    } else {
                        article.classList.add('hidden');
                    }
                });

                // Gestion du message "Aucun résultat"
                let noResultsMessage = document.querySelector('.no-articles');
                if (!hasResults) {
                    if (!noResultsMessage) {
                        noResultsMessage = document.createElement('div');
                        noResultsMessage.className = 'no-articles';
                        noResultsMessage.innerHTML = `
                    <h2>Aucun résultat trouvé</h2>
                    <p>Essayez avec d'autres mots-clés</p>
                `;
                        articlesGrid.appendChild(noResultsMessage);
                    }
                } else if (noResultsMessage) {
                    noResultsMessage.remove();
                }

                // Réappliquer le tri actuel après la recherche
                const currentSort = document.getElementById('sort-select').value;
                sortArticles(currentSort);
            }

            // Fonction de tri
            function sortArticles(sortType) {
                const articlesArray = Array.from(articles).filter(article => !article.classList.contains('hidden'));

                articlesArray.sort((a, b) => {
                    switch(sortType) {
                        case 'price_asc':
                            return getPriceFromArticle(a) - getPriceFromArticle(b);
                        case 'price_desc':
                            return getPriceFromArticle(b) - getPriceFromArticle(a);
                        case 'date_asc':
                            return new Date(getDateFromArticle(a)) - new Date(getDateFromArticle(b));
                        case 'date_desc':
                            return new Date(getDateFromArticle(b)) - new Date(getDateFromArticle(a));
                        case 'name_asc':
                            return getNameFromArticle(a).localeCompare(getNameFromArticle(b));
                        case 'name_desc':
                            return getNameFromArticle(b).localeCompare(getNameFromArticle(a));
                        default:
                            return 0;
                    }
                });

                // Vider et reremplir la grille avec les articles triés
                articlesGrid.innerHTML = '';
                articlesArray.forEach(article => {
                    articlesGrid.appendChild(article);
                });
            }

            // Fonctions utilitaires pour extraire les valeurs
            function getPriceFromArticle(article) {
                const priceText = article.querySelector('.article-price').textContent;
                // Enlever d'abord le symbole € et les espaces
                const withoutCurrency = priceText.replace('€', '').trim();
                // Enlever toutes les virgules (séparateurs de milliers) et convertir en nombre
                const cleanPrice = withoutCurrency.replace(/,/g, '');
                return parseFloat(cleanPrice);
            }

            function getNameFromArticle(article) {
                return article.querySelector('.article-title').textContent.trim();
            }

            function getDateFromArticle(article) {
                return article.dataset.date || new Date().toISOString();
            }

            // Écouteurs d'événements
            searchInput.addEventListener('input', filterArticles);

            const sortSelect = document.getElementById('sort-select');
            sortSelect.addEventListener('change', function() {
                sortArticles(this.value);
            });

            // Gestion de la wishlist
            const favoriteButtons = document.querySelectorAll('.favorite-button');

            favoriteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const articleId = this.dataset.articleId;
                    const isCurrentlyFavorite = this.classList.contains('active');

                    fetch('toggle_wishlist.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `article_id=${articleId}&action=${isCurrentlyFavorite ? 'remove' : 'add'}`
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.classList.toggle('active');
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

            // Tri initial
            sortArticles('date_desc');
        });
    </script>
</body>
</html>