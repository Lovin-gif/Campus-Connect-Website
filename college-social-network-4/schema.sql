-- ============================================================
-- College Social Networking Web Project - Database Schema
-- Engine: MySQL 8.0+
-- ============================================================

CREATE DATABASE IF NOT EXISTS college_social_network;
USE college_social_network;

-- ------------------------------------------------------------
-- 1. USERS
-- One table for all account types; role determines permissions.
-- Registration is auto-approved; `status` remains for admins to
-- manually suspend or reject an account after the fact.
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
    status          ENUM('pending','approved','rejected','suspended') NOT NULL DEFAULT 'approved',
    avatar_path     VARCHAR(255) NULL,
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
    attempt_count   INT NOT NULL DEFAULT 0,
    expires_at      TIMESTAMP NOT NULL,
    consumed_at     TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- Password reset tokens. A reset link's token is hashed at rest,
-- same pattern as OTP codes.
-- ------------------------------------------------------------
CREATE TABLE password_resets (
    reset_id        INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    token_hash      VARCHAR(255) NOT NULL,
    expires_at      TIMESTAMP NOT NULL,
    used_at         TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- Login attempts, used to rate-limit brute-force login guesses.
-- We check "how many failures for this identifier in the last
-- N minutes" rather than keeping a running counter, so successes
-- naturally reset the window.
-- ------------------------------------------------------------
CREATE TABLE login_attempts (
    attempt_id      INT AUTO_INCREMENT PRIMARY KEY,
    identifier      VARCHAR(150) NOT NULL,
    succeeded       BOOLEAN NOT NULL DEFAULT FALSE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
-- Posts publish immediately; `status` remains so an admin can
-- reject/hide a post after the fact.
-- ------------------------------------------------------------
CREATE TABLE posts (
    post_id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    post_type       ENUM('general','job','announcement') NOT NULL DEFAULT 'general',
    title           VARCHAR(200),
    content         TEXT NOT NULL,
    status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved',
    reviewed_by     INT NULL,
    -- Set when this row is a share/repost of another post. Always
    -- points at the original (never at another share), so shares
    -- don't chain.
    shared_from_post_id INT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (shared_from_post_id) REFERENCES posts(post_id) ON DELETE SET NULL
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
    updated_at      TIMESTAMP NULL,
    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- 4c. FOLLOWS. One-way (Twitter/LinkedIn-style) connections that
-- power each profile's follower/following counts and the feed's
-- "Following" tab.
-- ------------------------------------------------------------
CREATE TABLE follows (
    follower_id     INT NOT NULL,
    followee_id     INT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (follower_id, followee_id),
    FOREIGN KEY (follower_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (followee_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CHECK (follower_id <> followee_id)
);

-- ------------------------------------------------------------
-- 4d. BLOCKS. A blocks B: B's content is hidden from A (and vice
-- versa for visibility purposes) and messaging between them stops.
-- ------------------------------------------------------------
CREATE TABLE blocks (
    blocker_id      INT NOT NULL,
    blocked_id      INT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (blocker_id, blocked_id),
    FOREIGN KEY (blocker_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CHECK (blocker_id <> blocked_id)
);

-- ------------------------------------------------------------
-- 4e. REPORTS. Flags a post, comment, or user for staff/admin
-- review. target_id is polymorphic (meaning depends on
-- target_type), so it can't carry its own foreign key.
-- ------------------------------------------------------------
CREATE TABLE reports (
    report_id       INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id     INT NOT NULL,
    target_type     ENUM('post','comment','user') NOT NULL,
    target_id       INT NOT NULL,
    reason          VARCHAR(255) NOT NULL,
    status          ENUM('open','reviewed','dismissed') NOT NULL DEFAULT 'open',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- 4b. LIKES on posts. One like per user per post (toggled on/off).
-- ------------------------------------------------------------
CREATE TABLE post_likes (
    post_id         INT NOT NULL,
    user_id         INT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (post_id, user_id),
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
-- Events publish immediately; `status` remains so an admin can
-- reject/hide an event after the fact.
-- ------------------------------------------------------------
CREATE TABLE events (
    event_id        INT AUTO_INCREMENT PRIMARY KEY,
    posted_by       INT NOT NULL,
    title           VARCHAR(200) NOT NULL,
    description     TEXT,
    event_date      DATETIME NOT NULL,
    location        VARCHAR(150),
    status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- 6b. EVENT RSVPs
-- ------------------------------------------------------------
CREATE TABLE event_rsvps (
    event_id        INT NOT NULL,
    user_id         INT NOT NULL,
    rsvp_status     ENUM('going','interested') NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (event_id, user_id),
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- 7. NOTIFICATIONS (system-generated, e.g. "your post was approved")
-- ------------------------------------------------------------
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    message         VARCHAR(255) NOT NULL,
    link            VARCHAR(255) NULL,
    is_read         BOOLEAN DEFAULT FALSE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- Indexes for common lookups
-- ------------------------------------------------------------
CREATE INDEX idx_posts_status ON posts(status);
CREATE INDEX idx_posts_shared_from ON posts(shared_from_post_id);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_messages_receiver ON messages(receiver_id, is_read);
CREATE INDEX idx_events_date ON events(event_date);
CREATE INDEX idx_follows_followee ON follows(followee_id);
CREATE INDEX idx_notifications_user ON notifications(user_id, is_read);
CREATE INDEX idx_login_attempts_identifier ON login_attempts(identifier, created_at);
CREATE INDEX idx_reports_status ON reports(status);
CREATE INDEX idx_password_resets_user ON password_resets(user_id);
