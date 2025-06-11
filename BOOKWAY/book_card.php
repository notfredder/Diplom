<?php
$imageFile = htmlspecialchars(trim($book['photo']));
$imagePath = 'uploads/books/' . $imageFile;
$imageUrl = file_exists(__DIR__ . '/' . $imagePath) ? $imagePath : 'uploads/books/placeholder.png';
?>

<a href="book.php?id=<?= $book['book_id'] ?>" class="book-card">
    <div class="book-image-container">
        <img src="<?= $imageUrl ?>" 
             alt="<?= htmlspecialchars($book['book_name']) ?>" 
             class="book-cover">
    </div>
    <div class="book-info">
        <div class="book-title"><?= htmlspecialchars($book['book_name'] ?? 'Без названия') ?></div>
        <div class="book-autor"><?= htmlspecialchars($book['autor'] ?? 'Автор не указан') ?></div>
    </div>
</a>