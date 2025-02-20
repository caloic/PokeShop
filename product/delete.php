<?php
require_once '../config.php';
require_once '../auth_check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Aucun article spécifié";
    header('Location: ../index.php');
    exit();
}

$article_id = (int)$_GET['id'];

// Récupérer l'article pour vérifier les droits
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
if ($article['user_id'] != $_SESSION['user_id']) {
    // Vérifier si l'utilisateur est admin
    $role_query = "SELECT role FROM users WHERE id = ?";
    $stmt = $mysqli->prepare($role_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user_role = $stmt->get_result()->fetch_assoc()['role'];

    if ($user_role !== 'admin') {
        $_SESSION['error'] = "Vous n'avez pas les droits pour supprimer cet article";
        header('Location: ../index.php');
        exit();
    }
}

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

    // Supprimer des paniers (optionnel)
    $delete_cart = "DELETE FROM carts WHERE article_id = ?";
    $stmt = $mysqli->prepare($delete_cart);
    $stmt->bind_param("i", $article_id);
    $stmt->execute();

    $mysqli->commit();
    $_SESSION['success'] = "Article archivé avec succès";
    header('Location: ../index.php');
    exit();

} catch (Exception $e) {
    $mysqli->rollback();
    $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
    header('Location: ../index.php');
    exit();
}
?>