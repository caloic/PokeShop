<?php
require_once 'config.php';
require_once 'auth_check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Vérifier si l'ID de la facture est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID de facture invalide');
}

$facture_id = (int)$_GET['id'];

// Récupérer la facture et vérifier que l'utilisateur a le droit de la télécharger
$query = "
    SELECT f.contenu, f.nom_fichier, c.user_id 
    FROM factures f
    JOIN commandes c ON f.commande_id = c.id
    WHERE f.id = ? AND c.user_id = ?
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $facture_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Facture non trouvée ou accès non autorisé');
}

$facture = $result->fetch_assoc();

// Vérifier que le contenu n'est pas vide
if (empty($facture['contenu'])) {
    die('Erreur : Le contenu de la facture est vide');
}

// Définir les headers pour le téléchargement du fichier texte
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="' . $facture['nom_fichier'] . '"');
header('Content-Length: ' . strlen($facture['contenu']));
header('Cache-Control: private, no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Envoyer le contenu
echo $facture['contenu'];
exit();