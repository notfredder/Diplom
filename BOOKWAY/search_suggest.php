<?php
require_once 'db.php';

header('Content-Type: application/json');

$query = $_GET['query'] ?? '';
$results = [];

if (!empty($query) && strlen($query) >= 2) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("SELECT book_id, book_name, autor, photo FROM book 
                           WHERE book_name LIKE ? OR autor LIKE ? 
                           ORDER BY book_name ASC
                           LIMIT 5");
    $searchTerm = "%$query%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
    
    $stmt->close();
    $conn->close();
}

echo json_encode($results);
?>