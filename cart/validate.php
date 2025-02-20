<?php
require_once '../config.php';
require_once '../auth_check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Vérifier si l'ID de commande est fourni
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    header('Location: ../account.php');
    exit();
}

$order_id = (int)$_GET['order_id'];

// Récupérer les détails de la commande et de la facture
$query = "
    SELECT 
        c.*,
        f.id as facture_id,
        f.nom_fichier,
        GROUP_CONCAT(CONCAT(ca.quantite, 'x ', ca.article_name) SEPARATOR ', ') as articles
    FROM commandes c
    LEFT JOIN factures f ON c.id = f.commande_id
    JOIN commande_articles ca ON c.id = ca.commande_id
    WHERE c.id = ? AND c.user_id = ?
    GROUP BY c.id, f.id, f.nom_fichier
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ../account.php');
    exit();
}

$commande = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Commande validée</title>
    <link rel="stylesheet" href="../styles/cart.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
</head>
<body>
<div class="container">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success-message">
            <?php
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <h1>Commande validée avec succès !</h1>

    <div class="order-details">
        <h2>Détails de la commande #<?php echo $commande['id']; ?></h2>
        <p><strong>Date :</strong> <?php echo date('d/m/Y H:i', strtotime($commande['date_transaction'])); ?></p>
        <p><strong>Montant total :</strong> <?php echo number_format($commande['montant_total'], 2); ?> €</p>
        <p><strong>Articles :</strong> <?php echo htmlspecialchars($commande['articles']); ?></p>
        <p><strong>Adresse de livraison :</strong><br>
            <?php echo htmlspecialchars($commande['adresse']); ?><br>
            <?php echo htmlspecialchars($commande['code_postal'] . ' ' . $commande['ville']); ?>
        </p>
    </div>

    <div class="download-section">
        <?php if ($commande['facture_id']): ?>
            <a href="../toggle_order.php?id=<?php echo $commande['facture_id']; ?>" class="btn">
                Télécharger la facture
            </a>
        <?php endif; ?>

        <a href="../account.php" class="btn btn-back">Retour à mon compte</a>
    </div>
</div>
</body>
</html>