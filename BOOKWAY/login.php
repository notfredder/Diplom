<?php
ob_start();
require_once 'db.php';
session_start();

// Перенесите проверку авторизации ПЕРЕД подключением header.php
if (isset($_SESSION['user_id'])) {
    header("Location: profile.php?username=" . urlencode($_SESSION['username']));
    exit;
}

require_once 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (loginUser($email, $password)) {
        $user = getUserByEmail($email);
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['avatar'] = $user['avatar'];
        $_SESSION['is_admin'] = $user['is_admin']; 
        $_SESSION['created_at'] = $user['created_at'];
        $_SESSION['gender'] = $user['gender'] ?? null;
        $_SESSION['age'] = $user['age'] ?? null;
        $_SESSION['website'] = $user['website'] ?? null;
        $_SESSION['profile_header'] = $user['profile_header'] ?? null;
        
        header("Location: profile.php?username=" . urlencode($_SESSION['username']));
        exit;
    } else {
        $_SESSION['error'] = "Неверный email или пароль";
        header("Location: login.php");
        exit;
    }
}
?>

<!-- Уберите DOCTYPE, html, head и body - они уже в header.php -->
<div class="auth-wrapper">
    <div class="auth-container">
    <h2>Вход</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert error"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required 
        value="<?= isset($_SESSION['new_username']) ? htmlspecialchars($_SESSION['new_username']) : '' ?>">
            <?php if (isset($_SESSION['new_username'])) unset($_SESSION['new_username']); ?>
        <input type="password" name="password" placeholder="Пароль" required>
        <button type="submit">Войти</button>
    </form>
    <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
    </div>
</div>
<?php
require_once 'footer.php';
?>