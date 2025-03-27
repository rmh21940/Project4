Summary of Enhancements and Fixes: Cybersecurity Lab Login System

1. Password Expiration & Enforcement
    - Implemented 90-day password expiration policy for admin accounts.
    - First login now enforces password change via PasswordChangeRequired flag.
    - Password expiration check compares LastPasswordChange with current date.
    - Password expiration triggers a prompt to update credentials before login is allowed.

2. Expiration Warning Notification
    - If a password is within 7 days of expiration, a warning is shown on login.
    - Admin is given the option to:
        - Change password now (recommended).
        - Continue to dashboard without changing password via explicit button click (not automatic redirect).

3. Dynamic Password Reset UI
    - Password change form is dynamically shown via JavaScript (showPasswordChangeForm()).
    - Password fields require:
        - Matching values.
        - Non-empty input.
        - Errors are displayed inline.
    - Password fields support “Show Password” checkbox with toggle logic.

4. Session Validation & Logging
    - Prevents multiple concurrent logins for the same admin:
        - Checks LoginLogs table for open sessions (SessionStatus = 'IN').
        - Offers admin the option to:
            - Force logout old session and re-login.
            - Continue to dashboard (manual override).
    - Password reset now logs a new session in LoginLogs on success.

5. AdminLogin Backend Overhaul (adminLogin.php)
    - Rewritten to:
        - Validate credentials.
        - Enforce expiration and first-login reset logic.
        - Support expiration warning flow.
        - Added return values:
            - passwordExpired
            - passwordExpiringSoon
            - daysLeft
    - Uses session tracking: $_SESSION['admin_logged_in'], $_SESSION['adminName'], and $_SESSION['adminLoginNum'].

6. Password Change Backend (changeAdminPassword.php)
    - Securely updates password using MD5 hash (same as login logic).
    - Resets:
        - PasswordChangeRequired = 0
        - LastPasswordChange = NOW()
    - Creates new login session in LoginLogs.

7. UI/UX Enhancements
    - Restructured admin.html to include:
        - Styled login form.
        - Conditionally visible password reset form.
        - Inline error/status messages.
        - “Return to Homepage” button placed inside .content container.
    - Used existing styles.css classes and added new ones where needed:
        - .form-box, .form-row, .warning-text, .error-text, .btn-margin-top, etc.
    - Ensured that label/input fields are aligned on one line for both New Password and Confirm Password.


8. Recommended Updates for RTM & QA Testing
    - RTM Updates:
        - Document new password expiration policy and enforcement logic.
        - Document admin session tracking and single-session enforcement.
        - Specify warning behavior and password change form UI.
        - Include testable conditions for:
            - Expired password
            - Password expiring soon
            - First-time login
    - QA Checklist:
        - Verify password change is required on first login.
        - Simulate login with password set 91+ days ago → force change.
        - Simulate login with password set 85+ days ago → show warning.
        - Ensure both password inputs must match.
        - Test “Show Password” functionality.
        - Verify admin session is logged correctly after login/reset.
        - Test force logout and re-login behavior.
        - Validate continue-to-dashboard option works when warning shown.
        - Ensure proper redirection and message handling on login error.

9. SQL script for testing admin password retention
    - Simulate password expiration by setting LastPasswordChange to 91 days ago
            UPDATE Admins
            SET LastPasswordChange = NOW() - INTERVAL 91 DAY
            WHERE AdminName = 'admin';
    - You can modify the number of days in the INTERVAL to test other conditions (e.g., set it to 85 to trigger the 7-day warning):
        - Simulate nearing expiration (e.g., 85 days ago)
            UPDATE Admins
            SET LastPasswordChange = NOW() - INTERVAL 85 DAY
            WHERE AdminName = 'admin';
    - These are safe to run multiple times for testing login behavior and password reset logic.


