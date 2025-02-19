<?php
require_once 'config.php';
session_start();

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté pour gérer votre wishlist.'
    ]);
    exit();
}

// Vérifier la présence des paramètres
if (!isset($_POST['article_id']) || !isset($_POST['action'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Paramètres manquants.'
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];
$article_id = (int)$_POST['article_id'];
$action = $_POST['action'];

try {
    if ($action === 'add') {
        // Vérifier si l'article existe
        $check_article = $mysqli->prepare("SELECT id FROM articles WHERE id = ?");
        $check_article->bind_param("i", $article_id);
        $check_article->execute();
        $article_result = $check_article->get_result();

        if ($article_result->num_rows === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Article inexistant.'
            ]);
            exit();
        }

        // Ajouter à la wishlist
        $stmt = $mysqli->prepare("INSERT IGNORE INTO wishlist (user_id, article_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $article_id);
        $result = $stmt->execute();

        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Article ajouté aux favoris' : 'Erreur lors de l\'ajout'
        ]);
    } elseif ($action === 'remove') {
        // Supprimer de la wishlist
        $stmt = $mysqli->prepare("DELETE FROM wishlist WHERE user_id = ? AND article_id = ?");
        $stmt->bind_param("ii", $user_id, $article_id);
        $result = $stmt->execute();

        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Article retiré des favoris' : 'Erreur lors de la suppression'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Action invalide.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur inattendue : ' . $e->getMessage()
    ]);
}