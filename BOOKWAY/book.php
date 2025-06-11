<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

$bookId = $_GET['id'] ?? 0;
$book = getBookById($bookId);

require_once 'header.php';

if (!$book) {
    echo '<div class="book-not-found"><p>Книга не найдена</p></div>';
    require_once 'footer.php';
    exit;
}

$imageFile = htmlspecialchars(trim($book['photo']));
$imagePath = 'uploads/books/' . $imageFile;
$imageUrl = file_exists(__DIR__ . '/' . $imagePath) ? $imagePath : 'uploads/books/placeholder.png';

// Получаем текущий статус книги для пользователя
$currentStatus = isset($_SESSION['user_id']) ? getBookStatus($_SESSION['user_id'], $book['book_id']) : null;
$statusOptions = getStatusOptions($currentStatus);
?>

<div class="book-detail-page">
    <div class="book-content-wrapper">
        <div class="book-cover-container" data-book-id="<?= $book['book_id'] ?>">
            <img src="<?= $imageUrl ?>" alt="<?= htmlspecialchars($book['book_name']) ?>" class="book-cover-large">
            
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="book-status-container">
                <div class="status-dropdown">
                    <button class="status-btn <?= $statusOptions['class'] ?>">
                        <?= $statusOptions['label'] ?>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="status-menu" style="display: none;">
                        <?php foreach ($statusOptions['menu'] as $status => $label): ?>
                            <?php if ($status === 'remove'): ?>
                                <div class="menu-divider"></div>
                                <button class="status-menu-item danger" 
                                        data-status="remove">
                                    <?= $label ?>
                                </button>
                            <?php else: ?>
                                <button class="status-menu-item" 
                                        data-status="<?= $status ?>">
                                    <?= $label ?>
                                </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
       
        <div class="book-info-right">
            <div class="book-header">
                <h1><?= htmlspecialchars($book['book_name']) ?></h1>
                <p class="book-author">
                    <a href="search.php?query=<?= urlencode($book['autor']) ?>" class="author-link">
                        <?= htmlspecialchars($book['autor']) ?>
                    </a>
                </p>
            </div>
            
            <div class="book-actions">
                <?php if (!empty($book['yabooks'])): ?>
                <a href="<?= htmlspecialchars($book['yabooks']) ?>" class="action-btn" target="_blank">
                    <img src="../uploads/YAKNIGI.jpg" alt="Читать на Я.Книги">
                </a>
                <?php endif; ?>
                
                <?php if (!empty($book['ozon'])): ?>
                <a href="<?= htmlspecialchars($book['ozon']) ?>" class="action-btn" target="_blank">
                    <img src="../uploads/OZON.jpg" alt="Купить на OZON">
                </a>
                <?php endif; ?>
            </div>

            <?php if (!empty($book['about'])): ?>
            <div class="book-description">
                <div class="description-text" data-max-lines="13">
                    <?= nl2br(htmlspecialchars($book['about'])) ?>
                </div>
                <button class="show-more-btn">ещё</button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="book-meta-grid">
        <?php if (!empty($book['age'])): ?>
        <div class="meta-item">
            <span class="meta-label">Возрастные ограничения:</span>
            <span class="meta-value"><?= htmlspecialchars($book['age']) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($book['copyright_holder'])): ?>
        <div class="meta-item">
            <span class="meta-label">Правообладатель:</span>
            <span class="meta-value"><?= htmlspecialchars($book['copyright_holder']) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($book['year'])): ?>
        <div class="meta-item">
            <span class="meta-label">Год выхода издания:</span>
            <span class="meta-value"><?= htmlspecialchars($book['year']) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($book['publishing_house'])): ?>
        <div class="meta-item">
            <span class="meta-label">Издательство:</span>
            <span class="meta-value"><?= htmlspecialchars($book['publishing_house']) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($book['translator'])): ?>
        <div class="meta-item">
            <span class="meta-label">Переводчик:</span>
            <span class="meta-value"><?= htmlspecialchars($book['translator']) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($book['pages'])): ?>
        <div class="meta-item">
            <span class="meta-label">Бумажных страниц:</span>
            <span class="meta-value"><?= htmlspecialchars($book['pages']) ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработка клика по кнопке статуса
    document.querySelectorAll('.status-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = this.nextElementSibling;
            const isVisible = menu.style.display === 'block';
            
            // Закрываем все открытые меню
            document.querySelectorAll('.status-menu').forEach(m => {
                m.style.display = 'none';
            });
            
            // Открываем/закрываем текущее меню
            menu.style.display = isVisible ? 'none' : 'block';
        });
    });
    
    // Закрытие меню при клике вне его
    document.addEventListener('click', function() {
        document.querySelectorAll('.status-menu').forEach(menu => {
            menu.style.display = 'none';
        });
    });
    
    // Обработка выбора статуса
    document.querySelectorAll('.status-menu-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const bookId = this.closest('.book-cover-container').dataset.bookId;
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
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при обновлении статуса');
            });
        });
    });
});

// Обработка кнопки "ещё" для описания книги
document.addEventListener('DOMContentLoaded', function() {
    const descriptionTexts = document.querySelectorAll('.description-text[data-max-lines]');
    
    descriptionTexts.forEach(textBlock => {
        const lineHeight = parseInt(getComputedStyle(textBlock).lineHeight);
        const maxLines = parseInt(textBlock.dataset.maxLines);
        const maxHeight = lineHeight * maxLines;
        
        if (textBlock.scrollHeight > maxHeight) {
            let button = textBlock.nextElementSibling;
            if (!button || !button.classList.contains('show-more-btn')) {
                button = document.createElement('button');
                button.className = 'show-more-btn';
                button.textContent = 'ещё';
                textBlock.parentNode.insertBefore(button, textBlock.nextSibling);
            }
            
            button.style.display = 'inline-block';
            textBlock.style.maxHeight = maxHeight + 'px';
            textBlock.style.overflow = 'hidden';
            
            button.addEventListener('click', () => {
                textBlock.classList.toggle('expanded');
                if (textBlock.classList.contains('expanded')) {
                    textBlock.style.maxHeight = textBlock.scrollHeight + 'px';
                    button.textContent = 'свернуть';
                } else {
                    textBlock.style.maxHeight = maxHeight + 'px';
                    button.textContent = 'ещё';
                }
            });
        }
    });
});
</script>

<?php
require_once 'footer.php';
?>