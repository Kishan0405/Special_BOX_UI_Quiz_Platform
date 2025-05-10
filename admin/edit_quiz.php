<?php
// admin/edit_quiz.php
require_once '../includes/database.php';
// If your sanitize function is only used for input cleanup and not for DB storage,
// you may remove or adjust its usage. Here, we rely on trim() to preserve special characters.
require_once 'admin_header.php';

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
if (!$quiz_id) {
    header("Location: admin_dashboard.php");
    exit();
}

$message = '';
$questions = [];

// Fetch existing questions for the quiz
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['questions'] as $question_id => $data) {
        // Use trim() to clean up extra whitespace without altering special characters.
        $question_text  = trim($data['question']);
        $explanation    = trim($data['explanation']);
        $difficulty     = trim($data['difficulty']);
        $points         = intval($data['points']);
        $hint           = trim($data['hint']);
        $tags           = trim($data['tags']);
        $correct_option = intval($data['correct_option']);
        $options        = [
            trim($data['option1']), 
            trim($data['option2']), 
            trim($data['option3']), 
            trim($data['option4'])
        ];

        // Update question record
        $stmt = $pdo->prepare("UPDATE questions SET question = ?, explanation = ?, difficulty = ?, points = ?, hint = ?, tags = ? WHERE id = ?");
        if ($stmt->execute([$question_text, $explanation, $difficulty, $points, $hint, $tags, $question_id])) {
            // Update answer options
            foreach ($options as $index => $option_text) {
                $is_correct = ($index + 1 == $correct_option) ? 1 : 0;
                // Assumes that the option IDs correspond to index+1 per question.
                $stmtOpt = $pdo->prepare("UPDATE options SET option_text = ?, is_correct = ? WHERE question_id = ? AND id = ?");
                $stmtOpt->execute([$option_text, $is_correct, $question_id, $index + 1]);
            }
        }
    }
    $message = "Questions updated successfully.";
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit Quiz - Special BOX UI Quiz</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin_add_question.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Optional inline styles for the preview modal */
        #previewModal {
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        #previewContent {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            width: 90%;
            max-width: 600px;
            border-radius: 5px;
            position: relative;
        }
        #closePreview {
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 28px;
            font-weight: bold;
        }
        /* Example styling for difficulty badge classes */
        .difficulty-easy {
            color: green;
            font-weight: bold;
        }
        .difficulty-medium {
            color: orange;
            font-weight: bold;
        }
        .difficulty-hard {
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>Edit Quiz Questions</h1>
            <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
        </header>
        <?php if (isset($message) && $message != ''): ?>
            <p class="success"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <?php foreach ($questions as $question): ?>
                <div class="question-block" id="question-block-<?php echo $question['id']; ?>">
                    <h3>Question ID: <?php echo htmlspecialchars($question['id']); ?></h3>
                    
                    <label for="question_<?php echo $question['id']; ?>">Question:</label>
                    <textarea name="questions[<?php echo $question['id']; ?>][question]" id="question_<?php echo $question['id']; ?>" required><?php echo htmlspecialchars($question['question']); ?></textarea>

                    <label for="explanation_<?php echo $question['id']; ?>">Explanation (Optional):</label>
                    <textarea name="questions[<?php echo $question['id']; ?>][explanation]" id="explanation_<?php echo $question['id']; ?>"><?php echo htmlspecialchars($question['explanation']); ?></textarea>

                    <label for="difficulty_<?php echo $question['id']; ?>">Difficulty:</label>
                    <select name="questions[<?php echo $question['id']; ?>][difficulty]" id="difficulty_<?php echo $question['id']; ?>" required>
                        <option value="Easy" <?php echo $question['difficulty'] == 'Easy' ? 'selected' : ''; ?>>Easy</option>
                        <option value="Medium" <?php echo $question['difficulty'] == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="Hard" <?php echo $question['difficulty'] == 'Hard' ? 'selected' : ''; ?>>Hard</option>
                    </select>

                    <label for="points_<?php echo $question['id']; ?>">Points:</label>
                    <input type="number" name="questions[<?php echo $question['id']; ?>][points]" id="points_<?php echo $question['id']; ?>" required min="1" value="<?php echo htmlspecialchars($question['points']); ?>">

                    <label for="hint_<?php echo $question['id']; ?>">Hint (Optional):</label>
                    <textarea name="questions[<?php echo $question['id']; ?>][hint]" id="hint_<?php echo $question['id']; ?>"><?php echo htmlspecialchars($question['hint']); ?></textarea>

                    <label for="tags_<?php echo $question['id']; ?>">Tags (Comma separated):</label>
                    <input type="text" name="questions[<?php echo $question['id']; ?>][tags]" id="tags_<?php echo $question['id']; ?>" value="<?php echo htmlspecialchars($question['tags']); ?>">

                    <h4>Answer Options</h4>
                    <?php
                    // Fetch options for the current question
                    $stmtOpt = $pdo->prepare("SELECT * FROM options WHERE question_id = ?");
                    $stmtOpt->execute([$question['id']]);
                    $options = $stmtOpt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($options as $index => $option): ?>
                        <label for="option_<?php echo $question['id'] . '_' . ($index + 1); ?>">Option <?php echo $index + 1; ?>:</label>
                        <input type="text" name="questions[<?php echo $question['id']; ?>][option<?php echo $index + 1; ?>]" id="option_<?php echo $question['id'] . '_' . ($index + 1); ?>" required value="<?php echo htmlspecialchars($option['option_text']); ?>">
                    <?php endforeach; ?>

                    <label for="correct_option_<?php echo $question['id']; ?>">Correct Option (1-4):</label>
                    <input type="number" name="questions[<?php echo $question['id']; ?>][correct_option]" id="correct_option_<?php echo $question['id']; ?>" min="1" max="4" required value="<?php echo array_search(1, array_column($options, 'is_correct')) + 1; ?>">

                    <!-- Preview button for this question -->
                    <button type="button" class="preview-button" data-question-id="<?php echo $question['id']; ?>">Preview Question</button>
                    
                    <hr>
                </div>
            <?php endforeach; ?>

            <div class="button-group">
                <input type="submit" value="Update Questions">
            </div>
        </form>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal" style="display:none;">
        <div id="previewContent">
            <span id="closePreview" style="cursor:pointer;">&times;</span>
            <h2>Question Preview</h2>
            <div id="previewDetails"></div>
        </div>
    </div>

    <script>
        // JavaScript for preview modal functionality
        document.querySelectorAll('.preview-button').forEach(function(button) {
            button.addEventListener('click', function() {
                var questionId = this.getAttribute('data-question-id');
                
                // Gather form data for the specific question block
                var question = document.getElementById('question_' + questionId).value;
                var explanation = document.getElementById('explanation_' + questionId).value;
                var difficulty = document.getElementById('difficulty_' + questionId).value;
                var points = document.getElementById('points_' + questionId).value;
                var hint = document.getElementById('hint_' + questionId).value;
                var tags = document.getElementById('tags_' + questionId).value;
                var option1 = document.getElementById('option_' + questionId + '_1').value;
                var option2 = document.getElementById('option_' + questionId + '_2').value;
                var option3 = document.getElementById('option_' + questionId + '_3').value;
                var option4 = document.getElementById('option_' + questionId + '_4').value;
                var correctOption = document.getElementById('correct_option_' + questionId).value;
                
                // Determine a CSS class for difficulty (optional styling)
                var difficultyClass = '';
                if (difficulty === 'Easy') {
                    difficultyClass = 'difficulty-easy';
                } else if (difficulty === 'Medium') {
                    difficultyClass = 'difficulty-medium';
                } else if (difficulty === 'Hard') {
                    difficultyClass = 'difficulty-hard';
                }
                
                // Build the preview content HTML
                var previewHTML = "<p><strong>Question:</strong> " + question + "</p>";
                previewHTML += "<p><strong>Difficulty:</strong> <span class='" + difficultyClass + "'>" + difficulty + "</span></p>";
                previewHTML += "<p><strong>Points:</strong> " + points + "</p>";
                
                if (hint) {
                    previewHTML += "<p><strong>Hint:</strong> " + hint + "</p>";
                }
                if (tags) {
                    previewHTML += "<p><strong>Tags:</strong> " + tags + "</p>";
                }
                
                previewHTML += "<p><strong>Answer Options:</strong></p>";
                previewHTML += "<ol>";
                previewHTML += "<li>" + option1 + (correctOption == 1 ? " <strong>(Correct)</strong>" : "") + "</li>";
                previewHTML += "<li>" + option2 + (correctOption == 2 ? " <strong>(Correct)</strong>" : "") + "</li>";
                previewHTML += "<li>" + option3 + (correctOption == 3 ? " <strong>(Correct)</strong>" : "") + "</li>";
                previewHTML += "<li>" + option4 + (correctOption == 4 ? " <strong>(Correct)</strong>" : "") + "</li>";
                previewHTML += "</ol>";
                
                if (explanation) {
                    previewHTML += "<p><strong>Explanation:</strong> " + explanation + "</p>";
                }
                
                // Set the preview modal's content and display it
                document.getElementById('previewDetails').innerHTML = previewHTML;
                document.getElementById('previewModal').style.display = "block";
            });
        });

        // Close the preview modal when clicking on the close button
        document.getElementById('closePreview').addEventListener('click', function() {
            document.getElementById('previewModal').style.display = "none";
        });

        // Close the preview modal when clicking outside the modal content
        window.addEventListener('click', function(event) {
            var modal = document.getElementById('previewModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        });
    </script>
</body>

</html>
