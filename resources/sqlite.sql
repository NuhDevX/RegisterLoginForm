-- Create users table
CREATE TABLE IF NOT EXISTS users (
    username TEXT PRIMARY KEY,
    password TEXT NOT NULL,
    registered_at INTEGER NOT NULL
);

-- Insert user
INSERT INTO users (username, password, registered_at) VALUES (:username, :password, :registered_at);

-- Select user
SELECT * FROM users WHERE username = :username;

-- Select all users
SELECT * FROM users;

-- Delete user
DELETE FROM users WHERE username = :username;
