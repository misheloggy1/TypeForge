# ⚡ TypeForge

**Master your typing speed & accuracy** — a sleek, modern typing test and training web application inspired by Monkeytype, built from scratch with PHP, MySQL, and vanilla JavaScript.

![TypeForge](https://img.shields.io/badge/Status-Active-brightgreen) ![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white) ![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?logo=mysql&logoColor=white) ![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?logo=javascript&logoColor=black) ![License](https://img.shields.io/badge/License-MIT-blue)

---

## 📖 About

TypeForge is a feature-rich typing speed test and training platform designed to help users improve their keyboard skills. It features real-time WPM & accuracy tracking, multiple difficulty modes, a global leaderboard system, intelligent weak-key detection with a visual keyboard heatmap, and targeted training drills — all wrapped in a stunning dark-themed UI.

---

## ✨ Key Features

### 🎯 Typing Test Engine
- **Real-time stats** — Live WPM, accuracy, and countdown timer updated every second
- **Multiple time modes** — 15s, 30s, 60s, and 120s test durations
- **Custom word categories** — Standard words, numbers & symbols, and pangrams
- **Daily Challenge** — A unique, seeded passage generated daily for fair global competition

### 🔥 Difficulty Modes
- **Beginner** — Standard typing with full backspace support
- **Pro** — Backspace completely disabled; every keystroke is final
- **Legend** — One single typo and the entire test fails instantly

### 🎹 Interactive Keyboard Heatmap
- **Per-test heatmap** — Visual keyboard overlay in the results modal showing which keys you missed
- **Lifetime analytics** — Dedicated full-page heatmap ([heatmap.php](heatmap.php)) with dynamically calculated error thresholds based on your personal history
- **Split-key rendering** — Number/symbol keys display independent error highlighting for each half

### 🏋️ Smart Training Mode
- **Weak-key targeting** — Automatically identifies your top 4 most-missed alphabetical keys from lifetime data
- **Filtered word pool** — Training passages are generated using only words containing your weak keys
- **Score isolation** — Training drill results are excluded from the global leaderboard to keep it fair

### 🏆 Leaderboard System
- **All-Time** — Global ranking based on best Net WPM (one entry per user)
- **Weekly** — Rolling 7-day leaderboard
- **Daily Challenge** — Same-passage ranking for the day's challenge
- **Difficulty badges** — Pro ⭐ and Legend ⚔️ badges displayed next to usernames with animated glow effects

### 👤 User Profile & Dashboard
- **Personal bests** — Best Net WPM and highest accuracy at a glance
- **WPM Progression chart** — Line chart (Chart.js) showing your last 25 test results
- **Recent test history** — Table of your 20 most recent tests with all stats

### 🔐 Authentication & Security
- **Email verification** — 6-digit OTP code sent via Brevo API during registration
- **Password reset** — Secure token-based password reset flow with expiry
- **Session-based auth** — Protected routes with session checks
- **Prepared statements** — SQL injection prevention on score saving and password operations
- **Environment variables** — All sensitive credentials stored in `.env` (excluded from Git)

### 🔊 Audio Feedback
- **3 keypress sound options** — Click, Pop, and Beep (or Off)
- **Zero-latency audio engine** — Uses Web Audio API (`AudioContext`) for instant playback with no network delay
- **Persistent preferences** — Sound and difficulty settings saved per-user in the database

### ⚙️ User Settings
- **Keypress audio selection** — Choose your preferred keystroke sound effect
- **Difficulty mode selection** — Switch between Beginner, Pro, and Legend modes
- **Settings persistence** — Preferences saved to the database and applied automatically on every visit

---

## 🛠️ Technology Stack

### Frontend
| Technology | Purpose |
|---|---|
| **HTML5** | Semantic page structure |
| **CSS3** | Custom dark theme with glassmorphism, animations, and responsive design |
| **JavaScript (ES6)** | Typing engine, real-time stats, audio system, heatmap rendering |
| **Chart.js** | WPM progression line charts on the profile page |
| **Google Fonts** | JetBrains Mono (monospace) & Lexend Deca (UI) |

### Backend
| Technology | Purpose |
|---|---|
| **PHP 8.x** | Server-side logic, routing, session management |
| **MySQL 8.x** | User accounts, test results, key fault aggregation |
| **Brevo API (v3)** | Transactional email delivery (OTP verification & password resets) |
| **cURL** | HTTP client for Brevo API integration |

---

## 🗄️ Database Schema

TypeForge uses **3 main tables**:

| Table | Description |
|---|---|
| `users` | Stores user accounts — username, email, hashed password, reset tokens, sound/difficulty preferences |
| `test_results` | Stores every typing test — Net WPM, Raw WPM, accuracy, difficulty mode, challenge date |
| `user_key_faults` | Aggregated lifetime key error counts per user (UPSERT pattern) |

---

## 🚀 Installation & Setup

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP)
- A [Brevo](https://www.brevo.com/) account (free tier works) for email functionality

### Step 1 — Clone the Repository

```bash
git clone https://github.com/your-username/TypeForge.git
```

Copy (or move) the cloned folder into your XAMPP web root:

```
C:\xampp\htdocs\TypeForge
```

### Step 2 — Create the MySQL Database

1. Start **Apache** and **MySQL** from the XAMPP Control Panel
2. Open [phpMyAdmin](http://localhost/phpmyadmin)
3. Create a new database named **`typeforge_db`**
4. Run the following SQL to create the required tables:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_expires DATETIME DEFAULT NULL,
    sound_pref VARCHAR(10) DEFAULT 'off',
    difficulty_pref VARCHAR(20) DEFAULT 'beginner',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE test_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    net_wpm INT NOT NULL,
    raw_wpm INT NOT NULL,
    accuracy INT NOT NULL,
    difficulty_mode VARCHAR(20) DEFAULT 'beginner',
    challenge_date DATE DEFAULT NULL,
    test_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE user_key_faults (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    missed_key VARCHAR(5) NOT NULL,
    error_count INT DEFAULT 0,
    UNIQUE KEY unique_user_key (user_id, missed_key),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Step 3 — Configure Environment Variables

Create a `.env` file in the project root (`C:\xampp\htdocs\TypeForge\.env`):

```env
DB_HOST="localhost"
DB_USER="root"
DB_PASS=""
DB_NAME="typeforge_db"
BREVO_API_KEY="your-brevo-api-key-here"
```

> [!IMPORTANT]
> Replace `your-brevo-api-key-here` with your actual Brevo API key from [https://app.brevo.com/settings/keys/api](https://app.brevo.com/settings/keys/api).

> [!WARNING]
> The `.env` file is already included in `.gitignore`. **Never** commit this file to version control.

### Step 4 — Launch the Application

1. Make sure Apache and MySQL are running in XAMPP
2. Open your browser and navigate to:

```
http://localhost/TypeForge/
```

3. Register an account, verify your email, and start typing! ⌨️

---

## 📁 Project Structure

```
TypeForge/
├── .env                  # Environment variables (DB credentials, API keys)
├── .gitignore            # Git ignore rules
├── db_connect.php        # Database connection (reads from .env)
├── index.php             # Main typing test page
├── script.js             # Core typing engine & game logic
├── style.css             # Global dark theme styles
├── login.php             # User login
├── register.php          # User registration + email OTP
├── verify.php            # Email verification (6-digit code)
├── forgot_password.php   # Password reset request
├── reset_password.php    # Password reset form (token-based)
├── logout.php            # Session logout
├── profile.php           # User dashboard & stats
├── leaderboard.php       # Global leaderboard (All-Time / Weekly / Daily)
├── heatmap.php           # Lifetime key fault analytics
├── train.php             # Targeted weak-key training mode
├── settings.php          # User preferences (sound, difficulty)
├── update_settings.php   # AJAX endpoint for saving settings
├── save_score.php        # AJAX endpoint for saving test results
├── run_migration.php     # One-time DB migration script
├── click.mp3             # Keypress sound — Click
├── pop.mp3               # Keypress sound — Pop
└── beep.mp3              # Keypress sound — Beep
```

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome! Feel free to open an issue or submit a pull request.

---

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

---

<p align="center">
  Built with ❤️ and ⌨️ by the TypeForge team
</p>
