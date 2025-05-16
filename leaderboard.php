<?php
require_once 'includes/database.php'; // Assume this file establishes $pdo connection
require_once 'includes/functions.php'; // Assume this file contains helper functions if needed
require_once 'includes/auth.php';      // Assume this handles authentication and session
require_once 'header.php'; // Optional: Include header if needed
// include 'header.php'; // Assuming header.php contains session_start(), doctype, head, etc.
// The HTML structure below includes its own head, so ensure header.php doesn't conflict
// or only contains PHP session/auth logic already covered by auth.php

// --- Data Fetching and Processing (Same as before) ---

// Default filter values
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
$sort_by  = isset($_GET['sort_by'])  ? $_GET['sort_by']      : 'score';
$order    = isset($_GET['order'])    ? $_GET['order']        : 'desc';
$limit    = isset($_GET['limit'])    ? intval($_GET['limit']) : 50;

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
    // Optionally, display a user-friendly error or die
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
    // Optionally, display a user-friendly error
}

// Calculate ranks (handle ties)
if (!empty($results)) {
    $rank = 1;
    $prev_rank_val = 1; // Store the actual rank number for ties
    $prev_score = null;
    $prev_time = null;

    foreach ($results as &$row) {
        if ($prev_score !== null && $row['percentage'] == $prev_score && $row['time_taken'] == $prev_time) {
            $row['rank'] = $prev_rank_val; // Assign the same rank number for a tie
        } else {
            $row['rank'] = $rank;
            $prev_rank_val = $rank;
        }
        $prev_score = $row['percentage'];
        $prev_time  = $row['time_taken'];
        $rank++; // Increment the counter for the next potential rank
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

            // Calculate user's rank
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
            $user_rank_stmt->execute([$uid, $event_id]);
            $user_position = $user_rank_stmt->fetchColumn();
        }
    } catch (PDOException $e) {
        error_log("Database error fetching user result/rank: " . $e->getMessage());
    }
}

