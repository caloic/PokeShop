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

// Vérifier si l'utilisateur a déjà acheté l'article
$has_purchased = false;
$existing_rating = null;
if ($is_logged_in) {
    $check_purchase_query = "
        SELECT COUNT(*) as has_purchased 
        FROM commande_articles ca
        JOIN commandes c ON ca.commande_id = c.id
        WHERE c.user_id = ? AND ca.article_id = ?
    ";
    $stmt = $mysqli->prepare($check_purchase_query);
    $stmt->bind_param("ii", $_SESSION['user_id'], $article_id);
    $stmt->execute();
    $purchase_result = $stmt->get_result()->fetch_assoc();
    $has_purchased = $purchase_result['has_purchased'] > 0;

    // Récupérer la note existante de l'utilisateur si elle existe
    if ($has_purchased) {
        $rating_query = "SELECT * FROM notes_articles WHERE user_id = ? AND article_id = ?";
        $stmt = $mysqli->prepare($rating_query);
        $stmt->bind_param("ii", $_SESSION['user_id'], $article_id);
        $stmt->execute();
        $existing_rating = $stmt->get_result()->fetch_assoc();
    }
}

// Traitement de la notation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rating'])) {
    if (!$is_logged_in) {
        $_SESSION['error'] = "Vous devez être connecté pour noter un article";
        header("Location: product.php?id=$article_id&slug=$slug");
        exit();
    }

    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    if (!$has_purchased) {
        $_SESSION['error'] = "Vous devez avoir acheté l'article pour le noter";
    } elseif ($rating < 1 || $rating > 5) {
        $_SESSION['error'] = "Note invalide";
    } else {
        try {
            if ($existing_rating) {
                // Mettre à jour la note existante
                $update_query = "UPDATE notes_articles SET note = ?, commentaire = ? WHERE user_id = ? AND article_id = ?";
                $stmt = $mysqli->prepare($update_query);
                $stmt->bind_param("isii", $rating, $comment, $_SESSION['user_id'], $article_id);
            } else {
                // Insérer une nouvelle note
                $insert_query = "INSERT INTO notes_articles (user_id, article_id, note, commentaire) VALUES (?, ?, ?, ?)";
                $stmt = $mysqli->prepare($insert_query);
                $stmt->bind_param("iiis", $_SESSION['user_id'], $article_id, $rating, $comment);
            }

            $stmt->execute();
            $_SESSION['success'] = "Votre note a été enregistrée";
            header("Location: product.php?id=$article_id&slug=$slug");
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de l'enregistrement de la note";
        }
    }
}

// Récupérer toutes les notes pour cet article
$reviews_query = "
    SELECT na.*, u.username 
    FROM notes_articles na
    JOIN users u ON na.user_id = u.id
    WHERE na.article_id = ?
    ORDER BY na.date_creation DESC
";
$stmt = $mysqli->prepare($reviews_query);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$reviews_result = $stmt->get_result();

// Calculer la note moyenne
$average_rating_query = "SELECT AVG(note) as moyenne, COUNT(*) as total_notes FROM notes_articles WHERE article_id = ?";
$stmt = $mysqli->prepare($average_rating_query);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$average_rating_result = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($article['nom']); ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/product_details.css">
    <link rel="stylesheet" href="styles/footer.css">
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
                    Par <a href="account.php?username=<?php echo urlencode($article['author']); ?>">
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

    <!-- Section de notation -->
    <?php if ($has_purchased && $is_logged_in): ?>
        <div class="rating-section">
            <h3>Noter cet article</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Note :</label>
                    <select name="rating" required>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?php echo $i; ?>"
                                <?php echo ($existing_rating && $existing_rating['note'] == $i) ? 'selected' : ''; ?>>
                                <?php echo $i; ?> / 5
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Commentaire :</label>
                    <textarea name="comment" rows="4"><?php echo $existing_rating ? htmlspecialchars($existing_rating['commentaire']) : ''; ?></textarea>
                </div>
                <button type="submit" name="submit_rating">Enregistrer ma note</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Section des reviews -->
    <div class="reviews-section">
        <h3>Avis et commentaires</h3>
        <?php if ($average_rating_result['total_notes'] > 0): ?>
            <div class="average-rating">
                Note moyenne : <?php echo number_format($average_rating_result['moyenne'], 1); ?> / 5
                (<?php echo $average_rating_result['total_notes']; ?> note<?php echo $average_rating_result['total_notes'] > 1 ? 's' : ''; ?>)
            </div>
        <?php endif; ?>

        <?php while ($review = $reviews_result->fetch_assoc()): ?>
            <div class="review-item">
                <div class="review-header">
                    <span class="username"><?php echo htmlspecialchars($review['username']); ?></span>
                    <span class="rating-value"><?php echo $review['note']; ?> / 5</span>
                    <span class="date"><?php echo date('d/m/Y', strtotime($review['date_creation'])); ?></span>
                </div>
                <?php if (!empty($review['commentaire'])): ?>
                    <div class="review-comment">
                        <?php echo htmlspecialchars($review['commentaire']); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>