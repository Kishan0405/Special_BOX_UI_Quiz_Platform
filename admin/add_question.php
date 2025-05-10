<?php
// admin/add_question.php
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once 'admin_header.php';

// Ensure UTF-8 encoding
$pdo->exec("SET NAMES 'utf8mb4'");

// Sanitize input while preserving special characters
function sanitize_input($data) {
    return trim($data);
}

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
if (!$quiz_id) {
    header("Location: admin_dashboard.php");
    exit();
}

requireLogin();
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question_text = sanitize_input($_POST['question']);
    $option1 = sanitize_input($_POST['option1']);
    $option2 = sanitize_input($_POST['option2']);
    $option3 = sanitize_input($_POST['option3']);
    $option4 = sanitize_input($_POST['option4']);
    $correct_option = intval($_POST['correct_option']);

    $explanation = sanitize_input($_POST['explanation'] ?? '');
    $difficulty = sanitize_input($_POST['difficulty'] ?? 'Easy');
    $points = intval($_POST['points'] ?? 1);
    $hint = sanitize_input($_POST['hint'] ?? '');
    $tags = sanitize_input($_POST['tags'] ?? '');

    // Handle file upload
    $image = null;
    if (isset($_FILES['question_image']) && $_FILES['question_image']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = '../Uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $filename = time() . '_' . basename($_FILES['question_image']['name']);
        $targetFile = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['question_image']['tmp_name'], $targetFile)) {
            $image = $filename;
        }
    }

    // Insert question
    $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question, explanation, image, difficulty, points, hint, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$quiz_id, $question_text, $explanation, $image, $difficulty, $points, $hint, $tags])) {
        $question_id = $pdo->lastInsertId();
        $options = [$option1, $option2, $option3, $option4];
        for ($i = 0; $i < count($options); $i++) {
            $is_correct = ($i + 1 == $correct_option) ? 1 : 0;
            $stmtOpt = $pdo->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
            $stmtOpt->execute([$question_id, $options[$i], $is_correct]);
        }
        $message = "Question added successfully.";
    } else {
        $message = "Error adding question.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Question - Special BOX UI Quiz</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin_add_question.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .ai-generator {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .ai-generator label {
            font-weight: 600;
            color: #4a5568;
        }
        .ai-generator textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            min-height: 100px;
            resize: vertical;
        }
        .ai-generator textarea:focus {
            border-color: #4299e1;
            outline: none;
        }
        .ai-generator .button-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .ai-generator button {
            background-color: #3182ce;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            flex-grow: 1;
            min-width: 120px;
            text-align: center;
        }
        .ai-generator button:hover {
            background-color: #2c5282;
        }
        .ai-generator .ai-status {
            margin-top: 10px;
            padding: 12px 15px;
            border-radius: 5px;
            min-height: 20px;
            font-size: 14px;
            color: #4a5568;
        }
        @media (max-width: 767px) {
            .ai-generator {
                padding: 15px;
            }
            .ai-generator button {
                padding: 10px 12px;
                min-width: unset;
                flex-basis: 100%;
            }
            .ai-generator .button-group {
                flex-direction: column;
                gap: 8px;
            }
        }
        @media (max-width: 480px) {
            .ai-generator {
                padding: 10px;
            }
            .ai-generator textarea {
                padding: 10px;
            }
            .ai-generator button {
                padding: 8px 10px;
            }
            .ai-generator .ai-status {
                padding: 10px;
            }
        }
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .form-group {
            flex: 1;
            min-width: 150px;
        }
        .success {
            color: green;
            padding: 10px;
            background: #e6ffed;
            border-radius: 5px;
        }
        #previewModal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        #previewContent {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            max-width: 600px;
            width: 90%;
            position: relative;
        }
        #closePreview {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
        }
        .difficulty-badge {
            padding: 2px 8px;
            border-radius: 4px;
        }
        .difficulty-easy { background: #d4edda; color: #155724; }
        .difficulty-medium { background: #fff3cd; color: #856404; }
        .difficulty-hard { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Add Question to Quiz</h1>
            <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
        </header>
        <?php if ($message): ?>
            <p class="success"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <div class="ai-generator">
            <label for="ai_prompt">Generate Question with Special BOX AI Mini:</label>
            <textarea id="ai_prompt" placeholder="Enter a topic for your question (e.g., 'Photosynthesis basics', 'JavaScript loops', 'World capitals')..."></textarea>
            <div class="button-group">
                <button type="button" id="aiGenerateQuestionBtn">Generate Question</button>
                <button type="button" id="aiGenerateOptionsBtn">Generate Options</button>
                <button type="button" id="aiGenerateExplanationBtn">Generate Explanation</button>
                <button type="button" id="aiGenerateHintBtn">Generate Hint</button>
                <button type="button" id="aiGenerateTagsBtn">Generate Tags</button>
            </div>
            <div id="ai_status" class="ai-status"></div>
        </div>
        <form method="POST" action="" enctype="multipart/form-data" id="questionForm">
            <label for="question">Question:</label>
            <textarea name="question" id="question" required placeholder="Enter your question here..."></textarea>

            <label for="explanation">Explanation (Optional):</label>
            <textarea name="explanation" id="explanation" placeholder="Explain why the answer is correct..."></textarea>

            <label for="question_image">Question Image (Optional):</label>
            <input type="file" name="question_image" id="question_image" accept="image/*">

            <div class="form-row">
                <div class="form-group">
                    <label for="difficulty">Difficulty:</label>
                    <select name="difficulty" id="difficulty" required>
                        <option value="Easy">Easy</option>
                        <option value="Medium">Medium</option>
                        <option value="Hard">Hard</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="points">Points:</label>
                    <input type="number" name="points" id="points" required min="1" value="1">
                </div>
            </div>

            <label for="hint">Hint (Optional):</label>
            <textarea name="hint" id="hint" placeholder="Provide a hint for difficult questions..."></textarea>

            <label for="tags">Tags (Comma separated):</label>
            <input type="text" name="tags" id="tags" placeholder="e.g., math, algebra, equations">

            <h3>Answer Options</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="option1">Option 1:</label>
                    <input type="text" name="option1" id="option1" required>
                </div>
                <div class="form-group">
                    <label for="option2">Option 2:</label>
                    <input type="text" name="option2" id="option2" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="option3">Option 3:</label>
                    <input type="text" name="option3" id="option3" required>
                </div>
                <div class="form-group">
                    <label for="option4">Option 4:</label>
                    <input type="text" name="option4" id="option4" required>
                </div>
            </div>

            <label for="correct_option">Correct Option (1-4):</label>
            <input type="number" name="correct_option" id="correct_option" min="1" max="4" required value="1">

            <div class="button-group">
                <input type="submit" value="Add Question">
                <button type="button" id="previewButton">Preview Question</button>
            </div>
        </form>
    </div>

    <div id="previewModal">
        <div id="previewContent">
            <span id="closePreview">×</span>
            <h2>Question Preview</h2>
            <div id="previewDetails"></div>
        </div>
    </div>

    <script>
        // AI Generator Logic
        const aiPromptTextarea = document.getElementById('ai_prompt');
        const aiStatusDiv = document.getElementById('ai_status');
        const aiButtons = document.querySelectorAll('.ai-generator button');
        const questionTextarea = document.getElementById('question');

        async function generateAiContent(targetField) {
            const prompt = aiPromptTextarea.value.trim();
            if (!prompt) {
                aiStatusDiv.textContent = 'Please enter a topic or idea first.';
                aiStatusDiv.style.color = 'red';
                return;
            }

            const questionText = questionTextarea.value.trim();
            const payload = { prompt, target: targetField };
            if (['options', 'explanation', 'hint', 'tags'].includes(targetField) && questionText) {
                payload.question = questionText;
            }

            aiButtons.forEach(btn => btn.disabled = true);
            aiStatusDiv.textContent = `Generating ${targetField}...`;
            aiStatusDiv.style.color = '#666';

            try {
                const response = await fetch('generate_content_api_add_question.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result.message || `Request failed: ${response.status}`);
                }

                if (targetField === 'options' && result.generated_options) {
                    const options = result.generated_options;
                    ['option1', 'option2', 'option3', 'option4'].forEach((id, index) => {
                        document.getElementById(id).value = options[index].text;
                    });
                    const correctIndex = options.findIndex(opt => opt.is_correct);
                    document.getElementById('correct_option').value = correctIndex + 1;
                    aiStatusDiv.textContent = 'Options generated successfully!';
                } else if (result.generated_text) {
                    const targetElement = document.getElementById(targetField);
                    if (targetElement) {
                        targetElement.value = result.generated_text;
                        aiStatusDiv.textContent = `${targetField.charAt(0).toUpperCase() + targetField.slice(1)} generated successfully!`;
                    } else {
                        throw new Error(`Target element '#${targetField}' not found.`);
                    }
                } else {
                    throw new Error('Unexpected response from AI service.');
                }
                aiStatusDiv.style.color = 'green';
            } catch (error) {
                console.error(`Error generating AI content for ${targetField}:`, error);
                aiStatusDiv.textContent = `Error: ${error.message}`;
                aiStatusDiv.style.color = 'red';
            } finally {
                aiButtons.forEach(btn => btn.disabled = false);
                setTimeout(() => {
                    if (aiStatusDiv.textContent && aiStatusDiv.style.color !== 'red') {
                        aiStatusDiv.textContent = '';
                    }
                }, 5000);
            }
        }

        document.getElementById('aiGenerateQuestionBtn').addEventListener('click', () => generateAiContent('question'));
        document.getElementById('aiGenerateOptionsBtn').addEventListener('click', () => generateAiContent('options'));
        document.getElementById('aiGenerateExplanationBtn').addEventListener('click', () => generateAiContent('explanation'));
        document.getElementById('aiGenerateHintBtn').addEventListener('click', () => generateAiContent('hint'));
        document.getElementById('aiGenerateTagsBtn').addEventListener('click', () => generateAiContent('tags'));

        // Preview Modal Logic
        document.getElementById('previewButton').addEventListener('click', function() {
            const question = document.getElementById('question').value;
            const explanation = document.getElementById('explanation').value;
            const difficulty = document.getElementById('difficulty').value;
            const points = document.getElementById('points').value;
            const hint = document.getElementById('hint').value;
            const tags = document.getElementById('tags').value;
            const option1 = document.getElementById('option1').value;
            const option2 = document.getElementById('option2').value;
            const option3 = document.getElementById('option3').value;
            const option4 = document.getElementById('option4').value;
            const correctOption = document.getElementById('correct_option').value;

            if (!question || !option1 || !option2 || !option3 || !option4 || !correctOption) {
                alert('Please fill out all required fields');
                return;
            }

            const difficultyClass = difficulty === 'Easy' ? 'difficulty-easy' : difficulty === 'Medium' ? 'difficulty-medium' : 'difficulty-hard';
            let previewHTML = `<p><strong>Question:</strong> ${question}</p>`;
            if (document.getElementById('question_image').files.length > 0) {
                previewHTML += "<p><strong>Image:</strong> Included (displayed after submission)</p>";
            }
            previewHTML += `<p><strong>Difficulty:</strong> <span class='difficulty-badge ${difficultyClass}'>${difficulty}</span></p>`;
            previewHTML += `<p><strong>Points:</strong> ${points}</p>`;
            if (hint) {
                previewHTML += `<p><strong>Hint:</strong> ${hint}</p>`;
            }
            if (tags) {
                previewHTML += `<p><strong>Tags:</strong> ${tags}</p>`;
            }
            previewHTML += "<p><strong>Options:</strong></p><ol>";
            previewHTML += `<li>${option1}${correctOption == 1 ? " <strong>(Correct)</strong>" : ""}</li>`;
            previewHTML += `<li>${option2}${correctOption == 2 ? " <strong>(Correct)</strong>" : ""}</li>`;
            previewHTML += `<li>${option3}${correctOption == 3 ? " <strong>(Correct)</strong>" : ""}</li>`;
            previewHTML += `<li>${option4}${correctOption == 4 ? " <strong>(Correct)</strong>" : ""}</li>`;
            previewHTML += "</ol>";
            if (explanation) {
                previewHTML += `<p><strong>Explanation:</strong> ${explanation}</p>`;
            }

            document.getElementById('previewDetails').innerHTML = previewHTML;
            document.getElementById('previewModal').style.display = "flex";
        });

        document.getElementById('closePreview').addEventListener('click', () => {
            document.getElementById('previewModal').style.display = "none";
        });

        window.addEventListener('click', (event) => {
            if (event.target == document.getElementById('previewModal')) {
                document.getElementById('previewModal').style.display = "none";
            }
        });
    </script>
</body>
</html>