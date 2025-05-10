<?php
// take_quiz.php

require_once 'includes/database.php';
require_once 'includes/functions.php';

session_start();

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
if (!$quiz_id) {
    header("Location: home.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();
if (!$quiz) {
    echo "Quiz not found.";
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Shuffle questions
shuffle($questions);

foreach ($questions as &$question) {
    $stmtOpt = $pdo->prepare("SELECT * FROM options WHERE question_id = ?");
    $stmtOpt->execute([$question['id']]);
    $options = $stmtOpt->fetchAll(PDO::FETCH_ASSOC);
    // Shuffle options
    shuffle($options);
    $question['options'] = $options;
}
unset($question);

$total_questions   = count($questions);
$is_offline_mode   = ($quiz['quiz_mode'] === 'offline_sequential' || $quiz['quiz_mode'] === 'offline_all_at_once');
$submit_button_text = $is_offline_mode ? 'Check Answers' : 'Submit Quiz';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($quiz['title']); ?> — Take Quiz</title>
    <link rel="stylesheet" href="css/take_quiz.css">
    <script>
        // --- NEW: track start time ---
        window.quizStartTime = Date.now();

        const quizConfig = {
            quizId: <?php echo $quiz_id; ?>,
            quizMode: '<?php echo $quiz['quiz_mode']; ?>',
            timeLimit: <?php echo $quiz['time_limit']; ?>,
            totalQuestions: <?php echo $total_questions; ?>,
            isOfflineMode: <?php echo $is_offline_mode ? 'true' : 'false'; ?>
        };
        let securityViolationDetected = false;
    </script>
</head>

<body oncontextmenu="return false;" oncopy="return false;" oncut="return false;" onpaste="return false;">
    <div class="container">
        <header>
            <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
            <p><?php echo htmlspecialchars($quiz['description']); ?></p>
            <?php if ($quiz['time_limit'] > 0): ?>
                <div id="timer">Time Left: <span id="time"><?php echo $quiz['time_limit']; ?></span> seconds</div>
            <?php else: ?>
                <div id="timer">No time limit</div>
            <?php endif; ?>
        </header>

        <div class="mode-toggle" style="display: <?php echo ($quiz['quiz_mode'] === 'both') ? 'flex' : 'none'; ?>;">
            <button class="btn active" id="singleBtn" onclick="toggleMode('single')">Single Page</button>
            <button class="btn" id="stepBtn" onclick="toggleMode('step')">Step-by-Step</button>
        </div>

        <div class="quiz-container">
            <div class="questions-panel">
                <h3>Questions</h3>
                <div class="question-nav">
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="question-number"
                            data-index="<?php echo $index; ?>"
                            data-qid="<?php echo $question['id']; ?>"
                            onclick="showQuestion(<?php echo $index; ?>)">
                            <?php echo $index + 1; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="quiz-controls" style="display: <?php echo ($quiz['quiz_mode'] === 'both' || $quiz['quiz_mode'] === 'online_sequential' || $quiz['quiz_mode'] === 'offline_sequential') ? 'flex' : 'none'; ?>;">
                    <button class="btn" id="prevBtn" onclick="previousQuestion()" disabled>Previous</button>
                    <button class="btn" id="nextBtn" onclick="nextQuestion()">Next</button>
                </div>
            </div>

            <form method="POST" action="submit_quiz.php" id="quizForm" onsubmit="return handleFormSubmit(event);">
                <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                <!-- NEW hidden field for time taken (in seconds) -->
                <input type="hidden" name="time_taken" id="timeTakenInput" value="0">

                <?php foreach ($questions as $index => $question): ?>
                    <div class="question-wrapper" data-index="<?php echo $index; ?>" data-qid="<?php echo $question['id']; ?>">
                        <div class="question-header">
                            <h3>Q<?php echo $index + 1; ?>. <?php echo htmlspecialchars($question['question']); ?></h3>
                            <button type="button" class="flag-btn" data-index="<?php echo $index; ?>" onclick="toggleFlag(<?php echo $index; ?>)">⚑</button>
                        </div>
                        <?php if ($is_offline_mode && !empty($question['hint'])): ?>
                            <div class="hint"><strong>Hint:</strong> <?php echo htmlspecialchars($question['hint']); ?></div>
                        <?php endif; ?>
                        <div class="options-container">
                            <?php foreach ($question['options'] as $option): ?>
                                <label class="option">
                                    <input
                                        type="radio"
                                        name="question_<?php echo $question['id']; ?>"
                                        value="<?php echo $option['id']; ?>"
                                        onchange="updateAnswered(<?php echo $index; ?>)"
                                        data-question-index="<?php echo $index; ?>"
                                        data-is-correct="<?php echo $option['is_correct'] ? 1 : 0; ?>">
                                    <?php echo htmlspecialchars($option['option_text']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (!empty($question['explanation'])): ?>
                            <div class="explanation" style="display: none;">
                                <strong>Explanation:</strong> <?php echo htmlspecialchars($question['explanation']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <div class="form-footer">
                    <button type="button" class="btn" id="finalSubmitBtn" onclick="handleFinalAction()" disabled>
                        <?php echo $submit_button_text; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        let currentMode = 'single';
        let currentQuestionIndex = 0;
        let flaggedQuestions = new Set();
        let answeredQuestions = new Set();
        let timer;
        let timeLeft = quizConfig.timeLimit;

        function handleSecurityViolation(reason) {
            if (securityViolationDetected) return;
            securityViolationDetected = true;
            clearInterval(timer);
            document.getElementById('timer').innerHTML = '<span style="color:white;">Quiz Locked</span>';
            alert(`Security Violation: ${reason}. Quiz locked.`);
            document.querySelectorAll('input, button').forEach(el => el.disabled = true);
        }

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) handleSecurityViolation("Tab switched");
        });

        document.addEventListener('keydown', (e) => {
            if (securityViolationDetected) {
                e.preventDefault();
                return;
            }
            if (e.key === "F12" || e.keyCode === 123 || (e.ctrlKey && e.shiftKey && ['I', 'J', 'C'].includes(e.key.toUpperCase())) || (e.ctrlKey && e.key.toUpperCase() === 'P')) {
                handleSecurityViolation("Unauthorized key press");
                e.preventDefault();
            }
        });

        window.addEventListener('blur', () => {
            if (!securityViolationDetected) handleSecurityViolation("Window focus lost");
        });

        function initializeQuiz() {
            if (quizConfig.quizMode.includes('sequential')) {
                currentMode = 'step';
                document.querySelector('.mode-toggle').style.display = 'none';
                document.querySelector('.quiz-controls').style.display = 'flex';
            } else if (quizConfig.quizMode.includes('all_at_once')) {
                currentMode = 'single';
                document.querySelector('.mode-toggle').style.display = 'none';
                document.querySelector('.quiz-controls').style.display = 'none';
            } else if (quizConfig.quizMode === 'both') {
                toggleMode(currentMode);
            }
            document.querySelectorAll('.question-wrapper').forEach((q, i) => {
                q.style.display = (currentMode === 'single' || i === currentQuestionIndex) ? 'block' : 'none';
            });
            updateNavigationButtons();
            updateQuestionNavigation();
            checkAllAnswered();
        }

        function toggleMode(mode) {
            if (quizConfig.quizMode !== 'both' || securityViolationDetected) return;
            currentMode = mode;
            document.getElementById('singleBtn').classList.toggle('active', currentMode === 'single');
            document.getElementById('stepBtn').classList.toggle('active', currentMode === 'step');
            document.querySelectorAll('.question-wrapper').forEach((q, i) => {
                q.style.display = (currentMode === 'single' || i === currentQuestionIndex) ? 'block' : 'none';
            });
            document.querySelector('.quiz-controls').style.display = (currentMode === 'step') ? 'flex' : 'none';
            updateNavigationButtons();
            if (currentMode === 'step') showQuestion(currentQuestionIndex);
        }

        function showQuestion(index) {
            if (index < 0 || index >= quizConfig.totalQuestions || securityViolationDetected) return;
            currentQuestionIndex = index;
            if (currentMode === 'step') {
                document.querySelectorAll('.question-wrapper').forEach((q, i) => {
                    q.style.display = i === index ? 'block' : 'none';
                });
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            } else {
                document.querySelector(`.question-wrapper[data-index="${index}"]`).scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
            updateNavigationButtons();
            updateQuestionNavigation();
        }

        function previousQuestion() {
            if (currentQuestionIndex > 0 && currentMode === 'step') showQuestion(currentQuestionIndex - 1);
        }

        function nextQuestion() {
            if (currentQuestionIndex < quizConfig.totalQuestions - 1 && currentMode === 'step') showQuestion(currentQuestionIndex + 1);
        }

        function updateNavigationButtons() {
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            if (prevBtn && nextBtn) {
                prevBtn.disabled = currentQuestionIndex === 0 || securityViolationDetected;
                nextBtn.disabled = currentQuestionIndex === quizConfig.totalQuestions - 1 || securityViolationDetected;
            }
        }

        function toggleFlag(index) {
            if (securityViolationDetected) return;
            const btn = document.querySelector(`.flag-btn[data-index="${index}"]`);
            const qNav = document.querySelector(`.question-number[data-index="${index}"]`);
            if (flaggedQuestions.has(index)) {
                flaggedQuestions.delete(index);
                btn.classList.remove('active');
                qNav.classList.remove('flagged');
            } else {
                flaggedQuestions.add(index);
                btn.classList.add('active');
                qNav.classList.add('flagged');
            }
        }

        function updateAnswered(index) {
            if (securityViolationDetected) return;
            if (!answeredQuestions.has(index)) {
                answeredQuestions.add(index);
                document.querySelector(`.question-number[data-index="${index}"]`).classList.add('answered');
                checkAllAnswered();
            }
        }

        function checkAllAnswered() {
            const finalButton = document.getElementById('finalSubmitBtn');
            finalButton.disabled = answeredQuestions.size !== quizConfig.totalQuestions || securityViolationDetected;
        }

        function updateQuestionNavigation() {
            document.querySelectorAll('.question-number').forEach(num => {
                num.classList.toggle('current', parseInt(num.getAttribute('data-index')) === currentQuestionIndex && currentMode === 'step');
            });
        }

        function startTimer() {
            const timerEl = document.getElementById("time");
            if (!timerEl || quizConfig.timeLimit <= 0) {
                if (document.getElementById('timer')) document.getElementById('timer').style.display = 'none';
                return;
            }
            timer = setInterval(() => {
                if (securityViolationDetected) {
                    clearInterval(timer);
                    return;
                }
                timeLeft--;
                timerEl.textContent = timeLeft;
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    timerEl.textContent = "0";
                    alert("Time's up! Submitting quiz.");
                    document.getElementById('quizForm').submit();
                } else if (timeLeft < 60) {
                    timerEl.parentElement.style.color = 'red';
                    timerEl.parentElement.style.fontWeight = 'bold';
                }
            }, 1000);
        }

        function handleFinalAction() {
            if (securityViolationDetected || answeredQuestions.size !== quizConfig.totalQuestions) {
                alert(`Please answer all ${quizConfig.totalQuestions} questions.`);
                return;
            }
            if (quizConfig.isOfflineMode) {
                showResults();
            } else {
                // this will trigger onsubmit
                document.getElementById('quizForm').requestSubmit();
            }
        }

        function handleFormSubmit(event) {
            if (securityViolationDetected || (!quizConfig.isOfflineMode && answeredQuestions.size !== quizConfig.totalQuestions)) {
                alert("Cannot submit. Please answer all questions or resolve security violation.");
                event.preventDefault();
                return false;
            }

            // --- NEW: compute and set time_taken ---
            const elapsedMs = Date.now() - window.quizStartTime;
            const elapsedSec = Math.round(elapsedMs / 1000);
            document.getElementById('timeTakenInput').value = elapsedSec;

            clearInterval(timer);
            document.getElementById('finalSubmitBtn').disabled = true;
            document.getElementById('finalSubmitBtn').textContent = 'Submitting…';

            return true;
        }

        function showResults() {
            if (!quizConfig.isOfflineMode || securityViolationDetected) return;
            clearInterval(timer);
            let score = 0;
            document.querySelectorAll('.question-wrapper').forEach(qWrapper => {
                const selectedInput = qWrapper.querySelector('input[type="radio"]:checked');
                const correctInput = qWrapper.querySelector('input[data-is-correct="1"]');
                const options = qWrapper.querySelectorAll('.option');
                qWrapper.querySelectorAll('input[type="radio"]').forEach(input => input.disabled = true);
                options.forEach(label => label.classList.remove('correct', 'incorrect'));
                if (selectedInput) {
                    if (selectedInput.dataset.isCorrect === '1') {
                        selectedInput.parentElement.classList.add('correct');
                        score++;
                    } else {
                        selectedInput.parentElement.classList.add('incorrect');
                        if (correctInput) correctInput.parentElement.classList.add('correct');
                    }
                } else if (correctInput) {
                    correctInput.parentElement.classList.add('correct');
                }
                const explanationDiv = qWrapper.querySelector('.explanation');
                if (explanationDiv) explanationDiv.style.display = 'block';
            });
            const checkButton = document.getElementById('finalSubmitBtn');
            checkButton.textContent = `Score: ${score} / ${quizConfig.totalQuestions}`;
            checkButton.disabled = true;
            alert(`Quiz finished! Your score: ${score} / ${quizConfig.totalQuestions}`);
        }

        document.addEventListener('DOMContentLoaded', () => {
            initializeQuiz();
            if (quizConfig.timeLimit > 0) startTimer();
            checkAllAnswered();
        });
    </script>
</body>

</html>