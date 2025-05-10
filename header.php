<?php
require_once 'includes/auth.php';
require_once 'includes/database.php';

// Function to get the current page filename with extension
function getCurrentPage()
{
   return basename($_SERVER['PHP_SELF']);
}

$currentPage = getCurrentPage();

$titles = [
   'home.php' => 'Home - Special BOX UI Quiz Platform',
   'profile.php' => 'Profile - Special BOX UI Quiz Platform',
   'my_quizzes.php' => 'My Quizzes - Special BOX UI Quiz Platform',
   'quiz_history.php' => 'Quiz History - Special BOX UI Quiz Platform',
   'leaderboard.php' => 'Leaderboard - Special BOX UI Quiz Platform',
   'help.php' => 'Help - Special BOX UI Quiz Platform',
   'login.php' => 'Login - Special BOX UI Quiz Platform',
   'register.php' => 'Register - Special BOX UI Quiz Platform'
];

$descriptions = [
   'home.php' => 'Discover Special BOX UI Quiz Platform, your interactive quiz and learning hub. Join now! (134 chars)',
   'profile.php' => 'Manage your profile on Special BOX UI Quiz Platform. Track progress and update info. (87 chars)',
   'my_quizzes.php' => 'Edit and share your quizzes on Special BOX UI Quiz Platform. Manage your creations! (85 chars)',
   'quiz_history.php' => 'Review quiz history on Special BOX UI Quiz Platform. Analyze scores and improve! (83 chars)',
   'leaderboard.php' => 'See top ranks on Special BOX UI Quiz Platform. Compete and lead the leaderboard! (83 chars)',
   'help.php' => 'Get support for Special BOX UI Quiz Platform. Access FAQs and tutorials. (73 chars)',
   'login.php' => 'Log in to Special BOX UI Quiz Platform. Access quizzes and features now! (73 chars)',
   'register.php' => 'Sign up for Special BOX UI Quiz Platform. Start creating quizzes today! (72 chars)'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?php echo isset($titles[$currentPage]) ? $titles[$currentPage] : 'Special BOX UI Quiz Platform'; ?></title>
   <meta name="description" content="<?php echo isset($descriptions[$currentPage]) ? $descriptions[$currentPage] : 'Welcome to Special BOX UI Quiz Platform, your go-to place for interactive quizzes and learning.'; ?>">
   <!-- Favicon Support -->
   <link rel="icon" type="image/x-icon" href="includes/specialboxuiquiz.png">
   <!-- Existing Stylesheets -->
   <link rel="stylesheet" href="css/style.css">
   <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Poppins:wght@400;600;700&family=Press+Start+2P&family=Roboto+Condensed:wght@700&family=Roboto+Mono&display=swap" rel="stylesheet">
   <!-- Bootstrap CSS -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" >
   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
   <!-- Animate.css -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
   <!-- Bootstrap JS Bundle with Popper -->
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
   <!-- jQuery -->
   <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<style>
   /* --- Google Fonts --- */
   @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
   @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&display=swap');

   :root {
      --qhead-primary-color: #3498db;
      --qhead-primary-dark: #2980b9;
      --qhead-text-light: #fff;
      --qhead-text-dark: #333;
      --qhead-background-light: #f5f7fa;
      --qhead-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      --qhead-transition: all 0.3s ease;
      --qhead-border-radius: 4px;
   }

   * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
   }

   body {
      font-family: 'Poppins', sans-serif;
      line-height: 1.6;
      color: var(--qhead-text-dark);
      background-color: var(--qhead-background-light);
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
   }

   .qhead-header.qhead-header {
      background: linear-gradient(135deg, var(--qhead-primary-color), var(--qhead-primary-dark));
      color: var(--qhead-text-light);
      box-shadow: var(--qhead-shadow);
      position: sticky !important;
      top: 0 !important;
      z-index: 1000;
      margin-bottom: 1rem !important;
   }

   .qhead-header-container.qhead-header-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 1.5rem !important;
      max-width: 1400px;
      margin: 0 auto;
   }

   .qhead-logo.qhead-logo a {
      color: var(--qhead-text-light);
      text-decoration: none;
      font-size: 1.5rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      transition: var(--qhead-transition);
   }

   .qhead-logo.qhead-logo a:hover {
      transform: scale(1.02);
   }

   .qhead-main-nav.qhead-main-nav {
      margin-top: 0.5rem;
   }

   .qhead-main-nav.qhead-main-nav ul {
      display: flex;
      list-style: none;
      gap: 1.2rem;
   }

   .qhead-main-nav.qhead-main-nav a {
      color: var(--qhead-text-light);
      text-decoration: none;
      font-weight: 500;
      padding: 0.6rem 1rem;
      border-radius: var(--qhead-border-radius);
      transition: var(--qhead-transition);
      position: relative;
   }

   .qhead-main-nav.qhead-main-nav a:hover {
      background-color: rgba(255, 255, 255, 0.15);
   }

   .qhead-main-nav.qhead-main-nav a::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      width: 0;
      height: 2px;
      background-color: var(--qhead-text-light);
      transition: var(--qhead-transition);
      transform: translateX(-50%);
   }

   .qhead-main-nav.qhead-main-nav a:hover::after {
      width: 70%;
   }

   .qhead-main-nav.qhead-main-nav a.qhead-active {
      background-color: rgba(255, 255, 255, 0.2);
      font-weight: 600;
   }

   .qhead-menu-toggle.qhead-menu-toggle {
      background: transparent;
      border: none;
      color: var(--qhead-text-light);
      font-size: 1.5rem;
      cursor: pointer;
      padding: 0.5rem;
      transition: var(--qhead-transition);
      display: none;
   }

   .qhead-menu-toggle.qhead-menu-toggle:hover {
      transform: scale(1.1);
   }

   .qhead-sr-only {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
      border-width: 0;
   }

   .qhead-mobile-nav.qhead-mobile-nav {
      position: fixed;
      top: 0;
      right: -100%;
      width: 80%;
      max-width: 300px;
      height: 100vh;
      background-color: var(--qhead-primary-dark);
      padding: 2rem 1rem;
      box-shadow: -5px 0 15px rgba(0, 0, 0, 0.2);
      transition: right 0.3s ease;
      z-index: 1001;
      overflow-y: auto;
   }

   .qhead-mobile-nav.qhead-mobile-nav.qhead-active {
      right: 0;
   }

   .qhead-mobile-nav.qhead-mobile-nav ul {
      list-style: none;
   }

   .qhead-mobile-nav.qhead-mobile-nav li {
      margin: 1rem 0;
   }

   .qhead-mobile-nav.qhead-mobile-nav a {
      color: var(--qhead-text-light);
      text-decoration: none;
      font-size: 1.1rem;
      display: block;
      padding: 0.8rem 1rem;
      border-radius: var(--qhead-border-radius);
      transition: var(--qhead-transition);
   }

   .qhead-mobile-nav.qhead-mobile-nav a:hover {
      background-color: rgba(255, 255, 255, 0.1);
   }

   .qhead-mobile-nav.qhead-mobile-nav a.qhead-active {
      background-color: rgba(255, 255, 255, 0.2);
      font-weight: 600;
   }

   .qhead-close-menu.qhead-close-menu {
      width: 100%;
      background-color: rgba(255, 255, 255, 0.1);
      color: var(--qhead-text-light);
      border: none;
      padding: 0.8rem;
      margin-top: 1.5rem;
      border-radius: var(--qhead-border-radius);
      cursor: pointer;
      font-size: 1rem;
      transition: var(--qhead-transition);
   }

   .qhead-close-menu.qhead-close-menu:hover {
      background-color: rgba(255, 255, 255, 0.2);
   }

   .qhead-menu-overlay.qhead-menu-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1000;
   }

   .qhead-menu-overlay.qhead-menu-overlay.qhead-active {
      display: block;
   }

   @media (max-width: 992px) {
      .qhead-main-nav.qhead-main-nav ul {
         gap: 0.8rem;
      }

      .qhead-main-nav.qhead-main-nav a {
         padding: 0.5rem 0.8rem;
         font-size: 0.9rem;
      }
   }

   @media (max-width: 768px) {
      .qhead-main-nav.qhead-main-nav {
         display: none;
      }

      .qhead-menu-toggle.qhead-menu-toggle {
         display: block;
      }

      .qhead-header-container.qhead-header-container {
         padding: 0.7rem 1rem !important;
      }

      .qhead-logo.qhead-logo a {
         font-size: 1.3rem;
      }
   }

   @keyframes qhead-fadeIn {
      from {
         opacity: 0;
      }

      to {
         opacity: 1;
      }
   }

   .qhead-header-container.qhead-header-container,
   .qhead-main-nav.qhead-main-nav,
   .qhead-mobile-nav.qhead-mobile-nav.qhead-active {
      animation: qhead-fadeIn 0.3s ease;
   }
