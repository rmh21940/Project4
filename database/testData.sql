USE VWLogin;

-- 1. Insert Students (6 students)
INSERT INTO Students (StudentName) VALUES 
  ('Smith'),
  ('Johnson'),
  ('Williams'),
  ('Brown'),
  ('Davis'),
  ('Miller');

-- 2. Insert Classes (20 classes in XXX999 format)
INSERT INTO Classes (ClassNum, ClassName) VALUES 
  ('CSC101', 'Introduction to Computer Science'),
  ('CSC102', 'Data Structures'),
  ('CSC201', 'Algorithms'),
  ('CSC202', 'Computer Architecture'),
  ('CSC301', 'Operating Systems'),
  ('CSC302', 'Database Systems'),
  ('CSC303', 'Networks'),
  ('CSC304', 'Software Engineering'),
  ('MAT101', 'Calculus I'),
  ('MAT102', 'Calculus II'),
  ('MAT201', 'Linear Algebra'),
  ('PHY101', 'Physics I'),
  ('PHY102', 'Physics II'),
  ('ENG101', 'English Literature'),
  ('ENG102', 'Technical Writing'),
  ('BIO101', 'Biology I'),
  ('BIO102', 'Biology II'),
  ('CHE101', 'Chemistry I'),
  ('CHE102', 'Chemistry II'),
  ('HIS101', 'World History');

-- 3. Insert Enrollments (sample enrollments)
INSERT INTO Enrollments (StudentName, ClassNum) VALUES 
  ('Smith', 'CSC101'),
  ('Smith', 'CSC201'),
  ('Johnson', 'CSC102'),
  ('Johnson', 'CSC303'),
  ('Williams', 'CSC101'),
  ('Williams', 'CSC102'),
  ('Brown', 'ENG101'),
  ('Brown', 'ENG102'),
  ('Davis', 'CSC201'),
  ('Miller', 'CSC302');

-- 4. Insert LoginLogs entries (12 entries using the single-row session design)

-- Smith: Two sessions for CSC101 (one closed, one open)
INSERT INTO LoginLogs (StudentName, ClassNum, ClassNumber, LoginTime, LogoutTime, SessionStatus)
VALUES ('Smith', 'CSC101', 'CSC101', '2025-03-25 08:00:00', '2025-03-25 08:30:00', 'OUT');

INSERT INTO LoginLogs (StudentName, ClassNum, ClassNumber, LoginTime, LogoutTime, SessionStatus)
VALUES ('Smith', 'CSC101', 'CSC101', '2025-03-25 09:00:00', NULL, 'IN');

-- Smith: One session for CSC201 (open)
INSERT INTO LoginLogs (StudentName, ClassNum, ClassNumber, LoginTime, LogoutTime, SessionStatus)
VALUES ('Smith', 'CSC201', 'CSC201', '2025-03-25 09:15:00', NULL, 'IN');

-- Johnson: Two sessions for CSC102 (one closed, one open)
INSERT INTO LoginLogs (StudentName, ClassNum, ClassNumber, LoginTime, LogoutTime, SessionStatus)
VALUES ('Johnson', 'CSC102', 'CSC102', '2025-03-25 08:15:00', '2025-03-25 08:45:00', 'OUT');

INSERT INTO LoginLogs (StudentName, ClassNum, ClassNumber, LoginTime, LogoutTime, SessionStatus)
VALUES ('Johnson', 'CSC102', 'CSC102', '2025-03-25 09:05:00', NULL, 'IN');

-- Williams: Two sessions for CSC101 (both open)
INSERT INTO LoginLogs (StudentName, ClassNum, ClassNumber, LoginTime, LogoutTime, SessionStatus)
VALUES ('Williams', 'CSC101', 'CSC101', '2025-03-25 09:20:00', NULL, 'IN');

INSERT INTO LoginLogs (StudentName, ClassNum, ClassNumber, LoginTime, LogoutTime, SessionStatus)
VALUES ('Williams', 'CSC101', 'CSC101', '2025-03-25 09:30:00', NULL, 'IN');

-- Brown: One session for ENG101 (closed) and one for ENG102 (open)
INSERT INTO LoginLogs (StudentName, ClassNum, ClassNumber, LoginTime, LogoutTime, SessionStatus)
VALUES ('Brown', 'ENG101', 'ENG101', '2025-03-25 08:50:00', '2025-03-25 09:10:00', 'OUT');

INSERT INTO LoginLogs (StudentName, ClassNum, ClassNumber, LoginTime, LogoutTime, SessionStatus)
VALUES ('Brown', 'ENG102', 'ENG102', '2025-03-25 09:40:00', NULL, 'IN');

-- Davis: One session for CSC201 (open)
INSERT INTO LoginLogs (StudentName, ClassNum, ClassNumber, LoginTime, LogoutTime, SessionStatus)
VALUES ('Davis', 'CSC201', 'CSC201', '2025-03-25 09:55:00', NULL, 'IN');

-- Miller: Two sessions for CSC302 (one closed, one open)
INSERT INTO LoginLogs (StudentName, ClassNum, ClassNumber, LoginTime, LogoutTime, SessionStatus)
VALUES ('Miller', 'CSC302', 'CSC302', '2025-03-25 08:30:00', '2025-03-25 09:00:00', 'OUT');

INSERT INTO LoginLogs (StudentName, ClassNum, ClassNumber, LoginTime, LogoutTime, SessionStatus)
VALUES ('Miller', 'CSC302', 'CSC302', '2025-03-25 09:10:00', NULL, 'IN');

-- 5. Insert old student log entries to test retention script

-- OldStudent1: "OUT" session (should be deleted by retention event)
INSERT INTO LoginLogs (StudentName, ClassNum, ClassNumber, LoginTime, LogoutTime, SessionStatus)
VALUES ('OldStudent1', 'CSC101', 'CSC101', DATE_SUB(NOW(), INTERVAL 91 DAY), DATE_SUB(NOW(), INTERVAL 90 DAY), 'OUT');

-- OldStudent2: "IN" session (should also be deleted)
INSERT INTO LoginLogs (StudentName, ClassNum, ClassNumber, LoginTime, LogoutTime, SessionStatus)
VALUES ('OldStudent2', 'CSC102', 'CSC102', DATE_SUB(NOW(), INTERVAL 91 DAY), NULL, 'IN');



