<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['avatar'])) {
    $uploadDir = 'uploads/avatars/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $filename = 'avatar_' . $_SESSION['user_id'] . '.' . $extension;
    $targetPath = $uploadDir . $filename;
    
    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
        $conn = connectToDatabase();
        $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE user_id = ?");
        $stmt->bind_param("si", $filename, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $_SESSION['avatar'] = $filename;
            $_SESSION['success'] = "Аватар успешно обновлён";
        }
        $conn->close();
    } else {
        $_SESSION['error'] = "Ошибка загрузки аватарки";
    }
}

header("Location: profile.php");
exit;
?>