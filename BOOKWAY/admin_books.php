<?php
session_start();
require_once 'db.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit;
}

// Получение списка всех книг
$conn = connectToDatabase();
$result = $conn->query("SELECT * FROM book ORDER BY book_id DESC");
$books = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();

require_once 'header.php';
?>
<div class="settings-page">
    <div class="settings-container">
<div class="admin-container">
    <h1>Управление книгами</h1>
    
    <div class="admin-tabs">
        <a href="admin.php">Добавить книгу</a>
        <a href="admin_books.php" class="active">Управление книгами</a>
        <a href="admin_users.php">Управление пользователями</a>
    </div>
    
    <div class="admin-content">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Обложка</th>
                    <th>Название</th>
                    <th>Автор</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                <tr>
                    <td><?= $book['book_id'] ?></td>
                    <td>
                        <img src="uploads/books/<?= $book['photo'] ?>" 
                             alt="<?= htmlspecialchars($book['book_name']) ?>" 
                             width="50">
                    </td>
                    <td><?= htmlspecialchars($book['book_name']) ?></td>
                    <td><?= htmlspecialchars($book['autor']) ?></td>
                    <td>
                        <a href="edit_book.php?id=<?= $book['book_id'] ?>" 
                           class="action-btn edit">Редактировать</a>
                        <a href="delete_book.php?id=<?= $book['book_id'] ?>" 
                           class="action-btn delete"
                           onclick="return confirm('Удалить эту книгу?')">Удалить</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
</div>
<?php require_once 'footer.php'; ?>