</style>

<body>
   <header class="qhead-header">
      <div class="qhead-header-container">
         <div class="qhead-logo">
            <a href="home.php" aria-label="Special BOX UI Quiz Platform Home">Special BOX UI Quiz</a>
         </div>
         <nav class="qhead-main-nav" aria-label="Primary Navigation">
            <ul>
               <li>
                  <a href="home.php" class="<?php echo $currentPage === 'home.php' ? 'qhead-active' : ''; ?>" aria-current="<?php echo $currentPage === 'home.php' ? 'page' : ''; ?>">Home</a>
               </li>
               <?php if (isLoggedIn()): ?>
                  <li>
                     <a href="profile.php" class="<?php echo $currentPage === 'profile.php' ? 'qhead-active' : ''; ?>" aria-current="<?php echo $currentPage === 'profile.php' ? 'page' : ''; ?>">Profile</a>
                  </li>
                  <li>
                     <a href="my_quizzes.php" class="<?php echo $currentPage === 'my_quizzes.php' ? 'qhead-active' : ''; ?>" aria-current="<?php echo $currentPage === 'my_quizzes.php' ? 'page' : ''; ?>">My Quizzes</a>
                  </li>
                  <li>
                     <a href="quiz_history.php" class="<?php echo $currentPage === 'quiz_history.php' ? 'qhead-active' : ''; ?>" aria-current="<?php echo $currentPage === 'quiz_history.php' ? 'page' : ''; ?>">Quiz History</a>
                  </li>
                  <li>
                     <a href="leaderboard.php" class="<?php echo $currentPage === 'leaderboard.php' ? 'qhead-active' : ''; ?>" aria-current="<?php echo $currentPage === 'leaderboard.php' ? 'page' : ''; ?>">Leaderboard</a>
                  </li>
                  <li>
                     <a href="help.php" class="<?php echo $currentPage === 'help.php' ? 'qhead-active' : ''; ?>" aria-current="<?php echo $currentPage === 'help.php' ? 'page' : ''; ?>">Help</a>
                  </li>
                  <li>
                     <a href="logout.php">Logout</a>
                  </li>
               <?php else: ?>
                  <li>
                     <a href="login.php" class="<?php echo $currentPage === 'login.php' ? 'qhead-active' : ''; ?>" aria-current="<?php echo $currentPage === 'login.php' ? 'page' : ''; ?>">Login</a>
                  </li>
                  <li>
                     <a href="register.php" class="<?php echo $currentPage === 'register.php' ? 'qhead-active' : ''; ?>" aria-current="<?php echo $currentPage === 'register.php' ? 'page' : ''; ?>">Register</a>
                  </li>
               <?php endif; ?>
            </ul>
         </nav>
         <button id="mobileMenuToggle" class="qhead-menu-toggle" aria-expanded="false" aria-controls="mobileMenu">
            <span class="qhead-sr-only">Toggle navigation menu</span>
            <span class="menu-icon">☰</span>
         </button>
      </div>
   </header>

   <nav id="mobileMenu" class="qhead-mobile-nav" aria-label="Mobile Navigation">
      <ul>
         <li>
            <a href="home.php" class="<?php echo $currentPage === 'home.php' ? 'qhead-active' : ''; ?>" aria-current="<?php echo $currentPage === 'home.php' ? 'page' : ''; ?>">Home</a>
         </li>
         <?php if (isLoggedIn()): ?>
            <li>
               <a href="profile.php" class="<?php echo $currentPage === 'profile.php' ? 'qhead-active' : ''; ?>" aria-current="<?php echo $currentPage === 'profile.php' ? 'page' : ''; ?>">Profile</a>
            </li>
            <li>
               <a href="my_quizzes.php" class="<?php echo $currentPage === 'my_quizzes.php' ? 'qhead-active' : ''; ?>" aria-current="<?php echo $currentPage === 'my_quizzes.php' ? 'page' : ''; ?>">My Quizzes</a>
            </li>
            <li>
               <a href="quiz_history.php" class="<?php echo $currentPage === 'quiz_history.php' ? 'qhead-active' : ''; ?>" aria-current="<?php echo $currentPage === 'quiz_history.php' ? 'page' : ''; ?>">Quiz History</a>
            </li>
            <li>
               <a href="leaderboard.php" class="<?php echo $currentPage === 'leaderboard.php' ? 'qhead-active' : ''; ?>" aria-current="<?php echo $currentPage === 'leaderboard.php' ? 'page' : ''; ?>">Leaderboard</a>
            </li>
            <li>
               <a href="help.php" class="<?php echo $currentPage === 'help.php' ? 'qhead-active' : ''; ?>" aria-current="<?php echo $currentPage === 'help.php' ? 'page' : ''; ?>">Help</a>
            </li>
            <li>
               <a href="logout.php">Logout</a>
            </li>
         <?php else: ?>
            <li>
               <a href="login.php" class="<?php echo $currentPage === 'login.php' ? 'qhead-active' : ''; ?>" aria-current="<?php echo $currentPage === 'login.php' ? 'page' : ''; ?>">Login</a>
            </li>
            <li>
               <a href="register.php" class="<?php echo $currentPage === 'register.php' ? 'qhead-active' : ''; ?>" aria-current="<?php echo $currentPage === 'register.php' ? 'page' : ''; ?>">Register</a>
            </li>
         <?php endif; ?>
         <li>
            <button id="mobileMenuClose" class="qhead-close-menu" aria-label="Close mobile menu">× Close</button>
         </li>
      </ul>
   </nav>
   <div id="menuOverlay" class="qhead-menu-overlay"></div>

   <main id="content"></main>

   <script>
      document.addEventListener('DOMContentLoaded', function() {
         const mobileMenuToggle = document.getElementById('mobileMenuToggle');
         const mobileMenu = document.getElementById('mobileMenu');
         const mobileMenuClose = document.getElementById('mobileMenuClose');
         const menuOverlay = document.getElementById('menuOverlay');

         function toggleMobileMenu() {
            const isMenuOpen = mobileMenu.classList.contains('qhead-active');
            mobileMenu.classList.toggle('qhead-active');
            menuOverlay.classList.toggle('qhead-active');
            mobileMenuToggle.setAttribute('aria-expanded', !isMenuOpen);
            document.body.style.overflow = !isMenuOpen ? 'hidden' : '';
         }

         if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', toggleMobileMenu);
         }
         if (mobileMenuClose) {
            mobileMenuClose.addEventListener('click', toggleMobileMenu);
         }
         if (menuOverlay) {
            menuOverlay.addEventListener('click', toggleMobileMenu);
         }

         document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mobileMenu.classList.contains('qhead-active')) {
               toggleMobileMenu();
            }
         });

         const mobileNavLinks = document.querySelectorAll('.qhead-mobile-nav a');
         mobileNavLinks.forEach(link => {
            link.addEventListener('click', function() {
               if (mobileMenu.classList.contains('qhead-active')) {
                  toggleMobileMenu();
               }
            });
         });

         window.addEventListener('resize', function() {
            if (window.innerWidth > 768 && mobileMenu.classList.contains('qhead-active')) {
               toggleMobileMenu();
            }
         });
      });
   </script>
</body>

</html>