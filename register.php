<?php
ob_start(); // Start output buffering
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'header.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = 'user'; // default role

    $email = sanitize($_POST['email']);
    $profile_picture_path = ''; // Initialize profile picture path

    // --- Email Validation ---
    // Only validate if an email was provided
    if (!empty($email)) { // Only validate if email is provided
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message .= "Invalid email address format. Please use a valid email (e.g., user@gmail.com). ";
        } else {
            // --- Check if email already exists ---
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $existing_email = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_email) {
                $message .= "Email address already exists. ";
            }
        }
    }

    // --- Check if username already exists ---
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_user) {
        $message .= "Username already exists. ";
    }

    // --- If no errors so far, handle profile picture and proceed with registration ---
    if (empty($message)) {
        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($_FILES['profile_picture']['type'], $allowed_types)) {
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $original_name = basename($_FILES['profile_picture']['name']);
                $safe_name = preg_replace("/[^A-Za-z0-9_.-]/", '_', $original_name);
                $filename = uniqid() . '_' . $safe_name;
                $filepath = $upload_dir . $filename;

                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $filepath)) {
                    $profile_picture_path = $filepath;
                } else {
                    $message .= "Error uploading profile picture. ";
                }
            } else {
                $message .= "Invalid profile picture file type. Only JPEG, PNG, and GIF are allowed. ";
            }
        }

        // If still no errors after picture upload handling, insert user
        if (empty($message)) {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, email, profile_picture) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$username, $hashed_password, $role, $email, $profile_picture_path])) {
                $_SESSION['registration_success'] = true;
                $_SESSION['registered_username'] = $username;
                header("Location: register_success.php");
                exit();
            } else {
                // This is a database error, should be logged in a real application
                $message .= "Error registering user. Please try again later.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Register - Special BOX UI Quiz</title>
    <link rel="stylesheet" href="css/user-info-container.css">
</head>

<body>
    <div class="user-info-container">
        <h2>Register</h2>
        <?php if ($message != ''): ?>
            <p class="error"><?php echo $message; ?></p>
        <?php endif; ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <label>Username:</label>
            <input type="text" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            <br />
            <label>Password:</label>
            <input type="password" name="password" required>
            <br />
            <label>Email: (Optional)</label>
            <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            <br />
            <label>Profile Picture:</label>
            <input type="file" name="profile_picture">
            <br />
            <input type="submit" value="Register">
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>

</html>
<?php
ob_end_flush(); // Flush the output buffer
?>