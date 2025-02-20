<?php
require_once '../config.php';
require_once 'auth_admin.php';

$user = null;

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Aucun utilisateur spécifié";
    header('Location: users.php');
    exit();
}

// Récupérer les informations de l'utilisateur
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $_GET['id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    $_SESSION['error'] = "Utilisateur non trouvé";
    header('Location: users.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $solde = floatval($_POST['solde']);
    $avatar = trim($_POST['avatar']);
    $new_password = trim($_POST['new_password']);

    try {
        // Vérifier si le nom d'utilisateur ou l'email existe déjà
        $check_query = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
        $stmt = $mysqli->prepare($check_query);
        $stmt->bind_param("ssi", $username, $email, $_GET['id']);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;

        if ($exists) {
            throw new Exception("Le nom d'utilisateur ou l'email existe déjà");
        }

        $updates = [];
        $types = "";
        $params = [];

        // Construire la requête de mise à jour dynamiquement
        $updates[] = "username = ?";
        $types .= "s";
        $params[] = $username;

        $updates[] = "email = ?";
        $types .= "s";
        $params[] = $email;

        $updates[] = "role = ?";
        $types .= "s";
        $params[] = $role;

        $updates[] = "solde = ?";
        $types .= "d";
        $params[] = $solde;

        $updates[] = "avatar = ?";
        $types .= "s";
        $params[] = $avatar;

        if (!empty($new_password)) {
            $updates[] = "password = ?";
            $types .= "s";
            $params[] = password_hash($new_password, PASSWORD_DEFAULT);
        }

        // Ajouter l'ID à la fin des paramètres
        $types .= "i";
        $params[] = $_GET['id'];

        $update_query = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
        $stmt = $mysqli->prepare($update_query);

        // Créer un tableau de références pour bind_param
        $refs = [];
        $refs[] = &$types;
        foreach ($params as $key => $value) {
            $refs[] = &$params[$key];
        }
        call_user_func_array([$stmt, 'bind_param'], $refs);

        $stmt->execute();

        $_SESSION['success'] = "Utilisateur modifié avec succès";
        header('Location: users.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = "Erreur lors de la modification : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modifier un utilisateur - Administration</title>
    <link rel="stylesheet" href="../styles/admin.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">

</head>
<body>
<div class="container">
    <div class="header">
        <h1>Modifier un utilisateur</h1>
        <a href="users.php" class="btn back-btn">Retour à la liste</a>
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
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required
                       value="<?php echo htmlspecialchars($user['username']); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo htmlspecialchars($user['email']); ?>">
            </div>

            <div class="form-group">
                <label for="role">Rôle</label>
                <select id="role" name="role" required>
                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Utilisateur</option>
                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                </select>
            </div>

            <div class="form-group">
                <label for="solde">Solde (€)</label>
                <input type="number" id="solde" name="solde" step="0.01" required
                       value="<?php echo $user['solde']; ?>">
            </div>

            <div class="form-group">
                <label for="avatar">URL de l'avatar</label>
                <input type="text" id="avatar" name="avatar"
                       value="<?php echo htmlspecialchars($user['avatar']); ?>"
                       onchange="previewAvatar(this.value)">
                <?php if ($user['avatar']): ?>
                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>"
                         alt="Avatar Preview"
                         id="avatar-preview"
                         class="avatar-preview">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="new_password">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                <input type="password" id="new_password" name="new_password"
                       minlength="6"
                       pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}"
                       title="Le mot de passe doit contenir au moins 6 caractères, dont une majuscule, une minuscule et un chiffre">
            </div>

            <button type="submit" class="btn save-btn">Enregistrer les modifications</button>
        </form>
    </div>
</div>

<script>
    function previewAvatar(url) {
        let preview = document.getElementById('avatar-preview');
        if (!preview) {
            preview = document.createElement('img');
            preview.id = 'avatar-preview';
            preview.className = 'avatar-preview';
            document.getElementById('avatar').parentNode.appendChild(preview);
        }
        preview.src = url;
    }
</script>
</body>
</html>