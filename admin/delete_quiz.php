<?php
ob_start(); // Start output buffering
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once 'admin_header.php';

// Get the quiz id from GET parameters
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
if (!$quiz_id) {
    header("Location: quiz_management.php");
    exit();
}

// Begin a transaction so that all deletions succeed or fail together
$pdo->beginTransaction();

try {
    // Fetch all questions for this quiz
    $stmtQuestions = $pdo->prepare("SELECT id FROM questions WHERE quiz_id = ?");
    $stmtQuestions->execute([$quiz_id]);
    $questions = $stmtQuestions->fetchAll(PDO::FETCH_ASSOC);

    // Delete options associated with each question
    $stmtDeleteOptions = $pdo->prepare("DELETE FROM options WHERE question_id = ?");
    foreach ($questions as $question) {
        $stmtDeleteOptions->execute([$question['id']]);
    }

    // Delete the questions for this quiz
    $stmtDeleteQuestions = $pdo->prepare("DELETE FROM questions WHERE quiz_id = ?");
    $stmtDeleteQuestions->execute([$quiz_id]);

    // Delete the quiz record itself from the quizzes table
    $stmtDeleteQuiz = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
    $stmtDeleteQuiz->execute([$quiz_id]);

    // Commit the transaction
    $pdo->commit();

    // Redirect back to the dashboard with a success message
    header("Location: quiz_management.php?message=Quiz+deleted+successfully");
    exit();
} catch (Exception $e) {
    // Roll back if any deletion fails
    $pdo->rollBack();
    echo "Error deleting quiz: " . $e->getMessage();
}
?>
