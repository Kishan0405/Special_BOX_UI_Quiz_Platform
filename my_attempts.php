<?php
// my_attempts.php
require_once 'includes/database.php';
require_once 'includes/auth.php';
include 'header.php';

date_default_timezone_set('Asia/Kolkata');

$userId = isLoggedIn() ? $_SESSION['user_id'] : null;
if (!$userId) {
    header('Location: login.php');
    exit;
}

// Function to safely output HTML
function safe_html(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Fetch general quiz attempts
$recentAttempts = [];
try {
    $stmtRecent = $pdo->prepare("
        SELECT qa.attempt_date, qa.score, qa.total, q.title
        FROM quiz_attempts qa
        JOIN quizzes q ON qa.quiz_id = q.id
        WHERE qa.user_id = ? AND qa.is_event = 0
        ORDER BY qa.attempt_date DESC LIMIT 10");
    $stmtRecent->execute([$userId]);
    $recentAttempts = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error fetching recent attempts: " . $e->getMessage());
}
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- Tailwind CSS CDN (pre-built) -->
<script src="https://cdn.tailwindcss.com"></script>

<style>
    :root {
        --primary-color: #6366F1;
        --success-color: #10B981;
        --warning-color: #F59E0B;
        --danger-color: #EF4444;
        --light-bg: #f8f9fa;
        --card-shadow: 0 4px 15px rgba(0, 0, 0, 0.07);
    }

    body {
        background-color: var(--light-bg);
        background-image:
            radial-gradient(at 0% 0%, hsla(217, 89%, 61%, 0.04) 0px, transparent 50%),
            radial-gradient(at 98% 1%, hsla(190, 86%, 72%, 0.06) 0px, transparent 50%),
            radial-gradient(at 50% 99%, hsla(262, 87%, 71%, 0.05) 0px, transparent 50%);
        color: var(--muted-text);
        /* Slightly softer base text color */
        font-size: 1rem;
        /* Base size (16px) */
        line-height: 1.6;
    }

    .section-title {
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        position: relative;
        padding-bottom: 1rem;
    }

    .section-title::after {
        content: '';
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        bottom: -4px;
        width: 100px;
        height: 5px;
        background: linear-gradient(90deg, var(--primary-color), var(--success-color));
        border-radius: 3px;
    }

    .attempt-card {
        border-radius: 0.75rem;
        border-left: 6px solid var(--primary-color);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .attempt-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .card-text-clamp {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    @media (max-width: 576px) {
        .section-title {
            font-size: 1.5rem;
        }

        .attempt-card .card-body {
            padding: 1rem;
        }
    }
</style>

<div class="main-content min-h-screen py-10">
    <div class="container">
        <h2 class="section-title text-center text-3xl text-gray-800 mb-12">
            <i class="fas fa-tasks text-indigo-500 mr-2"></i> My General Quiz Attempts
        </h2>

        <?php if (!empty($recentAttempts)): ?>
            <div class="row">
                <?php foreach ($recentAttempts as $attempt):
                    $totalQ = (int)($attempt['total'] ?? 0);
                    $score = (int)$attempt['score'];
                    $scorePercentage = ($totalQ > 0) ? round(($score / $totalQ) * 100) : 0;
                    $badgeClass = $scorePercentage >= 80 ? 'bg-success' : ($scorePercentage >= 50 ? 'bg-warning text-dark' : 'bg-danger');
                ?>
                    <div class="col-lg-6 col-md-6 mb-4">
                        <div class="attempt-card card border-0 shadow-sm bg-white">
                            <div class="card-body p-4">
                                <h3 class="card-title text-lg font-semibold text-indigo-600 mb-2">
                                    <?php echo safe_html($attempt['title']); ?>
                                </h3>
                                <p class="text-sm text-gray-600 card-text-clamp mb-3">
                                    Attempted on: <?php echo date("M j, Y, g:i A", strtotime($attempt['attempt_date'])); ?>
                                </p>
                                <span class="badge <?php echo $badgeClass; ?> rounded-pill px-3 py-2">
                                    <?php echo $score; ?>/<?php echo $totalQ; ?> (<?php echo $scorePercentage; ?>%)
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center">
                <div class="card border-0 shadow-sm bg-light p-5 mx-auto max-w-md">
                    <i class="fas fa-ghost fa-3x mb-3 text-gray-300"></i>
                    <h5 class="text-gray-600 font-normal mb-1">No Attempts Yet</h5>
                    <p class="text-gray-500 text-sm">Start taking quizzes to see your attempts here!</p>
                    <a href="home.php" class="btn btn-primary mt-3">
                        <i class="fas fa-play mr-1"></i> Explore Quizzes
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

<script>
    $(document).ready(function() {
        $('.card-text-clamp').each(function() {
            const maxChars = 80;
            let text = $(this).text();
            if (text.length > maxChars) {
                $(this).text(text.substring(0, maxChars) + '...');
            }
        });
    });
</script>