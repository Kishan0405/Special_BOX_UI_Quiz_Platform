<?php

require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'header.php';

if (!isset($_SESSION['registration_success']) || !$_SESSION['registration_success']) {
    header("Location: register.php");
    exit();
}

$username = $_SESSION['registered_username'] ?? '';

unset($_SESSION['registration_success']);
unset($_SESSION['registered_username']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - Special BOX UI Quiz</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="success-container">
        <i class="fas fa-check-circle success-icon"></i>
        <h2>Registration Successful!</h2>
        <p>Thank you for registering. You will be automatically redirected to the login page in 10 seconds.</p>
        <p class="redirect-message">Redirecting...</p>
    </div>

    <script>
        const redirectMessage = document.querySelector('.redirect-message');
        let timer = 10;

        function updateTimer() {
            redirectMessage.textContent = `Redirecting in ${timer} seconds...`;
            timer--;
            if (timer < 0) {
                window.location.href = 'login.php?username=<?php echo urlencode($username); ?>';
            } else {
                setTimeout(updateTimer, 1000);
            }
        }

        updateTimer();
    </script>
</body>

</html>

<style>
    /* Registration Success Page Styles */

    :root {
        --primary-color: #4CAF50;
        --secondary-color: #2E7D32;
        --text-color: #333;
        --light-bg: #f9f9f9;
        --shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html,
    body {
        height: 100%;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: var(--light-bg);
        color: var(--text-color);
    }

    .success-container {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 90%;
        max-width: 500px;
        background-color: white;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: var(--shadow);
        text-align: center;
        animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translate(-50%, -50%) scale(0.9);
        }

        to {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }
    }

    .success-icon {
        font-size: 5rem;
        color: var(--primary-color);
        margin-bottom: 1.5rem;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }

        100% {
            transform: scale(1);
        }
    }

    h2 {
        font-size: 2rem;
        margin-bottom: 1rem;
        color: var(--secondary-color);
    }

    p {
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .redirect-message {
        font-weight: 600;
        font-size: 1rem;
        color: var(--primary-color);
        animation: blink 1s infinite;
    }

    @keyframes blink {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }

        100% {
            opacity: 1;
        }
    }

    /* Media Queries for Responsiveness */
    @media (max-width: 768px) {
        .success-container {
            width: 95%;
            padding: 1.5rem;
        }

        .success-icon {
            font-size: 4rem;
        }

        h2 {
            font-size: 1.7rem;
        }

        p {
            font-size: 1rem;
        }
    }

    @media (max-width: 480px) {
        .success-container {
            padding: 1.2rem;
        }

        .success-icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
        }

        h2 {
            font-size: 1.4rem;
        }

        p {
            font-size: 0.9rem;
            margin-bottom: 0.8rem;
        }
    }

    @media (prefers-reduced-motion) {

        .success-icon,
        .success-container,
        .redirect-message {
            animation: none;
        }
    }
</style>