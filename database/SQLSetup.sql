-- ===============================
-- VWLogin: Database Setup Script
-- ===============================

-- 1. Drop old database (if exists) and create a fresh one
DROP DATABASE IF EXISTS VWLogin;
CREATE DATABASE VWLogin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE VWLogin;

-- 2. Create Students table
CREATE TABLE Students (
    StudentNum INT AUTO_INCREMENT,
    StudentName VARCHAR(255) NOT NULL,
    PRIMARY KEY (StudentNum),
    UNIQUE KEY (StudentName)
);

-- 3. Create Classes table
CREATE TABLE Classes (
    ClassNum VARCHAR(6) NOT NULL,
    ClassName VARCHAR(255) NOT NULL,
    PRIMARY KEY (ClassNum)
);

-- 4. Create Enrollments table
CREATE TABLE Enrollments (
    StudentName VARCHAR(255) NOT NULL,
    ClassNum VARCHAR(6) NOT NULL,
    FOREIGN KEY (StudentName) REFERENCES Students(StudentName),
    FOREIGN KEY (ClassNum) REFERENCES Classes(ClassNum)
);

-- 5. Create Admins table with support for:
--    - Hashed passwords
--    - First-time password change
--    - 90-day expiration policy
--    - Expiration warnings
CREATE TABLE Admins (
    AdminNum INT AUTO_INCREMENT,
    AdminName VARCHAR(255) NOT NULL,
    AdminPass VARCHAR(255) NOT NULL,
    PasswordChangeRequired TINYINT(1) NOT NULL DEFAULT 1,
    LastPasswordChange DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (AdminNum),
    UNIQUE KEY (AdminName)
);

-- Insert default admin user with MD5-hashed password: "pass123"
-- PasswordChangeRequired = 1 (must change on first login)
INSERT INTO Admins (
    AdminName,
    AdminPass,
    PasswordChangeRequired,
    LastPasswordChange
) VALUES (
    'admin',
    '32250170a0dca92d53ec9624f336ca24', -- md5('pass123')
    1,
    NOW()
);

-- 6. Create LoginLogs table
CREATE TABLE LoginLogs (
    LoginNum INT AUTO_INCREMENT,
    StudentName VARCHAR(255) NULL,
    AdminName VARCHAR(255) NULL,
    ClassNum VARCHAR(6) NULL,
    ClassNumber VARCHAR(6) NULL,
    LoginTime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    LogoutTime TIMESTAMP NULL,
    SessionStatus ENUM('IN', 'OUT') NOT NULL DEFAULT 'IN',
    PRIMARY KEY (LoginNum),
    FOREIGN KEY (ClassNum) REFERENCES Classes(ClassNum) ON DELETE SET NULL
);

-- 7. Create trigger to copy ClassNum into ClassNumber
DELIMITER //

CREATE TRIGGER before_insert_loginlogs
BEFORE INSERT ON LoginLogs
FOR EACH ROW
BEGIN
  SET NEW.ClassNumber = NEW.ClassNum;
END;
//

DELIMITER ;

-- 8. Create events for:
--    - Auto-logout of stale sessions
--    - Retention cleanup for logs older than 90 days

SET GLOBAL event_scheduler = ON;

DROP EVENT IF EXISTS auto_logout_event;
DROP EVENT IF EXISTS retention_cleanup_event;

CREATE EVENT auto_logout_event
ON SCHEDULE EVERY 1 DAY
STARTS '2025-03-21 22:00:00'
DO
  UPDATE VWLogin.LoginLogs
  SET LogoutTime = NOW(),
      SessionStatus = 'OUT'
  WHERE SessionStatus = 'IN';

CREATE EVENT retention_cleanup_event
ON SCHEDULE EVERY 1 DAY
STARTS '2025-03-21 22:05:00'
DO
  DELETE FROM VWLogin.LoginLogs
  WHERE LoginTime < NOW() - INTERVAL 90 DAY;

-- Optional indexes for performance
CREATE INDEX idx_LoginLogs_Student ON LoginLogs (StudentName, LoginTime);
CREATE INDEX idx_LoginLogs_Admin ON LoginLogs (AdminName, LoginTime);
