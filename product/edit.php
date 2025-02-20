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
    $_SESSION['error'] = "Article non trouvé";
    header('Location: ../index.php');
    exit();
}

// Vérifier les droits d'accès
if ($article['user_id'] != $_SESSION['user_id'] && $user_role !== 'admin') {
    $_SESSION['error'] = "Vous n'avez pas les droits pour modifier cet article";
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        header('Location: delete.php?id=' . $article_id);
        exit();
    }

    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $prix = floatval($_POST['prix']);
    $quantite = intval($_POST['quantite']);
    $image_url = trim($_POST['image_url']);
    $slug = strtolower(str_replace(' ', '-', $nom));

    try {
        $mysqli->begin_transaction();

        // Vérifier si le nouveau slug existe déjà pour un autre article
        $check_slug = "SELECT id FROM articles WHERE slug = ? AND id != ?";
        $stmt = $mysqli->prepare($check_slug);
        $stmt->bind_param("si", $slug, $article_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $slug = $slug . '-' . rand(1, 999);
        }

        // Mettre à jour l'article
        $update_article = "
            UPDATE articles 
            SET nom = ?, description = ?, prix = ?, image_url = ?, slug = ?
            WHERE id = ?
        ";
        $stmt = $mysqli->prepare($update_article);
        $stmt->bind_param("ssdssi", $nom, $description, $prix, $image_url, $slug, $article_id);
        $stmt->execute();

        // Mettre à jour le stock
        $update_stock = "UPDATE stocks SET quantite = ? WHERE article_id = ?";
        $stmt = $mysqli->prepare($update_stock);
        $stmt->bind_param("ii", $quantite, $article_id);
        $stmt->execute();

        $mysqli->commit();
        $_SESSION['success'] = "Article modifié avec succès";
        header('Location: ../index.php');
        exit();

    } catch (Exception $e) {
        $mysqli->rollback();
        $_SESSION['error'] = "Erreur lors de la modification : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modifier l'article</title>
    <link rel="stylesheet" href="../styles/product.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Modifier l'article</h1>
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