<?php
require_once '../config.php';
require_once 'auth_admin.php';

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Aucun article spécifié";
    header('Location: articles.php');
    exit();
}

$article_id = (int)$_GET['id'];

try {
    $mysqli->begin_transaction();

    // Supprimer d'abord les références dans le panier
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

header('Location: articles.php');
exit();
?>