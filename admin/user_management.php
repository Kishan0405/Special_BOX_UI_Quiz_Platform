<?php
// admin/admin_profile.php
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once 'admin_header.php';

requireLogin();

// Fetch current user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch();

// Check if the current user is admin (ID 1 and role 'admin')
$is_admin = ($current_user['id'] == 1 && $current_user['role'] === 'admin');

// Handle user deletion
$delete_message = "";
if ($is_admin && isset($_GET['delete_user_id']) && is_numeric($_GET['delete_user_id'])) {
    $delete_user_id = filter_var($_GET['delete_user_id'], FILTER_SANITIZE_NUMBER_INT);

    // Prevent admin from deleting themselves or the main admin
    if ($delete_user_id != 1) {
        $stmt_check = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt_check->execute([$delete_user_id]);
        $user_to_delete = $stmt_check->fetch();

        if ($user_to_delete) {
            $stmt_delete = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt_delete->execute([$delete_user_id])) {
                $delete_message = "User with ID " . htmlspecialchars($delete_user_id) . " has been deleted successfully.";
            } else {
                $delete_message = "Error occurred while deleting user.";
            }
        } else {
            $delete_message = "User not found.";
        }
    } else {
        $delete_message = "You cannot delete the main administrator account.";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Profile - Special BOX UI Quiz</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="profile-container">
        <h2>User Profile</h2>

        <?php if (isset($_GET['msg'])): ?>
            <p class="<?php echo (strpos($_GET['msg'], 'deleted') !== false) ? 'success-msg' : 'error-msg'; ?>">
                <?php echo htmlspecialchars($_GET['msg']); ?>
            </p>
        <?php endif; ?>


        <?php if (!empty($delete_message)): ?>
            <p class="<?php echo (strpos($delete_message, 'deleted') !== false) ? 'success-msg' : 'error-msg'; ?>">
                <?php echo htmlspecialchars($delete_message); ?>
            </p>
        <?php endif; ?>

        <?php if ($is_admin): ?>
            <h3>All Users</h3>
            <table class="admin-users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Display Name</th>
                        <th>Email</th>
                        <th>GPay Info</th>
                        <th>Profile Picture</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt_users = $pdo->query("SELECT id, username, display_name, email, gpay_info, profile_picture FROM users");
                    while ($user_item = $stmt_users->fetch()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user_item['id']); ?></td>
                            <td><?php echo htmlspecialchars($user_item['username']); ?></td>
                            <td><?php echo htmlspecialchars($user_item['display_name']); ?></td>
                            <td><?php echo htmlspecialchars($user_item['email']); ?></td>
                            <td><?php echo htmlspecialchars($user_item['gpay_info']); ?></td>
                            <td>
                                <?php if (!empty($user_item['profile_picture'])): ?>
                                    <img src="../<?php echo htmlspecialchars($user_item['profile_picture']); ?>" alt="Profile Picture" class="profile-picture-thumb">
                                <?php else: ?>
                                    No Picture
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user_item['id'] != 1): ?>
                                    <a href="delete_user.php?delete_user_id=<?php echo $user_item['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                <?php else: ?>
                                    <span style="color: gray;">Cannot Delete</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="profile-card">
                <div class="profile-info">
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($current_user['username']); ?></p>
                    <p><strong>Role:</strong> <?php echo htmlspecialchars($current_user['role']); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>


<style>
    .profile-container {
    max-width: 100%;
    width: 90%;
    margin: 3rem auto;
    padding: 2rem;
    background-color: #fff;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    overflow-x: auto; /* prevent horizontal overflow */
}

.profile-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 1.5rem;
}

.profile-info {
    text-align: left;
    width: 100%;
    max-width: 400px;
}

.profile-info p {
    font-size: 1rem;
    margin: 0.7rem 0;
    line-height: 1.5;
}

/* Wrap table in a responsive container */
.table-responsive {
    width: 100%;
    overflow-x: auto;
    margin-top: 1.5rem;
}

.admin-users-table {
    width: 100%;
    min-width: 700px; /* prevent squeeze on mobile */
    border-collapse: collapse;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.admin-users-table th,
.admin-users-table td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #eaeaea;
    text-align: left;
    word-wrap: break-word;
}

.admin-users-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #444;
}

.admin-users-table tr:hover {
    background-color: #f9f9f9;
}

.delete-btn {
    background-color: #f44336;
    color: white;
    padding: 0.4rem 0.8rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background-color 0.2s ease;
    display: inline-block;
}

.delete-btn:hover {
    background-color: #d32f2f;
}

.profile-picture-thumb {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 0.75rem;
    vertical-align: middle;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.success-msg, .error-msg {
    padding: 0.7rem;
    margin: 1rem 0;
    border-radius: 4px;
    font-weight: 500;
}

.success-msg {
    color: #155724;
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
}

.error-msg {
    color: #721c24;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
}

/* Responsive tweaks */
@media (max-width: 768px) {
    .profile-container {
        padding: 1.2rem;
        margin: 1rem auto;
    }

    .admin-users-table th,
    .admin-users-table td {
        padding: 0.5rem;
        font-size: 0.9rem;
    }
}

</style>