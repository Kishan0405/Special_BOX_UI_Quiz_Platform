<?php

require_once 'includes/database.php'; // Provides $conn and/or $pdo
require_once 'includes/auth.php';     // Handles authentication and sets $_SESSION['user_id']
include 'header.php';                 // Includes headers and potentially starts session

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$user_id = $_SESSION['user_id'] ?? null;

$result = null;
$attemptFetched = false;

// Priority 1: Check if result exists in session
if (isset($_SESSION['quiz_result'])) {
    $result = $_SESSION['quiz_result'];
    unset($_SESSION['quiz_result']);

    // If event quiz, insert into event_quiz_results table
    if (isset($result['is_event']) && $result['is_event'] === true && isset($result['event_id']) && $user_id) {
        $userId = $user_id;
        $eventId = $result['event_id'];
        $score = $result['score'];
        $totalQuestions = $result['total'];
        $timeTaken = $result['timeTaken'] ?? null;

        $sql = "INSERT INTO event_quiz_results (user_id, event_id, score, total_questions, time_taken)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                score = VALUES(score),
                time_taken = VALUES(time_taken),
                submission_time = CURRENT_TIMESTAMP";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iiiss", $userId, $eventId, $score, $totalQuestions, $timeTaken);
            $stmt->execute();
            $stmt->close();
        }
    }
}
// Priority 2: Fallback to fetching latest attempt from DB if no session data
elseif ($quiz_id && $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM quiz_attempts WHERE quiz_id = ? AND user_id = ? ORDER BY attempt_date DESC LIMIT 1");
    $stmt->execute([$quiz_id, $user_id]);
    $attempt = $stmt->fetch();

    if ($attempt) {
        $result = [
            'score' => $attempt['score'],
            'total' => $attempt['total'],
            'is_event' => $attempt['is_event'],
            'timeTaken' => $attempt['time_taken'] ?? null,
            'answers' => [] // No detailed answers available in this case
        ];
        $attemptFetched = true;
    } else {
        echo "<h2>No quiz attempt found.</h2>";
        exit();
    }
} else {
    // No result available and no quiz_id
    header("Location: home.php");
    exit();
}

// Calculate percentage
$percentage = ($result['score'] / $result['total']) * 100;

// Determine result message
function getResultMessage($score, $total) {
    $percent = ($score / $total) * 100;
    if ($percent >= 90) return "Excellent! You're a quiz master!";
    elseif ($percent >= 75) return "Great job! You've done very well!";
    elseif ($percent >= 50) return "Good effort! Keep practicing!";
    else return "Don't worry! Practice makes perfect!";
}

$resultMessage = getResultMessage($result['score'], $result['total']);
$hasDetailedResults = isset($result['answers']) && !empty($result['answers']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Result - Special BOX UI Quiz</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/quiz_result.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container result-container">
    <header class="result-header">
        <h1><i class="fas fa-award"></i> Quiz Result</h1>
    </header>

    <div class="result-card">
        <div class="result-summary">
            <h2><?php echo $resultMessage; ?></h2>
            <div class="score-display">
                <div class="score-circle">
                    <span class="score-number"><?php echo $result['score']; ?></span>
                    <span class="score-total">/ <?php echo $result['total']; ?></span>
                </div>
            </div>
            <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                </div>
                <div class="progress-label"><?php echo round($percentage); ?>% Correct</div>
            </div>
            <?php if (!empty($result['timeTaken'])): ?>
                <div class="time-taken"><i class="fas fa-clock"></i> Time taken: <?php echo $result['timeTaken']; ?></div>
            <?php endif; ?>
            <div class="quiz-type">
                <?php if (!empty($result['is_event'])): ?>
                    <p>This attempt was part of an <strong>event quiz</strong>.</p>
                <?php else: ?>
                    <p>This was a <strong>regular quiz attempt</strong>.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($hasDetailedResults): ?>
        <div class="detailed-results">
            <h3>Question Review</h3>
            <div class="results-list">
                <?php foreach($result['answers'] as $index => $answer): ?>
                    <div class="result-item <?php echo $answer['correct'] ? 'correct' : 'incorrect'; ?>">
                        <div class="question-number">Q<?php echo $index + 1; ?></div>
                        <div class="result-details">
                            <p class="question-text"><?php echo htmlspecialchars($answer['question']); ?></p>
                            <p class="answer-info">
                                <?php if($answer['correct']): ?>
                                    <i class="fas fa-check-circle"></i> Correct
                                <?php else: ?>
                                    <i class="fas fa-times-circle"></i> Incorrect
                                    <span class="correct-answer">Correct answer: <?php echo htmlspecialchars($answer['correctAnswer']); ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="result-actions">
            <?php if (empty($result['is_event'])): ?>
                <a href="quiz.php" class="btn primary-btn"><i class="fas fa-redo"></i> Try Again</a>
            <?php endif; ?>
            <a href="home.php" class="btn secondary-btn"><i class="fas fa-home"></i> Back to Home</a>
        </div>
    </div>

    <div class="share-section">
        <h3>Share Your Result</h3>
        <div class="social-buttons">
            <a href="#" class="social-btn facebook" onclick="shareResult('facebook')"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-btn twitter" onclick="shareResult('twitter')"><i class="fab fa-twitter"></i></a>
            <a href="#" class="social-btn whatsapp" onclick="shareResult('whatsapp')"><i class="fab fa-whatsapp"></i></a>
        </div>
    </div>

    <footer class="result-footer">
        <p>&copy; <?php echo date('Y'); ?> Special BOX UI Quiz. All rights reserved.</p>
    </footer>
</div>

<script>
function shareResult(platform) {
    const score = "<?php echo $result['score']; ?>";
    const total = "<?php echo $result['total']; ?>";
    const message = `I scored ${score} out of ${total} on the Special BOX UI Quiz!`;
    const url = window.location.href;

    let shareUrl = '';
    switch(platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}&quote=${encodeURIComponent(message)}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(message)}&url=${encodeURIComponent(url)}`;
            break;
        case 'whatsapp':
            shareUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(message + ' ' + url)}`;
            break;
    }
    window.open(shareUrl, '_blank');
}
</script>
</body>
</html>
