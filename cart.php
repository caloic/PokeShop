<?php
require_once 'config.php';
require_once 'auth_check.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
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

        .cart-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }

        .item-details {
            flex: 1;
        }

        .item-price {
            font-weight: bold;
            color: #2ecc71;
        }

        .quantity-input {
            width: 60px;
            padding: 5px;
            margin: 0 10px;
        }

        .update-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }

        .update-btn:hover {
            background-color: #0056b3;
        }

        .total {
            text-align: right;
            padding: 20px;
            font-size: 1.2em;
            font-weight: bold;
        }

        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }

        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .error {
            background-color: #ffebee;
            color: #c62828;
        }

        .back-to-shop {
            text-decoration: none;
            color: #007bff;
            margin-right: 20px;
        }

        .checkout-btn {
            background-color: #2ecc71;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        .checkout-btn:hover {
            background-color: #27ae60;
        }

        .empty-cart {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .solde-info {
            font-size: 1.1em;
            color: #333;
            margin-bottom: 10px;
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
    <a href="index.php" class="back-to-shop">← Retour aux articles</a>
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
                <a href="checkout.php" class="checkout-btn">Procéder au paiement</a>
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
        <a href="index.php" class="update-btn">Retour à la boutique</a>
    </div>
<?php endif; ?>
</body>
</html>