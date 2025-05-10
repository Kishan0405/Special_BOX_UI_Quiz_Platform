<?php
// validate_password.php
require_once 'includes/database.php';
require_once 'includes/auth.php';

$data = json_decode(file_get_contents('php://input'), true);
$quizId = $data['quiz_id'];
$enteredPassword = $data['password'];

if (!$quizId || !$enteredPassword) {
    echo json_encode(['success' => false, 'error' => 'Missing data']);
    exit();
}

$stmt = $pdo->prepare("SELECT quiz_password FROM quizzes WHERE id = ?");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();

if ($quiz && password_verify($enteredPassword, $quiz['quiz_password'])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Incorrect password']);
}
?>
