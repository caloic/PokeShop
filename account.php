<?php
require_once 'config.php';
require_once 'auth_check.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Déterminer quel profil afficher
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];
$is_own_profile = $profile_id === $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $mysqli->prepare($user_query);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header('Location: index.php');
    exit();
}

// Traitement du formulaire de mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_own_profile) {
    if (isset($_POST['update_profile'])) {
        $email = trim($_POST['email']);
        $avatar = trim($_POST['avatar']);
        $current_password = $_POST['current_password'];
        $new_password = trim($_POST['new_password']);

        try {
            // Vérifier si l'email existe déjà
            $check_email = "SELECT id FROM users WHERE email = ? AND id != ?";
            $stmt = $mysqli->prepare($check_email);
            $stmt->bind_param("si", $email, $_SESSION['user_id']);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                throw new Exception("Cet email est déjà utilisé");
            }

            if (!empty($new_password)) {
                // Vérifier le mot de passe actuel
                $check_password = "SELECT password FROM users WHERE id = ?";
                $stmt = $mysqli->prepare($check_password);
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $current_hash = $stmt->get_result()->fetch_assoc()['password'];

                if (!password_verify($current_password, $current_hash)) {
                    throw new Exception("Mot de passe actuel incorrect");
                }

                // Mettre à jour avec le nouveau mot de passe
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $update_query = "UPDATE users SET email = ?, avatar = ?, password = ? WHERE id = ?";
                $stmt = $mysqli->prepare($update_query);
                $stmt->bind_param("sssi", $email, $avatar, $new_hash, $_SESSION['user_id']);
            } else {
                // Mise à jour sans changement de mot de passe
                $update_query = "UPDATE users SET email = ?, avatar = ? WHERE id = ?";
                $stmt = $mysqli->prepare($update_query);
                $stmt->bind_param("ssi", $email, $avatar, $_SESSION['user_id']);
            }

            $stmt->execute();
            $_SESSION['success'] = "Profil mis à jour avec succès";
            header('Location: account.php');
            exit();

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
    } elseif (isset($_POST['add_funds'])) {
        $amount = floatval($_POST['amount']);
        if ($amount > 0) {
            $update_solde = "UPDATE users SET solde = solde + ? WHERE id = ?";
            $stmt = $mysqli->prepare($update_solde);
            $stmt->bind_param("di", $amount, $_SESSION['user_id']);
            $stmt->execute();
            $_SESSION['success'] = "Solde mis à jour avec succès";
            header('Location: account.php');
            exit();
        }
    }
}

// Récupérer les articles publiés
$articles_query = "
    SELECT articles.*, stocks.quantite 
    FROM articles 
    LEFT JOIN stocks ON articles.id = stocks.article_id 
    WHERE articles.user_id = ?
    ORDER BY articles.date_publication DESC
";
$stmt = $mysqli->prepare($articles_query);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$articles = $stmt->get_result();

