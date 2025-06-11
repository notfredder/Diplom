<?php
session_start();
require_once 'db.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit;
}

$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получение данных книги
$conn = connectToDatabase();
$stmt = $conn->prepare("SELECT * FROM book WHERE book_id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();

if (!$book) {
    header('Location: admin_books.php');
    exit;
}

// Обработка формы
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
    $photo = $book['photo'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/books/';
        $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename);
        $photo = $filename;
        
        // Удаляем старое изображение, если оно не placeholder.png
        if ($book['photo'] !== 'placeholder.png') {
            @unlink($uploadDir . $book['photo']);
        }
    }

    // Обновление книги в БД
    $stmt = $conn->prepare("
        UPDATE book SET 
            book_name = ?, 
            autor = ?, 
            photo = ?, 
            about = ?, 
            ozon = ?, 
            yabooks = ?, 
            age = ?, 
            copyright_holder = ?, 
            year = ?, 
            publishing_house = ?, 
            translator = ?, 
            pages = ?
        WHERE book_id = ?
    ");
    
    $stmt->bind_param(
        "ssssssssssssi",
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
        $bookData['pages'],
        $book_id
    );
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Книга успешно обновлена!";
        header("Location: admin_books.php");
        exit;
    } else {
        $error = "Ошибка при обновлении книги: " . $conn->error;
    }
    
    $conn->close();
}

require_once 'header.php';
?>

<div class="settings-page">
    <div class="settings-container">
        <h1>Редактирование книги</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Название книги*</label>
                <input type="text" name="book_name" value="<?= htmlspecialchars($book['book_name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Автор*</label>
                <input type="text" name="autor" value="<?= htmlspecialchars($book['autor']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Обложка</label>
                <div class="avatar-preview-container">
                    <img id="bookPreview" src="uploads/books/<?= $book['photo'] ?>" 
                         alt="Текущая обложка" class="current-header" style="max-height: 200px;">
                    <label for="bookInput" class="upload-btn">
                        <i class="fas fa-image"></i> Выбрать изображение
                    </label>
                    <input type="file" id="bookInput" name="photo" accept="image/*">
                    <p class="hint">Рекомендуемый размер: 500×700 px</p>
                </div>
            </div>
            
            <div class="form-group">
                <label>Описание*</label>
                <textarea name="about" rows="5" required><?= htmlspecialchars($book['about']) ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Ссылка на Ozon</label>
                    <input type="url" name="ozon" value="<?= htmlspecialchars($book['ozon']) ?>">
                </div>
                
                <div class="form-group">
                    <label>Ссылка на Я.Книги</label>
                    <input type="url" name="yabooks" value="<?= htmlspecialchars($book['yabooks']) ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Возрастные ограничения</label>
                    <input type="text" name="age" value="<?= htmlspecialchars($book['age']) ?>" placeholder="16+">
                </div>
                
                <div class="form-group">
                    <label>Правообладатель</label>
                    <input type="text" name="copyright_holder" value="<?= htmlspecialchars($book['copyright_holder']) ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Год издания</label>
                    <input type="text" name="year" value="<?= htmlspecialchars($book['year']) ?>">
                </div>
                
                <div class="form-group">
                    <label>Издательство</label>
                    <input type="text" name="publishing_house" value="<?= htmlspecialchars($book['publishing_house']) ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Переводчик</label>
                    <input type="text" name="translator" value="<?= htmlspecialchars($book['translator']) ?>">
                </div>
                
                <div class="form-group">
                    <label>Количество страниц</label>
                    <input type="text" name="pages" value="<?= htmlspecialchars($book['pages']) ?>">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="save-btn">Сохранить изменения</button>
                <a href="admin_books.php" class="logout-btn">Отмена</a>
            </div>
        </form>
    </div>
</div>

<script>
// Предпросмотр обложки книги
document.getElementById('bookInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const preview = document.getElementById('bookPreview');
            preview.src = event.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php require_once 'footer.php'; ?>