<?php
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

// Fetch current user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch();

// Allow only main admin (ID 1)
if (!($current_user['id'] == 1 && $current_user['role'] === 'admin')) {
    header("Location: admin_profile.php");
    exit;
}

// Validate user ID to delete
if (isset($_GET['delete_user_id']) && is_numeric($_GET['delete_user_id'])) {
    $delete_user_id = filter_var($_GET['delete_user_id'], FILTER_SANITIZE_NUMBER_INT);

    if ($delete_user_id != 1) {
        $stmt_check = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt_check->execute([$delete_user_id]);
        $user_to_delete = $stmt_check->fetch();

        if ($user_to_delete) {
            $stmt_delete = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt_delete->execute([$delete_user_id])) {
                $msg = "User  with ID $delete_user_id deleted successfully.";
                header("Location: user_management.php?msg=" . urlencode($msg)); // Redirect to user_management.php
                exit;
            } else {
                $msg = "Error deleting user.";
                header("Location: admin_profile.php?msg=" . urlencode($msg));
                exit;
            }
        } else {
            $msg = "User  not found.";
            header("Location: admin_profile.php?msg=" . urlencode($msg));
            exit;
        }
    } else {
        $msg = "You cannot delete the main administrator account.";
        header("Location: admin_profile.php?msg=" . urlencode($msg));
        exit;
    }
} else {
    header("Location: admin_profile.php");
    exit;
}