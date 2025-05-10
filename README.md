# Special BOX UI Quiz Platform

A modern, secure quiz platform developed in India by **Special BOX UI**, headquartered in Udupi, India. This platform was created by **Kishan Raj**, a Biotechnology Engineering student at **N.M.A.M Institute of Technology**, Nitte, Karkala.

---

## 🌟 Overview

The **Special BOX UI Quiz Platform** is designed for students, educators, and quiz enthusiasts. It supports individual and group quizzes, private events, and AI-assisted quiz creation, with a strong focus on privacy and security.

---

## 🔐 Authentication

- Simple user authentication with only **Username**, **Password**, and **Email**.
- Email and username are mandatory for quiz creation and private event participation.
- Non-registered users can attempt public quizzes, but scores and correct answers are hidden.
- Registered users enjoy full access to quiz reports, answers, events, and more.

---

## 🔑 Key Features

- 🧾 **Account Control**: Only registered users can access private events and detailed results.
- 🛡️ **Strict Quiz Locking**: Once a quiz is locked, neither users nor admins can resume it.
- 🔍 **User Search**: Search for participants in private events using username or email.
- ✨ **Quiz Modes**: Choose from **Sequential** or **Step-by-Step** quiz layouts.
- 🤖 **Special BOX AI Mini Version**: Generate quizzes using PHP-based backend AI.
- 📋 **Instruction Panel**: Clear guidelines before quiz begins.
- 🧠 **Create & Manage Quizzes**: Admin dashboard for creating, editing, and deleting quizzes.
- 🗃️ **Upcoming Features**:
  - Report Analytics
  - Category Management
  - Separate interfaces for quiz creation and management (from June 2025)

---

## ⚠️ Notes & Disclaimers

- We do **not** store or share your email, username, or password with anyone.
- Occasionally, due to upgrades, user accounts may be **automatically deleted** and **cannot be recovered**.
- **Report bugs** or interface issues to help improve the platform.
- Developer retains full rights to remove or review content based on community feedback or policy violations.
- **Special BOX UI is not liable** for any data breaches, misuse, or violations by users.

---

## 🚀 Getting Started

### Prerequisites

- PHP 7.4+
- MySQL/MariaDB
- Apache/Nginx server
- Composer (optional)

### Installation

```bash
git clone https://github.com/Kishan0405/specialboxuiquiz.git
cd specialboxuiquiz
# Import database from 'data.sql' into your MySQL server
# Configure /config/db.php with your database credentials
