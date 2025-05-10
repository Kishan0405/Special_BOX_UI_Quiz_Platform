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
    <title>Privacy and Policy - Special BOX UI Quiz Platform</title>
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
            <h1 class="text-4xl md:text-5xl font-extrabold text-blue-800 drop-shadow">Privacy Policy</h1>
            <p class="text-sm md:text-base text-gray-600 mt-3 italic">Last Updated: May 10, 2025</p>
        </header>

        <div class="bg-white p-6 md:p-10 rounded-xl shadow-2xl border border-blue-100">
            <p class="mb-5 leading-relaxed">This Privacy Policy describes how Special BOX UI ("we," "us," or "our"), the developer of the Special BOX UI Quiz Platform ("Platform" or "Service"), collects, uses, shares, and protects your personal information when you use our Platform. Our head office is located in Udupi, India. The Platform is developed by Kishan Raj, a student at N M A M Institute of Technology, Nitte Karkala.</p>
            <p class="mb-8 leading-relaxed">We are committed to protecting your privacy and handling your data in an open and transparent manner, in compliance with applicable data protection laws in India.</p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">1. Information We Collect</h2>
            <p class="mb-5 leading-relaxed">We collect information to provide and improve our Service to you. The types of personal information we may collect include:</p>

            <h3 class="text-xl md:text-2xl font-semibold mt-6 mb-3 text-gray-700">1.1. Information You Provide Directly</h3>
            <ul class="list-disc list-inside ml-4 mb-4 space-y-2">
                <li class="leading-relaxed"><strong>Account Information:</strong> When you create an account, we collect your username, password (which is stored in a hashed format for security), and a valid email address.</li>
                <li class="leading-relaxed"><strong>User-Generated Content:</strong> Any quizzes, questions, or other content you create, submit, or post on the Platform.</li>
                <li class="leading-relaxed"><strong>Communications:</strong> If you contact us directly (e.g., for support or feedback), we may receive additional information about you such as your name (if provided), email address, the contents of the message and/or attachments you may send us, and any other information you may choose to provide.</li>
            </ul>

            <h3 class="text-xl md:text-2xl font-semibold mt-6 mb-3 text-gray-700">1.2. Information We Collect Automatically</h3>
            <ul class="list-disc list-inside ml-4 mb-4 space-y-2">
                <li class="leading-relaxed"><strong>Usage Data:</strong> Information about your interactions with the Platform, such as quizzes attempted, scores achieved (for registered users), answers submitted (which are updated in our database for registered users), features used, time spent on pages, and other actions taken.</li>
                <li class="leading-relaxed"><strong>Log Data:</strong> When you access the Platform, our servers automatically record information that your browser sends. This Log Data may include your Internet Protocol (IP) address, browser type and settings, the date and time of your request, how you used the Platform, and cookie data.</li>
                <li class="leading-relaxed"><strong>Cookies and Similar Technologies:</strong> We use cookies and similar tracking technologies to track activity on our Service and hold certain information. Cookies are files with a small amount of data which may include an anonymous unique identifier. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent. However, if you do not accept cookies, you may not be able to use some portions of our Service. (See Section 4 for more details on Cookies).</li>
            </ul>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">2. How We Use Your Information</h2>
            <p class="mb-5 leading-relaxed">We use the information we collect for various purposes, including:</p>
            <ul class="list-disc list-inside ml-4 mb-4 space-y-2">
                <li class="leading-relaxed">To provide, operate, and maintain our Platform.</li>
                <li class="leading-relaxed">To create and manage your account and authenticate users.</li>
                <li class="leading-relaxed">To process your quiz attempts, display scores, and provide correct answers (for registered users).</li>
                <li class="leading-relaxed">To enable you to create, manage, and share quizzes.</li>
                <li class="leading-relaxed">To facilitate your participation in public or private events, including allowing users to search for other users by username and email for private event invitations.</li>
                <li class="leading-relaxed">To personalize your experience on the Platform.</li>
                <li class="leading-relaxed">To communicate with you, including responding to your inquiries, providing customer support, and sending you service-related announcements, updates, security alerts, and administrative messages.</li>
                <li class="leading-relaxed">To improve the Platform, including developing new features (like Report Analytics and Category Management), and enhancing user experience. This includes using data from bug reports and feedback.</li>
                <li class="leading-relaxed">To monitor and analyze trends, usage, and activities in connection with our Platform.</li>
                <li class="leading-relaxed">To enforce our Terms and Conditions and other policies.</li>
                <li class="leading-relaxed">To detect, prevent, and address technical issues, fraud, or security concerns, including the "Strict Security Mode" for quizzes.</li>
                <li class="leading-relaxed">For compliance with legal obligations.</li>
            </ul>
            <p class="mb-8 leading-relaxed">The "Special BOX AI Mini" feature, which uses PHP and Special BOX AI content for quiz creation, processes the information you input to generate quiz content. You are responsible for reviewing AI-generated content.</p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">3. How We Share Your Information</h2>
            <p class="mb-5 leading-relaxed">We do not sell your personal information. We may share your information in the following circumstances:</p>
            <ul class="list-disc list-inside ml-4 mb-4 space-y-2">
                <li class="leading-relaxed"><strong>With Other Users (for Events):</strong> Your username and email address may be searchable by other users on the Platform for the sole purpose of inviting you to private online events.</li>
                <li class="leading-relaxed"><strong>With Service Providers:</strong> We may share your information with third-party vendors and service providers that perform services on our behalf, such as web hosting, data analysis, payment processing (if applicable in the future), email delivery, and customer service. These service providers will only have access to your information to the extent necessary to perform these tasks on our behalf and are obligated not to disclose or use it for any other purpose.</li>
                <li class="leading-relaxed"><strong>For Legal Reasons:</strong> We may disclose your information if required to do so by law or in response to valid requests by public authorities (e.g., a court or a government agency), or if we believe in good faith that such action is necessary to:
                    <ul class="list-circle list-inside ml-6 mt-2 space-y-2">
                        <li class="leading-relaxed">Comply with a legal obligation.</li>
                        <li class="leading-relaxed">Protect and defend the rights or property of Special BOX UI.</li>
                        <li class="leading-relaxed">Prevent or investigate possible wrongdoing in connection with the Service.</li>
                        <li class="leading-relaxed">Protect the personal safety of users of the Service or the public.</li>
                        <li class="leading-relaxed">Protect against legal liability.</li>
                    </ul>
                </li>
                <li class="leading-relaxed"><strong>Business Transfers:</strong> If Special BOX UI is involved in a merger, acquisition, or asset sale, your Personal Information may be transferred. We will provide notice before your Personal Information is transferred and becomes subject to a different Privacy Policy.</li>
                <li class="leading-relaxed"><strong>With Your Consent:</strong> We may disclose your personal information for any other purpose with your explicit consent.</li>
            </ul>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">4. Cookies and Tracking Technologies</h2>
            <p class="mb-5 leading-relaxed">We use cookies and similar tracking technologies to enhance your experience on our Platform. These may include:</p>
            <ul class="list-disc list-inside ml-4 mb-4 space-y-2">
                <li class="leading-relaxed"><strong>Essential Cookies:</strong> Necessary for the Platform to function and cannot be switched off. They are usually only set in response to actions made by you which amount to a request for services, such as setting your privacy preferences, logging in, or filling in forms.</li>
                <li class="leading-relaxed"><strong>Performance Cookies:</strong> Allow us to count visits and traffic sources so we can measure and improve the performance of our site.</li>
                <li class="leading-relaxed"><strong>Functionality Cookies:</strong> Enable the Platform to provide enhanced functionality and personalization.</li>
            </ul>
            <p class="mb-8 leading-relaxed">You can control and/or delete cookies as you wish – for details, see aboutcookies.org. You can delete all cookies that are already on your computer and you can set most browsers to prevent them from being placed. If you do this, however, you may have to manually adjust some preferences every time you visit a site and some services and functionalities may not work.</p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">5. Data Security</h2>
            <p class="mb-5 leading-relaxed">We take the security of your data seriously and implement reasonable administrative, technical, and physical security measures designed to protect your personal information from unauthorized access, use, alteration, and disclosure. For example, passwords are encrypted using hashing algorithms. However, please remember that no method of transmission over the Internet or method of electronic storage is 100% secure. While we strive to use commercially acceptable means to protect your Personal Information, we cannot guarantee its absolute security.</p>
            <p class="mb-8 leading-relaxed">In the event of a data breach that is likely to result in a high risk to your rights and freedoms, we will notify you and the relevant authorities as required by applicable law.</p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">6. Data Retention</h2>
            <p class="mb-5 leading-relaxed">We will retain your personal information only for as long as is necessary for the purposes set out in this Privacy Policy, or as long as your account is active. We will retain and use your information to the extent necessary to comply with our legal obligations (for example, if we are required to retain your data to comply with applicable laws), resolve disputes, and enforce our legal agreements and policies.</p>
            <p class="mb-8 leading-relaxed">Please note, as mentioned in our Terms and Conditions, that during Platform upgrades, user accounts may be deleted automatically, and we may not be able to recover such accounts or associated data at this time.</p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">7. Your Data Protection Rights</h2>
            <p class="mb-5 leading-relaxed">Depending on your location and subject to applicable law, you may have certain rights regarding your personal information. In India, these rights are governed by the Digital Personal Data Protection Act, 2023, and other relevant legislation. These may include the right to:</p>
            <ul class="list-disc list-inside ml-4 mb-4 space-y-2">
                <li class="leading-relaxed"><strong>Access:</strong> Request access to the personal information we hold about you.</li>
                <li class="leading-relaxed"><strong>Rectification:</strong> Request correction of inaccurate or incomplete personal information.</li>
                <li class="leading-relaxed"><strong>Erasure (Right to be Forgotten):</strong> Request deletion of your personal information, subject to certain conditions.</li>
                <li class="leading-relaxed"><strong>Restrict Processing:</strong> Request the restriction of processing of your personal information, under certain conditions.</li>
                <li class="leading-relaxed"><strong>Object to Processing:</strong> Object to our processing of your personal information, under certain conditions.</li>
                <li class="leading-relaxed"><strong>Data Portability:</strong> Request the transfer of your personal information to another organization, or directly to you, under certain conditions.</li>
                <li class="leading-relaxed"><strong>Withdraw Consent:</strong> Withdraw your consent at any time where we relied on your consent to process your personal information.</li>
                <li class="leading-relaxed"><strong>Lodge a Complaint:</strong> Lodge a complaint with a supervisory authority (e.g., the Data Protection Board of India, once fully established and operational).</li>
            </ul>
            <p class="mb-8 leading-relaxed">To exercise any of these rights, please contact us at <a href="mailto:specialboxui@hotmail.com" class="text-orange-600 hover:text-orange-800 transition duration-300 ease-in-out underline">specialboxui@hotmail.com</a>. We may need to verify your identity before responding to your request.</p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">8. Children's Privacy</h2>
            <p class="mb-8 leading-relaxed">Our Service is not intended for use by children under the age of 18 (or the age of consent stipulated by applicable Indian law for processing personal data without parental consent) without verifiable parental consent. We do not knowingly collect personally identifiable information from children under this age. If you are a parent or guardian and you are aware that your child has provided us with Personal Information without your consent, please contact us. If we become aware that we have collected Personal Information from children without verification of parental consent, we take steps to remove that information from our servers.</p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">9. Links to Other Websites</h2>
            <p class="mb-8 leading-relaxed">Our Service may contain links to other websites that are not operated by us. If you click on a third-party link, you will be directed to that third party's site. We strongly advise you to review the Privacy Policy of every site you visit. We have no control over and assume no responsibility for the content, privacy policies, or practices of any third-party sites or services.</p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">10. Changes to This Privacy Policy</h2>
            <p class="mb-8 leading-relaxed">We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last Updated" date at the top of this Privacy Policy. You are advised to review this Privacy Policy periodically for any changes. Changes to this Privacy Policy are effective when they are posted on this page.</p>

            <h2 class="text-2xl md:text-3xl font-bold mt-8 mb-4 text-teal-700 border-b-2 border-teal-200 pb-2">11. Contact Us</h2>
            <p class="mb-8 leading-relaxed">If you have any questions about these Terms, please contact us at: <a href="mailto:specialboxui@hotmail.com" class="text-orange-600 hover:text-orange-800 transition duration-300 ease-in-out underline">specialboxui@hotmail.com</a> or via LinkedIn</p>

            <p class="text-base text-gray-600 mt-10 text-center italic">Thank you for trusting Special BOX UI Quiz Platform with your information.</p>
        </div>
    </div>
</body>

</html>

<?php
include 'footer.php'; // Include footer
?>