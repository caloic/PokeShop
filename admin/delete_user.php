<?php
require_once '../config.php';
require_once 'auth_admin.php';

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Aucun utilisateur spécifié";
    header('Location: users.php');
    exit();
}

$user_id = (int)$_GET['id'];

// Empêcher la suppression de son propre compte
if ($user_id === $_SESSION['user_id']) {
    $_SESSION['error'] = "Vous ne pouvez pas supprimer votre propre compte";
    header('Location: users.php');
    exit();
}

try {
    $mysqli->begin_transaction();

    // Supprimer d'abord les paniers de l'utilisateur
    $delete_cart = "DELETE FROM carts WHERE user_id = ?";
    $stmt = $mysqli->prepare($delete_cart);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Supprimer les commandes de l'utilisateur
    // Note: Vous pourriez vouloir conserver les commandes pour l'historique
    // Dans ce cas, ne pas exécuter ces requêtes
    $delete_commande_articles = "
        DELETE ca FROM commande_articles ca
        INNER JOIN commandes c ON ca.commande_id = c.id
        WHERE c.user_id = ?
    ";
    $stmt = $mysqli->prepare($delete_commande_articles);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $delete_commandes = "DELETE FROM commandes WHERE user_id = ?";
    $stmt = $mysqli->prepare($delete_commandes);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Finalement, supprimer l'utilisateur
    $delete_user = "DELETE FROM users WHERE id = ?";
    $stmt = $mysqli->prepare($delete_user);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $mysqli->commit();
    $_SESSION['success'] = "Utilisateur supprimé avec succès";

} catch (Exception $e) {
    $mysqli->rollback();
    $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
}

header('Location: users.php');
exit();
?>