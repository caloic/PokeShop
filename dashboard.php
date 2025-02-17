<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
<h2>Bienvenue <?php echo $_SESSION['username']; ?></h2>
<a href="logout.php">Déconnexion</a>
</body>
</html>