<?php
require_once '../config.php';
require_once 'auth_admin.php';

$article = null;
$is_edit = false;

if (isset($_GET['id'])) {
    $is_edit = true;
    $query = "
        SELECT articles.*, stocks.quantite 
        FROM articles 
        LEFT JOIN stocks ON articles.id = stocks.article_id 
        WHERE articles.id = ?
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $article = $stmt->get_result()->fetch_assoc();

    if (!$article) {
        $_SESSION['error'] = "Article non trouvé";
        header('Location: articles.php');
        exit();
    }
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

        if ($is_edit) {
            // Mise à jour de l'article
            $update_article = "
                UPDATE articles 
                SET nom = ?, description = ?, prix = ?, image_url = ?, slug = ?
                WHERE id = ?
            ";
            $stmt = $mysqli->prepare($update_article);
            $stmt->bind_param("ssdssi", $nom, $description, $prix, $image_url, $slug, $_GET['id']);
            $stmt->execute();

            // Mise à jour du stock
            $update_stock = "UPDATE stocks SET quantite = ? WHERE article_id = ?";
            $stmt = $mysqli->prepare($update_stock);
            $stmt->bind_param("ii", $quantite, $_GET['id']);
            $stmt->execute();
        } else {
            // Création d'un nouvel article
            $insert_article = "
                INSERT INTO articles (nom, description, prix, image_url, slug)
                VALUES (?, ?, ?, ?, ?)
            ";
            $stmt = $mysqli->prepare($insert_article);
            $stmt->bind_param("ssdss", $nom, $description, $prix, $image_url, $slug);
            $stmt->execute();
            $article_id = $mysqli->insert_id;

            // Création du stock
            $insert_stock = "INSERT INTO stocks (article_id, quantite) VALUES (?, ?)";
            $stmt = $mysqli->prepare($insert_stock);
            $stmt->bind_param("ii", $article_id, $quantite);
            $stmt->execute();
        }

        $mysqli->commit();
        $_SESSION['success'] = $is_edit ? "Article modifié avec succès" : "Article créé avec succès";
        header('Location: articles.php');
        exit();

    } catch (Exception $e) {
        $mysqli->rollback();
        $_SESSION['error'] = "Erreur lors de l'enregistrement : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $is_edit ? "Modifier" : "Ajouter"; ?> un article - Administration</title>
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
            font-size: 16px;
            text-decoration: none;
        }

        .save-btn {
            background-color: #2ecc71;
            color: white;
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
        <h1><?php echo $is_edit ? "Modifier" : "Ajouter"; ?> un article</h1>
        <a href="articles.php" class="btn back-btn">Retour à la liste</a>
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
                <input type="text" id="nom" name="nom" required
                       value="<?php echo $article ? htmlspecialchars($article['nom']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required><?php echo $article ? htmlspecialchars($article['description']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="prix">Prix (€)</label>
                <input type="number" id="prix" name="prix" step="0.01" required
                       value="<?php echo $article ? $article['prix'] : ''; ?>">
            </div>

            <div class="form-group">
                <label for="quantite">Quantité en stock</label>
                <input type="number" id="quantite" name="quantite" required
                       value="<?php echo $article ? $article['quantite'] : '0'; ?>">
            </div>

            <div class="form-group">
                <label for="image_url">URL de l'image</label>
                <input type="text" id="image_url" name="image_url" required
                       value="<?php echo $article ? htmlspecialchars($article['image_url']) : ''; ?>"
                       onchange="previewImage(this.value)">
                <img id="preview" class="preview-image" style="display: none;">
            </div>

            <button type="submit" class="btn save-btn">
                <?php echo $is_edit ? "Enregistrer les modifications" : "Créer l'article"; ?>
            </button>
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

    // Charger la prévisualisation si une URL existe déjà
    window.onload = function() {
        const imageUrl = document.getElementById('image_url').value;
        if (imageUrl) {
            previewImage(imageUrl);
        }
    };
</script>
</body>
</html>