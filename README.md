# Project4

# VWCC Cybersecurity Lab Login System

A web-based attendance and session tracking system developed for the **Virginia Western Community College Cybersecurity Lab** by **Team 5 Solutions**.  
This system streamlines login/logout workflows for students and admins while providing real-time monitoring, class enrollment management, and secure session handling.

---

## Key Features

### Student Interface
- Simple last-name login with class selection
- Class enrollment verification before login
- Secure session tracking with timestamps
- Automatic logout after inactivity

### Admin Dashboard
- Admin login with active session detection
- Add, delete, and manage student records
- Class creation and removal (with format validation)
- Enroll or unenroll students in specific classes
- View real-time session logs in the UI
- Download full login history as CSV

### Security & System Integrity
- Server-side session validation via PHP
- Prepared statements using PDO (SQL injection safe)
- Central login log table (`LoginLogs`)
- Admin session handling with conflict resolution
- Auto-logout of stale sessions via `autoLogout.php`

---

## Project Structure

```
.
├── database/
│   └── SQLSetup.sql
│
├── PHP/
│   ├── db.php
│   ├── adminLogin.php
│   ├── adminLogout.php
│   ├── adminDashboard.php
│   ├── classSelection.php
│   ├── studentLogin.php
│   ├── studentLogout.php
│   ├── studentStatus.php
│   ├── getStudentEnrollments.php
│   ├── getLogs.php
│   └── getAllClasses.php
│
├── HTML/
│   ├── images/
│       └── SQLSetup.sql
│   ├── index.html
│   ├── login.html
│   ├── logout.html
│   ├── admin.html
│   ├── admin_dashboard.html
│   ├── viewLogs.html
│   ├── styles.css
│   └── script.js
│
├── README.md
├── LICENSE
├── Bash Script Usage Instructions.md
├── Raspberry Pi Kiosk Setup Instructions.md
└── raspberry_pi_kiosk_auto_setup.sh
```

---

## Tech Stack

| Layer     | Tech Used                          |
|-----------|------------------------------------|
| Frontend  | HTML5, CSS3, Vanilla JavaScript    |
| Backend   | PHP 8+, PDO (prepared statements)  |
| Database  | MySQL                              |
| Platform  | Raspberry Pi (optional kiosk deployment) |

---

## Output Files & Reporting
- **CSV Report**: Admins can download session history via `adminDashboard`
- **Real-Time Logs**: `viewLogs.html` presents live data from `LoginLogs` table

---

## Security Considerations
- Password verification via `password_verify()` (admin login)
- Session conflict handling for admins
- PDO with prepared statements throughout
- For production: recommend migrating to hashed credentials for students

---

## Documentation Included
- `README.md` — You’re reading it
- `Bash Script Usage Instructions.md` — Manual setup script guide
- `Raspberry Pi Kiosk Setup Instructions.md` — Guide to kiosk setup
- `script.js` — Annotated JavaScript across all pages

---

## Credits
**Team 5 Solutions**  
Developed for the Virginia Western Community College Cybersecurity Program

**Team 5 Members**
- Robert Hendrix – Project Manager / Front-End Developer
- Patrick Russell – Front-End Developer
- Aiden Mehta – Back-End Developer
- Dylan Bender – Back-End Developer
- Casey McKittrick – Quality Assurance
- Marshall McCullough – Quality Assurance

---

## Status
- **Version**: 1.0.0  
- **Stage**: Production-ready







