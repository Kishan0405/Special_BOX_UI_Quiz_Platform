<?php
// Start session if auth.php doesn't already do it
// session_start(); // Make sure session is started BEFORE any output, usually done in auth.php

// Include necessary files
require_once 'includes/auth.php'; // Handles authentication checks and might start session
require_once 'includes/database.php'; // Provides $pdo database connection
require_once 'includes/functions.php'; // For any general utility functions

// --- Authentication Check ---
// Redirect to login page if user is not logged in
// This MUST happen before any HTML output
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}

// Get the logged-in user's ID from the session
$user_id = $_SESSION['user_id'];

// Initialize variables for messages and flags
$success_message = '';
$error_message = '';
$validation_errors = [];
$reload_page = false; // Flag for JS reload

// --- Handle Profile Update (POST Request) ---
// This entire block MUST come before including header.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get submitted data, trim whitespace
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $remove_picture = isset($_POST['remove_profile_picture']);

    // Basic Validation
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $validation_errors[] = "Invalid email format.";
    }

    if (empty($username)) {
        $validation_errors[] = "Username is required.";
    }

    if (!empty($new_password)) {
        if (strlen($new_password) < 8) {
            $validation_errors[] = "Password must be at least 8 characters long.";
        }
        if ($new_password !== $confirm_password) {
            $validation_errors[] = "New passwords do not match.";
        }
    }

    // --- Profile Picture Upload Handling ---
    $profile_picture_path = null; // Default to null (no change unless uploaded or removed)
    $upload_dir = 'uploads/profile_pictures/'; // Define upload_dir for use in delete later

    // Check if a file was uploaded
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowed_types)) {
            $validation_errors[] = "Only JPEG, PNG, and GIF images are allowed.";
        }

        // Validate file size (e.g., max 5MB)
        $max_size = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $max_size) {
            $validation_errors[] = "Profile picture size exceeds the limit (5MB).";
        }

        // If no validation errors for the file
        if (empty($validation_errors)) {
            // Create directory if it doesn't exist (check moved inside file handling)
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
                    $validation_errors[] = "Error creating upload directory.";
                }
            }

            // Check if directory is writable after potentially creating it
            if (empty($validation_errors) && !is_writable($upload_dir)) {
                $validation_errors[] = "Upload directory is not writable.";
            }

            // If still no errors
            if (empty($validation_errors)) {
                // Generate a unique filename
                $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = uniqid() . '.' . strtolower($file_ext);
                $upload_path = $upload_dir . $new_filename;

                // Move the uploaded file
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $profile_picture_path = $upload_path; // Set the path for database update

                    // Delete old profile picture if it exists and is different
                    $stmt_old_pic = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
                    $stmt_old_pic->execute([$user_id]);
                    $old_pic = $stmt_old_pic->fetchColumn();

                    if ($old_pic && is_string($old_pic) && file_exists($old_pic) && $old_pic !== $profile_picture_path) {
                        // Security check: Ensure the file path seems legitimate and inside the upload dir
                        if (strpos(realpath($old_pic), realpath($upload_dir)) === 0) {
                            unlink($old_pic); // Delete the old file
                        } else {
                            error_log("Attempted to delete file outside designated directory: " . $old_pic);
                        }
                    }
                } else {
                    $validation_errors[] = "Error uploading file. Check permissions and path.";
                    error_log("Failed to move uploaded file to: " . $upload_path); // Log detailed error
                }
            }
        }
    } elseif ($remove_picture) {
        // User checked the remove picture checkbox
        $profile_picture_path = null; // Will be set to NULL in DB

        // Delete old profile picture from server
        $stmt_old_pic = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
        $stmt_old_pic->execute([$user_id]);
        $old_pic = $stmt_old_pic->fetchColumn();

        if ($old_pic && is_string($old_pic) && file_exists($old_pic)) {
            // Security check
            if (strpos(realpath($old_pic), realpath($upload_dir)) === 0) {
                unlink($old_pic); // Delete the old file
            } else {
                error_log("Attempted to delete file outside designated directory: " . $old_pic);
            }
        }
    }
    // If no file uploaded AND remove not checked, $profile_picture_path remains null,
    // meaning the UPDATE query won't touch the profile_picture column unless explicitly needed.

    // --- Update Database if no validation errors ---
    if (empty($validation_errors)) {
        // Build query dynamically based on whether picture needs updating
        $sql_parts = [];
        $params = [];

        // Always include email and username for update
        $sql_parts[] = "email = ?";
        $params[] = $email;

        $sql_parts[] = "username = ?";
        $params[] = $username;

        // Add profile_picture update only if a new one was uploaded OR removal was requested
        if ($profile_picture_path !== null || $remove_picture) {
            $sql_parts[] = "profile_picture = ?";
            $params[] = $profile_picture_path; // This will be the new path or null if removed
        }

        // Add password update if a new password was provided
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql_parts[] = "password = ?";
            $params[] = $hashed_password;
        }

        // Only proceed if there's something to update
        if (!empty($sql_parts)) {
            $sql = "UPDATE users SET " . implode(', ', $sql_parts) . " WHERE id = ?";
            $params[] = $user_id;

            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                // Redirect AFTER successful update
                header('Location: profile.php?update=success');
                exit(); // Crucial to stop script execution after redirect

            } catch (PDOException $e) {
                $error_message = "Database error: Could not update profile.";
                error_log("Profile update failed for user $user_id: " . $e->getMessage()); // Log detailed error
            }
        } else {
            // This case (no changes submitted at all) might be less likely if email is always submitted
            // but good to have a path for it.
            header('Location: profile.php?update=nochange');
            exit();
        }
    } else {
        // If there are validation errors from POST, compose an error message
        $error_message = "Please fix the following errors:<br>" . implode("<br>", $validation_errors);
    }
} // End of POST request handling

