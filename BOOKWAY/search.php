<?php
session_start();
require_once 'db.php';

$query = trim($_GET['query'] ?? '');
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? '';
$publisher = $_GET['publisher'] ?? '';

if (!empty($query)) {
    $conn = connectToDatabase();
    $searchTerm = "%$query%";
    $stmt = $conn->prepare("SELECT * FROM book WHERE book_name LIKE ? OR autor LIKE ?");
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $books = $result->fetch_all(MYSQLI_ASSOC);
    $title = "Результаты поиска: \"".htmlspecialchars($query)."\"";
    $stmt->close();
    $conn->close();
} elseif ($category === 'random') {
    $books = getRandomBooks(50);
    $title = "Все книги";
} elseif ($sort === 'latest') {
    $books = getLatestBooks(50);
    $title = "Последние добавленные книги";
} elseif ($sort === 'new') {
    $books = getNewBooksByYear(50);
    $title = "Новые книги (по году издания)";
} elseif ($publisher === 'ast') {
    $books = getBooksByPublisher('Издательство АСТ', 50);
    $title = "Книги издательства АСТ";
} else {
    $books = [];
    $title = "Результаты поиска";
}

require_once 'header.php';
?>

<div class="search-results-container">
    <h1><?= $title ?></h1>
    
    <?php if (!empty($books)): ?>
        <div class="books-grid">
            <?php foreach ($books as $book): ?>
                <div class="book-card">
                    <a href="book.php?id=<?= $book['book_id'] ?>">
                        <div class="book-image-container">
                            <?php 
                            $imageFile = htmlspecialchars(trim($book['photo']));
                            $imagePath = 'uploads/books/' . $imageFile;
                            $imageUrl = file_exists($imagePath) ? $imagePath : 'uploads/books/placeholder.png';
                            ?>
                            <img src="<?= $imageUrl ?>" 
                                 alt="<?= htmlspecialchars($book['book_name']) ?>" 
                                 class="book-cover">
                        </div>
                        <div class="book-info">
                            <div class="book-title"><?= htmlspecialchars($book['book_name'] ?? 'Без названия') ?></div>
                            <div class="book-autor"><?= htmlspecialchars($book['autor'] ?? 'Автор не указан') ?></div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="no-results">Книги не найдены</p>
    <?php endif; ?>
</div>

<?php
require_once 'footer.php';
?>