<?php
require_once 'includes/database.php';
require_once 'includes/auth.php';
include 'header.php'; // Assumes header.php starts HTML, head, and includes navbar

// Set the default timezone to IST for consistency
date_default_timezone_set('Asia/Kolkata');

$userId = isLoggedIn() ? $_SESSION['user_id'] : null;

// Function to safely output HTML
function safe_html(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Initialize variables
$searchQuery = '';
$searchResults = [];
$noResults = false;

if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    $searchQuery = trim($_GET['query']);
    try {
        // Search quizzes where title or description matches the query, excluding event quizzes
        $stmt = $pdo->prepare("
            SELECT q.*
            FROM quizzes q
            LEFT JOIN events e ON q.id = e.quiz_id
            WHERE e.quiz_id IS NULL
            AND (q.title LIKE ? OR q.description LIKE ?)
            ORDER BY q.created_at DESC
        ");
        $likeQuery = '%' . $searchQuery . '%';
        $stmt->execute([$likeQuery, $likeQuery]);
        $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($searchResults)) {
            $noResults = true;
        }
    } catch (PDOException $e) {
        error_log("Database error during search: " . $e->getMessage());
        $noResults = true; // Treat as no results to avoid breaking the UI
    }
} else {
    $noResults = true; // No query provided
}
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- Animate.css -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<link rel="stylesheet" href="css/new_home.css"> <!-- Reuse same styles as home.php -->

<style>
/* --- Google Fonts --- */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&display=swap');
</style>

<div class="main-content">
    <!-- Search Results Section -->
    <section class="py-5">
        <div class="container">
            <h1 class="display-5 fw-bold mb-4 text-center animate__animated animate__fadeIn">
                <i class="fas fa-search me-2 text-primary"></i> Search Results
            </h1>
            <p class="lead text-center mb-5 animate__animated animate__fadeIn" style="animation-delay: 0.2s;">
                <?php if ($searchQuery): ?>
                    Showing results for "<strong><?php echo safe_html($searchQuery); ?></strong>"
                <?php else: ?>
                    Please enter a search term to find quizzes.
                <?php endif; ?>
            </p>

            <?php if ($noResults): ?>
                <div class="col-12 text-center animate__animated animate__fadeIn" style="animation-delay: 0.4s;">
                    <div class="card border-0 shadow-sm bg-light p-4 mx-auto" style="max-width: 500px;">
                        <img src="includes/empty-box.png" alt="No results found" class="img-fluid mb-3 mx-auto" style="width: 90px; opacity: 0.7;">
                        <h5 class="text-muted fw-normal mb-1">No Quizzes Found</h5>
                        <p class="text-secondary small mb-0">Try a different search term or explore our <a href="home.php" class="text-primary fw-bold">general quizzes</a>!</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($searchResults as $index => $quiz): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="quiz-card general-quiz-card animate__animated animate__fadeInUp" style="animation-delay: <?php echo 0.05 * $index; ?>s">
                                <div class="card-header bg-transparent border-0 pt-3 pb-0 px-lg-4">
                                    <span class="badge bg-primary bg-opacity-10 text-primary-emphasis py-1 px-2 rounded-pill small fw-medium">
                                        <i class="fas fa-book-open me-1"></i> General
                                    </span>
                                </div>
                                <div class="card-body pt-2 px-lg-4">
                                    <h3 class="h5 fw-bold mb-2 quiz-card-title">
                                        <?php if ($quiz['quiz_password']): ?>
                                            <i class="fas fa-lock fa-xs text-muted me-1" title="Password Protected"></i>
                                        <?php endif; ?>
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
                </div>
            <?php endif; ?>
        </div>

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
    </section>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

