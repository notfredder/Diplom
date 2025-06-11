<?php
function connectToDatabase() {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $dbname = 'bookway';
    
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

function getAllBooks() {
    $conn = connectToDatabase();
    $result = $conn->query("SELECT * FROM book");
    
    if (!$result) {
        die("Ошибка запроса: " . $conn->error);
    }
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getNewBooks($limit = 10) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("SELECT * FROM book ORDER BY book_id DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getBookById($id) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("SELECT * FROM book WHERE book_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getRandomBooks($limit = 15) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("SELECT * FROM book ORDER BY RAND() LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getLatestBooks($limit = 10) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("SELECT * FROM book ORDER BY book_id DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getNewBooksByYear($limit = 10) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("SELECT * FROM book ORDER BY year DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getBooksByPublisher($publisher, $limit = 10) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("SELECT * FROM book WHERE publishing_house = ? ORDER BY year DESC LIMIT ?");
    $stmt->bind_param("si", $publisher, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}
// Все функции теперь используют connectToDatabase() вместо глобальной $conn
function registerUser($username, $email, $password) {
    if (strlen($username) > 18) {
        return false;
    }
    
    $conn = connectToDatabase();
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed);
    $success = $stmt->execute();
    $conn->close();
    return $success;
}

function getUserByEmail($email) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $conn->close();
    return $result;
}

function loginUser($email, $password) {
    $user = getUserByEmail($email);
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    return false;
}
/**
 * Получает книги пользователя с их статусами
 */
function getUserBooks($userId) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("
        SELECT b.*, ub.status 
        FROM user_books ub
        JOIN book b ON ub.book_id = b.book_id
        WHERE ub.user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $books = $result->fetch_all(MYSQLI_ASSOC);
    $conn->close();
    return $books;
}

/**
 * Преобразует статус книги в читаемый формат
 */

function updateBookStatus($userId, $bookId, $status) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("
        INSERT INTO user_books (user_id, book_id, status) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
            status = VALUES(status)
    ");
    $stmt->bind_param("iis", $userId, $bookId, $status);
    $result = $stmt->execute();
    $conn->close();
    return $result;
}

function getBookStatus($userId, $bookId) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("SELECT status FROM user_books WHERE user_id = ? AND book_id = ?");
    $stmt->bind_param("ii", $userId, $bookId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $conn->close();
    return $result ? $result['status'] : null;
}

function usernameExists($username) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $conn->close();
    return (bool)$result;
}

function getUserByUsername($username) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $conn->close();
    return $result;
}
?>