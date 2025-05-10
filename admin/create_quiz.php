<?php
// admin/create_quiz.php
session_start();
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin(); // Ensure admin is logged in

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_id = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : null;
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $instructions = trim($_POST['instructions']);
    $time_limit = intval($_POST['time_limit']);
    $access_type = $_POST['access_type']; // 'direct' or 'password'
    $quiz_mode = $_POST['quiz_mode']; // 'online_sequential', 'online_all_at_once', etc.
    $quiz_password = null;
    $user_id = $_SESSION['user_id'];
    $is_event = isset($_POST['is_event']) && $_POST['is_event'] == '1' ? 1 : 0;

    // Validate inputs
    if (empty($title) || empty($description) || $time_limit <= 0) {
        header("Location: quiz_management.php?message=Invalid quiz details");
        exit();
    }

    // Handle password protection
    if ($access_type === 'password') {
        $quiz_password = trim($_POST['quiz_password']);
        if (empty($quiz_password)) {
            header("Location: quiz_management.php?message=Password required for password-protected quiz");
            exit();
        }
        $quiz_password = password_hash($quiz_password, PASSWORD_DEFAULT);
    }

    try {
        $pdo->beginTransaction();

        if ($quiz_id) {
            // Update existing quiz
            $stmt = $pdo->prepare("
                UPDATE quizzes 
                SET title = ?, description = ?, instructions = ?, time_limit = ?, 
                    access_type = ?, quiz_password = ?, quiz_mode = ?, is_event = ?, event_id = NULL
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([
                $title,
                $description,
                $instructions,
                $time_limit,
                $access_type,
                $quiz_password,
                $quiz_mode,
                $is_event,
                $quiz_id,
                $user_id
            ]);

            if ($stmt->rowCount() === 0) {
                throw new Exception("Quiz not found or unauthorized");
            }
        } else {
            // Insert new quiz
            $stmt = $pdo->prepare("
                INSERT INTO quizzes (title, description, instructions, time_limit, access_type, quiz_password, quiz_mode, user_id, is_event)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $title,
                $description,
                $instructions,
                $time_limit,
                $access_type,
                $quiz_password,
                $quiz_mode,
                $user_id,
                $is_event
            ]);
            $quiz_id = $pdo->lastInsertId();
        }

        // Handle event creation
        if ($is_event) {
            // Validate event timing
            $start_date = $_POST['start_date'] ?? '';
            $start_time = $_POST['start_time'] ?? '';
            $end_date = $_POST['end_date'] ?? '';
            $end_time = $_POST['end_time'] ?? '';

            if (empty($start_date) || empty($start_time) || empty($end_date) || empty($end_time)) {
                throw new Exception("All event timing fields are required");
            }

            $start_datetime = $start_date . ' ' . $start_time;
            $end_datetime = $end_date . ' ' . $end_time;

            // Validate datetime format
            $start_dt = DateTime::createFromFormat('Y-m-d H:i', $start_datetime);
            $end_dt = DateTime::createFromFormat('Y-m-d H:i', $end_datetime);
            if (!$start_dt || !$end_dt || $end_dt <= $start_dt) {
                throw new Exception("Invalid or illogical event timing");
            }

            $is_for_all = isset($_POST['event_for_all']) && $_POST['event_for_all'] == '1' ? 1 : 0;

            // Check if event already exists
            $stmt_check_event = $pdo->prepare("SELECT id FROM events WHERE quiz_id = ?");
            $stmt_check_event->execute([$quiz_id]);
            $existing_event = $stmt_check_event->fetch();

            if ($existing_event) {
                // Update existing event
                $stmt_event = $pdo->prepare("
                    UPDATE events 
                    SET start_datetime = ?, end_datetime = ?, is_for_all = ?
                    WHERE id = ?
                ");
                $stmt_event->execute([$start_datetime, $end_datetime, $is_for_all, $existing_event['id']]);
                $event_id = $existing_event['id'];
            } else {
                // Insert new event
                $stmt_event = $pdo->prepare("
                    INSERT INTO events (quiz_id, start_datetime, end_datetime, is_for_all)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt_event->execute([$quiz_id, $start_datetime, $end_datetime, $is_for_all]);
                $event_id = $pdo->lastInsertId();
            }

            // Update quiz with event_id
            $stmt_update_quiz = $pdo->prepare("UPDATE quizzes SET event_id = ? WHERE id = ?");
            $stmt_update_quiz->execute([$event_id, $quiz_id]);

            // Handle event participants
            if (isset($_POST['event_by_group']) && $_POST['event_by_group'] == '1' && !$is_for_all) {
                // Clear existing participants
                $stmt_clear = $pdo->prepare("DELETE FROM event_participants WHERE event_id = ?");
                $stmt_clear->execute([$event_id]);

                // Insert new participants
                if (isset($_POST['selected_user_ids']) && is_array($_POST['selected_user_ids'])) {
                    $stmt_participant = $pdo->prepare("
                        INSERT INTO event_participants (event_id, user_id)
                        VALUES (?, ?)
                    ");
                    foreach ($_POST['selected_user_ids'] as $participant_user_id) {
                        $participant_user_id = intval($participant_user_id);
                        // Verify user exists
                        $stmt_check_user = $pdo->prepare("SELECT id FROM users WHERE id = ?");
                        $stmt_check_user->execute([$participant_user_id]);
                        if ($stmt_check_user->fetch()) {
                            $stmt_participant->execute([$event_id, $participant_user_id]);
                        }
                    }
                }
            } else {
                // Clear participants if not by group
                $stmt_clear = $pdo->prepare("DELETE FROM event_participants WHERE event_id = ?");
                $stmt_clear->execute([$event_id]);
            }
        } else {
            // If not an event, clear any existing event association
            $stmt_update_quiz = $pdo->prepare("UPDATE quizzes SET event_id = NULL WHERE id = ?");
            $stmt_update_quiz->execute([$quiz_id]);
            // Delete any associated event and participants
            $stmt_delete_event = $pdo->prepare("DELETE FROM events WHERE quiz_id = ?");
            $stmt_delete_event->execute([$quiz_id]);
        }

        $pdo->commit();
        $message = $quiz_id ? "Quiz updated successfully" : "Quiz created successfully";
        header("Location: quiz_management.php?message=" . urlencode($message));
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: quiz_management.php?message=" . urlencode("Error: " . $e->getMessage()));
        exit();
    }
} else {
    header("Location: quiz_management.php");
    exit();
}
?>