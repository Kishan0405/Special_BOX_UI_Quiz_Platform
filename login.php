<?php
ob_start(); // Start output buffering

require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'header.php';  // Now this output is buffered

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$message = '';
$username = '';
if (isset($_GET['username'])) {
    $username = sanitize($_GET['username']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password']; // do not sanitize password

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        header("Location: home.php");
        exit();
    } else {
        $message = "Invalid login credentials.";
    }
}

ob_end_flush(); // Flush the buffer and send output
?>
<!DOCTYPE html>
<html>

<head>
    <title>Login - Special BOX UI Quiz</title>
    <link rel="stylesheet" href="css/user-info-container.css">
</head>

<body>
    <div class="user-info-container">
        <h2>Login</h2>
        <?php if ($message !== ''): ?>
            <p class="error"><?php echo $message; ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <label>Username:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            <br />
            <label>Password:</label>
            <input type="password" name="password" required>
            <br />
            <input type="submit" value="Login">
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>

</html>