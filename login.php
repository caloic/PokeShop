<?php
require_once 'config.php';
session_start();

// Si l'utilisateur est déjà connecté, rediriger vers l'index
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

// Gestion de l'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = $_POST['reg_username'];
    $email = $_POST['reg_email'];
    $password = $_POST['reg_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } else {
        // Vérification de l'unicité du nom d'utilisateur et de l'email
        $check_query = "SELECT username, email FROM users WHERE username = ? OR email = ?";
        $check_stmt = $mysqli->prepare($check_query);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $existing = $result->fetch_assoc();
            if ($existing['username'] === $username) {
                $error = "Ce nom d'utilisateur existe déjà";
            } else {
                $error = "Cette adresse email est déjà utilisée";
            }
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $insert_stmt = $mysqli->prepare($insert_query);
            $insert_stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($insert_stmt->execute()) {
                // Connexion automatique après inscription
                $_SESSION['user_id'] = $mysqli->insert_id;
                $_SESSION['username'] = $username;

                // Rediriger vers la page demandée ou l'index
                $redirect = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : 'index.php';
                unset($_SESSION['redirect_url']);
                header('Location: ' . $redirect);
                exit();
            }
        }
    }
}

// Gestion de la connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $login = $_POST['login_username']; // Peut être username ou email
    $password = $_POST['login_password'];

    $query = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        // Rediriger vers la page demandée ou l'index
        $redirect = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : 'index.php';
        unset($_SESSION['redirect_url']);
        header('Location: ' . $redirect);
        exit();
    } else {
        $error = "Identifiants incorrects";
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
        label {
            display: block;
            margin-bottom: 5px;
        }
        input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .error {
            color: #dc3545;
            background-color: #f8d7da;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .success {
            color: #28a745;
            background-color: #d4edda;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .back-to-home {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-to-home:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="tabs">
    <button class="tab active" onclick="showTab('login')">Connexion</button>
    <button class="tab" onclick="showTab('register')">Inscription</button>
</div>

<!-- Formulaire de connexion -->
<div id="login" class="form-container active">
    <form method="POST">
        <div class="form-group">
            <label>Nom d'utilisateur ou Email</label>
            <input type="text" name="login_username" required>
        </div>

        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="login_password" required>
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
            <label>Email</label>
            <input type="email" name="reg_email" required>
        </div>

        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="reg_password" required
                   minlength="6" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}"
                   title="Le mot de passe doit contenir au moins 6 caractères, dont une majuscule, une minuscule et un chiffre">
        </div>

        <div class="form-group">
            <label>Confirmer le mot de passe</label>
            <input type="password" name="confirm_password" required>
        </div>

        <button type="submit" name="register">S'inscrire</button>
    </form>
</div>

<a href="index.php" class="back-to-home">Retour à l'accueil</a>

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