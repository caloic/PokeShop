<?php
require_once 'config.php';
require_once 'auth_check.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupérer le contenu du panier
$cart_query = "
    SELECT 
        carts.article_id,
        carts.quantite as cart_quantite,
        articles.nom,
        articles.prix,
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

// Calculer le total
$total = 0;
$items = [];
while ($item = $cart_items->fetch_assoc()) {
    $total += $item['prix'] * $item['cart_quantite'];
    $items[] = $item;
}

// Vérifier le solde de l'utilisateur
$user_query = "SELECT solde FROM users WHERE id = ?";
$stmt = $mysqli->prepare($user_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user_result = $stmt->get_result();
$user_solde = $user_result->fetch_assoc()['solde'];

// Si le solde est insuffisant, rediriger vers le panier
if ($user_solde < $total) {
    $_SESSION['error'] = "Solde insuffisant pour procéder au paiement";
    header('Location: cart.php');
    exit();
}

// Traitement du formulaire de commande
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adresse = trim($_POST['adresse']);
    $ville = trim($_POST['ville']);
    $code_postal = trim($_POST['code_postal']);

    if (empty($adresse) || empty($ville) || empty($code_postal)) {
        $_SESSION['error'] = "Tous les champs sont obligatoires";
    } else {
        // Début de la transaction
        $mysqli->begin_transaction();

        try {
            // Créer la commande
            $create_order = "INSERT INTO commandes (user_id, montant_total, adresse, ville, code_postal) VALUES (?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($create_order);
            $stmt->bind_param("idsss", $_SESSION['user_id'], $total, $adresse, $ville, $code_postal);
            $stmt->execute();
            $commande_id = $mysqli->insert_id;

            // Ajouter les articles de la commande
            $insert_items = "INSERT INTO commande_articles (commande_id, article_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)";
            $update_stock = "UPDATE stocks SET quantite = quantite - ? WHERE article_id = ?";

            foreach ($items as $item) {
                // Vérifier le stock une dernière fois
                if ($item['cart_quantite'] > $item['stock_disponible']) {
                    throw new Exception("Stock insuffisant pour l'article " . $item['nom']);
                }

                // Ajouter l'article à la commande
                $stmt = $mysqli->prepare($insert_items);
                $stmt->bind_param("iiid", $commande_id, $item['article_id'], $item['cart_quantite'], $item['prix']);
                $stmt->execute();

                // Mettre à jour le stock
                $stmt = $mysqli->prepare($update_stock);
                $stmt->bind_param("ii", $item['cart_quantite'], $item['article_id']);
                $stmt->execute();
            }

            // Déduire le montant du solde utilisateur
            $update_solde = "UPDATE users SET solde = solde - ? WHERE id = ?";
            $stmt = $mysqli->prepare($update_solde);
            $stmt->bind_param("di", $total, $_SESSION['user_id']);
            $stmt->execute();

            // Vider le panier
            $clear_cart = "DELETE FROM carts WHERE user_id = ?";
            $stmt = $mysqli->prepare($clear_cart);
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();

            // Générer la facture
            $facture_content = "FACTURE\n\n";
            $facture_content .= "Date: " . date("Y-m-d H:i:s") . "\n";
            $facture_content .= "Commande #" . $commande_id . "\n\n";
            $facture_content .= "Articles:\n";

            foreach ($items as $item) {
                $facture_content .= sprintf(
                    "- %s x%d : %.2f €\n",
                    $item['nom'],
                    $item['cart_quantite'],
                    $item['prix'] * $item['cart_quantite']
                );
            }

            $facture_content .= "\nTotal: " . number_format($total, 2) . " €\n";
            $facture_content .= "\nAdresse de livraison:\n";
            $facture_content .= $adresse . "\n";
            $facture_content .= $code_postal . " " . $ville . "\n";

            // Créer le dossier factures s'il n'existe pas
            if (!file_exists('factures')) {
                mkdir('factures', 0777, true);
            }

            // Sauvegarder la facture
            $facture_file = "factures/facture_" . $commande_id . "_" . date("Y-m-d") . ".txt";
            file_put_contents($facture_file, $facture_content);

            // Valider la transaction
            $mysqli->commit();

            $_SESSION['success'] = "Commande validée avec succès !";
            $_SESSION['facture_path'] = $facture_file;
            header('Location: cart_validate.php?order_id=' . $commande_id);
            exit();

        } catch (Exception $e) {
            $mysqli->rollback();
            $_SESSION['error'] = "Erreur lors de la commande : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Finaliser la commande</title>
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

        .order-summary {
            margin-bottom: 30px;
        }

        .item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .total {
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 20px;
            text-align: right;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            background: #2ecc71;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background: #27ae60;
        }

        .error {
            color: #e74c3c;
            background: #fdf7f7;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .success {
            color: #2ecc71;
            background: #f7fdf7;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error">
            <?php
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <h1>Finaliser la commande</h1>

    <div class="order-summary">
        <h2>Résumé de la commande</h2>
        <?php foreach ($items as $item): ?>
            <div class="item">
                    <span>
                        <?php echo htmlspecialchars($item['nom']); ?>
                        (x<?php echo $item['cart_quantite']; ?>)
                    </span>
                <span>
                        <?php echo number_format($item['prix'] * $item['cart_quantite'], 2); ?> €
                    </span>
            </div>
        <?php endforeach; ?>

        <div class="total">
            Total : <?php echo number_format($total, 2); ?> €
        </div>
    </div>

    <form method="POST">
        <div class="form-group">
            <label for="adresse">Adresse de livraison</label>
            <input type="text" id="adresse" name="adresse" required>
        </div>

        <div class="form-group">
            <label for="ville">Ville</label>
            <input type="text" id="ville" name="ville" required>
        </div>

        <div class="form-group">
            <label for="code_postal">Code postal</label>
            <input type="text" id="code_postal" name="code_postal" required
                   pattern="[0-9]{5}" title="Le code postal doit contenir 5 chiffres">
        </div>

        <button type="submit">Valider la commande</button>
    </form>
</div>
</body>
</html>