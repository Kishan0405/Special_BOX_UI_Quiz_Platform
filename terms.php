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
    <title>Terms and Conditions - Special BOX UI Quiz Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
        <header class="mb-10 text-center">
            <h1 class="text-4xl md:text-5xl font-extrabold text-blue-800 drop-shadow">Terms and Conditions</h1>
            <p class="text-sm md:text-base text-gray-600 mt-3 italic">Last Updated: May 10, 2025</p>
        </header>

        <div class="bg-white p-6 md:p-10 rounded-xl shadow-2xl border border-blue-100">

            <p class="mb-5 leading-relaxed">Welcome to the Special BOX UI Quiz Platform ("Platform", "Service", "we", "us", "our"). This Platform is developed in India by Special BOX UI, with its head office in Udupi, India, by Kishan Raj, a biotechnology engineering student at N M A M Institute of Technology, Nitte Karkala.</p>

            <p class="mb-8 leading-relaxed">These Terms and Conditions ("Terms") govern your access to and use of our Platform and its related services. By accessing or using the Platform, you agree to be bound by these Terms and our Privacy Policy (available at <a href="privacy_policy.php" class="text-orange-600 hover:text-orange-800 transition duration-300 ease-in-out underline">Link to Privacy Policy</a>). If you do not agree to these Terms, please do not use the Platform.</p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">1. Platform Overview</h2>
            <p class="mb-6 leading-relaxed">The Special BOX UI Quiz Platform is designed to provide users with an interactive quiz experience. Users can attempt quizzes, and registered users can create and manage their own quizzes and participate in events.</p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">2. User Accounts</h2>
            <p class="mb-3 leading-relaxed"><strong>2.1. Registration:</strong> To access all features of the Platform, including creating quizzes, viewing scores, and participating in events, you must create an account. Registration requires a unique username, a password, and a valid email address. You agree to provide accurate, current, and complete information during the registration process.</p>
            <p class="mb-3 leading-relaxed"><strong>2.2. Account Responsibility:</strong> You are responsible for safeguarding your password and for all activities that occur under your account. You agree to notify us immediately of any unauthorized use of your account.</p>
            <p class="mb-3 leading-relaxed"><strong>2.3. Data Usage:</strong> Your username and email address may be used to search for users for online private events hosted on the Platform. We are committed to protecting your data; however, please review our Privacy Policy for detailed information on data handling.</p>
            <p class="mb-3 leading-relaxed"><strong>2.4. Non-Account Holders:</strong> Users without an account ("Guests") can attempt quizzes available to the public. However, Guests will not have their scores displayed or access to correct answers post-quiz. Guest responses are not updated in our database for personalized tracking. Guests do not have access to private events or quiz creation tools.</p>
            <p class="mb-6 leading-relaxed"><strong>2.5. Account Deletion During Upgrades:</strong> Please note that from time to time, while upgrading our Platform or its underlying infrastructure, user accounts may be unintentionally deleted. Currently, we may not have the capability to recover such lost accounts or associated data. We recommend users maintain backups of any critical information where possible.</p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">3. Use of the Platform</h2>
            <p class="mb-3 leading-relaxed"><strong>3.1. Permitted Use:</strong> You agree to use the Platform only for lawful purposes and in accordance with these Terms.</p>
            <p class="mb-3 leading-relaxed"><strong>3.2. Instructions:</strong> A set of instructions is available for quiz participation. Please read these instructions carefully before starting any quiz.</p>
            <p class="mb-3 leading-relaxed"><strong>3.3. Strict Security Mode:</strong> Our Platform may employ a Strict Security Mode for attempting quizzes. If this mode is triggered (e.g., due to suspected cheating or navigating away from the quiz window), your quiz session may be locked. Once locked, it may be impossible for both the user and the quiz administrator to resume or continue that specific quiz attempt.</p>
            <p class="mb-6 leading-relaxed"><strong>3.4. Prohibited Conduct:</strong> You agree not to:
            <ul class="list-disc list-inside ml-4 mb-4 space-y-2">
                <li class="leading-relaxed">Use the Platform in any way that violates any applicable national or international law or regulation.</li>
                <li class="leading-relaxed">Engage in any conduct that restricts or inhibits anyone's use or enjoyment of the Platform, or which, as determined by us, may harm the Platform or users of the Platform.</li>
                <li class="leading-relaxed">Use any robot, spider, or other automatic device, process, or means to access the Platform for any purpose, including monitoring or copying any of the material on the Platform.</li>
                <li class="leading-relaxed">Introduce any viruses, trojan horses, worms, logic bombs, or other material that is malicious or technologically harmful.</li>
                <li class="leading-relaxed">Attempt to gain unauthorized access to, interfere with, damage, or disrupt any parts of the Platform, the server on which the Platform is stored, or any server, computer, or database connected to the Platform.</li>
            </ul>
            </p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">4. User-Generated Content (Quizzes)</h2>
            <p class="mb-3 leading-relaxed"><strong>4.1. Creating Quizzes:</strong> Registered users can create their own quizzes by clicking "My Quizzes" which will open an admin dashboard. Users are solely responsible for the content of the quizzes they create, including its accuracy, legality, and appropriateness.</p>
            <p class="mb-3 leading-relaxed"><strong>4.2. Content Standards:</strong> User-generated quizzes must not contain any material that is defamatory, obscene, indecent, abusive, offensive, harassing, violent, hateful, inflammatory, or otherwise objectionable. Quizzes must not promote sexually explicit material, violence, or discrimination based on race, sex, religion, nationality, disability, sexual orientation, or age.</p>
            <p class="mb-3 leading-relaxed"><strong>4.3. Special BOX AI Mini:</strong> The Platform may integrate "Special BOX AI Mini," a feature utilizing PHP in the backend with Special BOX AI content, to assist in creating quizzes and adding questions. If you use this AI tool, you are responsible for reviewing and verifying the accuracy and appropriateness of all AI-generated content before publishing your quiz.</p>
            <p class="mb-3 leading-relaxed"><strong>4.4. Platform Rights:</strong> The developer of the Platform (Special BOX UI) reserves all rights to review, monitor, edit, or delete any user-created quizzes at any time, without notice, for any reason, including but not limited to, poor performance, negative user reviews from the community, or violation of these Terms.</p>
            <p class="mb-6 leading-relaxed"><strong>4.5. License to Platform:</strong> By creating quizzes on the Platform, you grant Special BOX UI a worldwide, non-exclusive, royalty-free, sublicensable, and transferable license to use, reproduce, distribute, prepare derivative works of, display, and perform the content in connection with the Service and otherwise in connection with the provision of the Service and Special BOX UI's business.</p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">5. Platform Features and Future Development</h2>
            <p class="mb-3 leading-relaxed"><strong>5.1. Quiz Layout Modes:</strong> Different quiz layout modes, such as "Sequential" and "Step by Step," may be available. These features can be enabled for enhanced user compatibility.</p>
            <p class="mb-3 leading-relaxed"><strong>5.2. Upcoming UI Changes (Quiz Management):</strong> Please note that from June 2025, the "Creating New Quiz" function and the "Manage Quizzes" (including addition, deletion, editing of quizzes, and editing the main page of quizzes) functions may be separated into different pages due to interface space constraints.</p>
            <p class="mb-3 leading-relaxed"><strong>5.3. Future Features:</strong> We are continuously working to improve the Platform. More features for taking quizzes, as well as "Report Analytics" and "Category Management," are planned for development in the upcoming months.</p>
            <p class="mb-6 leading-relaxed"><strong>5.4. Feedback and Bug Reporting:</strong> We encourage users to report any issues, bugs, or annoyances encountered while using the Platform. Your feedback is valuable for improving the user experience. Please send reports to <a href="mailto:specialboxui@hotmail.com" class="text-orange-600 hover:text-orange-800 transition duration-300 ease-in-out underline">specialboxui@hotmail.com</a>.</p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">6. Intellectual Property</h2>
            <p class="mb-6 leading-relaxed">The Platform and its original content (excluding content provided by users), features, and functionality are and will remain the exclusive property of Special BOX UI and its licensors. The Platform is protected by copyright, trademark, and other laws of both India and foreign countries. Our trademarks and trade dress may not be used in connection with any product or service without the prior written consent of Special BOX UI.</p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">7. Disclaimers</h2>
            <p class="mb-3 leading-relaxed font-mono text-sm bg-gray-100 p-4 rounded">THE PLATFORM IS PROVIDED ON AN "AS IS" AND "AS AVAILABLE" BASIS. SPECIAL BOX UI MAKES NO REPRESENTATIONS OR WARRANTIES OF ANY KIND, EXPRESS OR IMPLIED, AS TO THE OPERATION OF THEIR SERVICES, OR THE INFORMATION, CONTENT OR MATERIALS INCLUDED THEREIN. YOU EXPRESSLY AGREE THAT YOUR USE OF THESE SERVICES, THEIR CONTENT, AND ANY SERVICES OR ITEMS OBTAINED FROM US IS AT YOUR SOLE RISK.</p>
            <p class="mb-6 leading-relaxed font-mono text-sm bg-gray-100 p-4 rounded">NEITHER SPECIAL BOX UI NOR ANY PERSON ASSOCIATED WITH SPECIAL BOX UI MAKES ANY WARRANTY OR REPRESENTATION WITH RESPECT TO THE COMPLETENESS, SECURITY, RELIABILITY, QUALITY, ACCURACY, OR AVAILABILITY OF THE SERVICES. WITHOUT LIMITING THE FOREGOING, NEITHER SPECIAL BOX UI NOR ANYONE ASSOCIATED WITH SPECIAL BOX UI REPRESENTS OR WARRANTS THAT THE SERVICES, THEIR CONTENT, OR ANY SERVICES OR ITEMS OBTAINED THROUGH THE SERVICES WILL BE ACCURATE, RELIABLE, ERROR-FREE, OR UNINTERRUPTED, THAT DEFECTS WILL BE CORRECTED, THAT THE SERVICES OR THE SERVER THAT MAKES IT AVAILABLE ARE FREE OF VIRUSES OR OTHER HARMFUL COMPONENTS OR THAT THE SERVICES OR ANY SERVICES OR ITEMS OBTAINED THROUGH THE SERVICES WILL OTHERWISE MEET YOUR NEEDS OR EXPECTATIONS.</p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">8. Limitation of Liability</h2>
            <p class="mb-6 leading-relaxed font-mono text-sm bg-gray-100 p-4 rounded">IN NO EVENT SHALL SPECIAL BOX UI, ITS DEVELOPERS, AFFILIATES, LICENSORS, SERVICE PROVIDERS, EMPLOYEES, AGENTS, OFFICERS, OR DIRECTORS BE LIABLE FOR DAMAGES OF ANY KIND, UNDER ANY LEGAL THEORY, ARISING OUT OF OR IN CONNECTION WITH YOUR USE, OR INABILITY TO USE, THE PLATFORM, ANY WEBSITES LINKED TO IT, ANY CONTENT ON THE PLATFORM OR SUCH OTHER WEBSITES, INCLUDING ANY DIRECT, INDIRECT, SPECIAL, INCIDENTAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES, INCLUDING BUT NOT LIMITED TO, PERSONAL INJURY, PAIN AND SUFFERING, EMOTIONAL DISTRESS, LOSS OF REVENUE, LOSS OF PROFITS, LOSS OF BUSINESS OR ANTICIPATED SAVINGS, LOSS OF USE, LOSS OF GOODWILL, LOSS OF DATA, AND WHETHER CAUSED BY TORT (INCLUDING NEGLIGENCE), BREACH OF CONTRACT, OR OTHERWISE, EVEN IF FORESEEABLE.
                IF ANY VIOLATIONS, DATA BREACHES, OR OTHER INCIDENTS OCCUR AFFECTING ANY USER, SPECIAL BOX UI PLATFORM IS NOT RESPONSIBLE OR LIABLE FOR ANY RESULTING DAMAGES OR LOSSES. YOUR SOLE REMEDY FOR DISSATISFACTION WITH THE PLATFORM IS TO STOP USING THE PLATFORM.</p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">9. Indemnification</h2>
            <p class="mb-6 leading-relaxed">You agree to defend, indemnify, and hold harmless Special BOX UI, its affiliates, licensors, and service providers, and its and their respective officers, directors, employees, contractors, agents, licensors, suppliers, successors, and assigns from and against any claims, liabilities, damages, judgments, awards, losses, costs, expenses, or fees (including reasonable attorneys' fees) arising out of or relating to your violation of these Terms or your use of the Platform, including, but not limited to, your user-generated content, any use of the Platform's content, services, and products other than as expressly authorized in these Terms.</p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">10. Termination</h2>
            <p class="mb-6 leading-relaxed">We may terminate or suspend your account and bar access to the Service immediately, without prior notice or liability, under our sole discretion, for any reason whatsoever and without limitation, including but not limited to a breach of the Terms.</p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">11. Governing Law</h2>
            <p class="mb-6 leading-relaxed">These Terms shall be governed and construed in accordance with the laws of India, without regard to its conflict of law provisions. Any legal suit, action, or proceeding arising out of, or related to, these Terms or the Platform shall be instituted exclusively in the competent courts of Udupi, Karnataka, India.</p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">12. Changes to Terms</h2>
            <p class="mb-6 leading-relaxed">We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material, we will make reasonable efforts to provide at least 30 days' notice prior to any new terms taking effect. What constitutes a material change will be determined at our sole discretion. By continuing to access or use our Service after those revisions become effective, you agree to be bound by the revised terms.</p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">13. Contact Us</h2>
            <p class="mb-6 leading-relaxed">If you have any questions about these Terms, please contact us at: <a href="mailto:specialboxui@hotmail.com" class="text-orange-600 hover:text-orange-800 transition duration-300 ease-in-out underline">specialboxui@hotmail.com</a> or via LinkedIn</p>

            <p class="text-base text-gray-600 mt-10 text-center italic">Thank you for using Special BOX UI Quiz Platform!</p>
        </div>
    </div>

</body>

</html>

<?php
include 'footer.php'; // Include footer
?>