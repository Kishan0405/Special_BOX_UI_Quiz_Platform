<?php
// header.php
if (session_status() == PHP_SESSION_NONE) {
}
require_once '../includes/auth.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); // Redirect to login page
    exit(); // Ensure no further code is executed
}

// Function to get the current page filename with extension
function getCurrentPage()
{
    $path = $_SERVER['PHP_SELF'];
    $filename = basename($path);
    return pathinfo($filename, PATHINFO_FILENAME) . ".php";
}

$currentPage = getCurrentPage();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Reset and Base Styles */
        :root {
            --primary-color: #3498db;
            --primary-dark: #2980b9;
            --accent-color: #e74c3c;
            --text-color: #333;
            --text-light: #fff;
            --background-light: #f5f7fa;
            --border-color: #ddd;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
            --border-radius: 4px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--background-light);
        }

        /* Header Styles */
        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--text-light);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Logo Styles */
        .logo a {
            color: var(--text-light);
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            transition: var(--transition);
        }

        .logo a:hover {
            transform: scale(1.02);
        }

        /* Main Navigation Styles */
        .main-nav ul {
            display: flex;
            list-style: none;
            gap: 1.2rem;
        }

        .main-nav a {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            padding: 0.6rem 1rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
            position: relative;
        }

        .main-nav a:hover {
            background-color: rgba(255, 255, 255, 0.15);
        }

        .main-nav a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background-color: var(--text-light);
            transition: var(--transition);
            transform: translateX(-50%);
        }

        .main-nav a:hover::after {
            width: 70%;
        }

        /* Mobile Menu Toggle Button */
        .menu-toggle {
            background: transparent;
            border: none;
            color: var(--text-light);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            transition: var(--transition);
            display: none;
        }

        .menu-toggle:hover {
            transform: scale(1.1);
        }

        .sr-only {
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

        /* Mobile Navigation Menu */
        .mobile-nav {
            position: fixed;
            top: 0;
            right: -100%;
            width: 80%;
            max-width: 300px;
            height: 100vh;
            background-color: var(--primary-dark);
            padding: 2rem 1rem;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.2);
            transition: right 0.3s ease;
            z-index: 1001;
            overflow-y: auto;
        }

        .mobile-nav.active {
            right: 0;
        }

        .mobile-nav ul {
            list-style: none;
        }

        .mobile-nav li {
            margin: 1rem 0;
        }

        .mobile-nav a {
            color: var(--text-light);
            text-decoration: none;
            font-size: 1.1rem;
            display: block;
            padding: 0.8rem 1rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .mobile-nav a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .close-menu {
            width: 100%;
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            border: none;
            padding: 0.8rem;
            margin-top: 1.5rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            transition: var(--transition);
        }

        .close-menu:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* Overlay for Mobile Menu */
        .menu-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .menu-overlay.active {
            display: block;
        }

        /* Admin Dashboard Link Special Styling */
        .main-nav a[href="admin/admin_dashboard.php"],
        .mobile-nav a[href="admin/admin_dashboard.php"] {
            background-color: var(--accent-color);
            font-weight: 600;
        }

        .main-nav a[href="admin/admin_dashboard.php"]:hover,
        .mobile-nav a[href="admin/admin_dashboard.php"]:hover {
            background-color: #c0392b;
        }

        /* Media Queries for Responsive Design */
        @media (max-width: 2000px) {
            .main-nav ul {
                gap: 0.8rem;
            }

            .main-nav a {
                padding: 0.5rem 0.8rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 2000px) {
            .main-nav {
                display: none;
            }

            .menu-toggle {
                display: block;
            }

            .header-container {
                padding: 0.7rem 1rem;
            }

            .logo a {
                font-size: 1.3rem;
            }
        }

        /* Active Link Styling */
        .main-nav a.active,
        .mobile-nav a.active {
            background-color: rgba(255, 255, 255, 0.2);
            font-weight: 600;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .header-container,
        .main-nav,
        .mobile-nav.active {
            animation: fadeIn 0.3s ease;
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="header-container">
            <div class="logo">
                <a href="admin_dashboard.php">Admin Panel</a>
            </div>
            <button id="mobileMenuToggle" class="menu-toggle" aria-expanded="false" aria-controls="mobileMenu">
                <span class="sr-only">Toggle navigation menu</span>
                <span class="menu-icon">&#9776;</span>
            </button>
        </div>
    </header>

    <nav id="mobileMenu" class="mobile-nav" aria-label="Admin Mobile Navigation">
        <ul>
            <li>
                <a href="admin_dashboard.php" class="<?php echo $currentPage === 'admin_dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
            </li>
            <li>
                <a href="user_management.php" class="<?php echo $currentPage === 'user_management.php' ? 'active' : ''; ?>">User Management</a>
            </li>
            <li>
                <a href="quiz_management.php" class="<?php echo $currentPage === 'quiz_management.php' ? 'active' : ''; ?>">Quiz Management</a>
            </li>
            <li>
                <a href="category_management.php" class="<?php echo $currentPage === 'category_management.php' ? 'active' : ''; ?>">Category Management</a>
            </li>
            <li>
                <a href="reports.php" class="<?php echo $currentPage === 'reports.php' ? 'active' : ''; ?>">Reports & Analytics</a>
            </li>
            <li>
                <a href="logout.php">Logout</a>
            </li>
            <li>
                <button id="mobileMenuClose" class="close-menu" aria-label="Close mobile menu">&times; Close</button>
            </li>
        </ul>
    </nav>
    <div id="menuOverlay" class="menu-overlay"></div>

    <main id="content">
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileMenuClose = document.getElementById('mobileMenuClose');
            const menuOverlay = document.getElementById('menuOverlay');

            function toggleMobileMenu() {
                const isMenuOpen = mobileMenu.classList.contains('active');
                mobileMenu.classList.toggle('active');
                menuOverlay.classList.toggle('active');
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
                if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
                    toggleMobileMenu();
                }
            });

            // Highlight active link based on URL
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.main-nav a, .mobile-nav a');
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
            });

            // Close mobile menu when a navigation link is clicked
            const mobileNavLinks = document.querySelectorAll('.mobile-nav a');
            mobileNavLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (mobileMenu.classList.contains('active')) {
                        toggleMobileMenu();
                    }
                });
            });

            // Auto-close mobile menu on window resize if above mobile breakpoint
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768 && mobileMenu.classList.contains('active')) {
                    toggleMobileMenu();
                }
            });
        });
    </script>
</body>

</html>