// Page title
$page_title = 'Quiz Leaderboard';
$current_event_title = 'Event'; // Default
if ($event_id > 0) {
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
    $tooltip_attrs = 'data-bs-toggle="tooltip" data-bs-placement="top" title="Rank ' . $rank . '"';
    switch ($rank) {
        case 1:
            return '<svg viewBox="0 0 24 24" width="24" height="24" ' . $tooltip_attrs . '>
                        <circle cx="12" cy="12" r="11" fill="gold" stroke="#f1c40f" stroke-width="1.5"/>
                        <text x="12" y="16" text-anchor="middle" font-size="12" font-weight="bold" fill="#333">1</text>
                        <path d="M12 2l1 3m-1 15l-1 3m11-12l-3 1m-15 1l-3-1m16.5-6.5l-1.5 1.5m1.5 13.5l-1.5-1.5m-13.5 1.5l1.5-1.5m-1.5-13.5l1.5 1.5"
                              stroke="#fff" stroke-width="1" stroke-linecap="round"/>
                    </svg>';
        case 2:
            return '<svg viewBox="0 0 24 24" width="24" height="24" ' . $tooltip_attrs . '>
                        <circle cx="12" cy="12" r="11" fill="silver" stroke="#bdc3c7" stroke-width="1.5"/>
                        <text x="12" y="16" text-anchor="middle" font-size="12" font-weight="bold" fill="#333">2</text>
                    </svg>';
        case 3:
            return '<svg viewBox="0 0 24 24" width="24" height="24" ' . $tooltip_attrs . '>
                        <circle cx="12" cy="12" r="11" fill="#cd7f32" stroke="#a56a29" stroke-width="1.5"/>
                        <text x="12" y="16" text-anchor="middle" font-size="12" font-weight="bold" fill="#333">3</text>
                    </svg>';
        default:
            return '<span class="badge bg-secondary rounded-pill" ' . $tooltip_attrs . '>' . htmlspecialchars($rank) . '</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <style>
        /* --- Google Fonts --- */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
        }

        /* Minimal styles for confetti, typically handled by JS but good to have fallback/base */
        .confetti {
            position: fixed;
            /* Changed from absolute to fixed to ensure full viewport coverage */
            top: -10px;
            /* Start slightly off-screen */
            opacity: 0.8;
            border-radius: 50%;
            animation: fall 3s linear forwards;
            /* 'forwards' keeps it at the end state */
            z-index: 9999;
            /* High z-index */
        }

        @keyframes fall {
            0% {
                transform: translateY(0) translateX(var(--fall-x, 0)) rotate(0deg);
                opacity: 0.8;
            }

            100% {
                transform: translateY(100vh) translateX(var(--fall-x, 0)) rotate(var(--fall-rotate, 720deg));
                opacity: 0;
            }
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .rank-cell {
            width: 60px;
            /* Fixed width for rank column */
            text-align: center;
        }

        .page-header {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
        }

        .card-header {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="container mt-3">
        <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
            <i class="fas fa-filter"></i> Filters
        </button>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="filterOffcanvasLabel"><i class="fas fa-filter"></i> Filters</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form id="leaderboardForm" method="GET" action="leaderboard.php">
                <div class="mb-3">
                    <label for="event_id" class="form-label">Select Event:</label>
                    <select class="form-select" id="event_id" name="event_id">
                        <option value="0">All Events</option>
                        <?php foreach ($events as $evt): ?>
                            <option value="<?php echo $evt['id']; ?>" <?php echo $evt['id'] == $event_id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($evt['event_title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="sort_by" class="form-label">Sort By:</label>
                    <select class="form-select" id="sort_by" name="sort_by">
                        <option value="score" <?php echo $sort_by == 'score' ? 'selected' : ''; ?>>Score (Highest %)</option>
                        <option value="time_taken" <?php echo $sort_by == 'time_taken' ? 'selected' : ''; ?>>Time Taken</option>
                        <option value="submission_time" <?php echo $sort_by == 'submission_time' ? 'selected' : ''; ?>>Submission Date</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="order" class="form-label">Order:</label>
                    <select class="form-select" id="order" name="order">
                        <?php if ($sort_by === 'time_taken'): ?>
                            <option value="asc" <?php echo $order == 'asc' ? 'selected' : ''; ?>>Fastest First</option>
                            <option value="desc" <?php echo $order == 'desc' ? 'selected' : ''; ?>>Slowest First</option>
                        <?php elseif ($sort_by === 'submission_time'): ?>
                            <option value="desc" <?php echo $order == 'desc' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="asc" <?php echo $order == 'asc' ? 'selected' : ''; ?>>Oldest First</option>
                        <?php else: ?>
                            <option value="desc" <?php echo $order == 'desc' ? 'selected' : ''; ?>>Highest First</option>
                            <option value="asc" <?php echo $order == 'asc' ? 'selected' : ''; ?>>Lowest First</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="limit" class="form-label">Show Top:</label>
                    <select class="form-select" id="limit" name="limit">
                        <?php foreach ([10, 25, 50, 100] as $n): ?>
                            <option value="<?php echo $n; ?>" <?php echo $limit == $n ? 'selected' : ''; ?>><?php echo $n; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="d-none"></button>
            </form>
        </div>
    </div>

    <div class="container mt-4">
        <h1 class="page-header mb-4"><?php echo htmlspecialchars($page_title); ?></h1>

        <?php if (isset($_SESSION['user_id']) && $event_id > 0): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <i class="fas fa-chart-line"></i> Your Position in <?php echo htmlspecialchars($current_event_title); ?>
                </div>
                <div class="card-body">
                    <?php if ($user_result && $user_position !== null): ?>
                        <div class="row text-center gy-3">
                            <div class="col-md-3 col-6">
                                <div class="fw-bold">Your Rank</div>
                                <div class="fs-4">#<?php echo htmlspecialchars($user_position); ?></div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="fw-bold">Your Score</div>
                                <div class="fs-4"><?php echo htmlspecialchars($user_result['score']); ?>/<?php echo htmlspecialchars($user_result['total_questions']); ?></div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="fw-bold">Percentage</div>
                                <div class="fs-4"><?php echo htmlspecialchars($user_result['percentage']); ?>%</div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="fw-bold">Time Taken</div>
                                <div class="fs-4"><?php echo htmlspecialchars(formatTime($user_result['time_taken'])); ?></div>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <a href="quiz_result.php?event_id=<?php echo htmlspecialchars($event_id); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-poll"></i> View Detailed Result
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">You have not completed this quiz event yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <i class="fas fa-trophy"></i> Top <?php echo $limit; ?> Rankings <?php echo $event_id > 0 ? 'for ' . htmlspecialchars($current_event_title) : ' (All Events)'; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="rank-cell text-center">Rank</th>
                                <th>Player</th>
                                <?php if ($event_id == 0): ?>
                                    <th>Event</th>
                                <?php endif; ?>
                                <th class="text-center">Score</th>
                                <th style="min-width: 120px;">Percentage</th>
                                <th class="text-center">Time Taken</th>
                                <th>Date Completed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($results)): ?>
                                <tr>
                                    <td colspan="<?php echo $event_id == 0 ? 7 : 6; ?>" class="text-center p-4">
                                        <div class="alert alert-warning mb-0">No leaderboard data available for the selected filters.</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($results as $row): ?>
                                    <tr class="<?php echo isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id'] ? 'table-info fw-bold' : ''; ?>">
                                        <td class="rank-cell text-center"><?php echo getRankBadge($row['rank']); ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <?php if ($event_id == 0): ?>
                                            <td><a href="leaderboard.php?event_id=<?php echo htmlspecialchars($row['event_id']); ?>" class="text-decoration-none"><?php echo htmlspecialchars($row['event_title']); ?></a></td>
                                        <?php endif; ?>
                                        <td class="text-center"><?php echo htmlspecialchars($row['score']); ?>/<?php echo htmlspecialchars($row['total_questions']); ?></td>
                                        <td>
                                            <div class="progress" style="height: 22px;" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo htmlspecialchars($row['percentage']); ?>%">
                                                <div class="progress-bar bg-primary text-white" role="progressbar" style="width: <?php echo htmlspecialchars($row['percentage']); ?>%;" aria-valuenow="<?php echo htmlspecialchars($row['percentage']); ?>" aria-valuemin="0" aria-valuemax="100">
                                                    <small><?php echo htmlspecialchars($row['percentage']); ?>%</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center"><?php echo htmlspecialchars(formatTime($row['time_taken'])); ?></td>
                                        <td><?php echo htmlspecialchars(date('M j, Y, g:i A', strtotime($row['submission_time']))); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if (!empty($results)): // Simple pagination example (not fully dynamic) 
            ?>
                <div class="card-footer bg-light text-center">
                    <small class="text-muted">Displaying top <?php echo count($results); ?> results.</small>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
    include 'footer.php'; // Optional: Include footer if needed
    ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            const leaderboardForm = document.getElementById('leaderboardForm');

            // Confetti effect for top 3 ranks (current user only)
            const userRow = document.querySelector('tr.table-info.fw-bold'); // More specific selector
            if (userRow) {
                const userRankCell = userRow.querySelector('.rank-cell');
                if (userRankCell) {
                    let rankNumberText = '';
                    const badgeSpan = userRankCell.querySelector('.badge'); // For ranks > 3
                    const svgText = userRankCell.querySelector('svg text'); // For ranks 1-3

                    if (badgeSpan) {
                        rankNumberText = badgeSpan.textContent.trim();
                    } else if (svgText) {
                        rankNumberText = svgText.textContent.trim();
                    }

                    const rankNumber = parseInt(rankNumberText);
                    if (!isNaN(rankNumber) && rankNumber >= 1 && rankNumber <= 3) {
                        setTimeout(celebrateTopRank, 600); // Add slight delay
                    }
                }
            }

            // Auto-submit form on select change (inside offcanvas)
            if (leaderboardForm) {
                leaderboardForm.querySelectorAll('select').forEach(el => {
                    el.addEventListener('change', () => {
                        const tableCard = document.querySelector('.leaderboard-card .card-body, .leaderboard-card .table-responsive');
                        if (tableCard) tableCard.style.opacity = '0.6'; // Visual cue
                        leaderboardForm.submit();
                    });
                });
            }

            // Update Order dropdown labels based on Sort By selection
            const sortBySelect = document.getElementById('sort_by');
            const orderSelect = document.getElementById('order');

            if (sortBySelect && orderSelect) {
                // Store original options text (might not be strictly needed if we always set them)
                const orderOptionsDefault = {
                    desc: 'Highest First',
                    asc: 'Lowest First'
                };

                function updateOrderLabels() {
                    const selectedSort = sortBySelect.value;
                    const descOption = orderSelect.querySelector('option[value="desc"]');
                    const ascOption = orderSelect.querySelector('option[value="asc"]');

                    if (!descOption || !ascOption) return; // Safety check

                    if (selectedSort === 'time_taken') {
                        ascOption.textContent = 'Fastest First';
                        descOption.textContent = 'Slowest First';
                    } else if (selectedSort === 'submission_time') {
                        descOption.textContent = 'Newest First';
                        ascOption.textContent = 'Oldest First';
                    } else { // Default for score/percentage
                        descOption.textContent = orderOptionsDefault.desc;
                        ascOption.textContent = orderOptionsDefault.asc;
                    }
                }
                // Initial update on load
                updateOrderLabels();
                // Update when sort_by changes
                sortBySelect.addEventListener('change', updateOrderLabels);
            }
        }); // End DOMContentLoaded

        // Confetti function
        function celebrateTopRank() {
            const confettiCount = 180; // Increased count
            const container = document.body; // Or a more specific container if needed
            const fragment = document.createDocumentFragment();
            const colors = ['#ffc107', '#ff6b6b', '#20c997', '#6f42c1', '#0dcaf0', '#fd7e14']; // Bootstrap-friendly palette

            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.classList.add('confetti'); // CSS class for basic styling and animation
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.animationDelay = Math.random() * 2.5 + 's'; // Varied delay
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];

                const size = Math.random() * 8 + 4; // Slightly larger confetti: 4px to 12px
                confetti.style.width = size + 'px';
                confetti.style.height = size + 'px';

                // Custom properties for animation variance
                confetti.style.setProperty('--fall-x', `${(Math.random() - 0.5) * 400}px`);
                confetti.style.setProperty('--fall-rotate', `${Math.random() * 1000 - 500}deg`);

                fragment.appendChild(confetti);
            }
            container.appendChild(fragment);

            // Remove confetti after animation
            setTimeout(() => {
                container.querySelectorAll('.confetti').forEach(c => c.remove());
            }, 5000); // Duration matches animation + delay
        }
    </script>
</body>

</html>