<script>
    $(document).ready(function() {
        // Clamp long descriptions
        $('.card-text-clamp').each(function() {
            const maxChars = 110;
            let text = $(this).text();
            if (text.length > maxChars) {
                $(this).text(text.substring(0, maxChars) + '...');
            }
        });

        // Reuse modal logic from home.php for quiz start buttons
        $('.main-content').on('click', '.show-instructions-btn', function() {
            if ($(this).prop('disabled')) {
                return;
            }

            const currentQuizId = $(this).data('quiz-id');
            const instructionsModal = new bootstrap.Modal('#instructionsModal');
            const $modal = $('#instructionsModal');
            const $loadingIndicator = $('#loadingIndicator');
            const $modalContentArea = $('#modalContentArea');
            const $modalFooterArea = $('#modalFooterArea');
            const $modalTitle = $('#modalQuizTitle');
            const $modalInstructions = $('#modalInstructionsContent');
            const $modalAlertsContainer = $('#modalAlertsContainer');
            const $modalDetailsContainer = $('#modalDetailsContent');
            const $modalActionButtons = $('#modalActionButtons');

            const verifyPasswordBtnId = 'verifyPasswordBtnModal';
            const startQuizBtnId = 'startQuizBtnModal';
            const viewResultLinkModalId = 'viewResultLinkModal';

            const passwordPromptHtml = `
            <div id="passwordPromptModal" class="alert alert-light border p-3 mb-0">
                <label for="quizPasswordModal" class="form-label fw-bold d-block mb-2"><i class="fas fa-lock me-1 text-primary"></i> Enter Password to Proceed</label>
                <input type="password" id="quizPasswordModal" class="form-control mb-1 shadow-none" placeholder="Quiz Password" autocomplete="current-password">
                <div id="passwordErrorModal" class="text-danger small mt-1" style="display: none;"><i class="fas fa-exclamation-circle me-1"></i> <span></span></div>
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
                let timeLimitHtml = '<span class="me-3 d-inline-flex align-items-center"><i class="fas fa-infinity me-2 text-primary opacity-75"></i> No Time Limit</span>';
                if (details.time_limit_seconds && details.time_limit_seconds > 0) {
                    const displayMinutes = Math.round(details.time_limit_seconds / 60);
                    timeLimitHtml = `<span class="me-3 d-inline-flex align-items-center"><i class="fas fa-hourglass-half me-2 text-primary opacity-75"></i> ${displayMinutes} min Limit</span>`;
                }
                const questionCountHtml = (details.question_count && details.question_count > 0) ?
                    `<span class="me-3 d-inline-flex align-items-center"><i class="fas fa-question-circle me-2 text-primary opacity-75"></i> ${details.question_count} Questions</span>` :
                    '';
                return `
                <div class="border-top pt-3 mt-4 text-muted d-flex flex-wrap gap-3">
                    ${questionCountHtml}
                    ${timeLimitHtml}
                </div>`;
            };

            // Reset Modal State
            $loadingIndicator.show();
            $modalContentArea.hide();
            $modalFooterArea.hide();
            $modalAlertsContainer.empty();
            $modalDetailsContainer.empty();
            $modalTitle.text('Loading...');
            $modalInstructions.html('');
            $modalActionButtons.empty();
            let currentPasswordRequired = false;

            instructionsModal.show();

            // AJAX Call to Get Instructions
            $.ajax({
                url: 'get_instructions.php',
                type: 'GET',
                data: {
                    quiz_id: currentQuizId
                },
                dataType: 'json',
                success: function(response) {
                    $loadingIndicator.hide();
                    if (!response || !response.success) {
                        $modalAlertsContainer.html(generalErrorHtml(response?.message || 'Could not load quiz details. Try again later.'));
                        $modalContentArea.show();
                        $modalFooterArea.show();
                        return;
                    }

                    $modalTitle.text(response.title || 'Quiz Details');
                    let instructionsHtml = (response.instructions || 'No specific instructions provided. Answer all questions to the best of your ability!')
                        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                        .replace(/__/g, '<br>')
                        .replace(/\n/g, '<br>');
                    $modalInstructions.html(instructionsHtml);
                    $modalDetailsContainer.html(generalDetailsHtml(response));

                    let canStart = true;
                    if (!response.has_questions) {
                        $modalAlertsContainer.append(noQuestionsHtml);
                        canStart = false;
                    }

                    currentPasswordRequired = response.requires_password ?? false;
                    if (currentPasswordRequired && canStart) {
                        $modalAlertsContainer.append(passwordPromptHtml);
                        $modalActionButtons.append(`
                        <button type="button" id="${verifyPasswordBtnId}" class="btn btn-primary btn-lg-sm">
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true" style="display: none;"></span>
                            <i class="fas fa-key icon me-1"></i> Verify & Proceed
                        </button>
                    `);
                    } else if (canStart) {
                        $modalActionButtons.append(`
                        <button type="button" id="${startQuizBtnId}" class="btn btn-success btn-lg-sm">
                            <i class="fas fa-play-circle me-1"></i> Let's Go!
                        </button>
                    `);
                    }

                    $modalContentArea.show();
                    $modalFooterArea.show();

                    if (currentPasswordRequired) {
                        setTimeout(() => {
                            $('#quizPasswordModal').focus();
                        }, 500);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAX Error fetching instructions:", textStatus, errorThrown, jqXHR.responseText);
                    $loadingIndicator.hide();
                    $modalAlertsContainer.html(generalErrorHtml('Failed to load quiz details. Please check your connection and try again.'));
                    $modalContentArea.show();
                    $modalFooterArea.show();
                }
            });

            // Password Verification Logic
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
                    url: 'validate_password.php',
                    type: 'POST',
                    data: JSON.stringify({
                        quiz_id: currentQuizId,
                        password: password
                    }),
                    contentType: 'application/json',
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.success) {
                            $('#passwordPromptModal').remove();
                            $('#passwordErrorModal').hide();
                            $button.remove();
                            currentPasswordRequired = false;
                            if ($(`#${startQuizBtnId}`).length === 0) {
                                $modalActionButtons.append(`
                                <button type="button" id="${startQuizBtnId}" class="btn btn-success btn-lg-sm">
                                    <i class="fas fa-play-circle me-1"></i> Let's Go!
                                </button>
                            `);
                            }
                            $(`#${startQuizBtnId}`).focus();
                        } else {
                            $passwordError.find('span').text(response?.error || 'Incorrect password. Please try again.');
                            $passwordError.show();
                            $passwordInput.addClass('is-invalid').focus().select();
                            $button.prop('disabled', false);
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
                    }
                });
            });

            // Handle Enter key in password field
            $modal.on('keypress', '#quizPasswordModal', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $(`#${verifyPasswordBtnId}`).trigger('click');
                }
            });

            // Start Quiz Button Logic
            $modal.on('click', `#${startQuizBtnId}`, function() {
                if ($(this).prop('disabled') || currentPasswordRequired) {
                    return;
                }
                const targetUrl = `take_quiz.php?quiz_id=${currentQuizId}`;
                window.location.href = targetUrl;
                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Starting...');
            });
        });
    });
</script>