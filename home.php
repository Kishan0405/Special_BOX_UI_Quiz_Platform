<?php
// home.php
require_once 'includes/database.php';
require_once 'includes/auth.php';
include 'header.php'; // Assumes header.php starts HTML, head, and includes navbar

// Set the default timezone to IST for consistency
date_default_timezone_set('Asia/Kolkata');

$userId = isLoggedIn() ? $_SESSION['user_id'] : null;
$isUserLoggedIn = isLoggedIn(); // For easier use in JS

// Fetch event attempts for the current user if logged in (Only Event IDs are needed here)
$userAttemptedEventIds = [];
if ($userId) {
    try {
        // Optimized to fetch only event_id for checking attempts
        $stmtAttempts = $pdo->prepare("SELECT event_id FROM event_quiz_results WHERE user_id = ?");
        $stmtAttempts->execute([$userId]);
        $userAttemptedEventIds = $stmtAttempts->fetchAll(PDO::FETCH_COLUMN, 0);
    } catch (PDOException $e) {
        error_log("Database error fetching event attempts: " . $e->getMessage());
        // Handle error gracefully, maybe show a message? For now, log it.
    }
}

// Function to safely output HTML
function safe_html(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Fetch username if user is logged in
$username = '';
if ($userId) {
    try {
        $stmtUser  = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmtUser->execute([$userId]);
        $username = $stmtUser->fetchColumn();
    } catch (PDOException $e) {
        error_log("Database error fetching username: " . $e->getMessage());
    }
}

?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- Animate.css -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

<link rel="stylesheet" href="css/new_home.css"> <!-- Custom styles for the page -->

<!-- Enhanced Dynamic Styles are included below -->

<div class="main-content"> <!-- Added a wrapper -->
    <!-- Hero Section -->
    <section class="hero-section text-center text-white py-5 animate__animated animate__fadeIn">
        <div class="container">
            <h1 class="display-3 fw-bold mb-3">
                <?php
                // Check if the username variable is set and not empty
                if (isset($username) && $username !== '') {
                    // If logged in, display the welcome message and icon
                    echo "Welcome " . safe_html($username) . " to QuizMaster! <i class=\"fas fa-rocket text-warning ms-2\"></i>";
                }
                // If not logged in, display a generic welcome message
                else {
                    echo "Welcome to QuizMaster! <i class=\"fas fa-rocket text-warning ms-2\"></i>";
                }
                // If $username is not set or empty, nothing is displayed here.
                ?>
            </h1>
            <p class="lead mb-4 mx-auto" style="max-width: 700px;">
                Flex your brain muscles! Tackle quizzes, conquer events, and become the ultimate Quiz Master!
            </p>
            <!-- Search Bar -->
            <div class="row justify-content-center mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                <div class="col-md-10 col-lg-8 col-xl-7">
                    <form method="GET" action="search_quizzes.php" class="search-box d-flex align-items-start bg-white p-1 shadow-sm">
                        <textarea
                            name="query"
                            class="form-control search-input py-2 flex-grow-1 shadow-none"
                            rows="1"
                            placeholder="Search for quizzes (e.g., Science, History...)"
                            required
                            aria-label="Search quizzes"></textarea>
                        <button
                            type="submit"
                            class="search-button btn btn-primary flex-shrink-0 ms-2"
                            aria-label="Search">
                            <i class="fas fa-search me-1"></i> Search
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </section>

    <div class="container py-5">

        <?php
        // Fetch Featured Quiz - Exclude event quizzes
        $featuredQuiz = null; // Initialize
        try {
            // Prioritize quizzes with more attempts maybe? Or just random non-event.
            $featuredStmt = $pdo->query("SELECT q.* FROM quizzes q LEFT JOIN events e ON q.id = e.quiz_id WHERE e.quiz_id IS NULL ORDER BY RAND() LIMIT 1");
            $featuredQuiz = $featuredStmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error fetching featured quiz: " . $e->getMessage());
        }

        if ($featuredQuiz):
        ?>
            <!-- Featured Quiz Section -->
            <section class="mb-5 pb-3 animate__animated animate__fadeIn" style="animation-delay: 0.4s;">
                <h2 class="section-title text-center mb-5"><i class="fas fa-star text-warning me-2"></i> Featured Quiz <i class="fas fa-star text-warning ms-2"></i></h2>
                <div class="quiz-card featured-quiz-card p-0 shadow-lg border-0 overflow-hidden">
                    <div class="row g-0 align-items-center">
                        <div class="col-md-8">
                            <div class="card-body p-4 p-lg-5">
                                <h3 class="h3 fw-bold mb-2 text-primary"><?php echo safe_html($featuredQuiz['title']); ?></h3>
                                <p class="small-text mb-3 fs-6"><?php echo safe_html($featuredQuiz['description']); ?></p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center p-4 p-lg-5" style="background: linear-gradient(to right, rgba(255, 255, 255, 0), rgba(255, 245, 228, 0.5));">
                            <button class="btn btn-lg btn-warning text-dark fw-bold shadow-sm btn-start show-instructions-btn" data-quiz-id="<?php echo $featuredQuiz['id']; ?>">
                                <i class="fas fa-bolt me-2"></i> Start Now!
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php
        // Fetch Trending Quizzes - Exclude event quizzes
        $trendingQuizzes = [];
        try {
            $trendingQuery = "
                SELECT q.*, COUNT(qa.id) as attempts
                FROM quizzes q
                LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id
                LEFT JOIN events e ON q.id = e.quiz_id
                WHERE e.quiz_id IS NULL
                GROUP BY q.id
                HAVING attempts > 0 -- Only show if actually attempted
                ORDER BY attempts DESC, q.created_at DESC -- Prioritize more recent popular
                LIMIT 3";
            $stmtTrending = $pdo->query($trendingQuery);
            $trendingQuizzes = $stmtTrending->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error fetching trending quizzes: " . $e->getMessage());
        }

        if (!empty($trendingQuizzes)):
        ?>
            <!-- Trending Quizzes Section -->
            <section class="mb-5 pb-3 animate__animated animate__fadeIn" style="animation-delay: 0.6s;">
                <h2 class="section-title text-center mb-5"><i class="fas fa-fire text-danger me-2"></i> Hot & Trending <i class="fas fa-fire text-danger ms-2"></i></h2>
                <div class="row">
                    <?php foreach ($trendingQuizzes as $index => $quizTrending): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="quiz-card trending-quiz-card animate__animated animate__fadeInUp" style="animation-delay: <?php echo 0.1 * $index; ?>s">
                                <div class="card-header bg-transparent border-0 pt-3 pb-0 px-lg-4">
                                    <span class="badge bg-danger bg-opacity-10 text-danger-emphasis py-1 px-2 rounded-pill small fw-medium">
                                        <i class="fas fa-chart-line me-1"></i> Trending
                                    </span>
                                </div>
                                <div class="card-body pt-2 px-lg-4">
                                    <h3 class="h5 fw-bold mb-2 quiz-card-title"><?php echo safe_html($quizTrending['title']); ?></h3>
                                    <p class="small-text mb-3 card-text-clamp"><?php echo safe_html($quizTrending['description']); ?></p>
                                </div>
                                <div class="card-footer d-flex justify-content-between align-items-center bg-light px-lg-4">
                                    <span class="badge text-secondary-emphasis bg-secondary bg-opacity-10 py-1 px-2 rounded-pill small fw-medium">
                                        <i class="fas fa-users me-1"></i> <?php echo (int)$quizTrending['attempts']; ?> Plays
                                    </span>
                                    <button class="btn btn-sm btn-danger btn-start show-instructions-btn" data-quiz-id="<?php echo $quizTrending['id']; ?>">
                                        <i class="fas fa-play me-1"></i> Start
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php
        // Fetch Upcoming/Active Events (Event Quizzes)
        $eventQuizzes = [];
        try {
            // Fetch necessary details including quiz password requirement
            $eventsQuery = "
                SELECT
                    q.id as quiz_id, q.title, q.description, q.quiz_password,
                    e.id as event_id, e.start_datetime, e.end_datetime, e.is_for_all
                FROM events e
                JOIN quizzes q ON e.quiz_id = q.id
                WHERE e.end_datetime > NOW()"; // Only future or current events

            $params = [];
            if ($userId) {
                // Logged-in user sees public events OR events they are invited to
                $eventsQuery .= " AND (e.is_for_all = 1 OR EXISTS (
                    SELECT 1 FROM event_participants ep WHERE ep.event_id = e.id AND ep.user_id = ?
                ))";
                $params[] = $userId;
            } else {
                // Logged-out user only sees public events
                $eventsQuery .= " AND e.is_for_all = 1";
            }
            $eventsQuery .= " ORDER BY e.start_datetime ASC LIMIT 6"; // Show a few more events

            $stmtEvents = $pdo->prepare($eventsQuery);
            $stmtEvents->execute($params);
            $eventQuizzes = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error fetching event quizzes: " . $e->getMessage());
        }

        if (!empty($eventQuizzes)):
        ?>
            <!-- Event Quizzes Section -->
            <section class="mb-5 pb-3 animate__animated animate__fadeIn" style="animation-delay: 0.8s;">
                <h2 class="section-title text-center mb-5"><i class="fas fa-calendar-check text-success me-2"></i> Scheduled Events <i class="fas fa-stopwatch text-success ms-2"></i></h2>
                <div class="row">
                    <?php foreach ($eventQuizzes as $index => $eventQuiz):
                        $startDateTime = new DateTime($eventQuiz['start_datetime'], new DateTimeZone('Asia/Kolkata'));
                        $endDateTime = new DateTime($eventQuiz['end_datetime'], new DateTimeZone('Asia/Kolkata'));
                        $now = new DateTime('now', new DateTimeZone('Asia/Kolkata')); // Already set, reinforcing

                        $isActive = ($now >= $startDateTime && $now <= $endDateTime);
                        $isUpcoming = ($now < $startDateTime);
                        $hasEnded = ($now > $endDateTime);

                        $countdownSeconds = $isUpcoming ? $startDateTime->getTimestamp() - $now->getTimestamp() : null;
                        $hasAttempted = $userId && in_array($eventQuiz['event_id'], $userAttemptedEventIds);

                        $buttonDisabled = true;
                        $buttonText = '';
                        $buttonIcon = 'fa-lock';
                        $buttonClass = 'btn-secondary disabled'; // Default disabled state
                        $statusBadge = '';

                        if ($hasAttempted) {
                            $buttonText = 'Attempted';
                            $buttonIcon = 'fa-check-circle';
                            $buttonClass = 'btn-outline-success disabled'; // Visually indicate completion
                            $statusBadge = '<span class="badge bg-success bg-opacity-10 text-success-emphasis py-1 px-2 rounded-pill small fw-medium float-end"><i class="fas fa-trophy me-1"></i> Completed</span>';
                        } elseif (!$isUserLoggedIn && ($isActive || $isUpcoming)) {
                            $buttonText = 'Login to Join';
                            $buttonIcon = 'fa-sign-in-alt';
                            // Keep $buttonClass as disabled default
                            $statusBadge = '<span class="badge bg-warning bg-opacity-10 text-warning-emphasis py-1 px-2 rounded-pill small fw-medium float-end"><i class="fas fa-user-lock me-1"></i> Login Required</span>';
                        } elseif ($isUpcoming) {
                            $buttonText = 'Coming Soon';
                            $buttonIcon = 'fa-clock';
                            // Keep $buttonClass as disabled default
                            $statusBadge = '<span class="badge bg-info bg-opacity-10 text-info-emphasis py-1 px-2 rounded-pill small fw-medium float-end"><i class="far fa-calendar-alt me-1"></i> Upcoming</span>';
                        } elseif ($isActive) {
                            $buttonText = 'Join Event!';
                            $buttonIcon = 'fa-play-circle';
                            $buttonDisabled = false;
                            $buttonClass = 'btn-success btn-start show-instructions-btn'; // Make it active
                            $statusBadge = '<span class="badge bg-danger bg-opacity-10 text-danger-emphasis py-1 px-2 rounded-pill small fw-medium float-end"><i class="fas fa-satellite-dish me-1"></i> Live Now!</span>';
                        } elseif ($hasEnded) { // Fallback, shouldn't appear due to query
                            $buttonText = 'Event Ended';
                            $buttonIcon = 'fa-ban';
                            // Keep $buttonClass as disabled default
                            $statusBadge = '<span class="badge bg-secondary bg-opacity-10 text-secondary-emphasis py-1 px-2 rounded-pill small fw-medium float-end"><i class="fas fa-times-circle me-1"></i> Ended</span>';
                        }
                    ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="quiz-card event-quiz-card animate__animated animate__fadeInUp" style="animation-delay: <?php echo 0.1 * $index; ?>s">
                                <div class="card-header bg-transparent border-0 pt-3 pb-0 px-lg-4 d-flex justify-content-between align-items-center">
                                    <span class="badge bg-success bg-opacity-10 text-success-emphasis py-1 px-2 rounded-pill small fw-medium">
                                        <i class="fas fa-calendar-day me-1"></i> Event
                                    </span>
                                    <?php echo $statusBadge; ?>
                                </div>
                                <div class="card-body pt-2 px-lg-4">
                                    <h3 class="h5 fw-bold mb-2 quiz-card-title">
                                        <?php if ($eventQuiz['quiz_password']) {
                                            echo '<i class="fas fa-lock fa-xs text-muted me-1" title="Password Protected"></i>';
                                        } ?>
                                        <?php echo safe_html($eventQuiz['title']); ?>
                                    </h3>
                                    <p class="small-text mb-2 card-text-clamp"><?php echo safe_html($eventQuiz['description']); ?></p>

                                    <div class="mb-1 small-text d-flex align-items-center text-success-emphasis">
                                        <i class="far fa-calendar-alt me-2 fa-fw" title="Start Time"></i>
                                        Starts: <?php echo $startDateTime->format('M j, g:i A T'); ?>
                                    </div>
                                    <div class="mb-2 small-text d-flex align-items-center text-danger-emphasis">
                                        <i class="far fa-calendar-times me-2 fa-fw" title="End Time"></i>
                                        Ends:   <?php echo $endDateTime->format('M j, g:i A T'); ?>
                                    </div>

                                    <?php if ($isUpcoming && $countdownSeconds > 0): ?>
                                        <div class="text-center mt-2 mb-1">
                                            <span class="badge countdown-timer bg-light text-dark border" data-countdown-seconds="<?php echo $countdownSeconds; ?>">
                                                Launching in...
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="card-footer text-center bg-light px-lg-4">
                                    <button class="btn btn-sm <?php echo $buttonClass; ?> w-100"
                                        data-quiz-id="<?php echo $eventQuiz['quiz_id']; ?>"
                                        data-event-id="<?php echo $eventQuiz['event_id']; ?>"
                                        data-has-attempted="<?php echo $hasAttempted ? 'true' : 'false'; ?>"
                                        <?php echo $buttonDisabled ? 'disabled' : ''; ?>>
                                        <i class="fas <?php echo $buttonIcon; ?> me-1"></i> <?php echo $buttonText; ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($isUserLoggedIn): ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info text-center shadow-sm d-inline-block mx-auto" style="max-width: 1000px;">
                        <i class="fas fa-info-circle fa-lg me-2 align-middle"></i> No upcoming or active events relevant to you right now. Explore general quizzes or check back soon!
                    </div>
                </div>
            <?php else: // Logged out and no public events 
            ?>
                <div class="col-12 text-center">
                    <div class="alert alert-warning text-center shadow-sm d-inline-block mx-auto" style="max-width: 1000px;">
                        <i class="fas fa-user-lock fa-lg me-2 align-middle"></i> Only public events are shown.
                        <a href="login.php" class="alert-link fw-bold">Login</a> or <a href="register.php" class="alert-link fw-bold">Register</a> to see events you're invited to!
                    </div>
                </div>
            <?php endif; ?>
            </section>

            <!-- All Available General Quizzes Section -->
            <section class="mb-5 pb-3 animate__animated animate__fadeIn" style="animation-delay: 1.0s;">
                <h2 class="section-title text-center mb-5"><i class="fas fa-puzzle-piece text-primary me-2"></i> Explore General Quizzes <i class="fas fa-brain text-primary ms-2"></i></h2>
                <div class="row">
                    <?php
                    $generalQuizzes = [];
                    try {
                        // Fetch general quizzes, maybe ordered by creation date
                        $stmt = $pdo->query("SELECT q.* FROM quizzes q LEFT JOIN events e ON q.id = e.quiz_id WHERE e.quiz_id IS NULL ORDER BY q.created_at DESC");
                        $generalQuizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        error_log("Database error fetching general quizzes: " . $e->getMessage());
                    }

                    if (!empty($generalQuizzes)):
                        foreach ($generalQuizzes as $index => $quiz):
                    ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="quiz-card general-quiz-card animate__animated animate__fadeInUp" style="animation-delay: <?php echo 0.05 * $index; ?>s">
                                    <div class="card-header bg-transparent border-0 pt-3 pb-0 px-lg-4">
                                        <span class="badge bg-primary bg-opacity-10 text-primary-emphasis py-1 px-2 rounded-pill small fw-medium">
                                            <i class="fas fa-book-open me-1"></i> General
                                        </span>
                                    </div>
                                    <div class="card-body pt-2 px-lg-4">
                                        <h3 class="h5 fw-bold mb-2 quiz-card-title">
                                            <?php if ($quiz['quiz_password']) {
                                                echo '<i class="fas fa-lock fa-xs text-muted me-1" title="Password Protected"></i>';
                                            } ?>
                                            <?php echo safe_html($quiz['title']); ?>
                                        </h3>
                                        <p class="small-text mb-3 card-text-clamp"><?php echo safe_html($quiz['description']); ?></p>
                                    </div>
                                    <div class="card-footer text-end bg-light px-lg-4">
                                        <button class="btn btn-sm btn-primary btn-start show-instructions-btn" data-quiz-id="<?php echo $quiz['id']; ?>">
                                            <i class="fas fa-play me-1"></i> Take Quiz
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center">
                            <div class="card border-0 shadow-sm bg-light p-4 mx-auto" style="max-width: 500px;">
                                <img src="includes/empty-box.png" alt="No quizzes found" class="img-fluid mb-3 mx-auto" style="width: 90px; opacity: 0.7;">
                                <h5 class="text-muted fw-normal mb-1">Aw, Snap!</h5>
                                <p class="text-secondary small mb-0">Looks like the quiz cupboard is bare for now. Check back soon for new challenges!</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <?php if ($isUserLoggedIn): ?>
                <!-- My Recent Activity Section -->
                <section class="animate__animated animate__fadeIn" style="animation-delay: 1.2s;">
                    <h2 class="section-title text-center mb-5"><i class="fas fa-history text-info me-2"></i> My Recent Brain Battles <i class="fas fa-user-astronaut text-info ms-2"></i></h2>
                    <div class="row justify-content-center">
                        <!-- Recent General Quiz Attempts -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow-sm border-0 rounded-lg overflow-hidden h-100">
                                <div class="card-header text-white" style="background: linear-gradient(135deg, #3b82f6, #2563eb); padding: 0.8rem 1.2rem;">
                                    <h5 class="mb-0 fw-normal fs-6"><i class="fas fa-tasks me-2"></i> Recent General Quizzes</h5>
                                </div>
                                <?php
                                $recentAttempts = [];
                                try {
                                    // Updated query to use 'total' from quiz_attempts and filter non-event quizzes using is_event
                                    $stmtRecent = $pdo->prepare("
                SELECT qa.attempt_date, qa.score, qa.total, q.title
                FROM quiz_attempts qa
                JOIN quizzes q ON qa.quiz_id = q.id
                WHERE qa.user_id = ? AND qa.is_event = 0
                ORDER BY qa.attempt_date DESC LIMIT 5");
                                    $stmtRecent->execute([$userId]);
                                    $recentAttempts = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);
                                } catch (PDOException $e) {
                                    error_log("Database error fetching recent general attempts: " . $e->getMessage());
                                }

                                if (!empty($recentAttempts)):
                                ?>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($recentAttempts as $attempt):
                                            $totalQ = (int)($attempt['total'] ?? 0); // Use 'total' column
                                            $score = (int)$attempt['score'];
                                            $scorePercentage = ($totalQ > 0) ? round(($score / $totalQ) * 100) : 0;
                                            $badgeClass = 'bg-danger';
                                            if ($scorePercentage >= 80) $badgeClass = 'bg-success';
                                            elseif ($scorePercentage >= 50) $badgeClass = 'bg-warning text-dark';
                                        ?>
                                            <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center small activity-item">
                                                <div>
                                                    <span class="fw-bold d-block text-primary-emphasis"><?php echo safe_html($attempt['title']); ?></span>
                                                    <span class="text-muted" style="font-size: 0.8em;"><?php echo date("M j, Y, g:i A", strtotime($attempt['attempt_date'])); ?></span>
                                                </div>
                                                <span class="badge <?php echo $badgeClass; ?> rounded-pill fs-7">
                                                    <?php echo $score; ?>/<?php echo $totalQ; ?> (<?php echo $scorePercentage; ?>%)
                                                </span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <div class="card-footer bg-light text-center py-2">
                                        <a href="my_attempts.php" class="btn btn-link btn-sm text-primary fw-medium p-0">View All Attempts</a>
                                    </div>
                                <?php else: ?>
                                    <div class="card-body text-center py-5 text-muted">
                                        <i class="fas fa-ghost fa-3x mb-3 text-light-emphasis"></i>
                                        <p class="mb-0 small">No general quiz voyages logged yet. Set sail!</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Recent Event Results -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow-sm border-0 rounded-lg overflow-hidden h-100">
                                <div class="card-header text-white" style="background: linear-gradient(135deg, #ec4899, #d946ef); padding: 0.8rem 1.2rem;">
                                    <h5 class="mb-0 fw-normal fs-6"><i class="fas fa-trophy me-2"></i> Recent Event Results</h5>
                                </div>
                                <?php
                                $recentEventAttempts = [];
                                try {
                                    $stmtRecentEvents = $pdo->prepare("
                                SELECT eqr.submission_time, eqr.score, eqr.total_questions, e.id as event_id, q.title
                                FROM event_quiz_results eqr
                                JOIN events e ON eqr.event_id = e.id
                                JOIN quizzes q ON e.quiz_id = q.id
                                WHERE eqr.user_id = ? ORDER BY eqr.submission_time DESC LIMIT 5");
                                    $stmtRecentEvents->execute([$userId]);
                                    $recentEventAttempts = $stmtRecentEvents->fetchAll(PDO::FETCH_ASSOC);
                                } catch (PDOException $e) {
                                    error_log("Database error fetching recent event attempts: " . $e->getMessage());
                                }

                                if (!empty($recentEventAttempts)):
                                ?>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($recentEventAttempts as $eventAttempt):
                                            $totalQ = (int)$eventAttempt['total_questions'];
                                            $score = (int)$eventAttempt['score'];
                                            $scorePercentage = ($totalQ > 0) ? round(($score / $totalQ) * 100) : 0;
                                            $badgeClass = 'bg-danger';
                                            if ($scorePercentage >= 80) $badgeClass = 'bg-success';
                                            elseif ($scorePercentage >= 50) $badgeClass = 'bg-warning text-dark';
                                        ?>
                                            <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center small activity-item">
                                                <div>
                                                    <span class="fw-bold d-block" style="color: #a21caf;"><?php echo safe_html($eventAttempt['title']); ?></span>
                                                    <span class="text-muted" style="font-size: 0.8em;"><?php echo date("M j, Y, g:i A", strtotime($eventAttempt['submission_time'])); ?></span>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge <?php echo $badgeClass; ?> rounded-pill fs-7 mb-1 d-block">
                                                        <?php echo $score; ?>/<?php echo $totalQ; ?> (<?php echo $scorePercentage; ?>%)
                                                    </span>
                                                    <a href="view_result.php?event_id=<?php echo $eventAttempt['event_id']; ?>" class="btn btn-link btn-sm text-primary fw-medium py-0 px-1" style="font-size: 0.8em;">
                                                        <i class="fas fa-eye fa-xs"></i> View Result
                                                    </a>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <div class="card-footer bg-light text-center py-2">
                                        <a href="my_events.php" class="btn btn-link btn-sm text-primary fw-medium p-0">View All Event Results</a>
                                    </div>
                                <?php else: ?>
                                    <div class="card-body text-center py-5 text-muted">
                                        <i class="far fa-calendar-times fa-3x mb-3 text-light-emphasis"></i>
                                        <p class="mb-0 small">No event showdowns recorded yet. Join the next one!</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; // End $isUserLoggedIn check for activity 
            ?>
    </div> <!-- /.container -->
</div> <!-- /.main-content -->
<?php
include 'footer.php'; // Include footer
?>

<!-- Instructions Modal -->
<div class="modal fade" id="instructionsModal" tabindex="-1" aria-labelledby="instructionsModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="instructionsModalLabel"><i class="fas fa-clipboard-list me-2"></i> Quiz Prep Zone</h5>
                <button type="button" class="btn-close btn-close" data-bs-dismiss="modal" aria-label="Close" aria-label="Close Modal"></button>
            </div>
            <div class="modal-body">
                <div id="loadingIndicator" class="text-center my-5">
                    <div class="loader"></div>
                    <p class="text-muted mt-2">Fetching mission briefing...</p>
                </div>
                <div id="modalContentArea" style="display: none;">
                    <h4 id="modalQuizTitle" class="fw-bold mb-3 text-primary-emphasis"></h4>
                    <div id="modalAlertsContainer" class="mb-4"></div>
                    <div class="bg-light p-3 rounded border mb-4">
                        <strong class="d-block mb-2 text-secondary-emphasis"><i class="fas fa-info-circle me-1"></i> Instructions:</strong>
                        <div id="modalInstructionsContent" class="small text-secondary"></div>
                    </div>
                    <div id="modalDetailsContent" class="small text-muted"></div>
                </div>
            </div>
            <div class="modal-footer justify-content-between" id="modalFooterArea" style="display: none;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
                <div id="modalActionButtons">
                    <a href="#" id="viewResultLinkModal" class="btn btn-info" style="display: none;">
                        <i class="fas fa-eye me-1"></i> View My Result
                    </a>
                    <button type="button" id="verifyPasswordBtnModal" class="btn btn-primary" style="display: none;">
                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true" style="display: none;"></span>
                        <i class="fas fa-key me-1"></i> Verify & Proceed
                    </button>
                    <button type="button" id="startQuizBtnModal" class="btn btn-success" style="display: none;">
                        <i class="fas fa-play-circle me-1"></i> Let's Go!
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.tailwindcss.com"></script>

<script>
    // --- JAVASCRIPT (Identical to provided, no functional changes needed for style update) ---
    function formatDateTimeReadable(dateTimeString) {
        if (!dateTimeString) return 'N/A';
        try {
            // Parse ISO 8601 datetime string (e.g., "2025-05-01T18:11:00+05:30")
            const date = new Date(dateTimeString);
            const options = {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                timeZoneName: 'short'
            };
            return date.toLocaleString(undefined, options); // e.g., "May 1, 2025, 6:11 PM IST"
        } catch (e) {
            console.error("Error formatting date:", e, dateTimeString);
            return dateTimeString; // Fallback
        }
    }

    function startCountdown(element) {
        let totalSeconds = parseInt(element.getAttribute('data-countdown-seconds'), 10);
        const parentCard = $(element).closest('.quiz-card');
        const startButton = parentCard.find('.btn-start'); // Get the specific button for this card

        if (isNaN(totalSeconds) || totalSeconds < 0) {
            element.textContent = 'Starting soon...';
            return; // Invalid duration
        }

        const intervalId = setInterval(() => {
            if (totalSeconds <= 0) {
                clearInterval(intervalId);
                element.innerHTML = '<i class="fas fa-play-circle me-1"></i> Event Live!';
                element.classList.remove('bg-light', 'text-dark', 'border');
                element.classList.add('live-indicator-badge'); // Change appearance

                // Only enable button if not already attempted and logged in
                const hasAttempted = startButton.data('has-attempted') === true;
                const isUserLoggedIn = <?php echo json_encode($isUserLoggedIn); ?>;
                if (isUserLoggedIn && !hasAttempted) {
                    startButton.prop('disabled', false)
                        .removeClass('btn-secondary disabled')
                        .addClass('btn-success')
                        .html('<i class="fas fa-play-circle me-1"></i> Join Event!');
                    // Also update status badge if exists
                    parentCard.find('.float-end') // Assuming status badge uses float-end
                        .removeClass('bg-info bg-opacity-10 text-info-emphasis')
                        .addClass('bg-danger bg-opacity-10 text-danger-emphasis')
                        .html('<i class="fas fa-satellite-dish me-1"></i> Live Now!');
                }
                element.closest('.event-quiz-card').addClass('event-live'); // Add class to card
                return;
            }

            const days = Math.floor(totalSeconds / (3600 * 24));
            const hours = Math.floor((totalSeconds % (3600 * 24)) / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            let timeString = 'Starts in: ';
            if (days > 0) timeString += `${days}d `;
            if (days > 0 || hours > 0) timeString += `${String(hours).padStart(2, '0')}h `;
            if (days > 0 || hours > 0 || minutes > 0) timeString += `${String(minutes).padStart(2, '0')}m `;
            timeString += `${String(seconds).padStart(2, '0')}s`;

            element.textContent = timeString.trim();
            totalSeconds--;
        }, 1000);
        // Store intervalId for potential cleanup if needed
        element.dataset.intervalId = intervalId;
    }

    $(document).ready(function() {
        // Initialize all countdown timers
        $('.countdown-timer[data-countdown-seconds]').each(function() {
            startCountdown(this);
        });

        $('.search-input').on('keypress', function(e) {
            if (e.which === 13 && !e.shiftKey) { // Enter key pressed without Shift
                e.preventDefault(); // Prevent adding a newline in textarea
                $(this).closest('form').submit(); // Submit the search form
            }
        });

        // Clamp long descriptions
        $('.card-text-clamp').each(function() {
            // Very basic clamp - consider a JS library for more robust clamping if needed
            const maxChars = 110; // Slightly increased length allowance
            let text = $(this).text();
            if (text.length > maxChars) {
                $(this).text(text.substring(0, maxChars) + '...');
            }
        });

        // --- Modal Logic ---
        const instructionsModal = new bootstrap.Modal('#instructionsModal');
        const isUserLoggedIn = <?php echo json_encode($isUserLoggedIn); ?>;
        let currentQuizId = null;
        let currentEventId = null;
        let currentPasswordRequired = false;

        // Cache Modal jQuery Elements
        const $modal = $('#instructionsModal');
        const $loadingIndicator = $('#loadingIndicator');
        const $modalContentArea = $('#modalContentArea');
        const $modalFooterArea = $('#modalFooterArea');
        const $modalTitle = $('#modalQuizTitle');
        const $modalInstructions = $('#modalInstructionsContent');
        const $modalAlertsContainer = $('#modalAlertsContainer');
        const $modalDetailsContainer = $('#modalDetailsContent');
        const $modalActionButtons = $('#modalActionButtons');

        // Specific buttons (now dynamically generated, these are the IDs we'll use)
        const verifyPasswordBtnId = 'verifyPasswordBtnModal';
        const startQuizBtnId = 'startQuizBtnModal';
        const viewResultLinkModalId = 'viewResultLinkModal';

        const passwordPromptHtml = `
        <div id="passwordPromptModal" class="alert alert-light border p-3 mb-0">
            <label for="quizPasswordModal" class="form-label fw-bold d-block mb-2"><i class="fas fa-lock me-1 text-primary"></i> Enter Password to Proceed</label>
            <input type="password" id="quizPasswordModal" class="form-control mb-1 shadow-none" placeholder="Quiz Password" autocomplete="current-password">
            <div id="passwordErrorModal" class="text-danger small mt-1" style="display: none;"><i class="fas fa-exclamation-circle me-1"></i> <span></span></div>
        </div>`;

        const timeRestrictionHtml = (start, end, type = 'warning') => `
        <div class="alert alert-${type} p-3 mb-2 d-flex align-items-center">
             <i class="fas ${type === 'info' ? 'fa-clock' : type === 'success' ? 'fa-play-circle' : 'fa-calendar-times'} fa-lg me-3"></i>
            <div>
                 <strong class="d-block mb-1">${type === 'info' ? 'Event Starts Soon' : type === 'success' ? 'Event Active' : 'Event Window'}</strong>
                <span class="d-block small">Available From: <strong>${start}</strong></span>
                <span class="d-block small">Available Until: <strong>${end}</strong></span>
             </div>
        </div>`;
        const alreadyAttemptedHtml = (eventId) => `
        <div class="alert alert-success p-3 mb-2 d-flex align-items-center">
             <i class="fas fa-check-circle fa-lg me-3"></i>
             <div>
                <strong class="d-block mb-1">Already Completed!</strong>
                 <span class="d-block small">You've already conquered this event quiz.</span>
            </div>
        </div>`;
        const loginRequiredHtml = `
        <div class="alert alert-warning p-3 mb-2 d-flex align-items-center">
             <i class="fas fa-user-lock fa-lg me-3"></i>
            <div>
                <strong class="d-block mb-1">Login Required</strong>
                 <span class="d-block small">Ready for the challenge? Please <a href='login.php' class='fw-bold alert-link'>login</a> or <a href='register.php' class='fw-bold alert-link'>register</a> first!</span>
             </div>
        </div>`;
        const generalErrorHtml = (message) => `
        <div class="alert alert-danger p-3 mb-2">
            <i class="fas fa-exclamation-triangle me-2"></i> ${message || 'Oops! Something went wrong.'}
        </div>`;
        const noQuestionsHtml = `
        <div class="alert alert-info p-3 mb-2 d-flex align-items-center">
             <i class="fas fa-info-circle fa-lg me-3"></i>
             <div>
                <strong class="d-block mb-1">Under Construction!</strong>
                <span class="d-block small">This quiz doesn't have any questions yet. Please check back later.</span>
             </div>
         </div>`;
        const generalDetailsHtml = (details) => {
            // Check if time_limit_seconds exists and is greater than 0
            let timeLimitHtml = '<span class="me-3 d-inline-flex align-items-center"><i class="fas fa-infinity me-2 text-primary opacity-75"></i> No Time Limit</span>';
            if (details.time_limit_seconds && details.time_limit_seconds > 0) {
                // Convert seconds to minutes and round for display
                const displayMinutes = Math.round(details.time_limit_seconds / 60);
                timeLimitHtml = `<span class="me-3 d-inline-flex align-items-center"><i class="fas fa-hourglass-half me-2 text-primary opacity-75"></i> ${displayMinutes} min Limit</span>`;
            }

            // Check if question_count exists and is greater than 0
            const questionCountHtml = (details.question_count && details.question_count > 0) ?
                `<span class="me-3 d-inline-flex align-items-center"><i class="fas fa-question-circle me-2 text-primary opacity-75"></i> ${details.question_count} Questions</span>` :
                ''; // Hide if 0 questions

            return `
            <div class="border-top pt-3 mt-4 text-muted d-flex flex-wrap gap-3">
                ${questionCountHtml}
                ${timeLimitHtml}
                ${/* Can add more details here like category, creator etc. if available */ ''}
            </div>`;
        };

        // === Attach click handler to ALL .show-instructions-btn (even future ones if loaded dynamically) ===
        // Using event delegation on a static parent ('body' or '.main-content')
        $('.main-content').on('click', '.show-instructions-btn', function() {
            // Ignore clicks on disabled buttons
            if ($(this).prop('disabled')) {
                return;
            }

            currentQuizId = $(this).data('quiz-id');
            currentEventId = $(this).data('event-id') || null;
            const buttonHasAttempted = $(this).data('has-attempted') === true; // Attempt status from the button itself

            // Reset Modal State
            $loadingIndicator.show();
            $modalContentArea.hide();
            $modalFooterArea.hide();
            $modalAlertsContainer.empty();
            $modalDetailsContainer.empty();
            $modalTitle.text('Loading...');
            $modalInstructions.html('');
            $modalActionButtons.empty(); // Clear previous buttons
            currentPasswordRequired = false;

            instructionsModal.show();

            // === AJAX Call to Get Instructions ===
            $.ajax({
                url: 'get_instructions.php',
                type: 'GET',
                data: {
                    quiz_id: currentQuizId,
                    event_id: currentEventId
                },
                dataType: 'json',
                success: function(response) {
                    $loadingIndicator.hide();

                    if (!response || !response.success) {
                        $modalAlertsContainer.html(generalErrorHtml(response?.message || 'Could not load quiz details. Try again later.'));
                        $modalContentArea.show();
                        $modalFooterArea.show(); // Show footer maybe for cancel button
                        return;
                    }

                    // Populate Modal Content
                    $modalTitle.text(response.title || 'Quiz Details');
                    // Enhanced replacement for newlines and added bolding for potential markers like **text**
                    let instructionsHtml = (response.instructions || 'No specific instructions provided. Answer all questions to the best of your ability!')
                        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>') // Bold text like **this**
                        .replace(/__/g, '<br>')
                        .replace(/\n/g, '<br>');
                    $modalInstructions.html(instructionsHtml);
                    $modalDetailsContainer.html(generalDetailsHtml(response));

                    // Determine Quiz State and Permissions
                    let canStart = true; // Assume can start unless restricted
                    let requiresPasswordNow = false; // Needs password verification step
                    let showViewResult = false;

                    // 1. Check for Questions
                    if (!response.has_questions) {
                        $modalAlertsContainer.append(noQuestionsHtml);
                        canStart = false;
                    }

                    // 2. Handle Event Logic
                    if (response.is_event) {
                        if (!isUserLoggedIn) {
                            $modalAlertsContainer.append(loginRequiredHtml);
                            canStart = false;
                        } else if (response.has_attempted || buttonHasAttempted) { // Check server response AND button data
                            $modalAlertsContainer.append(alreadyAttemptedHtml(currentEventId));
                            showViewResult = true; // Allow viewing results
                            canStart = false;
                        } else {
                            // Check Time Window (using server time for accuracy)
                            const nowServer = new Date(response.current_time + 'Z'); // Ensure UTC interpretation
                            const start = new Date(response.start_time + 'Z');
                            const end = new Date(response.end_time + 'Z');
                            const formattedStart = formatDateTimeReadable(response.start_time);
                            const formattedEnd = formatDateTimeReadable(response.end_time);

                            if (nowServer < start) { // Upcoming
                                $modalAlertsContainer.append(timeRestrictionHtml(formattedStart, formattedEnd, 'info'));
                                canStart = false;
                            } else if (nowServer > end) { // Ended
                                $modalAlertsContainer.append(timeRestrictionHtml(formattedStart, formattedEnd, 'secondary')); // Use different style for ended
                                // Could potentially allow viewing results here too if configured
                                canStart = false;
                            } else { // Active
                                // Optional: Show active time window for clarity - now handled by general detail
                                $modalAlertsContainer.append(timeRestrictionHtml(formattedStart, formattedEnd, 'success'));
                            }
                        }
                    }

                    // 3. Handle Password Requirement (only if 'canStart' is still true)
                    currentPasswordRequired = response.requires_password ?? false;
                    if (currentPasswordRequired && canStart) {
                        // Append after any existing alerts like time restriction
                        $modalAlertsContainer.append(passwordPromptHtml);
                        requiresPasswordNow = true; // User must enter password first
                    }

                    // 4. Setup Action Buttons based on state
                    if (showViewResult && currentEventId) {
                        // Made button slightly bigger with standard Bootstrap classes
                        $modalActionButtons.append(`<a href="view_result.php?event_id=${currentEventId}" id="${viewResultLinkModalId}" class="btn btn-info btn-lg-sm"><i class="fas fa-eye me-1"></i> View My Result</a>`);
                    }

                    if (canStart) {
                        // Made buttons slightly bigger with standard Bootstrap classes
                        if (requiresPasswordNow) {
                            // Show only verify button
                            $modalActionButtons.append(`
                             <button type="button" id="${verifyPasswordBtnId}" class="btn btn-primary btn-lg-sm">
                                 <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true" style="display: none;"></span>
                                 <i class="fas fa-key icon me-1"></i> Verify & Proceed
                             </button>
                         `);
                        } else {
                            // Show direct start button
                            $modalActionButtons.append(`
                            <button type="button" id="${startQuizBtnId}" class="btn btn-success btn-lg-sm">
                                <i class="fas fa-play-circle me-1"></i> Let's Go!
                            </button>
                         `);
                        }
                    } else {
                        // If cannot start and no view result shown, the cancel button is always there.
                        // No additional button needed usually, alerts explain the situation.
                    }

                    $modalContentArea.show();
                    $modalFooterArea.show();

                    // Auto-focus password field if it's shown
                    if (requiresPasswordNow) {
                        setTimeout(() => {
                            $('#quizPasswordModal').focus();
                        }, 500); // Small delay for modal animation
                    }

                }, // end success
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAX Error fetching instructions:", textStatus, errorThrown, jqXHR.responseText);
                    $loadingIndicator.hide();
                    $modalAlertsContainer.html(generalErrorHtml('Failed to load quiz details. Please check your connection and try again.'));
                    $modalContentArea.show();
                    $modalFooterArea.show(); // Show footer for cancel
                }
            }); // end ajax
        }); // end .show-instructions-btn click

        // --- Modal: Password Verification Logic ---
        // Using event delegation for the dynamically added button
        $modal.on('click', `#${verifyPasswordBtnId}`, function() {
            const $passwordInput = $('#quizPasswordModal');
            const $passwordError = $('#passwordErrorModal');
            const password = $passwordInput.val().trim();
            const $button = $(this);
            const $spinner = $button.find('.spinner-border');
            const $icon = $button.find('.icon');

            if (!password) {
                $passwordError.find('span').text('Password cannot be empty.');
                $passwordError.show();
                $passwordInput.addClass('is-invalid').focus();
                return;
            }
            $passwordError.hide();
            $passwordInput.removeClass('is-invalid');

            $button.prop('disabled', true);
            $spinner.show();
            $icon.hide();

            $.ajax({
                url: 'validate_password.php', // Endpoint to check password
                type: 'POST',
                data: JSON.stringify({
                    quiz_id: currentQuizId,
                    password: password
                }),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response && response.success) {
                        // Password correct! Hide password prompt and show start button
                        $('#passwordPromptModal').remove(); // Remove the prompt visually
                        $('#passwordErrorModal').hide();
                        $button.remove(); // Remove the verify button
                        currentPasswordRequired = false; // Update state

                        // Add the final 'Start' button if it wasn't there
                        if ($(`#${startQuizBtnId}`).length === 0) {
                            $modalActionButtons.append(`
                             <button type="button" id="${startQuizBtnId}" class="btn btn-success btn-lg-sm">
                                 <i class="fas fa-play-circle me-1"></i> Let's Go!
                             </button>
                         `);
                        }
                        // Potentially trigger the start immediately or let user click 'Let's Go' now
                        // For better UX, let user click 'Let's Go' now
                        $(`#${startQuizBtnId}`).focus();

                    } else {
                        // Incorrect password or other error
                        $passwordError.find('span').text(response?.error || 'Incorrect password. Please try again.');
                        $passwordError.show();
                        $passwordInput.addClass('is-invalid').focus().select();
                        $button.prop('disabled', false); // Re-enable button
                        $spinner.hide();
                        $icon.show();
                    }
                },
                error: function() {
                    $passwordError.find('span').text('An error occurred during password verification. Please try again.');
                    $passwordError.show();
                    $passwordInput.addClass('is-invalid');
                    $button.prop('disabled', false);
                    $spinner.hide();
                    $icon.show();
                },
                complete: function() {
                    // Ensure button is re-enabled in case of error without explicit re-enabling in error block
                    if ($passwordError.is(':visible')) {
                        $button.prop('disabled', false);
                        $spinner.hide();
                        $icon.show();
                    }
                }
            });
        });

        // --- Modal: Handle Enter key in password field ---
        $modal.on('keypress', '#quizPasswordModal', function(e) {
            if (e.which === 13) { // Enter key pressed
                e.preventDefault(); // Prevent form submission (if it were in a form)
                $(`#${verifyPasswordBtnId}`).trigger('click'); // Trigger the verify button click
            }
        });

        // --- Modal: Start Quiz Button Logic ---
        // Using event delegation for the dynamically added button
        $modal.on('click', `#${startQuizBtnId}`, function() {
            if ($(this).prop('disabled') || currentPasswordRequired) {
                // Should not happen if password required state is managed correctly
                console.warn("Start button clicked unexpectedly while password might still be required.");
                return;
            }
            const targetUrl = `take_quiz.php?quiz_id=${currentQuizId}${currentEventId ? '&event_id=' + currentEventId : ''}`;
            window.location.href = targetUrl;
            // Optional: Add a visual loading state on the button before redirecting
            $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Starting...');
        });

        // Optional: Clear modal state when hidden, especially if issues arise
        $modal.on('hidden.bs.modal', function() {
            // Restore button states if they were modified during redirect attempt
            const $startBtn = $(`#${startQuizBtnId}`);
            if ($startBtn.length > 0 && $startBtn.prop('disabled')) {
                $startBtn.prop('disabled', false).html('<i class="fas fa-play-circle me-1"></i> Let\'s Go!');
            }

            $modalAlertsContainer.empty();
            $modalDetailsContainer.empty();
            $modalActionButtons.empty();
            $modalTitle.text('');
            $modalInstructions.html('');
            // No need to clear password field if the prompt element is removed on success
            // If verify fails, we leave the password for user to correct
            $('#passwordErrorModal').hide();
            $('#quizPasswordModal').removeClass('is-invalid'); // Clear error state visually
            currentQuizId = null;
            currentEventId = null;
            currentPasswordRequired = false;
        });

        $modal.on('click', function(e) {
            if (e.target === this) { // Only trigger when clicking the backdrop
                instructionsModal.hide();
            }
        });

    }); // end document.ready
</script>