<?php
require_once 'includes/database.php'; // Assume this file establishes $pdo connection
require_once 'includes/functions.php'; // Assume this file contains helper functions if needed
require_once 'includes/auth.php';     // Assume this handles authentication and session
include 'header.php';             // Assuming header.php contains session_start(), doctype, head, etc.

// --- Data Fetching and Processing (Same as before) ---

// Default filter values
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
$sort_by   = isset($_GET['sort_by'])   ? $_GET['sort_by']     : 'score';
$order     = isset($_GET['order'])     ? $_GET['order']       : 'desc';
$limit     = isset($_GET['limit'])     ? intval($_GET['limit']) : 50;

// Validate sorting parameters
$valid_sort_fields = ['score', 'time_taken', 'submission_time'];
$valid_order       = ['asc', 'desc'];
if (!in_array($sort_by, $valid_sort_fields)) $sort_by = 'score';
if (!in_array($order, $valid_order))       $order   = 'desc';
if ($limit <= 0) $limit = 50; // Ensure positive limit

// Fetch all events for dropdown
$events = [];
try {
    $stmt = $pdo->query(
        "SELECT e.id, q.title AS event_title
         FROM events e
         JOIN quizzes q ON e.quiz_id = q.id
         ORDER BY q.title"
    );
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Database error fetching events: " . $e->getMessage());
}

// Build base leaderboard query
$params = [];
$sql =
    "SELECT
        r.id, r.user_id, u.username, q.title AS event_title, e.id AS event_id,
        r.score, r.total_questions, r.time_taken, r.submission_time,
        ROUND((r.score/r.total_questions)*100,1) AS percentage
    FROM event_quiz_results r
    JOIN users   u ON r.user_id  = u.id
    JOIN events  e ON r.event_id = e.id
    JOIN quizzes q ON e.quiz_id  = q.id";

// Apply event filter
if ($event_id > 0) {
    $sql .= " WHERE r.event_id = ?";
    $params[] = $event_id;
}

// Apply sorting
$sql .= " ORDER BY ";
if ($sort_by === 'score') {
    $sql .= "percentage $order, time_taken ASC"; // Tie-breaker for score sort
} else {
    $sql .= "$sort_by $order";
}

// Apply limit
$sql .= " LIMIT ?";
$params[] = $limit;

// Execute leaderboard query
$results = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Database error fetching leaderboard results: " . $e->getMessage());
}

// Calculate ranks (handle ties)
if (!empty($results)) {
    $rank = 1;
    $prev = ['score' => null, 'time' => null, 'rank' => 1];
    foreach ($results as &$row) {
        if ($prev['score'] !== null && $row['percentage'] == $prev['score'] && $row['time_taken'] == $prev['time']) {
            $row['rank'] = $prev['rank']; // Same rank for tie
        } else {
            $row['rank'] = $rank;
            $prev['rank'] = $rank;
        }
        $prev['score'] = $row['percentage'];
        $prev['time']  = $row['time_taken'];
        $rank++;
    }
    unset($row); // Unset reference
}

// Determine current user's position and result for the selected event
$user_position = null;
$user_result   = null;
if (isset($_SESSION['user_id']) && $event_id > 0) {
    $uid = $_SESSION['user_id'];
    try {
        // Check if user has a result first
        $has_result_stmt = $pdo->prepare("SELECT 1 FROM event_quiz_results WHERE user_id = ? AND event_id = ? LIMIT 1");
        $has_result_stmt->execute([$uid, $event_id]);

        if ($has_result_stmt->fetchColumn()) {
            // Fetch user's result
            $user_result_stmt = $pdo->prepare(
                "SELECT score, total_questions, time_taken, ROUND((score/total_questions)*100,1) AS percentage
                 FROM event_quiz_results WHERE user_id = ? AND event_id = ?"
            );
            $user_result_stmt->execute([$uid, $event_id]);
            $user_result = $user_result_stmt->fetch(PDO::FETCH_ASSOC);

            // Calculate user's rank (simplified slightly for clarity, original logic kept)
            // This subquery approach can be less efficient on large tables. Consider window functions if performance is an issue.
            $user_rank_stmt = $pdo->prepare(
                "SELECT COUNT(*) + 1 AS position
                 FROM event_quiz_results r_comp
                 JOIN event_quiz_results r_user ON r_user.event_id = r_comp.event_id AND r_user.user_id = ?
                 WHERE r_comp.event_id = ?
                   AND (
                        (r_comp.score / r_comp.total_questions) > (r_user.score / r_user.total_questions)
                        OR (
                            (r_comp.score / r_comp.total_questions) = (r_user.score / r_user.total_questions)
                            AND r_comp.time_taken < r_user.time_taken
                           )
                       )"
            );
            $user_rank_stmt->execute([$uid, $event_id]); // Pass uid once, event_id once
            $user_position = $user_rank_stmt->fetchColumn();
        }
    } catch (PDOException $e) {
        error_log("Database error fetching user result/rank: " . $e->getMessage());
    }
}

