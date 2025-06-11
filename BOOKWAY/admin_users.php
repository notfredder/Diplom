<?php
session_start();
require_once 'db.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit;
}

// Получение списка всех пользователей
$conn = connectToDatabase();
$result = $conn->query("SELECT * FROM users ORDER BY user_id DESC");
$users = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();

require_once 'header.php';
?>
<div class="settings-page">
    <div class="settings-container">
<div class="admin-container">
    <h1>Управление пользователями</h1>
    
    <div class="admin-tabs">
        <a href="admin.php">Добавить книгу</a>
        <a href="admin_books.php">Управление книгами</a>
        <a href="admin_users.php" class="active">Управление пользователями</a>
    </div>
    
    <div class="admin-content">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Аватар</th>
                    <th>Имя пользователя</th>
                    <th>Email</th>
                    <th>Дата регистрации</th>
                    <th>Админ</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['user_id'] ?></td>
                    <td>
                        <img src="uploads/avatars/<?= $user['avatar'] ?>" 
                             alt="<?= htmlspecialchars($user['username']) ?>" 
                             width="40" style="border-radius: 50%;">
                    </td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                    <td><?= $user['is_admin'] ? 'Да' : 'Нет' ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= $user['user_id'] ?>" 
                           class="action-btn edit">Редактировать</a>
                        <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                            <a href="delete_user.php?id=<?= $user['user_id'] ?>" 
                               class="action-btn delete"
                               onclick="return confirm('Удалить этого пользователя?')">Удалить</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>