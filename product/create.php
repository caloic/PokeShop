<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $prix = floatval($_POST['prix']);
    $quantite = intval($_POST['quantite']);
    $image_url = trim($_POST['image_url']);
    $slug = strtolower(str_replace(' ', '-', $nom));

    try {
        $mysqli->begin_transaction();

        $check_deleted = "SELECT is_deleted FROM articles WHERE id = ?";
        $stmt = $mysqli->prepare($check_deleted);
        $stmt->bind_param("i", $article_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $article = $result->fetch_assoc();

        if ($article['is_deleted']) {
            $_SESSION['error'] = "Cet article a été supprimé et ne peut pas être modifié";
            header('Location: ../index.php');
            exit();
        }

        // Vérifier si le slug existe déjà
        $check_slug = "SELECT id FROM articles WHERE slug = ?";
        $stmt = $mysqli->prepare($check_slug);
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            // Ajouter un nombre aléatoire au slug
            $slug = $slug . '-' . rand(1, 999);
        }

        // Insérer l'article
        $insert_article = "INSERT INTO articles (nom, description, prix, image_url, slug, user_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($insert_article);
        $stmt->bind_param("ssdssi", $nom, $description, $prix, $image_url, $slug, $_SESSION['user_id']);
        $stmt->execute();

        $article_id = $mysqli->insert_id;

        // Insérer le stock
        $insert_stock = "INSERT INTO stocks (article_id, quantite) VALUES (?, ?)";
        $stmt = $mysqli->prepare($insert_stock);
        $stmt->bind_param("ii", $article_id, $quantite);
        $stmt->execute();

        $mysqli->commit();
        $_SESSION['success'] = "Article créé avec succès";
        header('Location: ../index.php');
        exit();

    } catch (Exception $e) {
        $mysqli->rollback();
        $_SESSION['error'] = "Erreur lors de la création : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Créer un article</title>
    <link rel="stylesheet" href="../styles/product.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Créer un nouvel article</h1>
        <a href="../index.php" class="btn back-btn">Retour au dashboard</a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error">
            <?php
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST">
            <div class="form-group">
                <label for="nom">Nom de l'article</label>
                <input type="text" id="nom" name="nom" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required></textarea>
            </div>

            <div class="form-group">
                <label for="prix">Prix (€)</label>
                <input type="number" id="prix" name="prix" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="quantite">Quantité en stock</label>
                <input type="number" id="quantite" name="quantite" min="0" required>
            </div>

            <div class="form-group">
                <label for="image_url">URL de l'image</label>
                <input type="text" id="image_url" name="image_url" required
                       onchange="previewImage(this.value)">
                <img id="preview" class="preview-image" style="display: none;">
            </div>

            <button type="submit" class="btn save-btn">Créer l'article</button>
        </form>
    </div>
</div>

<script>
    function previewImage(url) {
        const preview = document.getElementById('preview');
        if (url) {
            preview.src = url;
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    }
</script>
</body>
</html>