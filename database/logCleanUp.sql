-- Ensure the Event Scheduler is enabled:
SET
    GLOBAL event_scheduler = ON;

USE VWLogin;

-- Drop the events if they already exist:
DROP EVENT IF EXISTS auto_logout_event;

DROP EVENT IF EXISTS retention_cleanup_event;

-- Create the auto-logout event:
CREATE EVENT auto_logout_event ON SCHEDULE EVERY 1 DAY STARTS '2025-03-26 22:00:00' DO
UPDATE VWLogin.LoginLogs
SET
    LogoutTime = NOW (),
    SessionStatus = 'OUT'
WHERE
    SessionStatus = 'IN';

-- Create the retention cleanup event:
CREATE EVENT retention_cleanup_event ON SCHEDULE EVERY 1 DAY STARTS '2025-03-26 22:05:00' DO
DELETE FROM VWLogin.LoginLogs
WHERE
    LoginTime < NOW () - INTERVAL 90 DAY;