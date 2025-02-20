<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../auth_check.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Gérer la mise à jour des quantités
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $article_id => $quantity) {
        if ($quantity <= 0) {
            // Supprimer l'article du panier
            $delete_query = "DELETE FROM carts WHERE user_id = ? AND article_id = ?";
            $stmt = $mysqli->prepare($delete_query);
            $stmt->bind_param("ii", $_SESSION['user_id'], $article_id);
            $stmt->execute();
        } else {
            // Vérifier le stock disponible
            $stock_query = "SELECT quantite FROM stocks WHERE article_id = ?";
            $stmt = $mysqli->prepare($stock_query);
            $stmt->bind_param("i", $article_id);
            $stmt->execute();
            $stock_result = $stmt->get_result();
            $stock = $stock_result->fetch_assoc()['quantite'];

            // Mettre à jour la quantité si le stock est suffisant
            if ($quantity <= $stock) {
                $update_query = "UPDATE carts SET quantite = ? WHERE user_id = ? AND article_id = ?";
                $stmt = $mysqli->prepare($update_query);
                $stmt->bind_param("iii", $quantity, $_SESSION['user_id'], $article_id);
                $stmt->execute();
            }
        }
    }
    $_SESSION['success'] = "Panier mis à jour avec succès";
    header('Location: cart.php');
    exit();
}

// Récupérer le contenu du panier
$cart_query = "
    SELECT 
        carts.article_id,
        carts.quantite as cart_quantite,
        articles.nom,
        articles.prix,
        articles.image_url,
        stocks.quantite as stock_disponible
    FROM carts
    JOIN articles ON carts.article_id = articles.id
    LEFT JOIN stocks ON articles.id = stocks.article_id
    WHERE carts.user_id = ?
";
$stmt = $mysqli->prepare($cart_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$cart_items = $stmt->get_result();

// Récupérer le solde de l'utilisateur
$user_query = "SELECT solde FROM users WHERE id = ?";
$stmt = $mysqli->prepare($user_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user_result = $stmt->get_result();
$user_solde = $user_result->fetch_assoc()['solde'];

// Calculer le total
$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mon Panier</title>
    <link rel="stylesheet" href="../styles/cart.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
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
    <a href="../index.php" class="back-to-shop">← Retour aux articles</a>
    <h1>Mon Panier</h1>
</div>

<?php if ($cart_items->num_rows > 0): ?>
    <form method="POST" class="cart-container">
        <?php while ($item = $cart_items->fetch_assoc()):
            $total += $item['prix'] * $item['cart_quantite'];
            ?>
            <div class="cart-item">
                <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                     alt="<?php echo htmlspecialchars($item['nom']); ?>">

                <div class="item-details">
                    <h3><?php echo htmlspecialchars($item['nom']); ?></h3>
                    <p class="item-price"><?php echo number_format($item['prix'], 2); ?> €</p>
                </div>

                <div>
                    <label>Quantité:</label>
                    <input type="number"
                           name="quantity[<?php echo $item['article_id']; ?>]"
                           value="<?php echo $item['cart_quantite']; ?>"
                           min="0"
                           max="<?php echo $item['stock_disponible']; ?>"
                           class="quantity-input">
                    <span>(Stock: <?php echo $item['stock_disponible']; ?>)</span>
                </div>
            </div>
        <?php endwhile; ?>

        <div class="total">
            Total: <?php echo number_format($total, 2); ?> €
        </div>

        <div style="text-align: right; padding: 20px;">
            <div class="solde-info">
                Votre solde: <?php echo number_format($user_solde, 2); ?> €
            </div>
            <button type="submit" name="update_cart" class="update-btn">Mettre à jour le panier</button>
            <?php if ($total <= $user_solde): ?>
                <a href="../checkout.php" class="checkout-btn">Procéder au paiement</a>
            <?php else: ?>
                <div class="message error" style="margin-top: 10px;">
                    Solde insuffisant pour procéder au paiement
                </div>
            <?php endif; ?>
        </div>
    </form>
<?php else: ?>
    <div class="cart-container empty-cart">
        <h2>Votre panier est vide</h2>
        <p>Retournez à la boutique pour ajouter des articles</p>
        <a href="../index.php" class="update-btn">Retour à la boutique</a>
    </div>
<?php endif; ?>
</body>
</html>