# College Social Networking Web Project

A PHP + MySQL platform connecting students, faculty, recruiters, and
placement staff — with admin-moderated registrations and posts.

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

## Creating the first Admin account

The signup form only offers student/faculty/recruiter/staff — admin
accounts are created directly in the database to avoid anyone
self-registering as admin. After registering a normal account and
completing its profile, promote it manually:

```sql
UPDATE users
SET role = 'admin', status = 'approved',
    email_verified_at = NOW(), profile_completed = TRUE
WHERE email = 'your-admin-email@example.com';
```

Then log in — you'll see the "Admin Panel" link in the top nav.

## What's built

- **Auth**: signup with email (required) + phone (optional), OTP
  verification on first sign-in, login with either identifier.
- **Profiles**: role-specific profile form shown right after signup.
- **Admin approval**: new accounts stay `pending` until an admin
  approves them (`admin/users.php`).
- **Feed**: general posts, job updates, and announcements — all
  admin-moderated before appearing (`admin/posts.php`). Comments on
  each post.
- **Events**: staff/admin post events; admin-moderated
  (`admin/events.php`).
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
