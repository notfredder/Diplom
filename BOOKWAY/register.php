<?php
ob_start();

require_once 'db.php';
session_start();

// Переносим всю логику обработки формы ПЕРЕД выводом HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Валидация
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Все поля обязательны для заполнения";
        header("Location: register.php");
        exit;
    }
    if (strlen($username) > 18) {
        $_SESSION['error'] = "Имя пользователя не должно превышать 18 символов";
        header("Location: register.php");
        exit;
    }
    
    // Проверка email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Некорректный формат email";
        header("Location: register.php");
        exit;
    }
    
    // Проверка существования пользователя
    if (getUserByEmail($email)) {
        $_SESSION['error'] = "Пользователь с этим email уже зарегистрирован";
        header("Location: register.php");
        exit;
    }
    // После проверки email добавьте:
    if (usernameExists($username)) {
        $_SESSION['error'] = "Имя пользователя уже занято";
        header("Location: register.php");
        exit;
    }
    
    // Регистрация
    if (registerUser($username, $email, $password)) {
            $_SESSION['success'] = "Регистрация успешна! Теперь войдите в систему.";
            $_SESSION['new_username'] = $username; // Сохраняем имя пользователя для отображения в форме входа
            header("Location: login.php");
        exit;
    } else {
        $_SESSION['error'] = "Ошибка регистрации. Возможно, имя пользователя или email уже заняты.";
        header("Location: register.php");
        exit;
    }
}

// Подключаем header.php только после всех возможных перенаправлений
require_once 'header.php';
?>

<div class="auth-wrapper">
    <div class="auth-container">
    <h2>Регистрация</h2>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <form method="POST">
        <input type="text" name="username" placeholder="Имя пользователя" required  maxlength="18"
               value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
        
        <input type="email" name="email" placeholder="Email" required
               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
        
        <input type="password" name="password" placeholder="Пароль" required>
        <button type="submit">Зарегистрироваться</button>
    </form>
    <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
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
<?php
require_once 'footer.php';
?>