// --- Handle success message from redirect ---
if (isset($_GET['update']) && $_GET['update'] === 'success') {
    $success_message = "Profile updated successfully!";
    $reload_page = true; // Flag to add the JavaScript for timed reload
} elseif (isset($_GET['update']) && $_GET['update'] === 'nochange') {
    $success_message = "No changes were submitted."; // Or a different message
}

// --- Fetch User Data for Display ---
$user = null; // Initialize $user
try {
    // Removed gpay_info, other_payment_info, display_name from SELECT
    $stmt = $pdo->prepare("SELECT id, username, role, email, profile_picture, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header('Location: logout.php?reason=notfound');
        exit();
    }
} catch (PDOException $e) {
    $error_message = "Database error: Could not fetch user data.";
    error_log("Profile fetch failed for user $user_id: " . $e->getMessage());
    $user = null;
}

// NOW it's safe to include the header and start outputting HTML
include 'header.php';

?>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Poppins:wght@400;600;700&family=Press+Start+2P&family=Roboto+Condensed:wght@700&family=Roboto+Mono&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&display=swap');

    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f8f9fa;
        color: #495057;
        line-height: 1.6;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        color: #343a40;
    }

    .profile-picture-container img {
        border: 3px solid #dee2e6;
    }
</style>

<main id="content" class="container mt-4 mb-5">
    <h1 class="mb-4">User Profile</h1>

    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; // Allow <br> tag from validation errors
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($user): // Only display form if user data was fetched successfully
    ?>
        <div class="row">
            <div class="col-md-4 text-center mb-4 mb-md-0">
                <h4>Profile Picture</h4>
                <div class="profile-picture-container mb-3">
                    <?php
                    $default_profile_pic = 'assets/images/default_profile.png'; // Ensure this path is correct
                    $profile_pic_src = $default_profile_pic; // Start with default

                    if (!empty($user['profile_picture']) && is_string($user['profile_picture']) && file_exists($user['profile_picture'])) {
                        $profile_pic_src = htmlspecialchars($user['profile_picture']) . '?t=' . time();
                    } elseif (!empty($user['profile_picture']) && is_string($user['profile_picture'])) {
                        error_log("Profile picture file not found for user {$user_id}: " . $user['profile_picture']);
                    }
                    ?>
                    <img src="<?php echo $profile_pic_src; ?>"
                         alt="<?php echo htmlspecialchars($user['username']); ?>'s Profile Picture" class="img-fluid rounded-circle border"
                         style="width: 150px; height: 150px; object-fit: cover;"
                         id="profile-img-display">
                </div>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Role:</strong> <?php echo htmlspecialchars(ucfirst($user['role'])); ?></p>
                <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
            </div>

            <div class="col-md-8">
                <form action="profile.php" method="post" enctype="multipart/form-data" id="profile-form">

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control <?php echo (!empty($validation_errors) && !empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? $user['email'] ?? ''); ?>">
                        <?php if (!empty($validation_errors) && isset($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) && !empty($_POST['email'])): ?>
                            <div class="invalid-feedback">Invalid email format.</div>
                        <?php endif; ?>
                        <small class="form-text text-muted">Optional. Used for Events by Group</small>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>

                    <div class="mb-3">
                        <label for="profile_picture" class="form-label">Change Profile Picture</label>
                        <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png,image/gif">
                        <small class="form-text text-muted">Allowed formats: JPG, PNG, GIF. Max size: 5MB.</small>
                    </div>

                    <?php
                    $has_removable_picture = !empty($user['profile_picture'])
                        && is_string($user['profile_picture'])
                        && file_exists($user['profile_picture']);
                    if ($has_removable_picture):
                    ?>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remove_profile_picture" name="remove_profile_picture">
                            <label class="form-check-label" for="remove_profile_picture">Remove current profile picture</label>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Profile</button>
                </form>
            </div>
        </div>
    <?php elseif (!$error_message) : ?>
        <div class="alert alert-warning">User data could not be loaded. Please try again later or contact support.</div>
    <?php endif; ?>

