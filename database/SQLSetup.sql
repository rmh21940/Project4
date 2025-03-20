-- 1. Drop old database (if exists) and create a fresh one
DROP DATABASE IF EXISTS VWLogin;

CREATE DATABASE VWLogin CHARACTER
SET
    utf8mb4 COLLATE utf8mb4_unicode_ci;

USE VWLogin;

-- 2. Create the Students table
CREATE TABLE
    Students (
        StudentNum INT AUTO_INCREMENT,
        StudentName VARCHAR(255) NOT NULL,
        PRIMARY KEY (StudentNum),
        UNIQUE KEY (StudentName)
    );

-- 3. Create the Classes table
CREATE TABLE
    Classes (
        ClassNum INT NOT NULL,
        ClassName VARCHAR(255) NOT NULL,
        PRIMARY KEY (ClassNum)
    );

-- 4. Create the Enrollments table
CREATE TABLE
    Enrollments (
        StudentName VARCHAR(255) NOT NULL,
        ClassNum INT NOT NULL,
        FOREIGN KEY (StudentName) REFERENCES Students (StudentName),
        FOREIGN KEY (ClassNum) REFERENCES Classes (ClassNum)
    );

-- 5. Create the Admins table
CREATE TABLE
    Admins (
        AdminNum INT AUTO_INCREMENT,
        AdminName VARCHAR(255) NOT NULL,
        AdminPass VARCHAR(255) NOT NULL,
        PRIMARY KEY (AdminNum),
        UNIQUE KEY (AdminName)
    );

-- 6. Create the LoginLogs table with single-row session design
CREATE TABLE
    LoginLogs (
        LoginNum INT AUTO_INCREMENT,
        StudentName VARCHAR(255) NULL,
        AdminName VARCHAR(255) NULL,
        ClassNum INT NULL,
        LoginTime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        LogoutTime TIMESTAMP NULL,
        SessionStatus ENUM ('IN', 'OUT') NOT NULL DEFAULT 'IN',
        PRIMARY KEY (LoginNum),
        FOREIGN KEY (ClassNum) REFERENCES Classes (ClassNum)
    );

-- Optional: Create indexes on LoginLogs to speed up queries
CREATE INDEX idx_LoginLogs_Student ON LoginLogs (StudentName, LoginTime);

CREATE INDEX idx_LoginLogs_Admin ON LoginLogs (AdminName, LoginTime);