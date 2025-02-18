<?php
require_once '../auth_check.php'; // Vérifie d'abord la connexion

// Vérifier si l'utilisateur est admin
$admin_check = "SELECT role FROM users WHERE id = ?";
$stmt = $mysqli->prepare($admin_check);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
?>