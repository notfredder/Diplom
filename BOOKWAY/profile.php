<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

// Получаем username из GET-параметра
$requested_username = $_GET['username'] ?? $_SESSION['username'];

// Находим пользователя в базе данных
$user = getUserByUsername($requested_username);

if (!$user) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Пользователь не найден</title>
        <link rel="stylesheet" href="css/main.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <?php require_once 'header.php'; ?>
    </head>
    <body>
        <div class="user-not-found-container">
            <div class="user-not-found-message">
                <i class="fas fa-user-slash"></i>
                <h1>Пользователь "<?= htmlspecialchars($requested_username) ?>" не найден</h1>
                <p>Проверьте правильность написания или <a href="index.php">вернитесь на главную</a></p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}


// Определяем, это свой профиль или чужой
$is_own_profile = isset($_SESSION['user_id']) && $user['user_id'] == $_SESSION['user_id'];

// Получаем данные для отображения
$userId = $user['user_id'];
$gender = $user['gender'] ?? '';
$age = $user['age'] ?? '';
$website = $user['website'] ?? '';
$profile_header = $user['profile_header'] ?? '';
$created_at = $user['created_at'] ?? '';
$userBooks = getUserBooks($userId);

// Группировка книг по статусам
$booksByStatus = [
    'all' => $userBooks,
    'planned' => array_filter($userBooks, fn($book) => $book['status'] === 'planned'),
    'reading' => array_filter($userBooks, fn($book) => $book['status'] === 'reading'),
    'completed' => array_filter($userBooks, fn($book) => $book['status'] === 'completed'),
    'on_hold' => array_filter($userBooks, fn($book) => $book['status'] === 'on_hold'),
    'dropped' => array_filter($userBooks, fn($book) => $book['status'] === 'dropped')
];
?>

<html>
<head>
    <title><?= $is_own_profile ? 'Мой профиль' : 'Профиль ' . htmlspecialchars($user['username']) ?></title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php require_once 'header.php'; ?>
</head>
<body>
    
    <div class="profile-container">
        <div class="profile-header-container">
            <div class="profile-header-image">
                <?php 
                $headerImage = 'placeholder.jpg';
                if (!empty($profile_header) && file_exists('uploads/hats/' . $profile_header)) {
                    $headerImage = $profile_header;
                }
                ?>
                <img src="uploads/hats/<?= $headerImage ?>" alt="Шапка профиля">
            </div>
    
    <div class="profile-info-container">
        <div class="profile-avatar">
            <img src="uploads/avatars/<?= $user['avatar'] ?? 'default_avatar.jpg' ?>" alt="Аватар">
        </div>
        
        <div class="profile-info">
            <h1><?= htmlspecialchars($user['username']) ?></h1>
            
            <div class="profile-meta">
                <?php if (!empty($gender) || !empty($age) || !empty($website) || !empty($created_at)): ?>
                    <div class="profile-details">
                        <?php if (!empty($gender)): ?>
                            <span><?= $gender === 'male' ? 'муж.' : 'жен.' ?></span>
                        <?php endif; ?>
                        
                        <?php if (!empty($age)): ?>
                            <span>/ <?= $age ?> лет</span>
                        <?php endif; ?>
                        
                        <?php if (!empty($website)): ?>
                            <span>/ <a href="<?= htmlspecialchars($website) ?>" target="_blank"><?= parse_url($website, PHP_URL_HOST) ?></a> /</span>
                        <?php endif; ?>
                        
                        <?php if (!empty($created_at)): ?>
                            <span> на сайте с <?= date('d.m.Y', strtotime($created_at)) ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
        
        <div class="profile-sections">
            <section class="bookshelf">
                <h2><?= $is_own_profile ? 'Моя книжная полка' : 'Книжная полка ' . htmlspecialchars($user['username']) ?></h2>
                <div class="status-tabs">
                    <button class="tab active" data-status="all">Все</button>
                    <button class="tab" data-status="planned">Запланировано</button>
                    <button class="tab" data-status="reading">Читаю</button>
                    <button class="tab" data-status="completed">Прочитано</button>
                    <button class="tab" data-status="on_hold">Отложено</button>
                    <button class="tab" data-status="dropped">Брошено</button>
                </div>
                
                <div class="books-grid">
                    <?php if (!empty($userBooks)): ?>
                        <?php foreach ($userBooks as $book): ?>
                            <a href="book.php?id=<?= $book['book_id'] ?>" class="profile-book-card">
                                <div class="profile-book-image-container">
                                    <img class="profile-book-cover" src="<?= getBookCover($book) ?>" alt="<?= htmlspecialchars($book['book_name']) ?>">
                                </div>
                                <div class="profile-book-info">
                                    <div>
                                        <h3 class="profile-book-title"><?= htmlspecialchars($book['book_name']) ?></h3>
                                        <p class="profile-book-author"><?= htmlspecialchars($book['autor']) ?></p>
                                    </div>
                                    <?php if ($is_own_profile): ?>
                                        <span class="profile-book-status <?= $book['status'] ?? '' ?> dropdown">
                                            <?= getStatusLabel($book['status'] ?? '') ?>
                                            <div class="status-menu">
                                                <?php foreach (getStatusOptions($book['status'] ?? null)['menu'] as $status => $label): ?>
                                                    <button class="status-menu-item" 
                                                            data-book-id="<?= $book['book_id'] ?>"
                                                            data-status="<?= $status ?>">
                                                        <?= $label ?>
                                                    </button>
                                                <?php endforeach; ?>
                                            </div>
                                        </span>
                                    <?php else: ?>
                                        <span class="profile-book-status <?= $book['status'] ?? '' ?>">
                                            <?= getStatusLabel($book['status'] ?? '') ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-message"><?= $is_own_profile ? 'Ваша книжная полка пуста' : 'Книжная полка пуста' ?></p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <?php if ($is_own_profile): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Обработка изменения статуса в профиле
        document.querySelectorAll('.profile-book-status .status-menu-item').forEach(item => {
            item.addEventListener('click', function() {
                const bookId = this.dataset.bookId;
                const status = this.dataset.status;
                
                fetch('update_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        book_id: bookId,
                        status: status
                    })
                }).then(() => location.reload());
            });
        });
        
        // Обработка клика по статусу книги (открытие меню)
        document.querySelectorAll('.profile-book-status').forEach(statusEl => {
            statusEl.addEventListener('click', function(e) {
                e.stopPropagation();
                const menu = this.querySelector('.status-menu');
                menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
            });
        });
        
        // Закрытие меню при клике вне его
        document.addEventListener('click', function() {
            document.querySelectorAll('.status-menu').forEach(menu => {
                menu.style.display = 'none';
            });
        });

        // Фильтрация по статусам
        document.querySelectorAll('.status-tabs .tab').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const status = this.dataset.status;
                
                document.querySelectorAll('.status-tabs .tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                document.querySelectorAll('.profile-book-card').forEach(card => {
                    const cardStatus = card.querySelector('.profile-book-status').classList.value;
                    
                    if (status === 'all' || cardStatus.includes(status)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    });
    </script>
    <?php endif; ?>
</body>
</html>
<?php
require_once 'footer.php';
?>