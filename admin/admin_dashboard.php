<?php
// admin/admin_dashboard.php
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once 'admin_header.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get user role
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$user_role = $user['role'] ?? '';

// Check if admin (user_id 1 and role admin)
$is_admin = ($user_id == 1 && $user_role == 'admin');

$message = isset($_GET['message']) ? $_GET['message'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard - Special BOX UI Quiz</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this quiz?');
        }
    </script>
</head>

<body>
    <div class="container">
        <?php if ($message != ''): ?>
            <p class="success"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if ($is_admin): ?>
            <h2>Existing Quizzes</h2>
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Search quizzes..."
                    value="<?php echo htmlspecialchars($search); ?>">
                <input type="submit" value="Search">
            </form>

            <?php
            // Fetch quizzes with search filter
            $sql = "SELECT * FROM quizzes";
            $params = [];
            if (!empty($search)) {
                $sql .= " WHERE title LIKE ? OR description LIKE ?";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $quizzes = $stmt->fetchAll();
            ?>

            <table border="1" cellpadding="8">
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($quizzes as $quiz): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                        <td><?php echo htmlspecialchars($quiz['description']); ?></td>
                        <td>
                            <a href="add_question.php?quiz_id=<?php echo $quiz['id']; ?>">Add Questions</a>
                            <a href="delete_quiz.php?quiz_id=<?php echo $quiz['id']; ?>"
                                onclick="return confirmDelete()">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p class="error">The information is not available to normal users.</p>
        <?php endif; ?>
    </div>
</body>

</html>
<style>
    /* styles.css */

    .container {
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .success {
        background-color: #d4edda;
        color: #155724;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 6px;
        border: 1px solid #c3e6cb;
    }

    .error {
        background-color: #f8d7da;
        color: #721c24;
        padding: 15px;
        margin: 20px 0;
        border-radius: 6px;
        border: 1px solid #f5c6cb;
    }

    form {
        margin-bottom: 30px;
    }

    input[type="text"],
    input[type="submit"],
    input[type="number"],
    textarea {
        width: 100%;
        padding: 12px;
        margin: 8px 0;
        border: 1px solid #ddd;
        border-radius: 6px;
        box-sizing: border-box;
        transition: border-color 0.3s ease;
    }

    input[type="text"]:focus,
    textarea:focus {
        border-color: #4a90e2;
        outline: none;
    }

    input[type="submit"] {
        background-color: #4a90e2;
        color: white;
        cursor: pointer;
        font-weight: bold;
        border: none;
        padding: 12px 25px;
        transition: background-color 0.3s ease;
    }

    input[type="submit"]:hover {
        background-color: #357abd;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    th {
        background-color: #4a90e2;
        color: white;
        padding: 15px;
        text-align: left;
    }

    td {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
    }

    tr:hover {
        background-color: #f8f9fa;
    }

    tr:last-child td {
        border-bottom: none;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .container {
            padding: 15px;
        }

        table {
            display: block;
            overflow-x: auto;
        }

        th,
        td {
            padding: 12px;
        }

        form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        input[type="text"] {
            flex: 1;
            min-width: 200px;
        }
    }

    @media (max-width: 480px) {

        table,
        thead,
        tbody,
        tr,
        td {
            display: block;
            width: 100%;
        }

        thead tr {
            position: absolute;
            top: -9999px;
            left: -9999px;
        }

        tr {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        td {
            border-bottom: none;
            position: relative;
            padding-left: 40%;
        }

        td:before {
            content: attr(data-label);
            position: absolute;
            left: 15px;
            width: 40%;
            padding-right: 15px;
            font-weight: bold;
            color: #4a90e2;
        }
    }
</style>