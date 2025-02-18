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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .users-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .users-table th,
        .users-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .users-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .users-table tr:hover {
            background-color: #f8f9fa;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin: 0 4px;
        }

        .edit-btn {
            background-color: #3498db;
            color: white;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: white;
        }

        .back-btn {
            background-color: #34495e;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
        }

        .role-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
        }

        .role-admin {
            background-color: #fee9e7;
            color: #c0392b;
        }

        .role-user {
            background-color: #edf7ed;
            color: #27ae60;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .error {
            background-color: #ffebee;
            color: #c62828;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
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