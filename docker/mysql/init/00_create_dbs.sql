-- Create the second database (hdts_monitor is already created via MYSQL_DATABASE env var).
-- The MYSQL_USER already has grants on hdts_monitor from the entrypoint script.
-- We only need to create hdtc_users and grant access.

CREATE DATABASE IF NOT EXISTS `hdtc_users`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;

-- Grant the app user (created by Docker entrypoint via MYSQL_USER) access to hdtc_users.
-- We use a wildcard approach since the username is set via env var.
GRANT ALL PRIVILEGES ON `hdtc_users`.* TO 'hdts_user'@'%';

FLUSH PRIVILEGES;
