<?php
// search.php - AJAX search endpoint
if(session_status()===PHP_SESSION_NONE) session_start();
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

$conn = db_connect();
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if(strlen($query) < 2){
    echo json_encode([]);
    exit;
}

// Escape and search
$query = mysqli_real_escape_string($conn, $query);
$sql = "SELECT id, title, author, price, image FROM products 
        WHERE title LIKE '%{$query}%' 
        OR author LIKE '%{$query}%' 
        OR description LIKE '%{$query}%'
        ORDER BY title ASC
        LIMIT 10";

$res = mysqli_query($conn, $sql);
$results = [];

while($row = mysqli_fetch_assoc($res)){
    $results[] = [
        'id' => (int)$row['id'],
        'title' => $row['title'],
        'author' => $row['author'],
        'price' => $row['price'],
        'image' => !empty($row['image']) ? UPLOADS_URL . $row['image'] : BASE_URL . 'placeholder.png'
    ];
}

echo json_encode($results);
?>
