<?php
// submit_quiz.php

require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
session_start();

// Ensure user is logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = "Please log in to submit a quiz.";
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: home.php");
    exit();
}

$quiz_id    = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;
$user_id    = $_SESSION['user_id'];
$time_taken = isset($_POST['time_taken']) ? intval($_POST['time_taken']) : 0;

// Verify quiz exists
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();
if (!$quiz) {
    $_SESSION['error'] = "Invalid quiz ID.";
    header("Location: home.php");
    exit();
}

// Check if part of an event
$stmt = $pdo->prepare("SELECT * FROM events WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$event = $stmt->fetch();

// Retrieve all questions
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();

if (empty($questions)) {
    $_SESSION['error'] = "No questions found for this quiz.";
    header("Location: home.php");
    exit();
}

// Calculate score
$score = 0;
foreach ($questions as $question) {
    $q_id = $question['id'];
    if (isset($_POST['question_' . $q_id])) {
        $selected_option = intval($_POST['question_' . $q_id]);
        $stmtOpt = $pdo->prepare("SELECT is_correct FROM options WHERE id = ?");
        $stmtOpt->execute([$selected_option]);
        $opt = $stmtOpt->fetch();
        if ($opt && $opt['is_correct']) {
            $score++;
        }
    }
}

$total    = count($questions);
$is_event = $event ? true : false;

try {
    if ($is_event) {
        // Be sure your event_quiz_results table has a `time_taken` INT column
        $stmt = $pdo->prepare("
            INSERT INTO event_quiz_results
                (user_id, event_id, score, total_questions, time_taken, submission_time)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $event['id'], $score, $total, $time_taken]);
    } else {
        // Be sure your quiz_attempts table has a `time_taken` INT column
        $stmt = $pdo->prepare("
            INSERT INTO quiz_attempts
                (user_id, quiz_id, score, total, time_taken, attempt_date)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $quiz_id, $score, $total, $time_taken]);
    }

    $_SESSION['quiz_result'] = [
        'quiz_id'  => $quiz_id,
        'score'    => $score,
        'total'    => $total,
        'is_event' => $is_event,
    ];

    header("Location: quiz_result.php");
    exit();
} catch (PDOException $e) {
    $_SESSION['error'] = "Error saving quiz attempt: " . $e->getMessage();
    header("Location: home.php");
    exit();
}
