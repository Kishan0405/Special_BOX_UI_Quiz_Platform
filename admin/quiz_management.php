<?php
// admin/quiz_management.php
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once 'admin_header.php'; // Assuming this includes session_start()

requireLogin(); // Ensure user is logged in

$message = isset($_GET['message']) ? $_GET['message'] : '';
$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Fetch user role for conditional display
$stmtRole = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmtRole->execute([$user_id]);
$user = $stmtRole->fetch();
$role = $user['role'] ?? 'user'; // Default role if not found
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Admin Dashboard - Special BOX UI Quiz</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin_quiz_management.css">
</head>

<style>
    .selected-users {
        margin-top: 10px;
    }

    .selected-user {
        padding: 5px;
        background: #f0f0f0;
        margin: 5px;
        display: inline-block;
        border-radius: 4px;
    }

    .selected-user button {
        margin-left: 8px;
        cursor: pointer;
        border: none;
        background: #ddd;
        padding: 2px 5px;
        border-radius: 3px;
    }

    .selected-user button:hover {
        background: #ccc;
    }

    .user-search-results {
        max-height: 150px;
        overflow-y: auto;
        border: 1px solid #ccc;
        margin-top: 5px;
        background: #fff;
    }

    .user-search-results div {
        padding: 5px;
        cursor: pointer;
    }

    .user-search-results div:hover {
        background: #e0e0e0;
    }

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
</style>