// Page title
$page_title = 'Quiz Leaderboard';
if ($event_id > 0) {
    // Find the event title for the specific event
    $current_event_title = 'Event'; // Default
    foreach ($events as $evt) {
        if ($evt['id'] == $event_id) {
            $current_event_title = $evt['event_title'];
            break;
        }
    }
    $page_title .= ' - ' . htmlspecialchars($current_event_title);
}

// Format seconds into MM:SS
function formatTime($sec)
{
    if ($sec === null || $sec < 0) return 'N/A';
    $sec = (int) $sec;
    return sprintf('%d:%02d', floor($sec / 60), $sec % 60);
}

// Function to generate rank badges
function getRankBadge($rank)
{
    $rank = (int) $rank;
    $tooltip = 'data-tooltip="Rank ' . $rank . '"';
    switch ($rank) {
        case 1:
            return '<svg class="rank-badge gold" viewBox="0 0 24 24" width="24" height="24" ' . $tooltip . '>
                        <circle cx="12" cy="12" r="11" fill="gold" stroke="#f1c40f" stroke-width="1.5"/>
                        <text x="12" y="16" text-anchor="middle" font-size="12" font-weight="bold" fill="#333">1</text>
                        <path class="sparkle" d="M12 2l1 3m-1 15l-1 3m11-12l-3 1m-15 1l-3-1m16.5-6.5l-1.5 1.5m1.5 13.5l-1.5-1.5m-13.5 1.5l1.5-1.5m-1.5-13.5l1.5 1.5"
                              stroke="#fff" stroke-width="1" stroke-linecap="round"/>
                    </svg>';
        case 2:
            return '<svg class="rank-badge silver" viewBox="0 0 24 24" width="24" height="24" ' . $tooltip . '>
                        <circle cx="12" cy="12" r="11" fill="silver" stroke="#bdc3c7" stroke-width="1.5"/>
                        <text x="12" y="16" text-anchor="middle" font-size="12" font-weight="bold" fill="#333">2</text>
                    </svg>';
        case 3:
            return '<svg class="rank-badge bronze" viewBox="0 0 24 24" width="24" height="24" ' . $tooltip . '>
                        <circle cx="12" cy="12" r="11" fill="#cd7f32" stroke="#a56a29" stroke-width="1.5"/>
                        <text x="12" y="16" text-anchor="middle" font-size="12" font-weight="bold" fill="#333">3</text>
                    </svg>';
        default:
            return '<span class="rank-number" ' . $tooltip . '>' . htmlspecialchars($rank) . '</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Poppins:wght@400;600;700&family=Press+Start+2P&family=Roboto+Condensed:wght@700&family=Roboto+Mono&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <style>
        /* --- Google Fonts --- */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&display=swap');

        /* --- Base Styles (Colors, Fonts, etc. - Adapted from original) --- */
        :root {
            --primary: #4a4e69;
            --secondary: #6a0572;
            --accent: #ff8c42;
            --success: #20b2aa;
            --danger: #e5383b;
            --info: #1e90ff;
            --warning: #ffc300;
            --light: #f4f7f6;
            --dark: #1a1b2b;
            --background: #e0eafc;
            --background-end: #cfdef3;
            --border-radius: 12px;
            --box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease-in-out;
            --spacing-sm: 1rem;
            --spacing-md: 1.5rem;
            --spacing-lg: 2.5rem;
            --font-heading: 'Roboto Condensed', sans-serif;
            --font-body: 'Open Sans', sans-serif;
            --font-mono: 'Roboto Mono', monospace;
            --font-retro: 'Press Start 2P', cursive;
            --sidebar-width: 300px;
            /* Width of the filter sidebar */
        }

        body {
            color: var(--dark);
            background: linear-gradient(135deg, var(--background), var(--background-end));
            transition: padding-left 0.3s ease-in-out;
            /* Smooth transition for content shift */
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            /* Slightly softer base text color */
            /* Base size (16px) */
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
            color: var(--dark-text);
            /* Darker color for headings */
        }

        .container {
            width: 100%;
            height: 100%;
        }

        /* --- Filter Sidebar Styles --- */
        /* --- Filter Sidebar Styles --- */
        .filter-toggle-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            /* Move button to the left */
            z-index: 1050;
            /* Above overlay */
            background-color: var(--secondary);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: var(--transition);
        }

        .filter-toggle-btn:hover {
            background-color: #7b249d;
            transform: scale(1.1);
        }

        .filter-nav {
            position: fixed;
            top: 0;
            right: 0;
            /* Start off-screen to the right */
            width: var(--sidebar-width);
            height: 100%;
            background-color: #ffffff;
            /* White background for sidebar */
            box-shadow: -4px 0 15px rgba(0, 0, 0, 0.15);
            /* Shadow on the left side */
            z-index: 1040;
            /* Below toggle button, above overlay */
            transform: translateX(100%);
            /* Initially hidden */
            transition: transform 0.3s ease-in-out;
            overflow-y: auto;
            /* Allow scrolling if content overflows */
            padding: var(--spacing-lg) var(--spacing-md);
            box-sizing: border-box;
        }

        .filter-nav.open {
            transform: translateX(0);
            /* Slide in */
        }

        .filter-nav-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-lg);
            padding-bottom: var(--spacing-sm);
            border-bottom: 1px solid #eee;
        }

        .filter-nav-header h3 {
            margin: 0;
            font-family: var(--font-heading);
            color: var(--primary);
            font-size: 1.5rem;
        }

        .filter-nav-close-btn {
            background: none;
            border: none;
            font-size: 1.8rem;
            color: var(--primary);
            cursor: pointer;
            padding: 5px;
            line-height: 1;
        }

        .filter-nav-close-btn:hover {
            color: var(--danger);
        }

        /* Styles for form inside the sidebar */
        #leaderboardForm {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
            /* Space between form groups */
        }

        #leaderboardForm .form-group {
            margin-bottom: 0;
            /* Remove default margin */
        }

        #leaderboardForm .form-group label {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.6rem;
            display: block;
            font-size: 0.95rem;
        }

        #leaderboardForm .form-group select {
            width: 100%;
            padding: 0.8rem 1rem;
            font-size: 1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            transition: var(--transition);
            background-color: var(--light);
            color: var(--dark);
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%234a4e69'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1.2em;
            cursor: pointer;
        }

        #leaderboardForm .form-group select:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.25rem rgba(106, 5, 114, 0.25);
        }

        /* Hide the submit button as changes trigger submit */
        #leaderboardForm button[type="submit"] {
            display: none;
        }

        /* Overlay for when sidebar is open */
        .filter-nav-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            /* Semi-transparent black */
            z-index: 1030;
            /* Below sidebar */
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out, visibility 0s 0.3s linear;
        }

        .filter-nav-overlay.active {
            opacity: 1;
            visibility: visible;
            transition: opacity 0.3s ease-in-out, visibility 0s 0s linear;
        }


        /* --- Other Styles (Page Header, Cards, Table - Adapted from original) --- */

        .page-header {
            font-family: var(--font-heading);
            color: var(--primary);
            font-weight: 700;
            padding-bottom: var(--spacing-sm);
            margin-top: var(--spacing-lg);
            /* Add margin top */
            margin-bottom: var(--spacing-lg);
            text-align: center;
            position: relative;
            font-size: 3rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .page-header::after {
            /* Trophy animation */
            content: "🏆";
            display: inline-block;
            margin-left: var(--spacing-sm);
            font-size: 1.3em;
            animation: trophy-bounce 2s infinite ease-in-out;
        }

        @keyframes trophy-bounce {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-10px) rotate(5deg);
            }
        }

        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
            margin-bottom: var(--spacing-lg);
            background-color: white;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(90deg, var(--primary), #5a189a);
            color: var(--light);
            font-weight: 600;
            padding: var(--spacing-sm) var(--spacing-md);
            display: flex;
            align-items: center;
            font-size: 1.3rem;
            font-family: var(--font-heading);
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .card-header i {
            margin-right: var(--spacing-sm);
            font-size: 1.1em;
        }

        /* Specific card headers */
        .card.user-position-card .card-header {
            background: linear-gradient(90deg, var(--success), var(--info));
        }

        .card.user-position-card .card-header i::before {
            content: "\f201";
            /* Chart bar icon */
        }

        .card.leaderboard-card .card-header i::before {
            content: "\f091";
            /* Trophy icon */
        }

        .card-body {
            padding: var(--spacing-md);
        }

        .card.user-position-card .card-body {
            background-color: rgba(32, 178, 170, 0.05);
        }

        .card.user-position-card .card-body strong {
            color: var(--primary);
            font-weight: 700;
        }

        .btn-outline {
            display: inline-block;
            padding: 0.6rem 1.2rem;
            color: var(--primary);
            border: 2px solid var(--primary);
            border-radius: 8px;
            text-decoration: none;
            transition: var(--transition);
            font-weight: 600;
            background-color: transparent;
        }

        .btn-outline:hover {
            background-color: var(--primary);
            color: var(--light);
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 0.8rem;
            margin-bottom: 0;
        }

        .table thead th {
            border-bottom: 2px solid var(--accent);
            color: var(--primary);
            font-weight: 700;
            padding: 1.2rem 1rem;
            text-align: left;
            white-space: nowrap;
            font-family: var(--font-heading);
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        .table thead th:first-child {
            text-align: center;
            width: 60px;
        }

        .table tbody tr {
            background-color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            border-radius: var(--border-radius);
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        .table tbody tr:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.15);
            background-color: var(--light);
        }

        .table tbody td {
            padding: 1.2rem 1rem;
            vertical-align: middle;
            border: none;
        }

        .table tbody tr td:first-child {
            border-top-left-radius: var(--border-radius);
            border-bottom-left-radius: var(--border-radius);
        }

        .table tbody tr td:last-child {
            border-top-right-radius: var(--border-radius);
            border-bottom-right-radius: var(--border-radius);
        }

        .table-info {
            background-color: rgba(30, 144, 255, 0.1) !important;
            font-weight: 600;
            border: 1px solid var(--info);
        }

        .table-info:hover {
            background-color: rgba(30, 144, 255, 0.2) !important;
        }

        /* Column specific styles */
        td:nth-child(1) {
            /* Rank */
            text-align: center;
            width: 60px;
        }

        td:nth-child(2) {
            /* Player Name */
            font-weight: 600;
            color: var(--dark);
        }

        td:nth-child(3) {
            /* Event Title (if shown) */
            color: var(--primary);
            font-size: 0.95rem;
        }

        td:nth-child(3) a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        td:nth-child(3) a:hover {
            color: var(--secondary);
            text-decoration: underline;
        }

        td:nth-child(<?php echo $event_id == 0 ? 4 : 3; ?>) {
            /* Score */
            font-weight: 700;
            color: var(--primary);
        }

        td:nth-child(<?php echo $event_id == 0 ? 5 : 4; ?>) {
            /* Percentage */
            position: relative;
            font-weight: 700;
            color: var(--primary);
        }

        /* Percentage bar */
        td:nth-child(<?php echo $event_id == 0 ? 5 : 4; ?>)::after {
            content: "";
            position: absolute;
            height: 6px;
            background: linear-gradient(to right, var(--success), var(--info));
            border-radius: 3px;
            bottom: 8px;
            left: 1rem;
            width: calc(var(--percentage, 0) * 1%);
            max-width: calc(100% - 2rem);
            opacity: 0.8;
            transition: width 1s ease-out;
        }

        td:nth-child(<?php echo $event_id == 0 ? 6 : 5; ?>) {
            /* Time Taken */
            font-family: var(--font-mono);
            color: var(--dark);
            font-size: 0.9rem;
        }

        td:nth-child(<?php echo $event_id == 0 ? 7 : 6; ?>) {
            /* Date Completed */
            font-size: 0.8rem;
            color: #6c757d;
        }

        /* Rank Cell and Badges */
        .rank-cell {
            position: relative;
            text-align: center;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
            width: 60px;
            font-family: var(--font-retro);
            font-size: 0.9rem;
        }

        .rank-badge {
            vertical-align: middle;
            filter: drop-shadow(0 3px 4px rgba(0, 0, 0, 0.25));
            display: inline-block;
            transition: transform 0.3s ease-in-out;
        }

        .rank-badge:hover {
            transform: scale(1.1);
        }

        .rank-badge text {
            font-family: var(--font-retro);
            pointer-events: none;
            user-select: none;
        }

        .rank-badge.gold circle {
            fill: #ffc300;
            stroke: #ffb000;
        }

        .rank-badge.silver circle {
            fill: #bdc3c7;
            stroke: #a0a4a7;
        }

        .rank-badge.bronze circle {
            fill: #cd7f32;
            stroke: #b5651d;
        }

        .rank-badge .sparkle {
            animation: sparkle 2s infinite linear;
            opacity: 0;
            transform-origin: center;
        }

        @keyframes sparkle {

            0%,
            100% {
                opacity: 0;
                transform: scale(0.8);
            }

            50% {
                opacity: 1;
                transform: scale(1.1);
            }
        }

        .rank-number {
            font-weight: 700;
            color: var(--primary);
            display: inline-block;
            min-width: 20px;
            transition: transform 0.3s ease-in-out;
        }

        .table tbody tr:hover td:first-child .rank-number {
            transform: scale(1.1);
        }

        /* Tooltip Styles */
        [data-tooltip] {
            position: relative;
            cursor: help;
        }

        [data-tooltip]:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            top: -35px;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--dark);
            color: var(--light);
            font-size: 0.8rem;
            padding: 0.6rem 1rem;
            border-radius: var(--border-radius);
            white-space: nowrap;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
            box-shadow: var(--box-shadow);
        }

        [data-tooltip]:hover::before {
            content: "";
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            border: 6px solid transparent;
            border-top-color: var(--dark);
            z-index: 1001;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }

        [data-tooltip]:hover::after,
        [data-tooltip]:hover::before {
            opacity: 1;
        }

        /* Alert/Info Message */
        .alert-info {
            background-color: rgba(30, 144, 255, 0.1);
            border: 1px solid var(--info);
            color: var(--primary);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-top: var(--spacing-md);
            text-align: center;
            font-style: italic;
        }

        /* Confetti Styles */
        .confetti {
            position: fixed;
            top: -10px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            pointer-events: none;
            z-index: 10000;
            animation: fall 3s linear forwards;
        }

        @keyframes fall {
            0% {
                transform: translate(0, 0) rotate(0deg);
                opacity: 1;
            }

            100% {
                transform: translate(var(--fall-x, 0), 100vh) rotate(var(--fall-rotate, 720deg));
                opacity: 0.6;
            }
        }

        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: var(--spacing-md);
            list-style: none;
            padding: 0;
        }

        .pagination li {
            margin: 0 0.25rem;
        }

        .pagination a,
        .pagination span {
            display: inline-block;
            padding: 0.5rem 1rem;
            color: var(--primary);
            border: 1px solid var(--primary);
            border-radius: 0.25rem;
            text-decoration: none;
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .pagination .active span {
            background-color: var(--primary);
            color: var(--light);
            border-color: var(--primary);
            font-weight: 600;
        }

        .pagination a:hover {
            background-color: var(--secondary);
            color: var(--light);
            border-color: var(--secondary);
        }

        .pagination .disabled span {
            color: #6c757d;
            border-color: #6c757d;
            cursor: not-allowed;
            opacity: 0.6;
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .page-header {
                font-size: 2.5rem;
            }

            .table thead th,
            .table tbody td {
                padding: 1rem 0.8rem;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                font-size: 2rem;
            }

            .table {
                display: block;
                overflow-x: auto;
                /* Enable horizontal scroll on small screens */
            }

            .table thead th,
            .table tbody td {
                padding: 0.8rem 0.6rem;
                white-space: nowrap;
                /* Prevent wrapping in table cells */
            }

            td:nth-child(<?php echo $event_id == 0 ? 5 : 4; ?>)::after {
                /* Percentage bar */
                left: 0.6rem;
                max-width: calc(100% - 1.2rem);
            }

            /* Make sidebar wider on smaller screens */
            :root {
                --sidebar-width: 280px;
            }

            body.filter-nav-open {
                /* padding-left: 0; */
                /* Disable content shift on mobile if preferred */
            }
        }

        @media (max-width: 576px) {
            .filter-toggle-btn {
                width: 45px;
                height: 45px;
                font-size: 1.3rem;
                top: 15px;
                right: 15px;
            }

            .page-header {
                font-size: 1.8rem;
                margin-top: 60px;
                /* Ensure space below fixed button */
            }

            .card.user-position-card .card-body div {
                min-width: 120px;
                font-size: 0.9rem;
            }

            .btn-outline {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>

    <button class="filter-toggle-btn" id="filterToggleBtn" aria-label="Toggle Filters">
        <i class="fas fa-filter"></i>
    </button>

    <nav class="filter-nav" id="filterNav">
        <div class="filter-nav-header">
            <h3><i class="fas fa-filter"></i> Filters</h3>
            <button class="filter-nav-close-btn" id="filterNavCloseBtn" aria-label="Close Filters">&times;</button>
        </div>
        <form id="leaderboardForm" method="GET" action="leaderboard.php">
            <div class="form-group">
                <label for="event_id">Select Event:</label>
                <select id="event_id" name="event_id">
                    <option value="0">All Events</option>
                    <?php foreach ($events as $evt): ?>
                        <option value="<?php echo $evt['id']; ?>" <?php echo $evt['id'] == $event_id ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($evt['event_title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="sort_by">Sort By:</label>
                <select id="sort_by" name="sort_by">
                    <option value="score" <?php echo $sort_by == 'score' ? 'selected' : ''; ?>>Score (Highest %)</option>
                    <option value="time_taken" <?php echo $sort_by == 'time_taken' ? 'selected' : ''; ?>>Time Taken (Fastest)</option>
                    <option value="submission_time" <?php echo $sort_by == 'submission_time' ? 'selected' : ''; ?>>Submission Date (Newest)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="order">Order:</label>
                <select id="order" name="order">
                    <?php if ($sort_by === 'time_taken'): // Special labels for time 
                    ?>
                        <option value="asc" <?php echo $order == 'asc' ? 'selected' : ''; ?>>Fastest First</option>
                        <option value="desc" <?php echo $order == 'desc' ? 'selected' : ''; ?>>Slowest First</option>
                    <?php elseif ($sort_by === 'submission_time'): // Special labels for date 
                    ?>
                        <option value="desc" <?php echo $order == 'desc' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="asc" <?php echo $order == 'asc' ? 'selected' : ''; ?>>Oldest First</option>
                    <?php else: // Default for score/percentage 
                    ?>
                        <option value="desc" <?php echo $order == 'desc' ? 'selected' : ''; ?>>Highest First</option>
                        <option value="asc" <?php echo $order == 'asc' ? 'selected' : ''; ?>>Lowest First</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="limit">Show Top:</label>
                <select id="limit" name="limit">
                    <?php foreach ([10, 25, 50, 100] as $n): ?>
                        <option value="<?php echo $n; ?>" <?php echo $limit == $n ? 'selected' : ''; ?>><?php echo $n; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" style="display: none;"></button>
        </form>
    </nav>

    <div class="filter-nav-overlay" id="filterNavOverlay"></div>

    <div class="container">
        <h1 class="page-header"><?php echo htmlspecialchars($page_title); ?></h1>

        <?php if (isset($_SESSION['user_id']) && $event_id > 0): ?>
            <div class="card user-position-card">
                <div class="card-header">
                    <i class="fas fa-chart-bar"></i> Your Position
                </div>
                <div class="card-body">
                    <?php if ($user_result && $user_position !== null): ?>
                        <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; justify-content: space-around; text-align: center;">
                            <div style="flex: 1; min-width: 150px;">Your Rank: <br><strong>#<?php echo htmlspecialchars($user_position); ?></strong></div>
                            <div style="flex: 1; min-width: 150px;">Your Score: <br><strong><?php echo htmlspecialchars($user_result['score']); ?>/<?php echo htmlspecialchars($user_result['total_questions']); ?></strong></div>
                            <div style="flex: 1; min-width: 150px;">Percentage: <br><strong><?php echo htmlspecialchars($user_result['percentage']); ?>%</strong></div>
                            <div style="flex: 1; min-width: 150px;">Time Taken: <br><strong><?php echo htmlspecialchars(formatTime($user_result['time_taken'])); ?></strong></div>
                        </div>
                        <div style="text-align: center; margin-top: var(--spacing-md);">
                            <a href="quiz_result.php?event_id=<?php echo htmlspecialchars($event_id); ?>" class="btn-outline">View Detailed Result</a>
                        </div>
                    <?php else: ?>
                        <p class="alert-info">You have not completed this quiz event yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="card leaderboard-card">
            <div class="card-header">
                <i class="fas fa-trophy"></i> Top <?php echo $limit; ?> Rankings <?php echo $event_id > 0 ? 'for ' . htmlspecialchars($current_event_title) : ' (All Events)'; ?>
            </div>
            <div class="card-body">
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="rank-cell">Rank</th>
                                <th>Player</th>
                                <?php if ($event_id == 0): ?>
                                    <th>Event</th>
                                <?php endif; ?>
                                <th>Score</th>
                                <th>Percentage</th>
                                <th>Time Taken</th>
                                <th>Date Completed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($results)): ?>
                                <tr>
                                    <td colspan="<?php echo $event_id == 0 ? 7 : 6; ?>" class="alert-info">No leaderboard data available for the selected filters.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($results as $row): ?>
                                    <tr class="<?php echo isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id'] ? 'table-info' : ''; ?>">
                                        <td class="rank-cell"><?php echo getRankBadge($row['rank']); ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <?php if ($event_id == 0): ?>
                                            <td><a href="leaderboard.php?event_id=<?php echo htmlspecialchars($row['event_id']); ?>"><?php echo htmlspecialchars($row['event_title']); ?></a></td>
                                        <?php endif; ?>
                                        <td><?php echo htmlspecialchars($row['score']); ?>/<?php echo htmlspecialchars($row['total_questions']); ?></td>
                                        <td data-percentage="<?php echo htmlspecialchars($row['percentage']); ?>" data-tooltip="<?php echo htmlspecialchars($row['percentage']); ?>%">
                                            <?php echo htmlspecialchars($row['percentage']); ?>%
                                        </td>
                                        <td><?php echo htmlspecialchars(formatTime($row['time_taken'])); ?></td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($row['submission_time'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <ul class="pagination">
                    <li class="page-item disabled"><span>Previous</span></li>
                    <li class="page-item active"><span>1</span></li>
                    <li class="page-item disabled"><span>Next</span></li>
                </ul>
            </div>
        </div>
    </div>

    <?php
    // Assuming footer.php includes the closing </body> and </html> tags,
    // and any necessary scripts. If not, add closing tags here.
    // include 'footer.php';
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterNav = document.getElementById('filterNav');
            const filterToggleBtn = document.getElementById('filterToggleBtn');
            const filterNavCloseBtn = document.getElementById('filterNavCloseBtn');
            const overlay = document.getElementById('filterNavOverlay');
            const leaderboardForm = document.getElementById('leaderboardForm');
            const body = document.body;

            // --- Sidebar Toggle Logic ---
            function openNav() {
                filterNav.classList.add('open');
                overlay.classList.add('active');
                // body.classList.add('filter-nav-open'); // Optional: If shifting content
                filterToggleBtn.setAttribute('aria-expanded', 'true');
            }

            function closeNav() {
                filterNav.classList.remove('open');
                overlay.classList.remove('active');
                // body.classList.remove('filter-nav-open'); // Optional: If shifting content
                filterToggleBtn.setAttribute('aria-expanded', 'false');
            }

            if (filterToggleBtn) {
                filterToggleBtn.addEventListener('click', openNav);
            }
            if (filterNavCloseBtn) {
                filterNavCloseBtn.addEventListener('click', closeNav);
            }
            if (overlay) {
                // Close sidebar if overlay is clicked
                overlay.addEventListener('click', closeNav);
            }
            // Close sidebar on Escape key press
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && filterNav.classList.contains('open')) {
                    closeNav();
                }
            });

            // --- Existing Functionality ---

            // Set CSS variable for percentage bar width
            document.querySelectorAll('td[data-percentage]').forEach(cell => {
                const percentage = parseFloat(cell.getAttribute('data-percentage'));
                if (!isNaN(percentage)) {
                    // Use requestAnimationFrame for smoother animation start
                    requestAnimationFrame(() => {
                        cell.style.setProperty('--percentage', percentage);
                    });
                }
            });

            // Confetti effect for top 3 ranks (current user only)
            const userRow = document.querySelector('tr.table-info');
            if (userRow) {
                const userRankCell = userRow.querySelector('.rank-cell');
                if (userRankCell) {
                    // More robust rank extraction
                    const rankText = userRankCell.textContent.trim() || userRankCell.querySelector('text')?.textContent.trim();
                    const rankNumber = parseInt(rankText);

                    if (!isNaN(rankNumber) && rankNumber >= 1 && rankNumber <= 3) {
                        setTimeout(celebrateTopRank, 500); // Add slight delay
                    }
                }
            }

            // Auto-submit form on select change (inside sidebar)
            leaderboardForm.querySelectorAll('select').forEach(el => {
                el.addEventListener('change', () => {
                    // Optional: Add a visual cue like dimming the table
                    const tableCard = document.querySelector('.leaderboard-card');
                    if (tableCard) tableCard.style.opacity = '0.6';
                    leaderboardForm.submit();
                });
            });

            // Update Order dropdown labels based on Sort By selection
            const sortBySelect = document.getElementById('sort_by');
            const orderSelect = document.getElementById('order');
            const orderOptions = { // Store original options text
                desc: orderSelect.querySelector('option[value="desc"]')?.textContent || 'Highest First',
                asc: orderSelect.querySelector('option[value="asc"]')?.textContent || 'Lowest First'
            };

            function updateOrderLabels() {
                const selectedSort = sortBySelect.value;
                const descOption = orderSelect.querySelector('option[value="desc"]');
                const ascOption = orderSelect.querySelector('option[value="asc"]');

                if (!descOption || !ascOption) return; // Safety check

                if (selectedSort === 'time_taken') {
                    ascOption.textContent = 'Fastest First'; // Ascending time is faster
                    descOption.textContent = 'Slowest First'; // Descending time is slower
                } else if (selectedSort === 'submission_time') {
                    descOption.textContent = 'Newest First'; // Descending date is newer
                    ascOption.textContent = 'Oldest First'; // Ascending date is older
                } else { // Default for score/percentage
                    descOption.textContent = orderOptions.desc;
                    ascOption.textContent = orderOptions.asc;
                }
            }

            // Initial update on load
            updateOrderLabels();
            // Update when sort_by changes
            sortBySelect.addEventListener('change', updateOrderLabels);

        }); // End DOMContentLoaded

        // Confetti function (same as before)
        function celebrateTopRank() {
            const confettiCount = 150;
            const container = document.body;
            const fragment = document.createDocumentFragment();
            const colors = ['#ffc300', '#ff8c42', '#20b2aa', '#6a0572', '#1e90ff', '#e5383b'];

            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.classList.add('confetti');
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.animationDelay = Math.random() * 2 + 's';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.width = confetti.style.height = (Math.random() * 6 + 3) + 'px';
                confetti.style.setProperty('--fall-x', `${(Math.random() - 0.5) * 300}px`);
                confetti.style.setProperty('--fall-rotate', `${Math.random() * 1000 - 500}deg`);
                fragment.appendChild(confetti);
            }
            container.appendChild(fragment);
            setTimeout(() => {
                container.querySelectorAll('.confetti').forEach(c => c.remove());
            }, 4000);
        }
    </script>
</body>

</html>