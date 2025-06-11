<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
// Добавьте это после проверки авторизации и перед HTML
$conn = connectToDatabase();
$stmt = $conn->prepare("SELECT gender, age, website, profile_header FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$conn->close();

$gender = $result['gender'] ?? '';
$age = $result['age'] ?? '';
$website = $result['website'] ?? '';
$profile_header = $result['profile_header'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     // Обработка изменения имени пользователя
    if (isset($_POST['username'])) {
        $newUsername = trim($_POST['username']);
        $currentUsername = $_SESSION['username'];
        
        if (!empty($newUsername) && $newUsername !== $currentUsername) {
            // Валидация длины имени
            if (strlen($newUsername) > 18) {
                $_SESSION['error'] = "Имя пользователя не должно превышать 18 символов";
                header("Location: settings.php");
                exit;
            }
            
            // Проверка на уникальность имени (кроме текущего пользователя)
            $conn = connectToDatabase();
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
            $stmt->bind_param("si", $newUsername, $_SESSION['user_id']);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows > 0) {
                $_SESSION['error'] = "Это имя пользователя уже занято";
                $conn->close();
                header("Location: settings.php");
                exit;
            }
            
            // Обновление имени пользователя
            $stmt = $conn->prepare("UPDATE users SET username = ? WHERE user_id = ?");
            $stmt->bind_param("si", $newUsername, $_SESSION['user_id']);
            if ($stmt->execute()) {
                $_SESSION['username'] = $newUsername;
                $changesMade = true;
            }
            $conn->close();
        }
    }
    // Обработка смены аватара
    if (!empty($_FILES['avatar'])) {
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
// Обработка дополнительных данных
    $gender = $_POST['gender'] ?? null;
    $age = $_POST['age'] ?? null;
    $website = $_POST['website'] ?? null;
    
    // Обновление дополнительных данных
    $conn = connectToDatabase();
    $stmt = $conn->prepare("UPDATE users SET gender = ?, age = ?, website = ? WHERE user_id = ?");
    $stmt->bind_param("sisi", $gender, $age, $website, $_SESSION['user_id']);
    $stmt->execute();
    $conn->close();
    
    // Обработка загрузки шапки профиля
    if (!empty($_FILES['profile_header'])) {
        $uploadDir = 'uploads/hats/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $extension = pathinfo($_FILES['profile_header']['name'], PATHINFO_EXTENSION);
        $filename = 'header_' . $_SESSION['user_id'] . '.' . $extension;
        $targetPath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['profile_header']['tmp_name'], $targetPath)) {
            $conn = connectToDatabase();
            $stmt = $conn->prepare("UPDATE users SET profile_header = ? WHERE user_id = ?");
            $stmt->bind_param("si", $filename, $_SESSION['user_id']);
            if ($stmt->execute()) {
                $_SESSION['profile_header'] = $filename;
                $_SESSION['success'] = "Шапка профиля успешно обновлена";
            }
            $conn->close();
        } else {
            $_SESSION['error'] = "Ошибка загрузки шапки профиля";
        }
    }
    
    header("Location: settings.php");
    exit;
}

require_once 'header.php';
?>

<div class="settings-page">
    <div class="settings-container">
        <h1>Настройки профиля</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group avatar-group">
                <label>Аватар</label>
                <div class="avatar-preview-container">
                    <img id="avatarPreview" src="uploads/avatars/<?= $_SESSION['avatar'] ?? 'default_avatar.jpg' ?>" 
                         alt="Текущий аватар" class="current-avatar">
                    <div class="avatar-upload">
                        <input type="file" id="avatarInput" name="avatar" accept="image/*">
                        <label for="avatarInput" class="upload-btn">Выбрать изображение</label>
                        <p class="hint">Рекомендуемый размер: 200×200 px</p>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Имя пользователя</label>
                <input type="text" name="username" value="<?= htmlspecialchars($_SESSION['username']) ?>">
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" disabled>
            </div>
<div class="settings-section">
    <h2>Дополнительная информация</h2>
    
<div class="form-group">
    <label>Шапка профиля</label>
    <div class="header-preview-container">
        <?php if (!empty($profile_header)): ?>
            <img id="headerPreview" src="uploads/hats/<?= htmlspecialchars($profile_header) ?>" 
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
            <option value="male" <?= ($gender ?? '') === 'male' ? 'selected' : '' ?>>Мужской</option>
            <option value="female" <?= ($gender ?? '') === 'female' ? 'selected' : '' ?>>Женский</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Возраст</label>
        <input type="number" name="age" value="<?= htmlspecialchars($age ?? '') ?>" min="1" max="120" class="form-control">
    </div>
    
    <div class="form-group">
        <label>Сайт</label>
        <input type="url" name="website" value="<?= htmlspecialchars($website ?? '') ?>" placeholder="https://example.com" class="form-control">
    </div>
</div>
            
            <div class="form-actions">
                <button type="submit" class="save-btn">Сохранить изменения</button>
                <a href="logout.php" class="logout-btn">Выйти из аккаунта</a>
            </div>
        </form>
    </div>
</div>
<script>
document.querySelector('input[name="username"]').addEventListener('input', function(e) {
    if (this.value.length > 18) {
        this.value = this.value.substring(0, 18);
        alert('Имя пользователя не должно превышать 18 символов');
    }
});
</script>
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