// Pour le profil personnel, récupérer les achats
if ($is_own_profile) {
    $purchases_query = "
    SELECT 
        c.date_transaction,
        c.montant_total,
        c.adresse,
        c.ville,
        c.code_postal,
        MAX(f.id) as facture_id,
        MAX(f.nom_fichier) as nom_fichier,
        GROUP_CONCAT(CONCAT(ca.quantite, 'x ', a.nom) SEPARATOR ', ') as articles
    FROM commandes c
    JOIN commande_articles ca ON c.id = ca.commande_id
    JOIN articles a ON ca.article_id = a.id
    LEFT JOIN factures f ON c.id = f.commande_id
    WHERE c.user_id = ?
    GROUP BY 
        c.id, 
        c.date_transaction, 
        c.montant_total, 
        c.adresse, 
        c.ville, 
        c.code_postal
    ORDER BY c.date_transaction DESC
";
    $stmt = $mysqli->prepare($purchases_query);
    $stmt->bind_param("i", $profile_id);
    $stmt->execute();
    $purchases = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $is_own_profile ? "Mon compte" : "Profil de " . htmlspecialchars($user['username']); ?></title>
    <link rel="stylesheet" href="styles/account.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1><?php echo $is_own_profile ? "Mon compte" : "Profil de " . htmlspecialchars($user['username']); ?></h1>
        <a href="index.php" class="btn back-btn">Retour au dashboard</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success">
            <?php
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error">
            <?php
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <div class="profile-section">
        <div class="profile-info">
            <?php if ($user['avatar']): ?>
                <img src="<?php echo htmlspecialchars($user['avatar']); ?>"
                     alt="Avatar"
                     class="avatar">
            <?php endif; ?>

            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
            <p>Membre depuis: <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>

            <?php if ($is_own_profile): ?>
                <p>Solde actuel: <?php echo number_format($user['solde'], 2); ?> €</p>

                <form method="POST" class="add-funds-form">
                    <div class="form-group">
                        <label for="amount">Ajouter des fonds (€)</label>
                        <input type="number" id="amount" name="amount" step="0.01" min="0" required>
                    </div>
                    <button type="submit" name="add_funds" class="btn save-btn">Ajouter</button>
                </form>

                <form method="POST" style="margin-top: 20px;">
                    <input type="hidden" name="update_profile" value="1">

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="avatar">URL de l'avatar</label>
                        <input type="text" id="avatar" name="avatar"
                               value="<?php echo htmlspecialchars($user['avatar']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="current_password">Mot de passe actuel</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>

                    <div class="form-group">
                        <label for="new_password">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                        <input type="password" id="new_password" name="new_password"
                               minlength="6"
                               pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}"
                               title="Le mot de passe doit contenir au moins 6 caractères, dont une majuscule, une minuscule et un chiffre">
                    </div>

                    <button type="submit" class="btn save-btn">Mettre à jour le profil</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="content-section">
            <?php if ($is_own_profile): ?>
                <div class="tabs">
                    <button class="tab active" onclick="showTab('articles')">Mes articles</button>
                    <button class="tab" onclick="showTab('purchases')">Mes achats</button>
                </div>
            <?php else: ?>
                <h2>Articles publiés</h2>
            <?php endif; ?>

            <div id="articles" class="tab-content active">
                <div class="articles-grid">
                    <?php while ($article = $articles->fetch_assoc()): ?>
                        <div class="article-card">
                            <img src="<?php echo htmlspecialchars($article['image_url']); ?>"
                                 alt="<?php echo htmlspecialchars($article['nom']); ?>"
                                 class="article-image">
                            <h3><?php echo htmlspecialchars($article['nom']); ?></h3>
                            <p class="price"><?php echo number_format($article['prix'], 2); ?> €</p>
                            <p>Stock: <?php echo $article['quantite']; ?></p>
                            <?php if ($is_own_profile): ?>
                                <a href="product/edit.php?id=<?php echo $article['id']; ?>"
                                   class="btn save-btn">Modifier</a>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <?php if ($is_own_profile): ?>
                <div id="purchases" class="tab-content">
                    <div class="purchases-list">
                        <?php while ($purchase = $purchases->fetch_assoc()): ?>
                            <div class="purchase-item">
                                <h3>Commande du <?php echo date('d/m/Y H:i', strtotime($purchase['date_transaction'])); ?></h3>
                                <p><strong>Montant total:</strong> <?php echo number_format($purchase['montant_total'], 2); ?> €</p>
                                <p><strong>Articles:</strong> <?php echo htmlspecialchars($purchase['articles']); ?></p>
                                <p><strong>Adresse de livraison:</strong><br>
                                    <?php echo htmlspecialchars($purchase['adresse']); ?><br>
                                    <?php echo htmlspecialchars($purchase['code_postal'] . ' ' . $purchase['ville']); ?>
                                </p>
                                <?php if ($purchase['facture_id']): ?>
                                    <a href="toggle_order.php?id=<?php echo $purchase['facture_id']; ?>"
                                       class="btn save-btn"
                                       download>
                                        Télécharger la facture
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                        <?php if ($purchases->num_rows === 0): ?>
                            <p>Aucun achat pour le moment.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function showTab(tabName) {
        // Masquer tous les contenus
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });

        // Désactiver tous les onglets
        document.querySelectorAll('.tab').forEach(tab => {
            tab.classList.remove('active');
        });

        // Afficher le contenu sélectionné
        document.getElementById(tabName).classList.add('active');

        // Activer l'onglet sélectionné
        event.target.classList.add('active');
    }
</script>
</body>
</html>