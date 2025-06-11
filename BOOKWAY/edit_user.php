<?php
session_start();
require_once 'db.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit;
}

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получение данных пользователя
$conn = connectToDatabase();
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header('Location: admin_users.php');
    exit;
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    $gender = $_POST['gender'] ?? null;
    $age = $_POST['age'] ?? null;
    $website = $_POST['website'] ?? null;
    
    // Если введен новый пароль
    $password_update = '';
    if (!empty($_POST['password'])) {
        $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_update = ", password_hash = ?";
    }
    
    // Обновление аватара
    $avatar = $user['avatar'];
    if (!empty($_FILES['avatar']['name'])) {
        $uploadDir = 'uploads/avatars/';
        $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $avatar = 'avatar_' . $user_id . '.' . $extension;
        move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $avatar);
    }
    
    // Обновление шапки профиля
    $profile_header = $user['profile_header'];
    if (!empty($_FILES['profile_header']['name'])) {
        $uploadDir = 'uploads/hats/';
        $extension = pathinfo($_FILES['profile_header']['name'], PATHINFO_EXTENSION);
        $profile_header = 'header_' . $user_id . '.' . $extension;
        move_uploaded_file($_FILES['profile_header']['tmp_name'], $uploadDir . $profile_header);
    }
    
    // Подготовка SQL запроса
    if (!empty($password_update)) {
        $sql = "UPDATE users SET 
                username = ?, 
                email = ?, 
                is_admin = ?, 
                gender = ?, 
                age = ?, 
                website = ?, 
                avatar = ?, 
                profile_header = ?
                $password_update
                WHERE user_id = ?";
    } else {
        $sql = "UPDATE users SET 
                username = ?, 
                email = ?, 
                is_admin = ?, 
                gender = ?, 
                age = ?, 
                website = ?, 
                avatar = ?, 
                profile_header = ?
                WHERE user_id = ?";
    }
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($password_update)) {
        $stmt->bind_param("ssisissiis", 
            $username, 
            $email, 
            $is_admin, 
            $gender, 
            $age, 
            $website, 
            $avatar, 
            $profile_header,
            $password_hash,
            $user_id);
    } else {
        $stmt->bind_param("ssisissii", 
            $username, 
            $email, 
            $is_admin, 
            $gender, 
            $age, 
            $website, 
            $avatar, 
            $profile_header,
            $user_id);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Данные пользователя успешно обновлены";
        header("Location: admin_users.php");
        exit;
    } else {
        $error = "Ошибка при обновлении данных: " . $conn->error;
    }
    
    $conn->close();
}

require_once 'header.php';
?>

<div class="settings-page">
    <div class="settings-container">
        <h1>Редактирование пользователя</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group avatar-group">
                <label>Аватар</label>
                <div class="avatar-preview-container">
                    <img id="avatarPreview" src="uploads/avatars/<?= $user['avatar'] ?>" 
                         alt="Текущий аватар" class="current-avatar">
                    <div class="avatar-upload">
                        <input type="file" id="avatarInput" name="avatar" accept="image/*">
                        <label for="avatarInput" class="upload-btn">Выбрать изображение</label>
                        <p class="hint">Рекомендуемый размер: 200×200 px</p>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Имя пользователя*</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Email*</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Новый пароль</label>
                <input type="password" name="password" placeholder="Оставьте пустым, чтобы не менять">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_admin" <?= $user['is_admin'] ? 'checked' : '' ?>>
                    Администратор
                </label>
            </div>
            
            <div class="settings-section">
                <h2>Дополнительная информация</h2>
                
                <div class="form-group">
                    <label>Шапка профиля</label>
                    <div class="header-preview-container">
                        <?php if (!empty($user['profile_header'])): ?>
                            <img id="headerPreview" src="uploads/hats/<?= htmlspecialchars($user['profile_header']) ?>" 
                                 alt="Текущая шапка" class="current-header">
                        <?php else: ?>
                            <img id="headerPreview" src="" alt="Текущая шапка" class="current-header" style="display:none;">
                        <?php endif; ?>
                        <label for="headerInput" class="upload-btn">
                            <i class="fas fa-image"></i> Выбрать изображение
                        </label>
                        <input type="file" id="headerInput" name="profile_header" accept="image/*">
                        <p class="hint">Рекомендуемый размер: 1200×400 px</p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Пол</label>
                    <select name="gender" class="form-control">
                        <option value="">Не указан</option>
                        <option value="male" <?= ($user['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Мужской</option>
                        <option value="female" <?= ($user['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Женский</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Возраст</label>
                    <input type="number" name="age" value="<?= htmlspecialchars($user['age'] ?? '') ?>" min="1" max="120" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Сайт</label>
                    <input type="url" name="website" value="<?= htmlspecialchars($user['website'] ?? '') ?>" placeholder="https://example.com" class="form-control">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="save-btn">Сохранить изменения</button>
                <a href="admin_users.php" class="logout-btn">Отмена</a>
            </div>
        </form>
    </div>
</div>

<script>
// Предпросмотр аватара перед загрузкой
document.getElementById('avatarInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('avatarPreview').src = event.target.result;
        }
        reader.readAsDataURL(file);
    }
});

// Предпросмотр шапки профиля
document.getElementById('headerInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const preview = document.getElementById('headerPreview');
            preview.src = event.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php require_once 'footer.php'; ?>