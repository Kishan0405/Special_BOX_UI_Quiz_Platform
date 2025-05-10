<?php
// check_questions.php
require_once 'includes/database.php';

$quizId = $_GET['quiz_id'] ?? null;

if ($quizId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE quiz_id = ?");
    $stmt->execute([$quizId]);
    $questionCount = $stmt->fetchColumn();

    echo json_encode(['has_questions' => $questionCount > 0]);
} else {
    echo json_encode(['has_questions' => false]);
}
?>
