<?php
require_once '../config.php';
require_once 'auth_admin.php';

// Récupérer tous les utilisateurs
$query = "SELECT * FROM users ORDER BY created_at DESC";
$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion des Utilisateurs - Administration</title>
    <link rel="stylesheet" href="../styles/admin.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Gestion des Utilisateurs</h1>
        <a href="index.php" class="back-btn">Retour au tableau de bord</a>
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

    <table class="users-table">
        <thead>
        <tr>
            <th>Avatar</th>
            <th>Nom d'utilisateur</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Solde</th>
            <th>Date d'inscription</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($user = $result->fetch_assoc()): ?>
            <tr>
                <td>
                    <?php if ($user['avatar']): ?>
                        <img src="<?php echo htmlspecialchars($user['avatar']); ?>"
                             alt="Avatar"
                             class="avatar">
                    <?php else: ?>
                        <div class="avatar" style="background-color: #ddd;"></div>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td>
                            <span class="role-badge <?php echo $user['role'] === 'admin' ? 'role-admin' : 'role-user'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                </td>
                <td><?php echo number_format($user['solde'], 2); ?> €</td>
                <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                <td>
                    <a href="edit_user.php?id=<?php echo $user['id']; ?>"
                       class="action-btn edit-btn">
                        Modifier
                    </a>
                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                        <a href="delete_user.php?id=<?php echo $user['id']; ?>"
                           class="action-btn delete-btn"
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                            Supprimer
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>