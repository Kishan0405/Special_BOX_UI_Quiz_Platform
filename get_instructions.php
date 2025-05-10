<?php
// get_instructions.php
require_once 'includes/database.php';
require_once 'includes/auth.php'; // Assuming isLoggedIn() is defined here

// Set content type to application/json
header('Content-Type: application/json');

$quizId = $_GET['quiz_id'] ?? null;
$eventId = $_GET['event_id'] ?? null;
// Determine userId based on login status
$userId = isLoggedIn() ? $_SESSION['user_id'] : null;

// Basic validation
if (!$quizId) {
    echo json_encode(['success' => false, 'message' => 'Invalid quiz ID']);
    exit;
}

try {
    // Fetch quiz details including the time_limit and question_count
    $stmt = $pdo->prepare("
        SELECT q.title, q.instructions, q.access_type, q.quiz_password, q.time_limit,
               (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as question_count
        FROM quizzes q
        WHERE q.id = ?");
    $stmt->execute([$quizId]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if quiz was found
    if (!$quiz) {
        echo json_encode(['success' => false, 'message' => 'Quiz not found']);
        exit;
    }

    // Prepare the initial response array
    $response = [
        'success' => true,
        'title' => $quiz['title'],
        // Replace '__' with newline characters in instructions
        'instructions' => str_replace('__', "\n", $quiz['instructions']),
        // Add time_limit in seconds to the response
        'time_limit_seconds' => (int)$quiz['time_limit'], // Cast to int
        // Add the actual question count to the response
        'question_count' => (int)$quiz['question_count'], // Added this line
        // Keep the boolean flag derived from the count (might be used elsewhere)
        'has_questions' => ((int)$quiz['question_count'] > 0),
        'requires_password' => ($quiz['access_type'] === 'password' && !empty($quiz['quiz_password'])),
        'is_event' => false,
        'has_attempted' => false, // Default to false
        'current_time' => time() * 1000 // Current server time in milliseconds for client sync
    ];

    // If event ID is provided, fetch event-specific details
    if ($eventId) {
        $stmtEvent = $pdo->prepare("
            SELECT start_datetime, end_datetime
            FROM events
            WHERE id = ? AND quiz_id = ?");
        $stmtEvent->execute([$eventId, $quizId]);
        $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

        // If event details are found
        if ($event) {
            $response['is_event'] = true;
            $response['start_time'] = $event['start_datetime'];
            $response['end_time'] = $event['end_datetime'];

            // Check if user has attempted this event quiz if user is logged in
            if ($userId) {
                $stmtAttempt = $pdo->prepare("
                    SELECT COUNT(*) FROM event_quiz_results
                    WHERE event_id = ? AND user_id = ?");
                $stmtAttempt->execute([$eventId, $userId]);
                // Set has_attempted based on the count
                $response['has_attempted'] = ($stmtAttempt->fetchColumn() > 0);
            }
        }
    }

    // Output the JSON response
    echo json_encode($response);

} catch (PDOException $e) {
    // Log the database error
    error_log("Database error fetching instructions: " . $e->getMessage());
    // Return a generic error message to the client
    echo json_encode(['success' => false, 'message' => 'An internal error occurred.']);
} catch (Exception $e) {
    // Catch any other unexpected errors
     error_log("An unexpected error occurred: " . $e->getMessage());
     echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
}
?>