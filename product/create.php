<?php
require_once '../config.php';
require_once '../auth_check.php';
session_start();

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $prix = floatval($_POST['prix']);
    $quantite = intval($_POST['quantite']);
    $image_url = trim($_POST['image_url']);
    $slug = strtolower(str_replace(' ', '-', $nom));

    try {
        $mysqli->begin_transaction();

        // Vérifier si le slug existe déjà
        $check_slug = "SELECT id FROM articles WHERE slug = ?";
        $stmt = $mysqli->prepare($check_slug);
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            // Ajouter un nombre aléatoire au slug
            $slug = $slug . '-' . rand(1, 999);
        }

        // Insérer l'article avec l'ID de l'utilisateur
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
        }

        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        textarea {
            height: 150px;
            resize: vertical;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        .save-btn {
            background-color: #2ecc71;
            color: white;
            font-size: 16px;
            width: 100%;
        }

        .back-btn {
            background-color: #34495e;
            color: white;
        }

        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 4px;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .error {
            background-color: #ffebee;
            color: #c62828;
        }
    </style>
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