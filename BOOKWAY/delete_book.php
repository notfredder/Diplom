<?php
session_start();
require_once 'db.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit;
}

$bookId = $_GET['id'] ?? 0;

if ($bookId) {
    $conn = connectToDatabase();
    
    // Сначала получаем информацию о книге, чтобы удалить изображение
    $stmt = $conn->prepare("SELECT photo FROM book WHERE book_id = ?");
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    
    if ($book) {
        // Удаляем изображение, если оно не placeholder
        if ($book['photo'] !== 'placeholder.png') {
            @unlink('uploads/books/' . $book['photo']);
        }
        
        // Удаляем книгу из БД
        $stmt = $conn->prepare("DELETE FROM book WHERE book_id = ?");
        $stmt->bind_param("i", $bookId);
        $stmt->execute();
    }
    
    $conn->close();
}

header('Location: admin_books.php');
exit;
?>