<?php
require_once 'config.php';
require_once 'auth_check.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $user_id = $_SESSION['user_id'];
    $upload_dir = __DIR__ . '/uploads/avatars/';
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    // Vérification des erreurs
    if ($_FILES['avatar']['error'] !== 0) {
        $_SESSION['error'] = "Erreur lors du téléchargement : code " . $_FILES['avatar']['error'];
        header('Location: account.php');
        exit();
    }

    // Vérification du type MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $_FILES['avatar']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        $_SESSION['error'] = "Format invalide. Seuls JPEG, PNG, GIF sont autorisés.";
        header('Location: account.php');
        exit();
    }

    // Vérification de la taille
    if ($_FILES['avatar']['size'] > $max_size) {
        $_SESSION['error'] = "Fichier trop volumineux (max : 5MB).";
        header('Location: account.php');
        exit();
    }

    // Assurer que le dossier existe
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Supprimer l'ancien avatar s'il existe
    $stmt = $mysqli->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($old_avatar);
    $stmt->fetch();
    $stmt->close();

    if ($old_avatar && file_exists($old_avatar)) {
        unlink($old_avatar);
    }

    // Générer un nouveau nom basé sur l'ID utilisateur
    $file_extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    $new_file_name = $upload_dir . $user_id . "_avatar." . $file_extension;

    // Déplacer le fichier
    if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $new_file_name)) {
        $_SESSION['error'] = "Erreur lors du transfert du fichier.";
        header('Location: account.php');
        exit();
    }

    // Mise à jour de la base de données
    $avatar_path = 'uploads/avatars/' . $user_id . "_avatar." . $file_extension;
    $stmt = $mysqli->prepare("UPDATE users SET avatar = ? WHERE id = ?");
    $stmt->bind_param("si", $avatar_path, $user_id);

    if (!$stmt->execute()) {
        unlink($new_file_name); // Supprimer en cas d'échec
        $_SESSION['error'] = "Erreur lors de la mise à jour de l'avatar.";
        header('Location: account.php');
        exit();
    }

    $_SESSION['success'] = "Avatar mis à jour avec succès!";
    header('Location: account.php');
    exit();
}
?>
