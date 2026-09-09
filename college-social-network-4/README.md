# Campus Connect

A PHP + MySQL social network connecting students, faculty, recruiters,
and placement staff on campuses across Fiji.

## Setup

1. Create the database and tables:
   ```
   mysql -u root -p < schema.sql
   ```
2. Edit `config/database.php` with your MySQL host/user/password (or
   set `DB_HOST`/`DB_NAME`/`DB_USER`/`DB_PASS` via a `.env` file — see
   `.env.example`).
3. Point your web server (Apache/XAMPP/Laragon) document root at this
   folder, or run PHP's built-in server for local testing:
   ```
   php -S localhost:8000
   ```
4. Visit `http://localhost:8000/` for the public landing page, or
   `http://localhost:8000/register.php` to create your first account
   directly.

## Design

- Brand palette (`assets/css/tokens.css`) takes cues from Fiji
  National University's green & gold, with a Pacific-ocean teal
  accent — an original identity, not FNU's actual branding.
- Typography: Nunito (headings) + DM Sans (body).
- Rounded, "clay"-style cards and shadows throughout, in the spirit
  of the playful reference used to design the UI.
- `assets/img/logo-mark.svg` is the logo mark (also the favicon);
  `includes/icons.php` holds the hand-inlined SVG icon set used
  across the site (no emoji-as-icons, no JS bundler required).

## What's built

- **Landing page** (`index.php`): public marketing homepage —
  redirects straight to the feed if already logged in.
- **Auth**: signup with email (required) + phone (optional), OTP
  verification on first sign-in, login with either identifier.
- **Profiles**: role-specific profile form shown right after signup;
  the account is logged in as soon as the profile is completed.
- **Feed**: general posts, job updates, and announcements, published
  immediately, with likes, shares (reposts to your own feed), and
  comments on each post.
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
