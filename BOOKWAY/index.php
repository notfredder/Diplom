<?php
session_start(); 
require_once 'db.php';

// Получаем данные для всех категорий
$randomBooks = getRandomBooks(15);
$latestBooks = getLatestBooks(10);
$newBooks = getNewBooksByYear(10);
$astBooks = getBooksByPublisher('Издательство АСТ');

require_once 'header.php';
?>

<div class="books-container">
    <!-- Все книги (рандомный порядок) -->
    <div class="books-section">
        <div class="section-header">
            <a href="search.php?category=random" class="section-title">Все книги</a>
        </div>
        <div class="books-scroll-container">
            <button class="scroll-arrow left" onclick="scrollSection('allBooksScroll', -1)">←</button>
            <div class="books-scroll" id="allBooksScroll">
                <?php foreach ($randomBooks as $book): ?>
                    <?php include 'book_card.php'; ?>
                <?php endforeach; ?>
                <a href="search.php?category=random" class="view-all-link">
                    <div class="view-all-btn">
                        <span class="arrow">→</span>
                        <span class="text">Посмотреть все</span>
                    </div>
                </a>
            </div>
            <button class="scroll-arrow right" onclick="scrollSection('allBooksScroll', 1)">→</button>
        </div>
    </div>

    <!-- Последнее добавленное (по book_id) -->
    <div class="books-section">
        <div class="section-header">
            <a href="search.php?sort=latest" class="section-title">Последнее добавленное</a>
        </div>
        <div class="books-scroll-container">
            <button class="scroll-arrow left" onclick="scrollSection('latestBooksScroll', -1)">←</button>
            <div class="books-scroll" id="latestBooksScroll">
                <?php foreach ($latestBooks as $book): ?>
                    <?php include 'book_card.php'; ?>
                <?php endforeach; ?>
                <a href="search.php?sort=latest" class="view-all-link">
                    <div class="view-all-btn">
                        <span class="arrow">→</span>
                        <span class="text">Посмотреть все</span>
                    </div>
                </a>
            </div>
            <button class="scroll-arrow right" onclick="scrollSection('latestBooksScroll', 1)">→</button>
        </div>
    </div>

    <!-- Новое (по году издания) -->
    <div class="books-section">
        <div class="section-header">
            <a href="search.php?sort=new" class="section-title">Новое</a>
        </div>
        <div class="books-scroll-container">
            <button class="scroll-arrow left" onclick="scrollSection('newBooksScroll', -1)">←</button>
            <div class="books-scroll" id="newBooksScroll">
                <?php foreach ($newBooks as $book): ?>
                    <?php include 'book_card.php'; ?>
                <?php endforeach; ?>
                <a href="search.php?sort=new" class="view-all-link">
                    <div class="view-all-btn">
                        <span class="arrow">→</span>
                        <span class="text">Посмотреть все</span>
                    </div>
                </a>
            </div>
            <button class="scroll-arrow right" onclick="scrollSection('newBooksScroll', 1)">→</button>
        </div>
    </div>

    <!-- Издательство АСТ -->
    <div class="books-section">
        <div class="section-header">
            <a href="search.php?publisher=ast" class="section-title">Издательство АСТ</a>
        </div>
        <div class="books-scroll-container">
            <button class="scroll-arrow left" onclick="scrollSection('astBooksScroll', -1)">←</button>
            <div class="books-scroll" id="astBooksScroll">
                <?php foreach ($astBooks as $book): ?>
                    <?php include 'book_card.php'; ?>
                <?php endforeach; ?>
                <a href="search.php?publisher=ast" class="view-all-link">
                    <div class="view-all-btn">
                        <span class="arrow">→</span>
                        <span class="text">Посмотреть все</span>
                    </div>
                </a>
            </div>
            <button class="scroll-arrow right" onclick="scrollSection('astBooksScroll', 1)">→</button>
        </div>
    </div>
</div>

<script>
// Функция для скролла с фиксированным шагом (за 3 нажатия)
function scrollSection(scrollId, direction) {
    const container = document.getElementById(scrollId);
    const scrollAmount = container.clientWidth / 3;
    
    container.scrollBy({
        left: scrollAmount * direction,
        behavior: 'smooth'
    });
    
    // Обновляем видимость стрелок после скролла
    setTimeout(() => updateArrowVisibility(scrollId), 300);
}

// Обновление видимости стрелок
function updateArrowVisibility(scrollId) {
    const container = document.getElementById(scrollId);
    const leftArrow = container.parentElement.querySelector('.scroll-arrow.left');
    const rightArrow = container.parentElement.querySelector('.scroll-arrow.right');
    
    leftArrow.style.display = container.scrollLeft > 10 ? 'flex' : 'none';
    rightArrow.style.display = container.scrollLeft + container.clientWidth < container.scrollWidth - 10 ? 'flex' : 'none';
}

// Инициализация при загрузке
document.addEventListener('DOMContentLoaded', function() {
    ['allBooksScroll', 'latestBooksScroll', 'newBooksScroll', 'astBooksScroll'].forEach(id => {
        updateArrowVisibility(id);
        document.getElementById(id).addEventListener('scroll', () => updateArrowVisibility(id));
    });
});

// Обновление при изменении размера окна
window.addEventListener('resize', function() {
    ['allBooksScroll', 'latestBooksScroll', 'newBooksScroll', 'astBooksScroll'].forEach(id => {
        updateArrowVisibility(id);
    });
});
</script>

<?php
require_once 'footer.php';
?>