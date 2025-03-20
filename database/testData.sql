USE VWLogin;

-- 1. Insert Students (6 students)
INSERT INTO
  Students (StudentName)
VALUES
  ('Smith'),
  ('Johnson'),
  ('Williams'),
  ('Brown'),
  ('Davis'),
  ('Miller');

-- 2. Insert Classes (5 classes)
INSERT INTO
  Classes (ClassNum, ClassName)
VALUES
  (101, 'Cybersecurity 101'),
  (102, 'Network Security'),
  (103, 'Digital Forensics'),
  (201, 'Cybersecurity 201'),
  (202, 'Advanced Cybersecurity');

-- 3. Insert Enrollments
-- Each student is enrolled in one or more classes
INSERT INTO
  Enrollments (StudentName, ClassNum)
VALUES
  ('Smith', 101),
  ('Smith', 201),
  ('Johnson', 102),
  ('Johnson', 103),
  ('Williams', 101),
  ('Williams', 102),
  ('Brown', 103),
  ('Brown', 202),
  ('Davis', 201),
  ('Miller', 202);

-- 4. Insert LoginLogs entries (12 entries)
-- Using the new single-row session design with columns: LoginNum, StudentName, AdminName, ClassNum, LoginTime, LogoutTime, SessionStatus
-- Smith: Two sessions for Cybersecurity 101 (one closed, one open)
INSERT INTO
  LoginLogs (
    StudentName,
    ClassNum,
    LoginTime,
    LogoutTime,
    SessionStatus
  )
VALUES
  (
    'Smith',
    101,
    '2025-03-25 08:00:00',
    '2025-03-25 08:30:00',
    'OUT'
  );

INSERT INTO
  LoginLogs (
    StudentName,
    ClassNum,
    LoginTime,
    LogoutTime,
    SessionStatus
  )
VALUES
  ('Smith', 101, '2025-03-25 09:00:00', NULL, 'IN');

-- Smith: One session for Cybersecurity 201 (open)
INSERT INTO
  LoginLogs (
    StudentName,
    ClassNum,
    LoginTime,
    LogoutTime,
    SessionStatus
  )
VALUES
  ('Smith', 201, '2025-03-25 09:15:00', NULL, 'IN');

-- Johnson: Two sessions for Network Security (one closed, one open)
INSERT INTO
  LoginLogs (
    StudentName,
    ClassNum,
    LoginTime,
    LogoutTime,
    SessionStatus
  )
VALUES
  (
    'Johnson',
    102,
    '2025-03-25 08:15:00',
    '2025-03-25 08:45:00',
    'OUT'
  );

INSERT INTO
  LoginLogs (
    StudentName,
    ClassNum,
    LoginTime,
    LogoutTime,
    SessionStatus
  )
VALUES
  ('Johnson', 102, '2025-03-25 09:05:00', NULL, 'IN');

-- Williams: Two sessions for Cybersecurity 101 (both open)
INSERT INTO
  LoginLogs (
    StudentName,
    ClassNum,
    LoginTime,
    LogoutTime,
    SessionStatus
  )
VALUES
  (
    'Williams',
    101,
    '2025-03-25 09:20:00',
    NULL,
    'IN'
  );

INSERT INTO
  LoginLogs (
    StudentName,
    ClassNum,
    LoginTime,
    LogoutTime,
    SessionStatus
  )
VALUES
  (
    'Williams',
    101,
    '2025-03-25 09:30:00',
    NULL,
    'IN'
  );

-- Brown: One session for Digital Forensics (closed) and one for Advanced Cybersecurity (open)
INSERT INTO
  LoginLogs (
    StudentName,
    ClassNum,
    LoginTime,
    LogoutTime,
    SessionStatus
  )
VALUES
  (
    'Brown',
    103,
    '2025-03-25 08:50:00',
    '2025-03-25 09:10:00',
    'OUT'
  );

INSERT INTO
  LoginLogs (
    StudentName,
    ClassNum,
    LoginTime,
    LogoutTime,
    SessionStatus
  )
VALUES
  ('Brown', 202, '2025-03-25 09:40:00', NULL, 'IN');

-- Davis: One session for Cybersecurity 201 (open)
INSERT INTO
  LoginLogs (
    StudentName,
    ClassNum,
    LoginTime,
    LogoutTime,
    SessionStatus
  )
VALUES
  ('Davis', 201, '2025-03-25 09:55:00', NULL, 'IN');

-- Miller: Two sessions for Advanced Cybersecurity (one closed, one open)
INSERT INTO
  LoginLogs (
    StudentName,
    ClassNum,
    LoginTime,
    LogoutTime,
    SessionStatus
  )
VALUES
  (
    'Miller',
    202,
    '2025-03-25 08:30:00',
    '2025-03-25 09:00:00',
    'OUT'
  );

INSERT INTO
  LoginLogs (
    StudentName,
    ClassNum,
    LoginTime,
    LogoutTime,
    SessionStatus
  )
VALUES
  ('Miller', 202, '2025-03-25 09:10:00', NULL, 'IN');