<body>
    <div class="container">
        <?php if ($message !== ''): ?>
            <p class="success"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <div class="dashboard-container">
            <div class="dashboard-column">
                <h2>Create New Quiz</h2>
                <div class="ai-generator">
                    <label for="ai_prompt">Generate Content with Special BOX AI Mini:</label>
                    <textarea id="ai_prompt" placeholder="Enter a topic or key ideas for your quiz..."></textarea>
                    <button type="button" id="aiGenerateTitleBtn">Generate Title</button>
                    <button type="button" id="aiGenerateDescBtn">Generate Description</button>
                    <button type="button" id="aiGenerateInstrBtn">Generate Instructions</button>
                    <div id="ai_status" class="ai-status"></div>
                </div>
                <form method="POST" action="create_quiz.php" id="createQuizForm">
                    <label for="title">Title:</label>
                    <input type="text" name="title" id="title" required>
                    <label for="description">Description:</label>
                    <textarea name="description" id="description" required></textarea>
                    <label for="instructions">Instructions:</label>
                    <textarea name="instructions" id="instructions" placeholder="Enter instructions..."></textarea>
                    <label for="time_limit">Time Limit (in seconds):</label>
                    <input type="number" name="time_limit" id="time_limit" required>
                    <fieldset>
                        <legend>Quiz Access</legend>
                        <label>
                            <input type="radio" name="access_type" value="direct" checked> Direct Access
                        </label>
                        <label>
                            <input type="radio" name="access_type" value="password"> Password Protected
                        </label>
                    </fieldset>
                    <div id="passwordField" style="display: none;">
                        <label for="quiz_password">Quiz Password:</label>
                        <input type="password" name="quiz_password" id="quiz_password">
                    </div>
                    <fieldset>
                        <legend>Quiz Taking Mode</legend>
                        <label><input type="radio" name="quiz_mode" value="online_sequential" checked> Online (Sequential)</label>
                        <label><input type="radio" name="quiz_mode" value="online_all_at_once"> Online (All at Once)</label>
                        <label><input type="radio" name="quiz_mode" value="offline_sequential"> Offline (Sequential)</label>
                        <label><input type="radio" name="quiz_mode" value="offline_all_at_once"> Offline (All at Once)</label>
                        <label><input type="radio" name="quiz_mode" value="both"> Both</label>
                    </fieldset>
                    <fieldset>
                        <legend>Create an Event (Optional)</legend>
                        <label>
                            <input type="checkbox" name="is_event" value="1" id="createEventCheckbox"> Make this quiz an event
                        </label>
                        <div id="eventTimingFields" style="display: none; margin-top: 10px;">
                            <label for="start_date">Start Date:</label>
                            <input type="date" name="start_date" id="start_date">
                            <label for="start_time">Start Time:</label>
                            <input type="time" name="start_time" id="start_time">
                            <label for="end_date">End Date:</label>
                            <input type="date" name="end_date" id="end_date">
                            <label for="end_time">End Time:</label>
                            <input type="time" name="end_time" id="end_time">
                        </div>
                    </fieldset>
                    <fieldset id="eventScopeFieldset" style="display: none;">
                        <legend>Event Scope</legend>
                        <?php if ($user_id == 1 && $role === 'admin'): ?>
                            <label>
                                <input type="checkbox" name="event_for_all" id="eventForAllCheckbox" value="1"> Event for All Registered Users
                            </label>
                        <?php endif; ?>
                        <label>
                            <input type="checkbox" name="event_by_group" id="eventByGroupCheckbox" value="1"> Assign Event to Specific Users/Group
                        </label>
                        <div id="groupSelectionField" style="display: none; margin-top: 10px;">
                            <label for="user_search">Search & Add Users by Email:</label>
                            <input type="text" id="user_search" placeholder="Type email to search...">
                            <div id="user_search_results" class="user-search-results"></div>
                            <label>Selected Users for Event:</label>
                            <div id="selected_users" class="selected-users"></div>
                        </div>
                    </fieldset>
                    <input type="submit" value="Create Quiz">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                </form>
            </div>
            <div class="dashboard-column">
                <h2>Existing Quizzes</h2>
                <div class="search-bar">
                    <input type="text" id="quizSearch" placeholder="Search quizzes...">
                </div>
                <table id="quizTable">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th># Questions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT q.*, COUNT(qu.id) as question_count
                                FROM quizzes q
                                LEFT JOIN questions qu ON q.id = qu.quiz_id ";
                        $params = [];
                        if ($role !== 'admin' || $user_id != 1) {
                            $sql .= " WHERE q.user_id = ? ";
                            $params[] = $user_id;
                        }
                        $sql .= " GROUP BY q.id ORDER BY q.created_at DESC";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($params);
                        while ($quiz = $stmt->fetch(PDO::FETCH_ASSOC)):
                            $displayDescription = str_replace('__', '<br>', htmlspecialchars($quiz['description']));
                            $question_count = $quiz['question_count'];
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                                <td><?php echo $displayDescription; ?></td>
                                <td><?php echo htmlspecialchars($question_count); ?></td>
                                <td class="action-links">
                                    <a href="add_question.php?quiz_id=<?php echo $quiz['id']; ?>">Add Questions</a>
                                    <a href="edit_quiz.php?quiz_id=<?php echo $quiz['id']; ?>">Edit</a>
                                    <a href="delete_quiz.php?quiz_id=<?php echo $quiz['id']; ?>" onclick="return confirm('Are you sure you want to delete this quiz?');">Delete</a>
                                    <a href="preview_quiz.php?quiz_id=<?php echo $quiz['id']; ?>" target="_blank">Preview</a>
                                    <a href="duplicate_quiz.php?quiz_id=<?php echo $quiz['id']; ?>">Duplicate</a>
                                    <a href="quiz_analytics.php?quiz_id=<?php echo $quiz['id']; ?>">Analytics</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        // Password field toggle
        document.querySelectorAll('input[name="access_type"]').forEach(function(elem) {
            elem.addEventListener('change', function() {
                document.getElementById('passwordField').style.display = this.value === 'password' ? 'block' : 'none';
            });
        });

        // Quiz search
        document.getElementById('quizSearch').addEventListener('keyup', function() {
            var searchTerm = this.value.toLowerCase();
            var rows = document.querySelectorAll('#quizTable tbody tr');
            rows.forEach(function(row) {
                var title = row.cells[0].textContent.toLowerCase();
                var description = row.cells[1].textContent.toLowerCase();
                row.style.display = (title.includes(searchTerm) || description.includes(searchTerm)) ? '' : 'none';
            });
        });

        // Event fields logic
        const createEventCheckbox = document.getElementById('createEventCheckbox');
        const eventFields = document.getElementById('eventTimingFields');
        const eventScopeFieldset = document.getElementById('eventScopeFieldset');
        const eventByGroupCheckbox = document.getElementById('eventByGroupCheckbox');
        const groupField = document.getElementById('groupSelectionField');
        const eventForAllCheckbox = document.getElementById('eventForAllCheckbox');

        createEventCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            eventFields.style.display = isChecked ? 'block' : 'none';
            eventScopeFieldset.style.display = isChecked ? 'block' : 'none';
            if (!isChecked) {
                eventByGroupCheckbox.checked = false;
                groupField.style.display = 'none';
                if (eventForAllCheckbox) {
                    eventForAllCheckbox.checked = false;
                }
            }
        });

        eventByGroupCheckbox.addEventListener('change', function() {
            groupField.style.display = this.checked ? 'block' : 'none';
            if (this.checked && eventForAllCheckbox) {
                eventForAllCheckbox.checked = false;
            }
        });

        if (eventForAllCheckbox) {
            eventForAllCheckbox.addEventListener('change', function() {
                if (this.checked && eventByGroupCheckbox.checked) {
                    eventByGroupCheckbox.checked = false;
                    groupField.style.display = 'none';
                }
            });
        }

        // User search and selection
        const userSearchInput = document.getElementById('user_search');
        const userSearchResults = document.getElementById('user_search_results');
        const selectedUsersDiv = document.getElementById('selected_users');
        const createQuizForm = document.getElementById('createQuizForm');
        let selectedUsers = [];

        userSearchInput.addEventListener('input', debounce(async function() {
            const query = this.value.trim();
            userSearchResults.innerHTML = '';
            if (query.length < 3) return;
            userSearchResults.innerHTML = '<div>Searching...</div>';
            try {
                const response = await fetch(`search_users.php?email=${encodeURIComponent(query)}`);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const users = await response.json();
                userSearchResults.innerHTML = '';
                if (users.length === 0) {
                    userSearchResults.innerHTML = '<div>No users found.</div>';
                } else {
                    users.forEach(user => {
                        if (!selectedUsers.some(selected => selected.id === user.id)) {
                            const div = document.createElement('div');
                            const displayName = user.display_name || user.username || 'N/A';
                            div.textContent = `${user.email} (${displayName})`;
                            div.dataset.userId = user.id;
                            div.dataset.userEmail = user.email;
                            div.dataset.userName = displayName;
                            div.addEventListener('click', () => addUser(user));
                            userSearchResults.appendChild(div);
                        }
                    });
                }
            } catch (error) {
                console.error('Error searching users:', error);
                userSearchResults.innerHTML = '<div style="color: red;">Error searching users.</div>';
            }
        }, 300));

        function addUser(user) {
            if (!selectedUsers.some(u => u.id === user.id)) {
                selectedUsers.push({
                    id: user.id,
                    email: user.email,
                    name: user.display_name || user.username
                });
                renderSelectedUsers();
            }
            userSearchInput.value = '';
            userSearchResults.innerHTML = '';
        }

        function removeUser(userId) {
            selectedUsers = selectedUsers.filter(u => u.id !== userId);
            renderSelectedUsers();
        }

        function renderSelectedUsers() {
            selectedUsersDiv.innerHTML = '';
            selectedUsers.forEach(user => {
                const userSpan = document.createElement('span');
                userSpan.className = 'selected-user';
                userSpan.textContent = `${user.email} (${user.name || ''}) `;
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.textContent = '✕';
                removeBtn.title = 'Remove user';
                removeBtn.addEventListener('click', () => removeUser(user.id));
                userSpan.appendChild(removeBtn);
                selectedUsersDiv.appendChild(userSpan);
                let hiddenInput = createQuizForm.querySelector(`input[name="selected_user_ids[]"][value="${user.id}"]`);
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'selected_user_ids[]';
                    hiddenInput.value = user.id;
                    createQuizForm.appendChild(hiddenInput);
                }
            });
            const currentHiddenInputs = createQuizForm.querySelectorAll('input[name="selected_user_ids[]"]');
            currentHiddenInputs.forEach(input => {
                const userId = parseInt(input.value, 10);
                if (!selectedUsers.some(u => u.id === userId)) {
                    input.remove();
                }
            });
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func.apply(this, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // AI Generator Logic
        const aiPromptTextarea = document.getElementById('ai_prompt');
        const aiStatusDiv = document.getElementById('ai_status');
        const aiButtons = document.querySelectorAll('.ai-generator button');

        async function generateAiContent(targetField) {
            const prompt = aiPromptTextarea.value.trim();
            if (!prompt) {
                aiStatusDiv.textContent = 'Please enter a topic or idea first.';
                aiStatusDiv.style.color = 'red';
                return;
            }
            aiButtons.forEach(btn => btn.disabled = true);
            aiStatusDiv.textContent = `Generating ${targetField}... please wait.`;
            aiStatusDiv.style.color = '#666';
            try {
                const response = await fetch('generate_content_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        prompt: prompt,
                        target: targetField
                    })
                });
                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result.message || `Request failed with status ${response.status}`);
                }
                if (result.generated_text) {
                    const targetElement = document.getElementById(targetField);
                    if (targetElement) {
                        targetElement.value = result.generated_text;
                        aiStatusDiv.textContent = `${targetField.charAt(0).toUpperCase() + targetField.slice(1)} generated successfully!`;
                        aiStatusDiv.style.color = 'green';
                    } else {
                        throw new Error(`Target element '#${targetField}' not found.`);
                    }
                } else if (result.error) {
                    throw new Error(result.message || 'Unknown error from AI service.');
                } else {
                    throw new Error("Received an unexpected response from the AI service.");
                }
            } catch (error) {
                console.error(`Error generating AI content for ${targetField}:`, error);
                aiStatusDiv.textContent = `Error: ${error.message}`; // Display full error message
                aiStatusDiv.style.color = 'red';
            } finally {
                aiButtons.forEach(btn => btn.disabled = false);
                setTimeout(() => {
                    if (aiStatusDiv.textContent !== '' && aiStatusDiv.style.color !== 'red') {
                        aiStatusDiv.textContent = '';
                    }
                }, 5000);
            }
        }

        document.getElementById('aiGenerateTitleBtn').addEventListener('click', () => generateAiContent('title'));
        document.getElementById('aiGenerateDescBtn').addEventListener('click', () => generateAiContent('description'));
        document.getElementById('aiGenerateInstrBtn').addEventListener('click', () => generateAiContent('instructions'));
    </script>
</body>

</html>