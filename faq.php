<?php
// home.php
require_once 'includes/database.php';
require_once 'includes/auth.php';
include 'header.php'; // Assumes header.php starts HTML, head, and includes navbar
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs - Special BOX UI Quiz Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Poppins:wght@400;600;700&family=Press+Start+2P&family=Roboto+Condensed:wght@700&family=Roboto+Mono&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* --- Google Fonts --- */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--qhead-text-dark);
            background-color: var(--qhead-background-light);
        }
    </style>

</head>

<body>

    <div>
        <h1 class="text-3xl font-bold text-center text-indigo-900 mb-6">Frequently Asked Questions</h1>
        <p class="text-center text-gray-600 mb-8">Welcome to the Special BOX UI Quiz Platform, developed by Kishan Raj, a biotechnology engineering student at NMAM Institute of Technology, Nitte Karkala, India.</p>

        <div class="mb-6">
            <input type="text" id="faqSearch" placeholder="Search FAQs..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>


        <div class="faq-item mb-4 p-4 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-md p-3 md:p-4">
            <div class="faq-question font-bold text-indigo-900 cursor-pointer">What is the Special BOX UI Quiz Platform?</div>
            <div class="faq-answer text-gray-700 mt-2 hidden">The Special BOX UI Quiz Platform is an innovative quiz platform developed in India, designed to offer engaging and secure quiz experiences for users. It supports both public and private quizzes, with features tailored for educational and entertainment purposes.</div>
        </div>

        <div class="faq-item mb-4 p-4 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-md p-3 md:p-4">
            <div class="faq-question font-bold text-indigo-900 cursor-pointer">Do I need an account to use the platform?</div>
            <div class="faq-answer text-gray-700 mt-2 hidden">Non-account holders can attempt all quizzes but won't see their scores or correct answers. Creating an account is mandatory to access full features, including private events, score tracking, and quiz creation.</div>
        </div>

        <div class="faq-item mb-4 p-4 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-md p-3 md:p-4">
            <div class="faq-question font-bold text-indigo-900 cursor-pointer">How secure is my data on this platform?</div>
            <div class="faq-answer text-gray-700 mt-2 hidden">Data such as email, password, and username is securely stored and not accessible by others. Username and email are only used for searching users in private events.</div>
        </div>

        <div class="faq-item mb-4 p-4 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-md p-3 md:p-4">
            <div class="faq-question font-bold text-indigo-900 cursor-pointer">What happens if I get locked out of a quiz?</div>
            <div class="faq-answer text-gray-700 mt-2 hidden">The platform uses a strict security mode. Once a quiz is locked, neither the user nor the quiz admin can continue or resume it, ensuring fair play and integrity.</div>
        </div>

        <div class="faq-item mb-4 p-4 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-md p-3 md:p-4">
            <div class="faq-question font-bold text-indigo-900 cursor-pointer">Can I create my own quizzes?</div>
            <div class="faq-answer text-gray-700 mt-2 hidden">Yes, users can create quizzes by accessing the admin dashboard via the "My Quizzes" section. The dashboard allows you to create and manage quizzes, with a 1-second delay for loading.</div>
        </div>

        <div class="faq-item mb-4 p-4 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-md p-3 md:p-4">
            <div class="faq-question font-bold text-indigo-900 cursor-pointer">What are the different quiz modes available?</div>
            <div class="faq-answer text-gray-700 mt-2 hidden">The platform offers Sequential and Step-by-Step modes for quiz layouts, providing flexibility and compatibility for various user preferences.</div>
        </div>

        <div class="faq-item mb-4 p-4 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-md p-3 md:p-4">
            <div class="faq-question font-bold text-indigo-900 cursor-pointer">What changes are coming in June 2025?</div>
            <div class="faq-answer text-gray-700 mt-2 hidden">From June 2025, "Creating New Quiz" and "Manage Quizzes" (including addition, deletion, and editing) will be separated into distinct pages to improve usability and address space constraints.</div>
        </div>

        <div class="faq-item mb-4 p-4 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-md p-3 md:p-4">
            <div class="faq-question font-bold text-indigo-900 cursor-pointer">How does the Special BOX AI Mini Version work?</div>
            <div class="faq-answer text-gray-700 mt-2 hidden">The platform uses a mini version of Special BOX AI, implemented in PHP, to assist in creating quizzes and adding questions. Always review AI-generated content for accuracy.</div>
        </div>

        <div class="faq-item mb-4 p-4 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-md p-3 md:p-4">
            <div class="faq-question font-bold text-indigo-900 cursor-pointer">What should I do if I encounter issues with the platform?</div>
            <div class="faq-answer text-gray-700 mt-2 hidden">If you face any issues or find the interface problematic, please report them to our community. We are committed to fixing bugs and improving the user experience based on your feedback.</div>
        </div>

        <div class="faq-item mb-4 p-4 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-md p-3 md:p-4">
            <div class="faq-question font-bold text-indigo-900 cursor-pointer">Can my account be deleted automatically?</div>
            <div class="faq-answer text-gray-700 mt-2 hidden">During platform upgrades, user accounts may occasionally be deleted automatically. Unfortunately, we cannot recover such accounts at this time.</div>
        </div>

        <div class="faq-item mb-4 p-4 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-md p-3 md:p-4">
            <div class="faq-question font-bold text-indigo-900 cursor-pointer">Who is responsible for data breaches or violations?</div>
            <div class="faq-answer text-gray-700 mt-2 hidden">Special BOX UI Platform is not responsible for any data breaches, violations, or other issues that may affect users. We recommend using strong passwords and secure email addresses.</div>
        </div>

        <div class="faq-item mb-4 p-4 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-md p-3 md:p-4">
            <div class="faq-question font-bold text-indigo-900 cursor-pointer">What new features are planned?</div>
            <div class="faq-answer text-gray-700 mt-2 hidden">In the coming months, we plan to introduce Report Analytics, Category Management, and additional quiz-taking features to enhance the platform's functionality.</div>
        </div>

        <div class="faq-item mb-4 p-4 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-md p-3 md:p-4">
            <div class="faq-question font-bold text-indigo-900 cursor-pointer">Who reviews or deletes quizzes?</div>
            <div class="faq-answer text-gray-700 mt-2 hidden">The platform developer, Kishan Raj, reserves the right to review or delete quizzes based on performance and user feedback from the community.</div>
        </div>

        <div class="faq-item mb-4 p-4 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-md p-3 md:p-4">
            <div class="faq-question font-bold text-indigo-900 cursor-pointer">What types of users can access the platform?</div>
            <div class="faq-answer text-gray-700 mt-2 hidden">Both registered and unregistered users can access the platform. However, registered users enjoy full access, including score viewing, event participation, and quiz creation capabilities.</div>
        </div>

        <div class="faq-item mb-4 p-4 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-md p-3 md:p-4">
            <div class="faq-question font-bold text-indigo-900 cursor-pointer">Why can't I see correct answers or scores?</div>
            <div class="faq-answer text-gray-700 mt-2 hidden">Only registered users can view scores and correct answers after completing a quiz. This is part of the platform's feature to encourage secure and accountable participation.</div>
        </div>

        <div class="faq-item mb-4 p-4 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-md p-3 md:p-4">
            <div class="faq-question font-bold text-indigo-900 cursor-pointer">How are private events managed?</div>
            <div class="faq-answer text-gray-700 mt-2 hidden">Private events are accessible only to users with valid usernames and emails. These identifiers are used to securely search and invite participants.</div>
        </div>

        <div class="faq-item mb-4 p-4 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-md p-3 md:p-4">
            <div class="faq-question font-bold text-indigo-900 cursor-pointer">What happens if my account gets deleted during upgrades?</div>
            <div class="faq-answer text-gray-700 mt-2 hidden">During major platform upgrades, some accounts may be deleted automatically. At present, we are unable to recover such accounts, so we recommend backing up important data periodically.</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <script>
        // Script for toggling FAQ answers
        document.querySelectorAll('.faq-question').forEach(item => {
            item.addEventListener('click', () => {
                const answer = item.nextElementSibling;
                // Toggle Tailwind's hidden/block classes
                answer.classList.toggle('hidden');
                answer.classList.toggle('block');
            });
        });

        // Script for FAQ search functionality
        const faqSearchInput = document.getElementById('faqSearch');
        const faqItems = document.querySelectorAll('.faq-item');

        faqSearchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();

            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question').textContent.toLowerCase();
                const answer = item.querySelector('.faq-answer').textContent.toLowerCase();

                if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                    // If search term is found, ensure item is visible
                    item.classList.remove('hidden');
                    item.classList.add('block');
                } else {
                    // If search term is not found, hide the item
                    item.classList.remove('block');
                    item.classList.add('hidden');
                }
            });
        });
    </script>
</body>

</html>

<?php
include 'footer.php'; // Include footer
?>