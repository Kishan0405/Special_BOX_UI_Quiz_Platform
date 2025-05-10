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
    <title>Available Soon</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0ea5e9;
            /* Sky blue */
            --text-dark: #1f2937;
            /* Gray-800 */
            --text-light: #4b5563;
            /* Gray-600 */
            --bg-start: #f0f9ff;
            /* Light blue */
            --bg-end: #e0f2fe;
            /* Lighter blue */
        }

        body {
            background: linear-gradient(135deg, var(--bg-start), var(--bg-end));
            color: var(--text-dark);
        }

        .container {
            padding: 2rem;
            text-align: center;
            max-width: 32rem;
        }

        .icon {
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1.5rem;
            fill: var(--primary-color);
            animation: pulse 2s infinite ease-in-out;
        }

        h1 {
            font-size: calc(2rem + 1vw);
            font-weight: 700;
            margin-bottom: 1rem;
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
            font-size: 1.125rem;
            color: var(--text-light);
            line-height: 1.5;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
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
                padding: 1.5rem;
            }

            h1 {
                font-size: 1.75rem;
            }

            p {
                font-size: 1rem;
            }

            .icon {
                width: 3rem;
                height: 3rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <svg class="icon" viewBox="0 0 24 24">
            <path d="M13 2v9h-2V2h2m2 0h1v9h-1V2zm-4 0H8v9H7V2h1zm8 18v2h-7v-2h7m-9 0v2H3v-2h7m4-3c0-1.1-.9-2-2-2h-2c-1.1 0-2 .9-2 2h-2v-2c0-2.2 1.8-4 4-4h2c2.2 0 4 1.8 4 4v2h-2z" />
        </svg>
        <h1>Coming Soon<span class="dots"><span>.</span><span>.</span><span>.</span></span></h1>
        <p>I'am working hard to bring this feature to you. Please check back later!</p>
    </div>
</body>

</html>