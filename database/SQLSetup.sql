-- 1. Drop old database (if exists) and create a fresh one
DROP DATABASE IF EXISTS VWLogin;

CREATE DATABASE VWLogin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE VWLogin;

-- 2. Create the Students table
CREATE TABLE Students (
    StudentNum INT AUTO_INCREMENT,
    StudentName VARCHAR(255) NOT NULL,
    PRIMARY KEY (StudentNum),
    UNIQUE KEY (StudentName)
);

-- 3. Create the Classes table
CREATE TABLE Classes (
    ClassNum VARCHAR(6) NOT NULL,
    ClassName VARCHAR(255) NOT NULL,
    PRIMARY KEY (ClassNum)
);

-- 4. Create the Enrollments table
CREATE TABLE Enrollments (
    StudentName VARCHAR(255) NOT NULL,
    ClassNum VARCHAR(6) NOT NULL,
    FOREIGN KEY (StudentName) REFERENCES Students(StudentName),
    FOREIGN KEY (ClassNum) REFERENCES Classes(ClassNum)
);

-- 5. Create the Admins table
CREATE TABLE Admins (
    AdminNum INT AUTO_INCREMENT,
    AdminName VARCHAR(255) NOT NULL,
    AdminPass VARCHAR(255) NOT NULL,
    PRIMARY KEY (AdminNum),
    UNIQUE KEY (AdminName)
);

-- 6. Create the LoginLogs table with single-row session design
CREATE TABLE LoginLogs (
    LoginNum INT AUTO_INCREMENT,
    StudentName VARCHAR(255) NULL,
    AdminName VARCHAR(255) NULL,
    ClassNum VARCHAR(6) NULL, 
    LoginTime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    LogoutTime TIMESTAMP NULL,
    SessionStatus ENUM('IN','OUT') NOT NULL DEFAULT 'IN',
    PRIMARY KEY (LoginNum),
    FOREIGN KEY (ClassNum) REFERENCES Classes(ClassNum)
);

-- 7. Create events to clean up LoginLogs table with autoLogout and retention policy
SET GLOBAL event_scheduler = ON;

DROP EVENT IF EXISTS auto_logout_event;
DROP EVENT IF EXISTS retention_cleanup_event;

CREATE EVENT auto_logout_event 
ON SCHEDULE EVERY 1 DAY 
STARTS '2025-03-21 22:00:00'
DO
  UPDATE VWLogin.LoginLogs
  SET LogoutTime = NOW(), SessionStatus = 'OUT'
  WHERE SessionStatus = 'IN';

CREATE EVENT retention_cleanup_event 
ON SCHEDULE EVERY 1 DAY 
STARTS '2025-03-21 22:05:00'
DO
  DELETE FROM VWLogin.LoginLogs
  WHERE LoginTime < NOW() - INTERVAL 90 DAY;

-- Optional: Create indexes on LoginLogs to speed up queries
CREATE INDEX idx_LoginLogs_Student ON LoginLogs(StudentName, LoginTime);
CREATE INDEX idx_LoginLogs_Admin   ON LoginLogs(AdminName, LoginTime);
