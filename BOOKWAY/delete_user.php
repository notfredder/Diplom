<?php
session_start();
require_once 'db.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit;
}

// Получаем ID пользователя для удаления
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($userId > 0) {
    try {
        $conn = connectToDatabase();
        
        // Нельзя удалить самого себя
        if ($userId == $_SESSION['user_id']) {
            $_SESSION['error'] = "Вы не можете удалить свой аккаунт";
            header('Location: admin_users.php');
            exit;
        }

        // 1. Получаем данные пользователя для удаления файлов
        $stmt = $conn->prepare("SELECT avatar, profile_header FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user) {
            // 2. Удаляем аватар (если не дефолтный)
            if (!empty($user['avatar']) && $user['avatar'] !== 'default_avatar.jpg') {
                $avatarPath = 'uploads/avatars/' . $user['avatar'];
                if (file_exists($avatarPath)) {
                    @unlink($avatarPath);
                }
            }

            // 3. Удаляем шапку профиля (если есть)
            if (!empty($user['profile_header'])) {
                $headerPath = 'uploads/hats/' . $user['profile_header'];
                if (file_exists($headerPath)) {
                    @unlink($headerPath);
                }
            }

            // 4. Удаляем пользователя из БД
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Пользователь успешно удалён";
            } else {
                throw new Exception("Ошибка удаления из БД");
            }
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Ошибка при удалении: " . $e->getMessage();
    } finally {
        if (isset($conn)) $conn->close();
    }
}

header('Location: admin_users.php');
exit;
?>