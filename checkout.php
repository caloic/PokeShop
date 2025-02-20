<?php
require_once 'config.php';
require_once 'auth_check.php';
require_once 'vendor/autoload.php'; // Chemin vers l'autoload de Composer

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

            // Créer un nouvel objet PDF
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // Définir les informations du document
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('MonSite');
            $pdf->SetTitle('Facture #' . $commande_id);
            $pdf->SetSubject('Facture de commande');

            // Supprimer les en-têtes et pieds de page par défaut
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Ajouter une page
            $pdf->AddPage();

            // Style pour le PDF
            $pdf->SetFont('helvetica', '', 10);

            // Contenu de la facture
            $html = '
            <style>
                .facture-header { text-align: center; margin-bottom: 20px; }
                .facture-details { margin-bottom: 20px; }
                .articles-table { width: 100%; border-collapse: collapse; }
                .articles-table th, .articles-table td { border: 1px solid #ddd; padding: 8px; }
            </style>
            <div class="facture-header">
                <h1>Facture #' . $commande_id . '</h1>
                <p>Date : ' . date('d/m/Y H:i') . '</p>
            </div>

            <div class="facture-details">
                <p><strong>Adresse de livraison :</strong><br>
                ' . htmlspecialchars($adresse) . '<br>
                ' . htmlspecialchars($code_postal . ' ' . $ville) . '</p>
            </div>

            <table class="articles-table">
                <thead>
                    <tr>
                        <th>Article</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>';

            // Ajouter les articles à la facture
            foreach ($items as $item) {
                $html .= '
                    <tr>
                        <td>' . htmlspecialchars($item['nom']) . '</td>
                        <td>' . $item['cart_quantite'] . '</td>
                        <td>' . number_format($item['prix'], 2) . ' €</td>
                        <td>' . number_format($item['prix'] * $item['cart_quantite'], 2) . ' €</td>
                    </tr>';

                // Ajouter l'article à la commande
                $insert_items = "INSERT INTO commande_articles (commande_id, article_id, quantite, prix_unitaire, article_name) VALUES (?, ?, ?, ?, ?)";
                $stmt = $mysqli->prepare($insert_items);
                $stmt->bind_param("iiids", $commande_id, $item['article_id'], $item['cart_quantite'], $item['prix'], $item['nom']);
                $stmt->execute();

                // Mettre à jour le stock
                $update_stock = "UPDATE stocks SET quantite = quantite - ? WHERE article_id = ?";
                $stmt = $mysqli->prepare($update_stock);
                $stmt->bind_param("ii", $item['cart_quantite'], $item['article_id']);
                $stmt->execute();
            }

            $html .= '
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right;"><strong>Total :</strong></td>
                        <td>' . number_format($total, 2) . ' €</td>
                    </tr>
                </tfoot>
            </table>';

            // Écrire le HTML dans le PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            // Générer le nom de fichier
            $nom_fichier = "facture_" . $commande_id . "_" . date("Y-m-d") . ".pdf";

            // Générer le contenu PDF
            $pdf_content = $pdf->Output('', 'S');

            // Insérer la facture dans la base de données
            $insert_facture = "INSERT INTO factures (commande_id, nom_fichier, contenu) VALUES (?, ?, ?)";
            $stmt_facture = $mysqli->prepare($insert_facture);
            $stmt_facture->bind_param("iss", $commande_id, $nom_fichier, $pdf_content);
            $stmt_facture->execute();

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

            // Valider la transaction
            $mysqli->commit();

            $_SESSION['success'] = "Commande validée avec succès !";
            header('Location: cart/validate.php?order_id=' . $commande_id);
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
    <link rel="stylesheet" href="styles/checkout.css">
    <link rel="icon" type="image/png" href="images/favicon.png">
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