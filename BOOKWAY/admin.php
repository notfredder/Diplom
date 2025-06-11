<?php
session_start();
require_once 'db.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit;
}

// Обработка формы добавления книги
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookData = [
        'book_name' => trim($_POST['book_name']),
        'autor' => trim($_POST['autor']),
        'about' => trim($_POST['about']),
        'ozon' => trim($_POST['ozon']),
        'yabooks' => trim($_POST['yabooks']),
        'age' => trim($_POST['age']),
        'copyright_holder' => trim($_POST['copyright_holder']),
        'year' => trim($_POST['year']),
        'publishing_house' => trim($_POST['publishing_house']),
        'translator' => trim($_POST['translator']),
        'pages' => trim($_POST['pages'])
    ];

    // Обработка загрузки изображения
    $photo = 'placeholder.png';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/books/';
        $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename);
        $photo = $filename;
    }

    // Добавление книги в БД
    $conn = connectToDatabase();
    $stmt = $conn->prepare("
        INSERT INTO book (
            book_name, autor, photo, about, ozon, yabooks, age, 
            copyright_holder, year, publishing_house, translator, pages
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param(
        "ssssssssssss",
        $bookData['book_name'],
        $bookData['autor'],
        $photo,
        $bookData['about'],
        $bookData['ozon'],
        $bookData['yabooks'],
        $bookData['age'],
        $bookData['copyright_holder'],
        $bookData['year'],
        $bookData['publishing_house'],
        $bookData['translator'],
        $bookData['pages']
    );
    
    if ($stmt->execute()) {
        $success = "Книга успешно добавлена!";
    } else {
        $error = "Ошибка при добавлении книги: " . $conn->error;
    }
    
    $conn->close();
}

require_once 'header.php';
?>
<div class="settings-page">
    <div class="settings-container">
<div class="admin-container">
    <h1>Панель администратора</h1>
    
    <div class="admin-tabs">
        <a href="admin.php" class="active">Добавить книгу</a>
        <a href="admin_books.php">Управление книгами</a>
        <a href="admin_users.php">Управление пользователями</a>
    </div>
    
    <div class="admin-content">
        <form method="POST" enctype="multipart/form-data" class="book-form">
            <?php if (isset($success)): ?>
                <div class="alert success"><?= $success ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert error"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <label>Название книги*</label>
                <input type="text" name="book_name" required>
            </div>
            
            <div class="form-group">
                <label>Автор*</label>
                <input type="text" name="autor" required>
            </div>
            
            <div class="form-group">
                <label>Обложка</label>
                <input type="file" name="photo" accept="image/*">
            </div>
            
            <div class="form-group">
                <label>Описание*</label>
                <textarea name="about" rows="5" required></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Ссылка на Ozon</label>
                    <input type="url" name="ozon">
                </div>
                
                <div class="form-group">
                    <label>Ссылка на Я.Книги</label>
                    <input type="url" name="yabooks">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Возрастные ограничения</label>
                    <input type="text" name="age" placeholder="16+">
                </div>
                
                <div class="form-group">
                    <label>Правообладатель</label>
                    <input type="text" name="copyright_holder">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Год издания</label>
                    <input type="text" name="year">
                </div>
                
                <div class="form-group">
                    <label>Издательство</label>
                    <input type="text" name="publishing_house">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Переводчик</label>
                    <input type="text" name="translator">
                </div>
                
                <div class="form-group">
                    <label>Количество страниц</label>
                    <input type="text" name="pages">
                </div>
            </div>
            
            <button type="submit" class="submit-btn">Добавить книгу</button>
        </form>
    </div>
</div>
</div>
</div>

<?php require_once 'footer.php'; ?>