<?php
require_once '../includes/database.php';
require_once '../includes/auth.php';

requireLogin(); // Ensure user is logged in

header('Content-Type: application/json');

$email = isset($_GET['email']) ? trim($_GET['email']) : '';

if (strlen($email) < 3) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, username, email, display_name FROM users WHERE email LIKE ? LIMIT 10");
    $stmt->execute(["%$email%"]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>