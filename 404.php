<?php
require_once 'includes/auth.php';
require_once 'header.php'; // Include the site header for consistent navigation
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found - Special BOX UI Quiz Platform</title>
    <meta name="description" content="Page not found on Special BOX UI Quiz Platform. Return to home or explore quizzes. (104 chars)">
    <!-- Bootstrap CSS (already included in header.php) -->
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Poppins:wght@400;600;700&family=Press+Start+2P&family=Roboto+Condensed:wght@700&family=Roboto+Mono&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/user-info-container.css" />
</head>

<style>
    /* --- Google Fonts --- */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&display=swap');

    body {
        font-family: 'Poppins', sans-serif;
        line-height: 1.6;
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
</style>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="bg-white p-6 rounded-lg shadow-lg user-info-container text-center">
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">404 - Page Not Found</h2>
                    <p class="text-gray-600 mb-6">Oops! The page you're looking for doesn't exist or has been moved.</p>
                    <div class="space-y-4">
                        <a href="home" class="block bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-300">
                            Return to Home
                        </a>
                        <p class="text-gray-600">
                            Or explore our <a href="home" class="text-blue-600 hover:underline">quizzes</a> or check the <a href="help" class="text-blue-600 hover:underline">help section</a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>