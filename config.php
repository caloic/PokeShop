<?php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'php_exam_cano');
define('DB_PORT', 8889);

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if ($mysqli->connect_error) {
        throw new Exception('Erreur de connexion (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }

    // Définir le jeu de caractères UTF-8
    $mysqli->set_charset("utf8mb4");

} catch (Exception $e) {
    // Logger l'erreur plutôt que l'afficher
    die('Erreur : ' . $e->getMessage());
}

// Pour tester la connexion
/*
if (isset($mysqli)) {
    echo 'Connexion réussie à la base de donnée';
}
*/
?>