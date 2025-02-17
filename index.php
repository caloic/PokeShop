<?php
require_once 'config.php';
session_start();

$error = '';
$success = '';

// Gestion de l'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = $_POST['reg_username'];
    $password = $_POST['reg_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } else {
        $check_query = "SELECT username FROM users WHERE username = ?";
        $check_stmt = $mysqli->prepare($check_query);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();

        if ($check_stmt->get_result()->num_rows > 0) {
            $error = "Ce nom d'utilisateur existe déjà";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO users (username, password) VALUES (?, ?)";
            $insert_stmt = $mysqli->prepare($insert_query);
            $insert_stmt->bind_param("ss", $username, $hashed_password);

            if ($insert_stmt->execute()) {
                $success = "Inscription réussie ! Vous pouvez maintenant vous connecter";
            }
        }
    }
}

// Gestion de la connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Nom d'utilisateur ou mot de passe incorrect";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Connexion / Inscription</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
        }
        .tabs {
            display: flex;
            margin-bottom: 20px;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            background: #f0f0f0;
            border: none;
            flex: 1;
        }
        .tab.active {
            background: #007bff;
            color: white;
        }
        .form-container {
            display: none;
        }
        .form-container.active {
            display: block;
        }
        .form-group {
            margin-bottom: 15px;
        }
        input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<?php if ($error): ?>
    <div class="error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="tabs">
    <button class="tab active" onclick="showTab('login')">Connexion</button>
    <button class="tab" onclick="showTab('register')">Inscription</button>
</div>

<!-- Formulaire de connexion -->
<div id="login" class="form-container active">
    <form method="POST">
        <div class="form-group">
            <label>Nom d'utilisateur</label>
            <input type="text" name="username" required>
        </div>

        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit" name="login">Se connecter</button>
    </form>
</div>

<!-- Formulaire d'inscription -->
<div id="register" class="form-container">
    <form method="POST">
        <div class="form-group">
            <label>Nom d'utilisateur</label>
            <input type="text" name="reg_username" required>
        </div>

        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="reg_password" required>
        </div>

        <div class="form-group">
            <label>Confirmer le mot de passe</label>
            <input type="password" name="confirm_password" required>
        </div>

        <button type="submit" name="register">S'inscrire</button>
    </form>
</div>

<script>
    function showTab(tabName) {
        // Cacher tous les formulaires
        document.querySelectorAll('.form-container').forEach(form => {
            form.classList.remove('active');
        });

        // Désactiver tous les onglets
        document.querySelectorAll('.tab').forEach(tab => {
            tab.classList.remove('active');
        });

        // Afficher le formulaire sélectionné
        document.getElementById(tabName).classList.add('active');

        // Activer l'onglet sélectionné
        event.target.classList.add('active');
    }
</script>
</body>
</html>