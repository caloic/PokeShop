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

    // Marquer l'article comme supprimé
    $delete_article = "UPDATE articles SET is_deleted = TRUE WHERE id = ?";
    $stmt = $mysqli->prepare($delete_article);
    $stmt->bind_param("i", $article_id);
    $stmt->execute();

    // Supprimer le stock (optionnel)
    $delete_stock = "DELETE FROM stocks WHERE article_id = ?";
    $stmt = $mysqli->prepare($delete_stock);
    $stmt->bind_param("i", $article_id);
    $stmt->execute();

    $mysqli->commit();
    $_SESSION['success'] = "Article archivé avec succès";
    header('Location: articles.php');
    exit();

} catch (Exception $e) {
    $mysqli->rollback();
    $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
    header('Location: articles.php');
    exit();
}
?>