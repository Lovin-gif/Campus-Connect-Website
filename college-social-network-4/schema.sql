-- ============================================================
-- College Social Networking Web Project - Database Schema
-- Engine: MySQL 8.0+
-- ============================================================

CREATE DATABASE IF NOT EXISTS college_social_network;
USE college_social_network;

-- ------------------------------------------------------------
-- 1. USERS
-- One table for all account types; role determines permissions.
-- Registration starts as 'pending' until admin approves it.
-- ------------------------------------------------------------
CREATE TABLE users (
    user_id         INT AUTO_INCREMENT PRIMARY KEY,
    role            ENUM('student','faculty','recruiter','staff','admin') NOT NULL,
    full_name       VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    phone_number    VARCHAR(20) NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL,
    phone_verified_at TIMESTAMP NULL,
    profile_completed BOOLEAN NOT NULL DEFAULT FALSE,
    status          ENUM('pending','approved','rejected','suspended') NOT NULL DEFAULT 'pending',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_by     INT NULL,
    approved_at     TIMESTAMP NULL,
    FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- OTP verification codes, used for first-time sign-in via
-- email or phone. A code is generated, sent, and checked
-- against this table before the account is marked verified.
-- ------------------------------------------------------------
CREATE TABLE otp_verifications (
    otp_id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    channel         ENUM('email','phone') NOT NULL,
    code_hash       VARCHAR(255) NOT NULL,
    expires_at      TIMESTAMP NOT NULL,
    consumed_at     TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- 2. ROLE-SPECIFIC PROFILE TABLES
-- Kept separate from `users` since each role has different
-- registration fields. 1:1 relationship with users.
-- ------------------------------------------------------------
CREATE TABLE student_profiles (
    user_id         INT PRIMARY KEY,
    student_id_no   VARCHAR(30) UNIQUE,
    programme       VARCHAR(150),
    year_of_study   INT,
    bio             TEXT,
    resume_path     VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE faculty_profiles (
    user_id         INT PRIMARY KEY,
    staff_id_no     VARCHAR(30) UNIQUE,
    department      VARCHAR(150),
    title           VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE recruiter_profiles (
    user_id         INT PRIMARY KEY,
    company_name    VARCHAR(150),
    company_website VARCHAR(255),
    industry        VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE staff_profiles (
    user_id         INT PRIMARY KEY,
    office          VARCHAR(150),
    position_title  VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- 3. POSTS (general updates, job posts, announcements)
-- All posts go through admin moderation before going live.
-- ------------------------------------------------------------
CREATE TABLE posts (
    post_id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    post_type       ENUM('general','job','announcement') NOT NULL DEFAULT 'general',
    title           VARCHAR(200),
    content         TEXT NOT NULL,
    status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    reviewed_by     INT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Extra fields specific to job posts (kept out of `posts` to avoid nulls everywhere)
CREATE TABLE job_details (
    post_id         INT PRIMARY KEY,
    job_title       VARCHAR(150) NOT NULL,
    location        VARCHAR(150),
    deadline        DATE,
    apply_link      VARCHAR(255),
    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- 4. COMMENTS on posts
-- ------------------------------------------------------------
CREATE TABLE comments (
    comment_id      INT AUTO_INCREMENT PRIMARY KEY,
    post_id         INT NOT NULL,
    user_id         INT NOT NULL,
    content         TEXT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- 5. CHAT / MESSAGING (simple direct messages, poll-based)
-- ------------------------------------------------------------
CREATE TABLE messages (
    message_id      INT AUTO_INCREMENT PRIMARY KEY,
    sender_id       INT NOT NULL,
    receiver_id     INT NOT NULL,
    content         TEXT NOT NULL,
    is_read         BOOLEAN DEFAULT FALSE,
    sent_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- 6. EVENTS (posted by staff/placement officers)
-- ------------------------------------------------------------
CREATE TABLE events (
    event_id        INT AUTO_INCREMENT PRIMARY KEY,
    posted_by       INT NOT NULL,
    title           VARCHAR(200) NOT NULL,
    description     TEXT,
    event_date      DATETIME NOT NULL,
    location        VARCHAR(150),
    status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- 7. NOTIFICATIONS (system-generated, e.g. "your post was approved")
-- ------------------------------------------------------------
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    message         VARCHAR(255) NOT NULL,
    is_read         BOOLEAN DEFAULT FALSE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- Indexes for common lookups
-- ------------------------------------------------------------
CREATE INDEX idx_posts_status ON posts(status);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_messages_receiver ON messages(receiver_id, is_read);
CREATE INDEX idx_events_date ON events(event_date);
