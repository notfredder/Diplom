<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['status' => 'error', 'message' => 'Доступ запрещен']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$bookId = $data['book_id'] ?? 0;
$status = $data['status'] ?? null;

if ($status === 'remove') {
    // Удаляем запись о книге пользователя
    $conn = connectToDatabase();
    $stmt = $conn->prepare("DELETE FROM user_books WHERE user_id = ? AND book_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $bookId);
    $result = $stmt->execute();
    $conn->close();
    
    if ($result) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Ошибка при удалении статуса']);
    }
} elseif ($status) {
    // Обновляем или добавляем статус
    $result = updateBookStatus($_SESSION['user_id'], $bookId, $status);
    
    if ($result) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Ошибка при обновлении статуса']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Не указан статус']);
}
?>