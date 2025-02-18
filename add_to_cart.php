<?php
require_once 'config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Vérifier si un article_id a été envoyé
if (!isset($_POST['article_id'])) {
    $_SESSION['error'] = "Aucun article sélectionné";
    header('Location: dashboard.php');
    exit();
}

$article_id = (int)$_POST['article_id'];
$user_id = $_SESSION['user_id'];

// Vérifier si l'article existe et s'il est en stock
$check_article = "SELECT articles.*, stocks.quantite 
                 FROM articles 
                 LEFT JOIN stocks ON articles.id = stocks.article_id 
                 WHERE articles.id = ?";
$stmt = $mysqli->prepare($check_article);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$result = $stmt->get_result();
$article = $result->fetch_assoc();

if (!$article) {
    $_SESSION['error'] = "Article introuvable";
    header('Location: dashboard.php');
    exit();
}

if ($article['quantite'] <= 0) {
    $_SESSION['error'] = "Article en rupture de stock";
    header('Location: dashboard.php');
    exit();
}

// Vérifier si l'article est déjà dans le panier
$check_cart = "SELECT * FROM carts WHERE user_id = ? AND article_id = ?";
$stmt = $mysqli->prepare($check_cart);
$stmt->bind_param("ii", $user_id, $article_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Article déjà dans le panier, augmenter la quantité
    $cart_item = $result->fetch_assoc();
    $new_quantity = $cart_item['quantite'] + 1;

    // Vérifier si la nouvelle quantité est disponible en stock
    if ($new_quantity > $article['quantite']) {
        $_SESSION['error'] = "Stock insuffisant";
        header('Location: dashboard.php');
        exit();
    }

    $update_cart = "UPDATE carts SET quantite = ? WHERE user_id = ? AND article_id = ?";
    $stmt = $mysqli->prepare($update_cart);
    $stmt->bind_param("iii", $new_quantity, $user_id, $article_id);
    $stmt->execute();
} else {
    // Ajouter l'article au panier
    $add_to_cart = "INSERT INTO carts (user_id, article_id, quantite) VALUES (?, ?, 1)";
    $stmt = $mysqli->prepare($add_to_cart);
    $stmt->bind_param("ii", $user_id, $article_id);
    $stmt->execute();
}

// Ajouter un message de succès dans la session
$_SESSION['success'] = "Article ajouté au panier";

// Rediriger vers le dashboard
header('Location: dashboard.php');
exit();
?>