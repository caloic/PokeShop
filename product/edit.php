<?php
require_once '../config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: ../dashboard.php');
    exit();
}

// Récupérer le rôle de l'utilisateur
$role_query = "SELECT role FROM users WHERE id = ?";
$stmt = $mysqli->prepare($role_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user_role = $stmt->get_result()->fetch_assoc()['role'];

$article_id = (int)$_GET['id'];

// Récupérer les informations de l'article
$query = "
    SELECT articles.*, stocks.quantite, users.username as author
    FROM articles 
    LEFT JOIN stocks ON articles.id = stocks.article_id
    LEFT JOIN users ON articles.user_id = users.id
    WHERE articles.id = ?
";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();

if (!$article) {
    header('Location: ../dashboard.php');
    exit();
}

// Vérifier si l'utilisateur est autorisé à modifier
if ($article['user_id'] !== $_SESSION['user_id'] && $user_role !== 'admin') {
    header('Location: ../dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        try {
            $mysqli->begin_transaction();

            // Supprimer les entrées du panier
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
            $delete_article = "DELETE FROM articles WHERE id = ? AND (user_id = ? OR ? = 'admin')";
            $stmt = $mysqli->prepare($delete_article);
            $stmt->bind_param("iis", $article_id, $_SESSION['user_id'], $user_role);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("Vous n'êtes pas autorisé à supprimer cet article");
            }

            $mysqli->commit();
            $_SESSION['success'] = "Article supprimé avec succès";
            header('Location: ../dashboard.php');
            exit();

        } catch (Exception $e) {
            $mysqli->rollback();
            $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
        }
    } else {
        $nom = trim($_POST['nom']);
        $description = trim($_POST['description']);
        $prix = floatval($_POST['prix']);
        $quantite = intval($_POST['quantite']);
        $image_url = trim($_POST['image_url']);
        $slug = strtolower(str_replace(' ', '-', $nom));

        try {
            $mysqli->begin_transaction();

            // Mettre à jour l'article
            $update_article = "
                UPDATE articles 
                SET nom = ?, description = ?, prix = ?, image_url = ?, slug = ?
                WHERE id = ? AND (user_id = ? OR ? = 'admin')
            ";
            $stmt = $mysqli->prepare($update_article);
            $stmt->bind_param("ssdssiss", $nom, $description, $prix, $image_url, $slug, $article_id, $_SESSION['user_id'], $user_role);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("Vous n'êtes pas autorisé à modifier cet article");
            }

            // Mettre à jour le stock
            $update_stock = "UPDATE stocks SET quantite = ? WHERE article_id = ?";
            $stmt = $mysqli->prepare($update_stock);
            $stmt->bind_param("ii", $quantite, $article_id);
            $stmt->execute();

            $mysqli->commit();
            $_SESSION['success'] = "Article modifié avec succès";
            header('Location: ../dashboard.php');
            exit();

        } catch (Exception $e) {
            $mysqli->rollback();
            $_SESSION['error'] = "Erreur lors de la modification : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modifier l'article</title>
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
            margin: 5px;
        }

        .save-btn {
            background-color: #2ecc71;
            color: white;
        }

        .delete-btn {
            background-color: #e74c3c;
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

        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .author-info {
            margin-bottom: 20px;
            color: #666;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Modifier l'article</h1>
        <a href="../dashboard.php" class="btn back-btn">Retour au dashboard</a>
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
        <div class="author-info">
            <p>Créé par: <?php echo htmlspecialchars($article['author']); ?></p>
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="nom">Nom de l'article</label>
                <input type="text" id="nom" name="nom" required
                       value="<?php echo htmlspecialchars($article['nom']); ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($article['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="prix">Prix (€)</label>
                <input type="number" id="prix" name="prix" step="0.01" min="0" required
                       value="<?php echo $article['prix']; ?>">
            </div>

            <div class="form-group">
                <label for="quantite">Quantité en stock</label>
                <input type="number" id="quantite" name="quantite" min="0" required
                       value="<?php echo $article['quantite']; ?>">
            </div>

            <div class="form-group">
                <label for="image_url">URL de l'image</label>
                <input type="text" id="image_url" name="image_url" required
                       value="<?php echo htmlspecialchars($article['image_url']); ?>"
                       onchange="previewImage(this.value)">
                <img id="preview" src="<?php echo htmlspecialchars($article['image_url']); ?>"
                     class="preview-image">
            </div>

            <div class="button-group">
                <button type="submit" class="btn save-btn">Enregistrer les modifications</button>
                <button type="submit" name="delete" class="btn delete-btn"
                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')">
                    Supprimer l'article
                </button>
            </div>
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