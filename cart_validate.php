<?php
require_once 'config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Vérifier si un order_id est fourni
if (!isset($_GET['order_id'])) {
    header('Location: index.php');
    exit();
}

$order_id = (int)$_GET['order_id'];

// Récupérer les détails de la commande
$order_query = "
    SELECT c.*, u.username, u.email
    FROM commandes c
    JOIN users u ON c.user_id = u.id
    WHERE c.id = ? AND c.user_id = ?
";
$stmt = $mysqli->prepare($order_query);
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: index.php');
    exit();
}

// Récupérer les articles de la commande
$items_query = "
    SELECT ca.*, a.nom
    FROM commande_articles ca
    JOIN articles a ON ca.article_id = a.id
    WHERE ca.commande_id = ?
";
$stmt = $mysqli->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Confirmation de commande</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .order-details {
            margin-top: 20px;
        }

        .order-items {
            margin-top: 20px;
        }

        .item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .download-btn {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }

        .back-btn {
            display: inline-block;
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="success-message">
        <h2>🎉 Commande confirmée !</h2>
        <p>Votre commande #<?php echo $order_id; ?> a été validée avec succès.</p>
    </div>

    <div class="order-details">
        <h3>Détails de la commande</h3>
        <p><strong>Date :</strong> <?php echo date('d/m/Y H:i', strtotime($order['date_transaction'])); ?></p>
        <p><strong>Montant total :</strong> <?php echo number_format($order['montant_total'], 2); ?> €</p>
        <p><strong>Adresse de livraison :</strong><br>
            <?php echo htmlspecialchars($order['adresse']); ?><br>
            <?php echo htmlspecialchars($order['code_postal'] . ' ' . $order['ville']); ?>
        </p>
    </div>

    <div class="order-items">
        <h3>Articles commandés</h3>
        <?php while ($item = $items->fetch_assoc()): ?>
            <div class="item">
                <span><?php echo htmlspecialchars($item['nom']); ?> (x<?php echo $item['quantite']; ?>)</span>
                <span><?php echo number_format($item['prix_unitaire'] * $item['quantite'], 2); ?> €</span>
            </div>
        <?php endwhile; ?>
    </div>

    <?php if (isset($_SESSION['facture_path'])): ?>
        <div style="margin-top: 30px">
            <a href="<?php echo htmlspecialchars($_SESSION['facture_path']); ?>" class="download-btn" download>
                Télécharger la facture
            </a>
            <?php unset($_SESSION['facture_path']); ?>
        </div>
    <?php endif; ?>

    <div style="margin-top: 30px">
        <a href="index.php" class="back-btn">Retour à la boutique</a>
    </div>
</div>
</body>
</html>