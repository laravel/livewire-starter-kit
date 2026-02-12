<?php

// Simple database configuration
$host = '127.0.0.1';
$dbname = 'filetracker_IESD';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables if they don't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_name VARCHAR(255) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS documents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        category_name VARCHAR(255) NOT NULL,
        document_name VARCHAR(255) NOT NULL,
        url TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    )");
    
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle API requests
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch($method) {
    case 'GET':
        if ($path === '/api/categories') {
            $stmt = $pdo->query("SELECT * FROM categories ORDER BY category_name");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } elseif ($path === '/api/documents') {
            $categoryId = $_GET['category_id'] ?? null;
            $search = $_GET['search'] ?? null;
            
            $sql = "SELECT d.*, c.category_name FROM documents d JOIN categories c ON d.category_id = c.id";
            $params = [];
            
            if ($categoryId) {
                $sql .= " WHERE d.category_id = ?";
                $params[] = $categoryId;
            }
            
            if ($search) {
                $sql .= ($categoryId ? " AND" : " WHERE") . " (d.document_name LIKE ? OR d.category_name LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            $sql .= " ORDER BY d.document_name";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            // Serve the main page
            include 'index.html';
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($path === '/api/categories') {
            $stmt = $pdo->prepare("INSERT INTO categories (category_name) VALUES (?)");
            $stmt->execute([$input['category_name']]);
            echo json_encode(['id' => $pdo->lastInsertId(), 'category_name' => $input['category_name']]);
        } elseif ($path === '/api/documents') {
            $stmt = $pdo->prepare("INSERT INTO documents (category_id, category_name, document_name, url) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $input['category_id'],
                $input['category_name'],
                $input['document_name'],
                $input['url']
            ]);
            echo json_encode(['id' => $pdo->lastInsertId()]);
        }
        break;
        
    case 'DELETE':
        if (preg_match('/\/api\/documents\/(\d+)/', $path, $matches)) {
            $stmt = $pdo->prepare("DELETE FROM documents WHERE id = ?");
            $stmt->execute([$matches[1]]);
            echo json_encode(['success' => true]);
        }
        break;
}
?>
