# HustleHub — Complete Technical Study Guide
### ITECA3-12 | Munashe Tsikada | EDUV4881584 | Eduvos NQF Level 7

Read this the morning of your presentation. Every page, every important line, every likely question — covered.

---

## TABLE OF CONTENTS

1. [Architecture Overview](#1-architecture-overview)
2. [Database — All 7 Tables](#2-database--all-7-tables)
3. [How Sessions Work in PHP](#3-how-sessions-work-in-php)
4. [includes/auth.php — The Security Foundation](#4-includesauthphp--the-security-foundation)
5. [config/db.php — Database Connection](#5-configdbphp--database-connection)
6. [pages/register.php — Creating an Account](#6-pagesregisterphp--creating-an-account)
7. [pages/login.php — Logging In](#7-pagesloginphp--logging-in)
8. [pages/browse.php — The Marketplace](#8-pagesbrowsephp--the-marketplace)
9. [pages/service_detail.php — Booking Form](#9-pagesservice_detailphp--booking-form)
10. [pages/process_booking.php — Creating the Booking + PayFast](#10-pagesprocess_bookingphp--creating-the-booking--payfast)
11. [pages/payfast_notify.php — Payment Confirmation](#11-pagespayfast_notifyphp--payment-confirmation)
12. [pages/booking_confirm.php and booking_cancel.php](#12-pagesbooking_confirmphp-and-booking_cancelphp)
13. [pages/update_booking_status.php — AJAX Status Engine](#13-pagesupdate_booking_statusphp--ajax-status-engine)
14. [pages/client_dashboard.php — Client View](#14-pagesclient_dashboardphp--client-view)
15. [pages/worker_dashboard.php — Worker View](#15-pagesworker_dashboardphp--worker-view)
16. [pages/create_listing.php — Worker Creates a Service](#16-pagescreate_listingphp--worker-creates-a-service)
17. [pages/edit_listing.php and delete_listing.php](#17-pagesedit_listingphp-and-delete_listingphp)
18. [pages/raise_dispute.php — Raising a Dispute](#18-pagesraise_disputephp--raising-a-dispute)
19. [pages/leave_review.php — Leaving a Review](#19-pagesleave_reviewphp--leaving-a-review)
20. [admin/admin_header.php — Admin Auth Gate](#20-adminadmin_headerphp--admin-auth-gate)
21. [admin/index.php — Admin Dashboard](#21-adminindexphp--admin-dashboard)
22. [admin/listings.php — Approve/Reject Listings](#22-adminlistingsphp--approvereject-listings)
23. [admin/disputes.php — Resolve Disputes](#23-admindisputesphp--resolve-disputes)
24. [admin/users.php — Manage Users](#24-adminusersphp--manage-users)
25. [admin/audit_log.php — Audit Trail](#25-adminaudit_logphp--audit-trail)
26. [assets/js/app.js — All the JavaScript](#26-assetsjsappjs--all-the-javascript)
27. [includes/header.php and footer.php](#27-includesheaderphp-and-footerphp)
28. [KEY CONCEPTS — Most Likely Exam Questions](#28-key-concepts--most-likely-exam-questions)

---

## 1. Architecture Overview

HustleHub is a **C2C (Consumer-to-Consumer) service marketplace** for South Africa. Think of it like Airbnb, but instead of rooms, workers list services like cleaning, painting, gardening.

### Technology Stack

| Layer | Technology | Why |
|---|---|---|
| Server language | PHP 8.3 | Server-side logic, database access, session management |
| Database | MySQL 9.x | Relational data — users, bookings, payments all linked |
| DB access library | PDO (PHP Data Objects) | Safe, prepared statements to prevent SQL injection |
| Frontend CSS | Bootstrap 5.3.3 (CDN) | Responsive grid, pre-built UI components |
| Frontend JS | jQuery 3.7.1 (CDN) | AJAX calls, real-time DOM manipulation |
| Icons | Bootstrap Icons 1.11.3 (CDN) | Icon library for admin panel |
| Payment | PayFast Sandbox | South African payment gateway for escrow |
| Dev server | `php -S localhost:8080` | PHP's built-in dev server |

### Request Flow (what happens when someone visits a page)

```
Browser → PHP file
  → includes auth.php (session check, CSRF token, timeout)
  → config/db.php (open MySQL connection via PDO)
  → page logic (read/write database)
  → includes header.php (HTML head, Bootstrap, navbar)
  → page HTML (the actual content)
  → includes footer.php (closing HTML, scripts)
```

### Folder Structure

```
platform/
├── config/db.php          — Database credentials + PDO connection
├── includes/
│   ├── auth.php           — Sessions, CSRF, role guard, helper functions
│   ├── header.php         — Shared HTML header + Bootstrap navbar
│   └── footer.php         — Shared footer, Bootstrap JS, jQuery, app.js
├── pages/                 — All user-facing pages
├── admin/                 — Admin-only pages + admin_header.php guard
├── assets/
│   ├── css/style.css      — Custom styles for main site
│   ├── css/admin.css      — Custom styles for admin panel
│   └── js/app.js          — jQuery logic (filters, AJAX, star picker)
├── uploads/               — User-uploaded files (not in use)
└── database/
    ├── schema.sql         — Creates all tables
    └── seed.sql           — Sample test data
```

---

## 2. Database — All 7 Tables

The schema is in `database/schema.sql`. Run it once to create the database.

### Table 1: `users`

Stores every account — workers, clients, admins, moderators.

```sql
CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED     NOT NULL AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(120)     NOT NULL,
  email         VARCHAR(180)     NOT NULL UNIQUE,   -- UNIQUE: no two accounts share an email
  phone         VARCHAR(20)      DEFAULT NULL,
  password      VARCHAR(255)     NOT NULL,           -- bcrypt hash, never plain text
  role          ENUM('worker','client','admin','moderator') NOT NULL DEFAULT 'client',
  is_verified   TINYINT(1)       NOT NULL DEFAULT 0, -- 1 = OTP verified
  otp_code      VARCHAR(10)      DEFAULT NULL,
  otp_expires   DATETIME         DEFAULT NULL,
  avg_rating    DECIMAL(3,2)     NOT NULL DEFAULT 0.00,  -- recalculated on every new review
  created_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP
)
```

**Key points:**
- `ENUM` means only those exact values are accepted by MySQL — it's enforced at the DB level
- `UNIQUE` on email means the `INSERT` will throw an error if you try to register twice with the same email — we catch that in PHP
- `password` stores the result of `password_hash()` — it looks like `$2y$10$...` — 60 characters minimum, which is why `VARCHAR(255)` is used
- `avg_rating` is recalculated in PHP every time a new review is submitted (not stored as a running average in SQL)

### Table 2: `services`

Worker service listings. Not visible publicly until an admin approves them.

```sql
CREATE TABLE IF NOT EXISTS services (
  id              INT UNSIGNED     NOT NULL AUTO_INCREMENT PRIMARY KEY,
  worker_id       INT UNSIGNED     NOT NULL,          -- FK → users.id
  title           VARCHAR(160)     NOT NULL,
  description     TEXT             NOT NULL,
  category        ENUM('cleaning','gardening','painting','moving','repairs','other') NOT NULL,
  price           DECIMAL(10,2)    NOT NULL,           -- stored as e.g. 250.00
  duration_hours  TINYINT UNSIGNED NOT NULL DEFAULT 1,
  image_path      VARCHAR(255)     DEFAULT NULL,       -- relative path like assets/images/listings/abc.jpg
  approval_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  FOREIGN KEY (worker_id) REFERENCES users(id) ON DELETE CASCADE
)
```

**Key points:**
- `DECIMAL(10,2)` for money — never use FLOAT for currency because floating-point rounding causes incorrect values like `R249.999999`
- `approval_status` defaults to `'pending'` — new listings are invisible until admin acts
- `ON DELETE CASCADE` means if a worker's account is deleted, their listings are also deleted automatically

### Table 3: `bookings`

One record per booking. The status column tracks the lifecycle.

```sql
CREATE TABLE IF NOT EXISTS bookings (
  id           INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
  service_id   INT UNSIGNED  NOT NULL,    -- FK → services.id
  client_id    INT UNSIGNED  NOT NULL,    -- FK → users.id (who booked)
  worker_id    INT UNSIGNED  NOT NULL,    -- FK → users.id (who provides the service)
  booking_date DATE          NOT NULL,
  status       ENUM('pending','confirmed','in_progress','completed','disputed','cancelled')
               NOT NULL DEFAULT 'pending'
)
```

**The booking lifecycle:**
```
pending  → (PayFast payment received) → confirmed
confirmed → (worker presses "Start Job") → in_progress
in_progress → (client presses "Confirm Complete") → completed
[any active status] → (dispute raised) → disputed
[any active status] → (cancelled by client or worker) → cancelled
```

### Table 4: `transactions`

Tracks the money. One row per booking. This is the escrow record.

```sql
CREATE TABLE IF NOT EXISTS transactions (
  id             INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
  booking_id     INT UNSIGNED  NOT NULL UNIQUE,        -- one transaction per booking
  amount         DECIMAL(10,2) NOT NULL,
  escrow_status  ENUM('held','released','refunded') NOT NULL DEFAULT 'held',
  payfast_id     VARCHAR(100)  DEFAULT NULL,           -- PayFast's own transaction ID
  released_by    INT UNSIGNED  DEFAULT NULL,           -- which admin released it
  released_at    DATETIME      DEFAULT NULL
)
```

**Escrow logic:**
- When the booking is created: `escrow_status = 'held'` — money is logically locked
- When client confirms job complete: `escrow_status = 'released'` — worker gets paid
- When admin resolves a dispute in the client's favour: `escrow_status = 'refunded'` — client gets money back

Note: PayFast handles the actual bank transfer. Our database just tracks the state.

### Table 5: `reviews`

One rating per person per booking. Both client and worker can review each other.

```sql
CREATE TABLE IF NOT EXISTS reviews (
  id           INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
  booking_id   INT UNSIGNED  NOT NULL,
  reviewer_id  INT UNSIGNED  NOT NULL,    -- who wrote the review
  reviewee_id  INT UNSIGNED  NOT NULL,    -- who was reviewed
  rating       TINYINT UNSIGNED NOT NULL,
  comment      TEXT          DEFAULT NULL,
  CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5),   -- DB-level validation
  UNIQUE KEY unique_review (booking_id, reviewer_id)       -- prevents duplicate reviews
)
```

**Key point:** The `UNIQUE KEY` on `(booking_id, reviewer_id)` means one person can only review once per booking — enforced by MySQL, not just PHP.

### Table 6: `disputes`

Raised when something goes wrong. Admins resolve them.

```sql
CREATE TABLE IF NOT EXISTS disputes (
  id              INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
  booking_id      INT UNSIGNED  NOT NULL,
  raised_by       INT UNSIGNED  NOT NULL,    -- client or worker who raised it
  reason          TEXT          NOT NULL,
  status          ENUM('open','under_review','resolved') NOT NULL DEFAULT 'open',
  admin_id        INT UNSIGNED  DEFAULT NULL, -- admin who resolved it
  resolution_note TEXT          DEFAULT NULL,
  resolved_at     DATETIME      DEFAULT NULL
)
```

### Table 7: `audit_log`

Records every admin action. Used for accountability.

```sql
CREATE TABLE IF NOT EXISTS audit_log (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  admin_id    INT UNSIGNED NOT NULL,      -- which admin did this
  action      VARCHAR(60)  NOT NULL,      -- e.g. 'LISTING_APPROVED', 'DISPUTE_RELEASED'
  target_type VARCHAR(30),               -- e.g. 'service', 'dispute'
  target_id   INT UNSIGNED,              -- the ID of what was acted on
  notes       TEXT,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

### Foreign Key Relationships (Entity-Relationship)

```
users ──< services (worker_id)
users ──< bookings (client_id)
users ──< bookings (worker_id)
services ──< bookings (service_id)
bookings ──< transactions (booking_id)  [one-to-one via UNIQUE]
bookings ──< reviews (booking_id)
bookings ──< disputes (booking_id)
users ──< reviews (reviewer_id, reviewee_id)
users ──< disputes (raised_by, admin_id)
users ──< audit_log (admin_id)
```

---

## 3. How Sessions Work in PHP

Sessions are how PHP remembers who is logged in between page requests. HTTP is stateless — every request is a fresh connection — so sessions solve this.

**How it works step by step:**

1. `session_start()` is called at the top of auth.php — this must happen before any HTML output
2. PHP checks if the browser sent a `Cookie: PHPSESSID=abc123` header
3. If yes, PHP loads the corresponding session data from the server's temp folder
4. If no, PHP creates a new session file and sends back `Set-Cookie: PHPSESSID=newid`
5. The `$_SESSION` superglobal array is now available — it persists across requests for that browser

**What we store in the session:**

```php
$_SESSION['user_id']    = 5;             // the user's ID from the database
$_SESSION['user_name']  = 'Thabo Nkosi'; // their display name
$_SESSION['user_email'] = 'thabo@...';   // their email
$_SESSION['role']       = 'worker';      // 'worker', 'client', 'admin', or 'moderator'
$_SESSION['last_active'] = time();       // Unix timestamp, updated every page load
$_SESSION['csrf_token'] = 'abc123...';   // random token for form security
```

**Why we need the session timeout:**

Without it, if you leave your laptop open in a café, anyone who sits down can access your account. The 30-minute check logs you out automatically.

---

## 4. includes/auth.php — The Security Foundation

This file is included at the top of almost every page. It runs before anything else.

```php
<?php
session_start();
```
Starts or resumes the session. Must be the very first thing — even one space before `<?php` would cause a "headers already sent" error.

```php
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true));
}
```
**CSRF token generation:**
- `mt_rand()` — generates a random integer
- `uniqid(seed, true)` — generates a unique string based on time + the random integer; the `true` parameter adds extra entropy (more randomness)
- `md5(...)` — hashes it to a fixed 32-character hex string
- This token is generated once and stored in the session. It stays the same for the whole session.
- Every form includes this token as a hidden field. When the form is submitted, we check the posted token matches the session token. If someone tries to submit a form from another website, they won't have this token.

```php
if (isset($_SESSION['last_active']) && (time() - $_SESSION['last_active'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: /login.php?reason=timeout');
    exit;
}
$_SESSION['last_active'] = time();
```
**Session timeout:**
- `time()` returns the current Unix timestamp (seconds since 1 January 1970)
- `1800` seconds = 30 minutes
- If the difference between now and the last page load is more than 30 minutes, destroy the session and redirect to login
- Then update `last_active` to now so the clock resets on every page load

```php
function requireRole($role)
{
    if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
        header('Location: /login.php');
        exit;
    }
    if ($_SESSION['role'] !== $role) {
        die('Access denied.');
    }
}
```
**Role guard:**
- First check: are they logged in at all? If `user_id` or `role` is not set in the session, redirect to login
- Second check: does their role match what this page requires?
- Used like `requireRole('worker')` at the top of worker-only pages
- For pages that allow both client AND worker (like raise_dispute, leave_review), we do a manual inline check instead: `if ($_SESSION['role'] !== 'client' && $_SESSION['role'] !== 'worker')`

```php
function verifyCsrfToken()
{
    $token = $_POST['csrf_token'] ?? '';
    if ($_SESSION['csrf_token'] !== $token) {
        die('Invalid request. Please go back and try again.');
    }
}
```
**CSRF verification:**
- `$_POST['csrf_token'] ?? ''` — read the token from the submitted form; if it's missing, use empty string
- `!==` is strict comparison — type AND value must match
- Called at the top of every POST handler before doing anything with the data

```php
function e($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
```
**XSS protection:**
- Converts `<`, `>`, `"`, `'`, `&` into safe HTML entities like `&lt;`, `&gt;`
- This prevents a user from injecting `<script>alert('hacked')</script>` into a form and having it execute when displayed on the page
- `ENT_QUOTES` — also escapes single quotes (important for inline HTML attributes)
- Used everywhere: `<?= e($user['full_name']) ?>`

```php
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}
```
Simple check — just returns true or false. Used in headers and pages to decide whether to show Login/Register links or the user's name.

---

## 5. config/db.php — Database Connection

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'hustlehub');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log('DB connection failed: ' . $e->getMessage());
    http_response_code(500);
    die('Service unavailable. Please try again later.');
}
```

**PDO Options explained:**
- `ERRMODE_EXCEPTION` — if any SQL query fails, PHP throws a `PDOException` which we can catch with try/catch. Without this, errors would silently fail.
- `FETCH_ASSOC` — when we call `$stmt->fetch()`, we get an associative array like `['id' => 5, 'email' => 'thabo@...']` instead of having both index and key
- `EMULATE_PREPARES => false` — makes PDO use the database's own prepared statement handling. More secure and avoids type casting issues.
- `charset=utf8mb4` — supports full Unicode including emoji, not just ASCII

**Why PDO instead of writing SQL directly:**

Never do this: `$pdo->query("SELECT * FROM users WHERE email = '$email'")` — if `$email` contains `'; DROP TABLE users; --`, you've been SQL injected.

Always do this:
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();
```
The `?` is a placeholder. PDO sends the query and the data separately to MySQL. MySQL treats the user input as pure data, not executable SQL — so no injection possible.

---

## 6. pages/register.php — Creating an Account

**Flow:** Page loads → shows form → user submits → validate → check email isn't taken → hash password → insert to DB → create session → redirect

```php
if (isLoggedIn()) { header('Location: ../index.php'); exit; }
```
If already logged in, don't show the register form — redirect home.

```php
verifyCsrfToken();
```
First thing on every POST — verify the hidden form token matches the session token.

```php
$name     = trim($_POST['full_name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$role     = $_POST['role'] ?? 'client';
```
- `$_POST['key'] ?? ''` — the `??` is the null coalescing operator. If the key doesn't exist in `$_POST`, use the right-hand value instead of triggering an error.
- `trim()` removes whitespace from both ends — prevents "  John  " being stored

```php
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Please enter a valid email address.';
}
```
`filter_var()` is a built-in PHP function that checks if a string is a valid email format.

```php
elseif (!in_array($role, ['client','worker'])) {
    $error = 'Invalid role selected.';
}
```
Server-side validation. Even though the HTML form only shows client/worker radio buttons, we validate on the server because someone could send a POST request manually with `role=admin`. This prevents privilege escalation.

```php
$chk = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$chk->execute([$email]);
if ($chk->fetch()) {
    $error = 'This email address is already registered.';
}
```
Try to find a user with this email. If `fetch()` returns anything (not false), the email is taken.

```php
$hash = password_hash($password, PASSWORD_BCRYPT);
```
`password_hash()` uses the BCrypt algorithm. BCrypt:
- Automatically generates a random salt (so two users with the same password get different hashes)
- Is intentionally slow — it takes ~100ms to hash. This makes brute-force attacks impractical.
- The result includes the algorithm, cost factor, salt, and hash all in one string

```php
$ins = $pdo->prepare(
    "INSERT INTO users (full_name, email, phone, password, role, is_verified)
     VALUES (?, ?, ?, ?, ?, 1)"
);
$ins->execute([$name, $email, $phone, $hash, $role]);
$newId = (int)$pdo->lastInsertId();
```
- `lastInsertId()` returns the auto-increment ID of the row just inserted
- `is_verified` is set to `1` immediately — originally the system had OTP email verification; for academic purposes it's skipped and users are auto-verified

```php
$_SESSION['user_id']    = $newId;
$_SESSION['user_name']  = $name;
$_SESSION['role']       = $role;
$_SESSION['last_active'] = time();
header("Location: $redirect");
exit;
```
Log them in immediately after registering. `exit` is important after `header()` — without it, PHP continues executing the rest of the page even though the redirect header was sent.

---

## 7. pages/login.php — Logging In

```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
```
- Look up the user by email
- `password_verify($plaintext, $hash)` — checks if the password they typed matches the stored BCrypt hash. It handles the salt extraction automatically.
- If either step fails, we show the same error: "Invalid email or password." — we deliberately don't say "email not found" or "wrong password" separately, because that would tell attackers which accounts exist.

```php
if (in_array($user['role'], ['admin','moderator'])) {
    header('Location: ../admin/dashboard.php'); exit;
} elseif ($user['role'] === 'worker') {
    header('Location: worker_dashboard.php'); exit;
} else {
    header('Location: ' . ($redirect ?: '../index.php')); exit;
}
```
Role-based redirect after login. Admins go to the admin panel, workers go to their dashboard, clients go to browse or wherever they were trying to go.

---

## 8. pages/browse.php — The Marketplace

```php
$cat   = $_GET['cat'] ?? 'all';
$q     = trim($_GET['q'] ?? '');
$maxP  = (int)($_GET['max_price'] ?? 2000);
```
Query parameters from the URL (e.g. `browse.php?cat=cleaning&q=home`). The `(int)` cast forces `max_price` to be an integer — even if someone puts `max_price=DROP TABLE`, it becomes `0`.

```php
$sql    = "SELECT s.*, u.full_name, u.avg_rating FROM services s 
           JOIN users u ON s.worker_id = u.id 
           WHERE s.approval_status = 'approved'";
$params = [];

if ($cat !== 'all' && $cat !== '') {
    $sql .= " AND s.category = ?";
    $params[] = $cat;
}
if ($q !== '') {
    $sql .= " AND (s.title LIKE ? OR s.description LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
```
**Dynamic query building:**
- Start with the base query (only approved services)
- Add `AND` clauses conditionally depending on what filters are active
- Build the `$params` array in the same order as the `?` placeholders
- The `LIKE '%search%'` searches for the term anywhere in the column
- The `JOIN users` gets the worker's name and rating alongside the service data in one query

The actual client-side filtering (search box, price slider, category pills) also runs in JavaScript — the PHP filter is the server-side backup for direct URL access and SEO.

---

## 9. pages/service_detail.php — Booking Form

```php
$serviceId = (int)($_GET['id'] ?? 0);
if (!$serviceId) { header('Location: browse.php'); exit; }
```
Safely cast the ID. If it's missing or zero, redirect back.

```php
$stmt = $pdo->prepare("
    SELECT s.*, u.full_name, u.avg_rating, u.bio, u.profile_pic
    FROM services s
    JOIN users u ON s.worker_id = u.id
    WHERE s.id = ? AND s.approval_status = 'approved'
");
```
The `AND s.approval_status = 'approved'` means even if someone guesses a pending listing's ID in the URL, they can't see it. The fetch will return false and we redirect them away.

The booking form on this page POSTs to `process_booking.php`. It includes:
- A hidden `csrf_token` field
- A hidden `service_id` field
- A date picker for `booking_date`
- A textarea for `notes`

---

## 10. pages/process_booking.php — Creating the Booking + PayFast

This is the most complex page. It: validates the request, prevents duplicate bookings, writes to two tables atomically, then builds and submits a PayFast payment form.

```php
requireRole('client');
verifyCsrfToken();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: browse.php');
    exit;
}
```
Three guards upfront: must be a client, CSRF token must match, must be a POST request. Direct URL access gets bounced.

```php
if (!$bookingDate || $bookingDate < date('Y-m-d', strtotime('+1 day'))) {
    header('Location: service_detail.php?id=' . $serviceId . '&err=invalid_date');
    exit;
}
```
`date('Y-m-d', strtotime('+1 day'))` — tomorrow's date. Bookings must be at least one day in the future.

```php
$dup = $pdo->prepare(
    "SELECT COUNT(*) FROM bookings WHERE client_id = ? AND service_id = ?
     AND status IN ('pending','confirmed','in_progress')"
);
$dup->execute([$clientId, $serviceId]);
if ($dup->fetchColumn() > 0) {
    header('Location: service_detail.php?id=' . $serviceId . '&msg=duplicate');
    exit;
}
```
**Duplicate booking guard:** If this client already has an active booking for this service, block it. `fetchColumn()` returns the scalar result of `COUNT(*)`.

### The Transaction Block — Most Important Part

```php
try {
    $pdo->beginTransaction();

    $b = $pdo->prepare(
        "INSERT INTO bookings (service_id, client_id, worker_id, booking_date, notes, status)
         VALUES (?, ?, ?, ?, ?, 'pending')"
    );
    $b->execute([$serviceId, $clientId, $service['worker_id'], $bookingDate, $notes]);
    $bookingId = (int)$pdo->lastInsertId();

    $t = $pdo->prepare(
        "INSERT INTO transactions (booking_id, amount, escrow_status)
         VALUES (?, ?, 'held')"
    );
    $t->execute([$bookingId, $service['price']]);

    $pdo->commit();

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('Booking failed: ' . $e->getMessage());
    header('Location: service_detail.php?id=' . $serviceId . '&err=server');
    exit;
}
```

**Why a transaction?**

Imagine the booking INSERT succeeds but the transaction INSERT fails (e.g. the server crashes between the two statements). Now we have a booking with no financial record — corrupted data. The database transaction wraps both INSERTs:
- `beginTransaction()` — starts the "all or nothing" block
- `commit()` — if both INSERTs succeed, write them permanently
- `rollBack()` — if anything fails, undo everything as if neither INSERT happened

This is called atomicity — either both happen or neither happens.

### Building the PayFast Payment Form

```php
$pfData = [
    'merchant_id'   => '10000100',
    'merchant_key'  => '46f0cd694581a',
    'return_url'    => $baseUrl . '/pages/booking_confirm.php?id=' . $bookingId,
    'cancel_url'    => $baseUrl . '/pages/booking_cancel.php?id=' . $bookingId,
    'notify_url'    => $baseUrl . '/pages/payfast_notify.php',
    'amount'        => number_format($service['price'], 2, '.', ''),
    'item_name'     => 'HustleHub: ' . substr($service['title'], 0, 100),
    'custom_int1'   => $bookingId,   // our booking ID, passed back to us by PayFast
];
```

```php
$pfString = '';
foreach ($pfData as $key => $value) {
    if ($value !== '') {
        $pfString .= $key . '=' . urlencode(trim($value)) . '&';
    }
}
$pfString = rtrim($pfString, '&');
$pfString .= '&passphrase=' . urlencode(trim($pfPassPhrase));
$pfData['signature'] = md5($pfString);
```

**PayFast Signature:**
- Concatenate all key=value pairs with `&` between them
- URL-encode each value (spaces become `%20`, etc.)
- Append the merchant's passphrase at the end
- MD5 hash the whole string
- This signature proves to PayFast that the payment request came from our server and hasn't been tampered with

The page then auto-submits an HTML form to the PayFast sandbox. The user sees "Redirecting to PayFast…" briefly.

---

## 11. pages/payfast_notify.php — Payment Confirmation

This is called by PayFast's servers directly after a successful payment. **The user never visits this URL themselves** — it's server-to-server.

```php
// No session available — this is server-to-server
require_once '../config/db.php';
```
No `auth.php` here because PayFast calls this, not the browser. No session.

```php
// Verify signature
$pfParamString = '';
foreach ($pfData as $key => $value) {
    if ($key !== 'signature') {
        $pfParamString .= $key . '=' . urlencode($value) . '&';
    }
}
$pfParamString = rtrim($pfParamString, '&');
$pfParamString .= '&passphrase=' . urlencode($pfPassPhrase);
$signature = md5($pfParamString);

if ($signature !== $pfData['signature']) {
    die('Invalid signature.');
}
```
Rebuild the signature from the posted data and compare. If they match, the request genuinely came from PayFast with our passphrase. If they don't match, reject it — someone is trying to fake a payment notification.

```php
if (!$tx || round($tx['amount'], 2) != round($amountGross, 2)) {
    error_log("Amount mismatch...");
    exit;
}
```
Even if the signature is valid, verify the amount PayFast says was paid matches what we have in our transactions table. Prevents someone faking a R1 payment for a R500 service.

```php
$pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ? AND status = 'pending'")
    ->execute([$bookingId]);
```
Only confirm if still pending — idempotent (safe to call twice without double-confirming).

---

## 12. pages/booking_confirm.php and booking_cancel.php

These are user-facing pages that PayFast redirects the browser to after payment.

- `booking_confirm.php` — PayFast sends the user here after successful payment. Shows a success message.
- `booking_cancel.php` — PayFast sends the user here if they clicked "Cancel" on the PayFast page. We can optionally delete the pending booking.

These are NOT where the booking gets confirmed — that happens in `payfast_notify.php` server-to-server, which runs independently and may run before or after the user sees these pages.

---

## 13. pages/update_booking_status.php — AJAX Status Engine

This is the page that app.js calls via AJAX. It doesn't return HTML — it returns JSON.

```php
header('Content-Type: application/json');
```
Tells the browser this response is JSON, not HTML.

```php
$allowed = false;
$expectedCurrent = '';

if ($role === 'client' && $newStatus === 'completed') {
    $allowed = true;
    $expectedCurrent = 'in_progress';
} elseif ($role === 'worker' && $newStatus === 'confirmed') {
    $allowed = true;
    $expectedCurrent = 'pending';
} elseif ($role === 'worker' && $newStatus === 'in_progress') {
    $allowed = true;
    $expectedCurrent = 'confirmed';
}

if (!$allowed) {
    echo json_encode(['success'=>false,'message'=>'Transition not permitted']); exit;
}
```

**State transition rules:**
| Who | Can change to | From |
|---|---|---|
| Worker | `confirmed` | `pending` (accepting a booking) |
| Worker | `in_progress` | `confirmed` (starting the job) |
| Client | `completed` | `in_progress` (confirming job is done) |

Nobody can set any status they want — each role is restricted to specific transitions.

```php
if ($role === 'client') {
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id=? AND client_id=?");
} else {
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id=? AND worker_id=?");
}
```
Ownership check — we make sure the booking ID belongs to this user. Without this, a client could change statuses on bookings that aren't theirs.

```php
if ($booking['status'] !== $expectedCurrent) {
    echo json_encode(['success'=>false,'message'=>'Cannot update from current status']); exit;
}
```
Checks the booking is currently in the right state before transitioning. Prevents double-clicking from running twice.

```php
if ($newStatus === 'completed') {
    $pdo->prepare(
        "UPDATE transactions SET escrow_status='released', released_by=?, released_at=NOW()
         WHERE booking_id=? AND escrow_status='held'"
    )->execute([$userId, $bookingId]);
}
```
When the client confirms completion, the escrow is released simultaneously with the booking status update — all inside the same transaction block.

---

## 14. pages/client_dashboard.php — Client View

Shows the client their bookings grouped by status. Key features:
- For `in_progress` bookings: a "Confirm Complete" button (calls app.js → AJAX to update_booking_status)
- For `completed` bookings: a "Leave Review" link (to leave_review.php)
- For active bookings: a "Raise Dispute" link (to raise_dispute.php)

The page queries:
```sql
SELECT b.*, s.title, s.price, u.full_name AS worker_name, t.escrow_status
FROM bookings b
JOIN services s ON b.service_id = s.id
JOIN users u ON b.worker_id = u.id
JOIN transactions t ON t.booking_id = b.id
WHERE b.client_id = ?
ORDER BY b.created_at DESC
```
One query with three JOINs gets all the information needed for the dashboard.

---

## 15. pages/worker_dashboard.php — Worker View

```php
requireRole('worker');
```
Only workers can access this page.

Shows:
- Stats (total bookings, completed, active, disputed)
- Active bookings table with "Accept" and "Start Job" buttons
- Their listings with Edit/Delete links

The "Accept" and "Start Job" buttons both call `update_booking_status.php` via AJAX in `app.js`.

---

## 16. pages/create_listing.php — Worker Creates a Service

```php
requireRole('worker');
```

**Image upload handling:**
```php
$ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExts = ['jpg', 'jpeg', 'png', 'webp'];

if ($file['size'] > $maxSize) {
    $error = 'Image must be under 2MB.';
} elseif (!in_array($ext, $allowedExts)) {
    $error = 'Only JPG, PNG, or WebP images are accepted.';
} else {
    $filename  = uniqid('', true) . '.' . $ext;
    $uploadDir = '../assets/images/listings/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    move_uploaded_file($file['tmp_name'], $uploadDir . $filename);
    $imagePath = 'assets/images/listings/' . $filename;
}
```

- `pathinfo()` extracts the file extension from the filename
- `strtolower()` normalises it — `JPG` and `jpg` both pass
- `uniqid('', true)` generates a unique filename — prevents filename collisions and path traversal attacks
- `move_uploaded_file()` is the safe PHP function for moving uploaded files — it verifies the file came from an actual upload and not from a crafted request
- The folder is created with `mkdir(path, 0755, true)` if it doesn't exist — `0755` is permissions (owner can read/write/execute, others can read), `true` creates parent directories too

After a successful save, the listing goes to the `services` table with `approval_status = 'pending'` — it won't appear on the browse page until an admin approves it.

---

## 17. pages/edit_listing.php and delete_listing.php

**edit_listing.php:**
```php
requireRole('worker');

$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND worker_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$service = $stmt->fetch();
if (!$service) { header('Location: worker_dashboard.php'); exit; }
```
The `AND worker_id = ?` ownership check is critical. Without it, any worker could edit any other worker's listing by changing the ID in the URL. We always verify the record belongs to the requesting user.

**delete_listing.php:**
Same pattern — fetch with `AND worker_id = ?`, then:
```php
$pdo->prepare("DELETE FROM services WHERE id = ? AND worker_id = ?")->execute([$id, $userId]);
```
The `AND worker_id = ?` in the DELETE itself is a double-safety — even if somehow the ownership check was bypassed, the DELETE won't affect other workers' listings.

---

## 18. pages/raise_dispute.php — Raising a Dispute

```php
if (!isLoggedIn() || ($_SESSION['role'] !== 'client' && $_SESSION['role'] !== 'worker')) {
    header('Location: /pages/login.php');
    exit;
}
```
Manual inline check because both client and worker can raise disputes. The `requireRole()` function only handles a single role.

```php
$clause = $role === 'client' ? 'b.client_id = ?' : 'b.worker_id = ?';
$stmt = $pdo->prepare("
    SELECT b.*, s.title, t.escrow_status
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN transactions t ON t.booking_id = b.id
    WHERE b.id = ? AND $clause
");
```
The ternary operator sets the WHERE clause dynamically based on the user's role. This ensures the booking belongs to them.

```php
if ($booking['escrow_status'] !== 'held') {
    // redirect away — can't dispute after escrow is already resolved
}

$existing = $pdo->prepare("SELECT id FROM disputes WHERE booking_id = ? AND status IN ('open','under_review')");
$existing->execute([$bookingId]);
if ($existing->fetch()) {
    // redirect — dispute already raised
}
```
Two guards: can only dispute while money is still in escrow, and can't raise duplicate disputes.

```php
$pdo->beginTransaction();
$pdo->prepare("INSERT INTO disputes (booking_id, raised_by, reason, status) VALUES (?,?,?,'open')")
    ->execute([$bookingId, $userId, $reason]);
$pdo->prepare("UPDATE bookings SET status='disputed' WHERE id=?")
    ->execute([$bookingId]);
$pdo->commit();
```
Two database writes — insert dispute record AND update booking status — wrapped in a transaction so they both succeed or both fail together.

---

## 19. pages/leave_review.php — Leaving a Review

```php
$already = $pdo->prepare("SELECT id FROM reviews WHERE booking_id = ? AND reviewer_id = ?");
$already->execute([$bookingId, $userId]);
if ($already->fetch()) {
    header('Location: client_dashboard.php?msg=already_reviewed');
    exit;
}
```
Prevents double-reviewing. The database also has a UNIQUE KEY on `(booking_id, reviewer_id)` but we check in PHP first to give a friendly message.

```php
$revieweeId = ($userId === (int)$booking['client_id']) ? $booking['worker_id'] : $booking['client_id'];
```
Ternary to determine who is being reviewed. If the logged-in user is the client, review the worker; if the worker, review the client.

```php
$pdo->prepare("INSERT INTO reviews (booking_id, reviewer_id, reviewee_id, rating, comment)
               VALUES (?, ?, ?, ?, ?)")->execute([$bookingId, $userId, $revieweeId, $rating, $comment]);

// Recalculate avg_rating
$avg = $pdo->prepare("SELECT AVG(rating) FROM reviews WHERE reviewee_id = ?");
$avg->execute([$revieweeId]);
$pdo->prepare("UPDATE users SET avg_rating = ? WHERE id = ?")
    ->execute([$avg->fetchColumn(), $revieweeId]);
```
After inserting the review, recalculate the average for the person being reviewed and update their `avg_rating` column in the users table. `AVG()` is a SQL aggregate function that calculates the mean of all values in that column for that user.

---

## 20. admin/admin_header.php — Admin Auth Gate

```php
session_start();

if (!isset($_SESSION['user_id'], $_SESSION['role']) ||
    !in_array($_SESSION['role'], ['admin','moderator'], true)) {
    header('Location: /pages/login.php');
    exit;
}
```
This file is `require_once`'d at the top of every admin page. It does `session_start()` itself (doesn't include auth.php) and checks the role is either admin or moderator. Any other role gets sent to login.

```php
$isSuperAdmin = $_SESSION['role'] === 'admin';
$adminName    = $_SESSION['user_name'] ?? 'Admin';
```
`$isSuperAdmin` is used throughout admin pages to show or hide super-admin-only features like the Users management page.

---

## 21. admin/index.php — Admin Dashboard

```php
$stats['total_users']       = $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('worker','client')")->fetchColumn();
$stats['pending_listings']  = $pdo->query("SELECT COUNT(*) FROM services WHERE approval_status = 'pending'")->fetchColumn();
$stats['escrow_held']       = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE escrow_status = 'held'")->fetchColumn();
```
- `COUNT(*)` counts all rows matching the WHERE condition
- `SUM(amount)` adds up all held escrow amounts
- `COALESCE(SUM(amount),0)` — if there are no held transactions, `SUM` returns NULL. `COALESCE` returns the first non-NULL value — so we get `0` instead of NULL.

Each stat is its own query — not the most efficient but very readable and fine for an academic project.

---

## 22. admin/listings.php — Approve/Reject Listings

```php
if ($_SESSION['csrf_token'] !== ($_POST['csrf_token'] ?? '')) {
    die('Invalid request.');
}
```
Manual CSRF check (this page includes `admin_header.php` not `auth.php`, so `verifyCsrfToken()` isn't available — the `e()` function is redefined in admin_header.php).

```php
if ($listingId && in_array($action, ['approved', 'rejected'], true)) {
    $pdo->prepare("UPDATE services SET approval_status = ? WHERE id = ?")
        ->execute([$action, $listingId]);

    $pdo->prepare(
        "INSERT INTO audit_log (admin_id, action, target_type, target_id, notes)
         VALUES (?, ?, 'service', ?, ?)"
    )->execute([
        $_SESSION['user_id'],
        $action === 'approved' ? 'LISTING_APPROVED' : 'LISTING_REJECTED',
        $listingId,
        "Listing ID $listingId set to $action"
    ]);
}
```
**Two writes per action:**
1. Update the service's `approval_status`
2. Write an audit log entry recording who did what and when

The `in_array($action, ['approved', 'rejected'], true)` validates the action before using it in SQL — even though it's a `?` placeholder, validating against a whitelist is good defensive practice.

---

## 23. admin/disputes.php — Resolve Disputes

The most complex admin page. When an admin resolves a dispute they choose:
- **Release** — give the money to the worker (job was done adequately)
- **Refund** — give the money back to the client (job wasn't done)

```php
$newEscrow  = $action === 'release' ? 'released' : 'refunded';
$newBooking = $action === 'release' ? 'completed' : 'cancelled';

$pdo->beginTransaction();

// 1. Update transaction escrow status
$pdo->prepare("UPDATE transactions SET escrow_status = ?, released_by = ?, released_at = NOW() WHERE id = ?")
    ->execute([$newEscrow, $_SESSION['user_id'], $row['tx_id']]);

// 2. Update booking status
$pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?")
    ->execute([$newBooking, $row['booking_id']]);

// 3. Resolve dispute with note
$pdo->prepare("
    UPDATE disputes SET status = 'resolved', admin_id = ?, resolution_note = ?, resolved_at = NOW()
    WHERE id = ?
")->execute([$_SESSION['user_id'], $resolutionNote, $disputeId]);

// 4. Audit log
$pdo->prepare("INSERT INTO audit_log (admin_id, action, target_type, target_id, notes)
               VALUES (?, ?, 'dispute', ?, ?)")
    ->execute([$_SESSION['user_id'], 'DISPUTE_' . strtoupper($newEscrow), $disputeId, "Note: $resolutionNote"]);

$pdo->commit();
```
**Four writes, one transaction:**
One dispute resolution touches: transactions, bookings, disputes, and audit_log — all inside a single transaction so if any one fails, everything rolls back.

---

## 24. admin/users.php — Manage Users

Super-admin only (checked via `$isSuperAdmin`). Allows:
- Viewing all users
- Changing a user's role
- Deactivating accounts

Key protection: **an admin cannot change their own role or deactivate themselves.** This is checked with `if ($userId !== $_SESSION['user_id'])`.

---

## 25. admin/audit_log.php — Audit Trail

Read-only view of the `audit_log` table. Shows every admin action with timestamp, who did it, what they did, and to what.

```sql
SELECT a.*, u.full_name AS admin_name
FROM audit_log a
JOIN users u ON a.admin_id = u.id
ORDER BY a.created_at DESC
LIMIT 100
```
The JOIN gets the admin's name alongside the log entry.

---

## 26. assets/js/app.js — All the JavaScript

jQuery is loaded from CDN in footer.php. `app.js` runs after the DOM is ready.

### Section 1 — Category Filter Pills

```javascript
$('#category-filters').on('click', '.btn-filter', function () {
    const cat = $(this).data('cat');
    $('.service-card').each(function () {
        const match = cat === 'all' || $(this).data('cat') === cat;
        $(this).toggle(match);
    });
    updateResultCount();
});
```
- `$('#category-filters').on('click', '.btn-filter', ...)` — event delegation. Instead of attaching a click handler to each button, attach one to the parent and listen for clicks that bubble up from `.btn-filter` children.
- `$(this).data('cat')` — reads the `data-cat="cleaning"` HTML attribute from the clicked button
- Each service card has `data-cat="cleaning"` on its container `<div>`. We compare these.
- `$(this).toggle(match)` — shows the card if match is true, hides it if false

### Section 2 — Price Range Slider

```javascript
$('#price-range').on('input', function () {
    const max = parseInt($(this).val());
    $('.service-card').each(function () {
        const priceOk = parseInt($(this).data('price')) <= max;
        const catOk   = activeCat === 'all' || $(this).data('cat') === activeCat;
        $(this).toggle(priceOk && catOk);
    });
});
```
Fires on every slider movement. Reads the card's `data-price` attribute and hides cards above the maximum. Must also respect the active category filter simultaneously.

### Section 3 — Live Search

```javascript
$('#search-input').on('input', function () {
    const q = $(this).val().toLowerCase().trim();
    $('.service-card').each(function () {
        const title = $(this).find('.card-title').text().toLowerCase();
        const cat   = $(this).data('cat');
        $(this).toggle(title.includes(q) || cat.includes(q));
    });
});
```
Fires on every keystroke. Reads the card's title text and category, shows the card if the search term appears in either.

### Sections 4, 5, 6 — AJAX Status Buttons

All three follow the same pattern:

```javascript
$(document).on('click', '.btn-confirm-complete', function () {
    const $btn      = $(this);
    const bookingId = $btn.data('booking-id');

    if (!confirm('Confirm the job is complete?...')) return;

    $btn.prop('disabled', true).html('<span class="spinner-border ..."></span>Processing…');

    $.ajax({
        url:  '../pages/update_booking_status.php',
        type: 'POST',
        data: {
            booking_id:  bookingId,
            new_status:  'completed',
            csrf_token:  $('meta[name=csrf-token]').attr('content')
        },
        success: function (res) {
            const data = (typeof res === 'string') ? JSON.parse(res) : res;
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
                $btn.prop('disabled', false).text('Confirm Complete');
            }
        },
        error: function () {
            alert('Network error. Please try again.');
            $btn.prop('disabled', false).text('Confirm Complete');
        }
    });
});
```

**AJAX flow explained:**
1. User clicks the button
2. `confirm()` shows a browser popup — if they cancel, `return` stops everything
3. Disable the button and show a spinner (prevents double-clicking)
4. `$.ajax()` sends a POST request to `update_booking_status.php` in the background
5. The CSRF token is read from a `<meta name="csrf-token">` tag in the HTML head
6. On success: if `data.success` is true, reload the page. If false, show the error message and re-enable the button
7. On network error: show a message and re-enable the button

**Why AJAX instead of a full form submit?**
Without AJAX, the user would click a button, the page would go white, reload, and scroll to the top. With AJAX, the update happens in the background — smoother experience.

### Section 7 — Character Counter

```javascript
function updateCharCount() {
    const len = $('#review-text').val().length;
    $('#char-count').text(len + '/500');
    if (len > 450) { $('#char-count').css('color', '#dc3545'); }  // red near limit
}
```
Updates a `<div id="char-count">` with the current character count. Turns red when approaching the 500-character limit.

### Section 8 — Star Rating Picker

The star rating HTML uses invisible radio inputs and visible `<label>` elements styled as stars. When a label is clicked, its corresponding radio input becomes checked, and CSS sibling selectors make the stars up to that point turn yellow.

---

## 27. includes/header.php and footer.php

**header.php** is included at the bottom of the PHP logic section of every page. By that point, `$pageTitle` should be set.

```php
$pageTitle = $pageTitle ?? 'HustleHub';
```
If the including page didn't set `$pageTitle`, default to 'HustleHub'.

The navbar shows different links depending on the role:
```php
if (isLoggedIn()):
    // Show the user's name and role-specific dashboard link
    // Show Logout link
else:
    // Show Login and Register links
endif;
```

The `<meta name="csrf-token" content="...">` tag in the HTML head is how app.js reads the CSRF token for AJAX requests.

**footer.php** contains:
- Closing `</main>` tag
- Footer HTML with links and the copyright notice with `<?= date('Y') ?>` (auto-updates the year)
- Bootstrap JS bundle (includes Popper for dropdowns)
- jQuery CDN script
- `/assets/js/app.js` — loaded last so the DOM is fully ready

---

## 28. KEY CONCEPTS — Most Likely Exam Questions

### "Explain how authentication works in your system"

**Answer:** Every page includes `auth.php` at the top, which calls `session_start()`. On login, we query the database for the user by email, then use `password_verify()` to check their password against the stored BCrypt hash. If it matches, we store their `user_id`, `role`, and `user_email` in the `$_SESSION` array. From that point on, every page can read `$_SESSION['user_id']` to know who is logged in. We also check `$_SESSION['last_active']` on every page — if it's been more than 30 minutes since their last action, we destroy the session and send them to the login page.

### "What is a prepared statement and why do you use them?"

**Answer:** A prepared statement separates the SQL query structure from the data values. Instead of building a query by concatenating user input directly (which allows SQL injection), we write the query with `?` placeholders, then call `execute()` with the actual values separately. MySQL receives the query structure and the data in two separate communications — it treats the values as pure data, not executable SQL. So even if someone enters `'; DROP TABLE users;--` as their email, it's stored as a literal string, not executed.

### "What is CSRF and how do you prevent it?"

**Answer:** Cross-Site Request Forgery is when a malicious website tricks a user's browser into making a request to our site (e.g. submitting a form) while they're logged in. Their session cookie gets sent automatically with the request, so our server can't tell the difference from a legitimate request. We prevent it with a CSRF token — a random value stored in the session and embedded as a hidden field in every form. When the form is submitted, we check the posted token matches the session token. A malicious external site can't know this token, so their crafted request will fail the check.

### "How does escrow work in your system?"

**Answer:** When a client books and pays, we create a `transactions` record with `escrow_status = 'held'`. PayFast processes the actual bank transfer and notifies us via `payfast_notify.php`. The money is logically "frozen" in our system — the worker can't access it yet. Once the job is done and the client clicks "Confirm Complete", we update the transaction to `escrow_status = 'released'`. If there's a dispute, an admin decides: if resolved in the worker's favour, status becomes `released`; if in the client's favour, `refunded`. PayFast then processes the actual payout or refund separately.

### "Why do you use database transactions?"

**Answer:** When we need to write to two tables at once and they must both succeed or both fail. For example: creating a booking (insert into `bookings`) and creating the escrow record (insert into `transactions`) must happen together. If the booking INSERT succeeds but the transaction INSERT fails, we'd have a booking with no financial record — corrupt data. By wrapping both in `beginTransaction()`/`commit()`, MySQL guarantees both happen atomically. If anything throws a `PDOException`, we call `rollBack()` and neither INSERT persists.

### "How does your role-based access control work?"

**Answer:** When a user logs in, we store their role in `$_SESSION['role']`. Pages that require a specific role call `requireRole('worker')` at the top — this checks the session role matches and kills execution if not. For pages accessible by multiple roles (raise dispute, leave review), we do a manual inline check: `if ($_SESSION['role'] !== 'client' && $_SESSION['role'] !== 'worker')`. Admin pages use a separate `admin_header.php` which checks `in_array($_SESSION['role'], ['admin','moderator'])`. No role can access another role's pages — the check happens server-side on every single page load, not just at login.

### "How does the AJAX work for the booking status buttons?"

**Answer:** The button has a `data-booking-id` attribute with the booking ID. When clicked, jQuery disables the button (to prevent double-clicking), then uses `$.ajax()` to POST to `update_booking_status.php` with the booking ID, the desired new status, and the CSRF token read from the page's meta tag. The PHP file returns a JSON response like `{"success":true}` or `{"success":false,"message":"..."}`. If success, we reload the page so the updated status is shown. This happens without a full page navigation — the update is invisible to the user except for the page reload.

### "How do you prevent a worker from editing another worker's listing?"

**Answer:** In `edit_listing.php` (and `delete_listing.php`), when we fetch the listing from the database, we include `AND worker_id = ?` in the WHERE clause with the logged-in user's ID from the session. If the listing exists but belongs to a different worker, the fetch returns false and we redirect away. The same `AND worker_id = ?` is included in the UPDATE and DELETE statements themselves as a second layer. We never trust the listing ID coming from the URL or form alone.

### "How does PayFast communicate with your server?"

**Answer:** PayFast uses an Instant Transaction Notification (ITN). After payment is complete, PayFast's servers make a POST request directly to our `payfast_notify.php` — this is server-to-server, the user's browser is not involved. PayFast sends all the payment details including a signature (MD5 hash of all values + our passphrase). We verify the signature to confirm the notification is genuine, then check the amount matches our record, then update the booking status to `confirmed` and store the PayFast transaction ID. The user's browser is separately redirected to `booking_confirm.php` which just shows them a success message.

### "What is BCrypt and why is it used for passwords?"

**Answer:** BCrypt is a password hashing algorithm specifically designed to be slow. `password_hash($password, PASSWORD_BCRYPT)` applies a one-way mathematical function to the password that cannot be reversed. The output includes a random salt (different every time, even for identical passwords) and the result of the hash. When a user logs in, `password_verify($input, $stored_hash)` runs the same computation and compares results — it doesn't "decrypt" anything. BCrypt is slow by design (takes ~100ms) which makes it impractical for attackers to brute-force millions of password guesses.

### "What happens if I submit the dispute form twice?"

**Answer:** Three things protect against this. First, before showing the dispute form we query `disputes` for any open/under_review dispute for that booking and redirect away if one exists. Second, the booking status is set to `disputed` atomically with the dispute INSERT in a transaction — so a second form submission would find the booking status is `disputed` not an active status. Third, the escrow check at the top would fail on a second attempt because an already-disputed booking's escrow is still `held` but the user would have been redirected by the first check anyway.

### "Why does the browse page filter work both in PHP and JavaScript?"

**Answer:** The PHP filter runs server-side when the page first loads — it queries only matching services from the database. The JavaScript filter (in app.js) works on the cards that are already rendered in the DOM — it shows/hides them without a page reload. We have both because: (1) if JavaScript is disabled, the PHP filter still works; (2) the JS filter is instant and doesn't require a network request; (3) search engine crawlers (no JS) still see the correct filtered content.

---

*End of study guide. You built all of this. You understand all of this. Go show them what you made.*
