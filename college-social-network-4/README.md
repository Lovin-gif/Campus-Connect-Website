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
  verification on first sign-in, login with either identifier,
  password reset via emailed (stubbed) link (`forgot_password.php`,
  `reset_password.php`).
- **Profiles** (`profile.php`, `edit_profile.php`): role-specific
  details, a profile photo, and a resume upload for students. Every
  post/comment author name links to their profile.
- **Follow system**: one-way follows with follower/following counts
  on each profile, and a Following tab on the feed alongside the
  global Everyone feed.
- **Feed**: general posts, job updates, and announcements, published
  immediately, with likes, shares (reposts to your own feed), and
  comments — all editable and deletable by their author.
- **Events**: staff/admin post events, published immediately, with
  Going/Interested RSVPs and attendee counts.
- **Messaging**: direct-message chat between any two approved users
  (`messages.php`), polling-based (refresh to see new messages).
- **Search** (`search.php`): people (by name/programme/department/
  company), posts, and events.
- **Notifications** (`notifications.php`): in-app notifications for
  likes, comments, shares, new followers, and new messages, with an
  unread-count badge in the nav.
- **Moderation & safety**: report a post/comment/user (`report.php`)
  into an admin-only review queue (`admin_reports.php`) that can
  delete the content or suspend the account; block a user
  (`profile.php`) to stop seeing their content and hide messaging in
  both directions.
- **Rate limiting**: login lockout after repeated failures, capped
  OTP guesses, and a cap on how fast one account can post.
- **CSRF protection** on every state-changing form.
- **Legal pages**: Terms, Privacy, and Community Guidelines
  (`terms.php`, `privacy.php`, `guidelines.php`), linked from
  registration and the site footer.

## Not yet built / good next steps

- Real email/SMS sending for OTPs and password resets (currently
  logged via `error_log` — swap in PHPMailer / Twilio in
  `includes/functions.php`).
- Real-time chat (WebSockets) instead of refresh-based.
- Groups/clubs/department spaces beyond the single global feed.