</main>

<?php // include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

<?php if ($reload_page): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.getElementById('success-alert');
            if (successAlert) {
                setTimeout(() => {
                    window.location.href = 'profile.php';
                }, 2500);
            }
        });
    </script>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('profile_picture');
        const imageDisplay = document.getElementById('profile-img-display');
        const removeCheckbox = document.getElementById('remove_profile_picture');
        const defaultSrc = '<?php echo $default_profile_pic; ?>';
        const originalSrc = imageDisplay ? imageDisplay.src : defaultSrc;

        if (fileInput && imageDisplay) {
            fileInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imageDisplay.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                    if (removeCheckbox) {
                        removeCheckbox.checked = false;
                    }
                } else if (!file) {
                    if (!removeCheckbox || !removeCheckbox.checked) {
                        imageDisplay.src = originalSrc;
                    } else {
                        imageDisplay.src = defaultSrc;
                    }
                }
            });
        }

        if (removeCheckbox && imageDisplay) {
            removeCheckbox.addEventListener('change', function(event) {
                if (event.target.checked) {
                    if (fileInput) {
                        fileInput.value = '';
                    }
                    imageDisplay.src = defaultSrc;
                } else {
                    // If a file was selected before checking 'remove', then unchecking 'remove'
                    // should ideally revert to that preview or the original image if no preview.
                    // Current logic reverts to originalSrc, which is the image state on page load.
                    // This is generally fine. If a file was previewed, then remove checked, then remove unchecked,
                    // it goes back to original, not the preview.
                    imageDisplay.src = originalSrc;
                }
            });
        }

        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');

        if (newPasswordInput && confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                if (newPasswordInput.value !== confirmPasswordInput.value) {
                    confirmPasswordInput.setCustomValidity("Passwords do not match.");
                } else {
                    confirmPasswordInput.setCustomValidity("");
                }
            });
        }
    });
</script>

</body>
</html>
