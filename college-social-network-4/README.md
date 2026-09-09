# College Social Networking Web Project

A PHP + MySQL platform connecting students, faculty, recruiters, and
placement staff.

## Setup

1. Create the database and tables:
   ```
   mysql -u root -p < schema.sql
   ```
2. Edit `config/database.php` with your MySQL host/user/password.
3. Point your web server (Apache/XAMPP/Laragon) document root at this
   folder, or run PHP's built-in server for local testing:
   ```
   php -S localhost:8000
   ```
4. Visit `http://localhost:8000/register.php` to create your first
   account.

## What's built

- **Auth**: signup with email (required) + phone (optional), OTP
  verification on first sign-in, login with either identifier.
- **Profiles**: role-specific profile form shown right after signup;
  the account is logged in as soon as the profile is completed.
- **Feed**: general posts, job updates, and announcements, published
  immediately. Comments on each post.
- **Events**: staff/admin post events, published immediately.
- **Messaging**: simple direct-message chat between any two approved
  users (`messages.php`), polling-based (refresh to see new
  messages).

## Not yet built / good next steps

- Real email/SMS sending for OTPs (currently logged via `error_log`
  — swap in PHPMailer / Twilio in `includes/functions.php`).
- File uploads (resumes, profile photos).
- Real-time chat (WebSockets) instead of refresh-based.
- Search/filtering on the feed.
- Password reset flow.
