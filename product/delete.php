<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../auth_check.php';

if (!isset($_GET['id'])) {
    header('Location: ../index.php');
    exit();
}

$article_id = (int)$_GET['id'];

// Vérifier que l'article existe et que l'utilisateur a les droits
$check_query = "SELECT user_id FROM articles WHERE id = ?";
$stmt = $mysqli->prepare($check_query);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();

if (!$article) {
    $_SESSION['error'] = "Article non trouvé";
    header('Location: ../index.php');
    exit();
}

// Vérifier les droits (être l'auteur ou admin)
if ($article['user_id'] != $_SESSION['user_id'] && $user_role !== 'admin') {
    $_SESSION['error'] = "Vous n'avez pas les droits pour supprimer cet article";
    header('Location: ../index.php');
    exit();
}

try {
    $mysqli->begin_transaction();

    // Vérifier s'il y a des commandes en cours pour cet article
    $check_orders = "SELECT COUNT(*) as count FROM commande_articles WHERE article_id = ?";
    $stmt = $mysqli->prepare($check_orders);
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $orders_count = $stmt->get_result()->fetch_assoc()['count'];

    if ($orders_count > 0) {
        throw new Exception("Impossible de supprimer l'article car il est lié à des commandes existantes");
    }

    // Supprimer du panier
    $delete_cart = "DELETE FROM carts WHERE article_id = ?";
    $stmt = $mysqli->prepare($delete_cart);
    $stmt->bind_param("i", $article_id);
    $stmt->execute();

    // Supprimer le stock
    $delete_stock = "DELETE FROM stocks WHERE article_id = ?";
    $stmt = $mysqli->prepare($delete_stock);
    $stmt->bind_param("i", $article_id);
    $stmt->execute();

    // Supprimer l'article
    $delete_article = "DELETE FROM articles WHERE id = ?";
    $stmt = $mysqli->prepare($delete_article);
    $stmt->bind_param("i", $article_id);
    $stmt->execute();

    $mysqli->commit();
    $_SESSION['success'] = "Article supprimé avec succès";

} catch (Exception $e) {
    $mysqli->rollback();
    $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
}

header('Location: ../index.php');
exit();
?>