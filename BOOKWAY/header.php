<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookWay - Каталог книг</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="icon" href="uploads/BW.ico" type="image/x-icon">
</head>
<body>
    <div id="page-wrapper">
    <div id="content">
    <header class="header">
        <div class="header__container">
            <div class="header__content">
                <!-- Логотип -->
                <a href="index.php" class="header__logo">
                    <img src="uploads/logo.png" alt="BookWay" class="logo-img">
                </a>

                <!-- Поиск -->
                <div class="header__search">
                    <form class="search-form" action="search.php" method="get">
                        <input type="text" 
                               name="query" 
                               id="searchInput" 
                               class="search-input" 
                               placeholder="Поиск по книгам и авторам..." 
                               value="<?= isset($_GET['query']) ? htmlspecialchars($_GET['query']) : '' ?>" 
                               autocomplete="off">
                        <button type="submit" class="search-button">Найти</button>
                    </form>
                    <div class="search-results" id="searchResults"></div>
                </div>

                <!-- Пользовательские действия -->
                <div class="header__user-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="user-dropdown">
                            <a href="profile.php?username=<?= htmlspecialchars($_SESSION['username']) ?>" class="user-btn">
                                <img src="uploads/avatars/<?= $_SESSION['avatar'] ?? 'default_avatar.jpg' ?>" class="user-avatar">
                                <span><?= htmlspecialchars($_SESSION['username']) ?></span>
                            </a>
                            <div class="dropdown-menu">
                                <a href="settings.php">Настройки</a>
                                <a href="logout.php">Выйти</a>
                            </div>
                        </div>
                                    <?php if (isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                            <a href="admin.php" class="admin-btn">
                                <i class="fas fa-cog"></i> Админка
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php" class="login-btn">Войти</a>
                        <a href="register.php" class="register-btn">Регистрация</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
    </header>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');
        const searchContainer = document.querySelector('.header__search');
        
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }
            
            fetch(`search_suggest.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        searchResults.innerHTML = data.map(item => `
                            <div class="search-result-item" data-id="${item.book_id}">
                                <img src="uploads/books/${item.photo || 'placeholder.jpg'}" 
                                     class="search-result-image" 
                                     alt="${item.book_name}">
                                <div class="search-result-text">
                                    <div class="search-result-title">${item.book_name}</div>
                                    <div class="search-result-author">${item.autor}</div>
                                </div>
                            </div>
                        `).join('');
                        
                        document.querySelectorAll('.search-result-item').forEach(item => {
                            item.addEventListener('click', function() {
                                window.location.href = `book.php?id=${this.getAttribute('data-id')}`;
                            });
                        });
                        
                        searchResults.style.display = 'block';
                    } else {
                        searchResults.innerHTML = '<div class="search-result-item">Ничего не найдено</div>';
                        searchResults.style.display = 'block';
                    }
                });
        });
        
        document.addEventListener('click', function(e) {
            if (!searchContainer.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    });
    </script>