<?php
include_once 'header.php';
include_once 'includes/database.php';
include_once 'includes/auth.php';
include_once 'includes/functions.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to Admin Panel...</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Tailwind CSS CDN (pre-built) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-color: #0ea5e9;
            /* Sky blue */
            --text-dark: #1f2937;
            /* Gray-800 */
            --text-light: #4b5563;
            /* Gray-600 */
            --bg-card: #ffffff;
            /* White */
            --border-light: #e5e7eb;
            /* Gray-200 */
            --note-bg: #f9fafb;
            /* Gray-50 */
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 2rem;
        }



        body {
            color: var(--text-dark);
            background: #f3f4f6;
            /* Light gray background */
        }

        .container {
            background: var(--bg-card);
            border-radius: 0.75rem;
            box-shadow: var(--shadow);
            padding: var(--spacing-lg);
            max-width: 32rem;
            width: 100%;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .icon {
            width: 3rem;
            height: 3rem;
            margin: 0 auto var(--spacing-md);
            fill: var(--primary-color);
            animation: spin 1.5s linear infinite;
        }

        h1 {
            font-size: calc(1.5rem + 0.5vw);
            font-weight: 700;
        }

        .dots span {
            display: inline-block;
            opacity: 0;
            animation: blink 1.4s infinite;
        }

        .dots span:nth-child(1) {
            animation-delay: 0s;
        }

        .dots span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .dots span:nth-child(3) {
            animation-delay: 0.4s;
        }

        p {
            font-size: 1rem;
            color: var(--text-light);
            margin-bottom: var(--spacing-sm);
        }

        .note {
            font-size: 0.75rem;
            color: var(--text-light);
            background: var(--note-bg);
            border: 1px solid var(--border-light);
            border-radius: 0.5rem;
            padding: var(--spacing-md);
            text-align: left;
            margin-top: var(--spacing-md);
        }

        .note strong {
            color: var(--text-dark);
        }

        hr {
            border: 0;
            border-top: 1px solid var(--border-light);
            margin: var(--spacing-md) 0;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 0;
            }

            50% {
                opacity: 1;
            }
        }

        @media (max-width: 640px) {
            .container {
                padding: var(--spacing-md);
            }

            h1 {
                font-size: 1.25rem;
            }

            p {
                font-size: 0.875rem;
            }

            .icon {
                width: 2.5rem;
                height: 2.5rem;
            }

            .note {
                font-size: 0.7rem;
            }
        }
    </style>
    <script>
        setTimeout(function() {
            window.location.href = 'admin/admin_dashboard.php';
        }, 8888); // Redirect after 8.88 seconds                                         
    </script>
</head>

<body>
    <div class="container">
        <svg class="icon" viewBox="0 0 24 24">
            <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8zm4-8a4 4 0 0 1-4 4V8a4 4 0 0 1 4 4z" />
        </svg>
        <h1>Redirecting to Admin Panel<span class="dots"><span>.</span><span>.</span><span>.</span></span></h1>
        <p>Welcome, Admin! Preparing your dashboard.</p>
        <p>You'll be redirected shortly. If not, ensure JavaScript is enabled.</p>
        <hr>
        <div class="note">
            <p><strong>Note:</strong> The full admin panel features are under development. You are being redirected to a temporary page.</p>
        </div>
    </div>
</body>

</html>