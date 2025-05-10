<?php
date_default_timezone_set('Asia/Kolkata');
require_once 'includes/database.php'; // $pdo = new PDO(...);
require_once 'footer_news_helper.php';

// If this is an AJAX POST, return JSON and exit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    header('Content-Type: application/json');

    $email = trim($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'invalid']);
        exit;
    }

    // Check if already subscribed
    $stmt = $pdo->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        echo json_encode(['status' => 'exists']);
    } else {
        $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email, subscribed_at) VALUES (?, NOW())");
        $stmt->execute([$email]);
        echo json_encode(['status' => 'subscribed']);
    }
    exit;
}
?>
<footer class="bg-gray-200 text-gray-700 pt-12 pb-6">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

            <!-- Explore Section -->
            <div class="mb-5 lg:mb-0">
                <h5 class="text-lg font-semibold text-gray-800 uppercase tracking-wider mb-4 border-b-2 border-blue-600 pb-2 inline-block">Explore</h5>
                <ul class="list-none space-y-5">
                    <li>
                        <a href="availablesoon.php" class="hover:text-blue-600 hover:underline transition-colors duration-200 flex items-center text-sm" style="text-decoration: none;">
                            <i class="fas fa-bolt fa-fw mr-3 text-gray-500" aria-hidden="true"></i> <span>Community</span>
                        </a>
                    </li>
                    <li>
                        <a href="my_quizzes.php" class="hover:text-blue-600 hover:underline transition-colors duration-200 flex items-center text-sm" style="text-decoration: none;">
                            <i class="fas fa-check fa-fw mr-3 text-gray-500" aria-hidden="true"></i> <span>My Quizzes</span>
                        </a>
                    </li>
                    <li>
                        <a href="my_events.php" class="hover:text-blue-600 hover:underline transition-colors duration-200 flex items-center text-sm" style="text-decoration: none;">
                            <i class="fas fa-calendar-check fa-fw mr-3 text-gray-500" aria-hidden="true"></i> <span>Events</span>
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li>
                            <a href="profile.php" class="hover:text-blue-600 hover:underline transition-colors duration-200 flex items-center text-sm" style="text-decoration: none;">
                                <i class="fas fa-user fa-fw mr-3 text-gray-500" aria-hidden="true"></i> <span>Profile</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="login.php" class="hover:text-blue-600 hover:underline transition-colors duration-200 flex items-center text-sm" style="text-decoration: none;">
                                <i class="fas fa-sign-in-alt fa-fw mr-3 text-gray-500" aria-hidden="true"></i> <span>Login</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Support Section -->
            <div class="mb-8 lg:mb-0">
                <h5 class="text-lg font-semibold text-gray-800 uppercase tracking-wider mb-4 border-b-2 border-blue-600 pb-2 inline-block" style="text-decoration: none;">Support</h5>
                <ul class="list-none space-y-5">
                    <li>
                        <a href="faq.php" class="hover:text-blue-600 hover:underline transition-colors duration-200 flex items-center text-sm" style="text-decoration: none;">
                            <i class="fas fa-question-circle fa-fw mr-3 text-gray-500" aria-hidden="true"></i> <span>FAQ</span>
                        </a>
                    </li>
                    <li>
                        <a href="availablesoon.php" class="hover:text-blue-600 hover:underline transition-colors duration-200 flex items-center text-sm" style="text-decoration: none;">
                            <i class="fas fa-envelope fa-fw mr-3 text-gray-500" aria-hidden="true"></i> <span>Contact Us</span>
                        </a>
                    </li>
                    <li>
                        <a href="terms.php" class="hover:text-blue-600 hover:underline transition-colors duration-200 flex items-center text-sm" style="text-decoration: none;">
                            <i class="fas fa-file-alt fa-fw mr-3 text-gray-500" aria-hidden="true"></i> <span>Terms</span>
                        </a>
                    </li>
                    <li>
                        <a href="privacy_policy.php" class="hover:text-blue-600 hover:underline transition-colors duration-200 flex items-center text-sm" style="text-decoration: none;">
                            <i class="fas fa-shield-alt fa-fw mr-3 text-gray-500" aria-hidden="true"></i> <span>Privacy</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Connect With Us Section -->
            <div class="mb-8 lg:mb-0">
                <h5 class="text-lg font-semibold text-gray-800 uppercase tracking-wider mb-4 border-b-2 border-blue-600 pb-2 inline-block">Connect With Me</h5>
                <p class="text-sm text-gray-600 mb-4 leading-relaxed">Follow me on social media for updates and community events!</p>
                <div class="flex space-x-6 mb-6">
                    <a href="https://www.linkedin.com/in/kishanbantakal/" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-blue-600 transform hover:-translate-y-1 transition-all duration-200" aria-label="LinkedIn">
                        <i class="fab fa-linkedin-in fa-2x" aria-hidden="true"></i>
                    </a>
                    <a href="https://github.com/Kishan0405" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-blue-600 transform hover:-translate-y-1 transition-all duration-200" aria-label="GitHub">
                        <i class="fab fa-github fa-2x" aria-hidden="true"></i>
                    </a>
                    <a href="http://t.me/kishanbantakal" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-blue-600 transform hover:-translate-y-1 transition-all duration-200" aria-label="Telegram">
                        <i class="fab fa-telegram-plane fa-2x" aria-hidden="true"></i>
                    </a>
                </div>

                <div>
                    <form id="newsletter-form" class="flex flex-col sm:flex-row w-full mx-auto">
                        <label for="newsletter-email" class="sr-only">Email for newsletter</label>
                        <input
                            type="email"
                            name="email"
                            id="newsletter-email"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-t-md sm:rounded-l-md sm:rounded-t-none focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 placeholder-gray-500 text-sm w-full"
                            placeholder="Enter your email"
                            aria-label="Email for newsletter"
                            required />
                        <button
                            type="submit"
                            id="subscribe-button"
                            class="bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white px-5 py-2 rounded-b-md sm:rounded-r-md sm:rounded-b-none text-sm font-semibold shadow-lg transform transition duration-200 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 flex items-center justify-center w-full sm:w-auto"
                            aria-label="Subscribe to newsletter">
                            <span class="mr-2">Subscribe</span>
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- News Update Section (Now Dynamic) -->
        <div class="mb-8 lg:mb-0">
            <h5 class="text-lg font-semibold text-gray-800 uppercase tracking-wider mb-4 border-b-2 border-blue-600 pb-2 inline-block">News Update</h5>
            <p class="text-sm text-gray-600 leading-relaxed">
                <?php echo htmlspecialchars($newsUpdate['content']); // Sanitize output 
                ?>
            </p>
            <p class="text-xs text-gray-500 mt-3">
                Last updated: <?php echo htmlspecialchars($newsUpdate['last_updated']); // Sanitize output 
                                ?>
            </p>
        </div>

        <!-- Footer Bottom -->
        <div class="border-t border-gray-300 pt-6 mt-8">
            <div class="flex flex-col md:flex-row justify-between items-center text-xs sm:text-sm text-gray-600">
                <p class="mb-4 md:mb-0 text-center md:text-left">
                    © 2024-<?php echo date('Y'); ?> Special BOX UI
                </p>
                <p class="text-center md:text-right">
                    Designed with <i class="fas fa-heart text-red-500 mx-1" aria-hidden="true"></i> by
                    <a href="https://specialboxuionline.wuaze.com/" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-700 font-medium" style="text-decoration:none;">Kishan Raj</a>
                    <!-- Update href with actual link if different -->
                </p>
            </div>
        </div>
    </div>
</footer>

<script>
    document.getElementById('newsletter-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const btn = document.getElementById('subscribe-button');
        const data = new FormData(form);

        fetch('footer.php', {
                method: 'POST',
                body: data
            })
            .then(res => res.json())
            .then(json => {
                if (json.status === 'invalid') {
                    alert('Please enter a valid email address.');
                    return;
                }
                // Show check-mark
                btn.innerHTML = '<i class="fas fa-check fa-lg" aria-hidden="true"></i>';
                btn.disabled = true;

                // After a short pause, replace the form
                setTimeout(() => {
                    form.outerHTML =
                        '<p class="text-green-600 font-medium text-center">' +
                        'You have already subscribed!' +
                        '</p>';
                }, 1000);
            })
            .catch(() => {
                alert('Something went wrong. Please try again later.');
            });
    });
</script>