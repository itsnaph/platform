# HustleHub — C2C Service Marketplace
## ITECA3-12 | Munashe Tsikada | EDUV4881584

> **Deliverable 3:** Prototype + Diagrams + Working Code + Documentation + Hosting

---

## Local Development — Quick Start

### Start the server (Windows)

#### **Easiest: Start Everything at Once**
Double-click `start-all.ps1` from File Explorer, or run:
```powershell
powershell -ExecutionPolicy Bypass -File "start-all.ps1"
```
The script:
- Checks if MySQL is already running — skips start if it is (safe to run repeatedly)
- Starts MySQL and waits until it is actually ready on port 3306
- Checks if PHP dev server is already running — skips if it is
- Starts the PHP dev server and waits until port 8080 is open
- Opens **http://localhost:8080** in your browser automatically

---

#### **Or: Start Manually (Advanced)**

**Terminal 1 — Start MySQL:**
```powershell
powershell -ExecutionPolicy Bypass -File "start-mysql.ps1"
```

**Terminal 2 — Start PHP Dev Server:**
```powershell
powershell -ExecutionPolicy Bypass -File "start-dev-server.ps1"
```

Wait for MySQL to show `ready for connections`, then open **http://localhost:8080**.

---

#### **Requirements — What Is Actually Installed**

| Component | Version | Path |
|---|---|---|
| PHP (active) | 8.5.6 | `C:\Users\User\scoop\apps\php\current\php.exe` |
| MySQL | 9.7.0 | `C:\Users\User\scoop\apps\mysql\current\bin\` |
| MySQL data dir | — | `C:\Users\User\scoop\persist\mysql\data` |

> **Note:** `C:\php-official\php.exe` (PHP 8.3.31) is present but has broken extensions
> (pdo_mysql, mysqli, gd, mbstring all fail to load). **Do not use it.** The startup
> scripts use Scoop PHP, which has all required extensions working.

---

### Test credentials

| Role | Email | Password |
|---|---|---|
| Admin | admin@hustlehub.co.za | Password@123 |
| Moderator | mod@hustlehub.co.za | Password@123 |
| Worker | thabo@example.co.za | Password@123 |
| Worker | lindiwe@example.co.za | Password@123 |
| Worker | david@example.co.za | Password@123 |
| Worker | moses@example.co.za | Password@123 |
| Client | amara@example.co.za | Password@123 |
| Client | priya@example.co.za | Password@123 |
| Client | james@example.co.za | Password@123 |

### Key URLs

| Page | URL |
|---|---|
| Homepage | http://localhost:8080 |
| Browse services | http://localhost:8080/pages/browse.php |
| Search | http://localhost:8080/pages/search.php?q=plumbing |
| Worker profile | http://localhost:8080/pages/worker_profile.php?id=3 |
| My account | http://localhost:8080/pages/my_account.php |
| Admin login | http://localhost:8080/pages/admin_login.php |
| Admin dashboard | http://localhost:8080/admin/dashboard.php |

### Database setup

**Already Configured:**
- Database `hustlehub` pre-created at `C:\Users\User\scoop\persist\mysql\data`
- Schema imported (users, listings, bookings, transactions, reviews, disputes)
- Seed data loaded with test accounts and sample services

**If Database Missing (Reset):**
```powershell
# From your project folder, run:
& "C:\Users\User\scoop\apps\mysql\current\bin\mysql.exe" -u root hustlehub < database/schema.sql
& "C:\Users\User\scoop\apps\mysql\current\bin\mysql.exe" -u root hustlehub < database/seed.sql
```

**Test Credentials** (pre-seeded):
| Role | Email | Password |
|---|---|---|
| Admin | admin@hustlehub.co.za | Password@123 |
| Worker | thabo@example.co.za | Password@123 |
| Client | amara@example.co.za | Password@123 |

---

## Tech Stack
- **Frontend:** HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons
- **JavaScript:** ES6 + jQuery 3.x (dynamic filters, Ajax status updates)
- **Backend:** PHP 8.5 (PDO, bcrypt, sessions, CSRF, RBAC)
- **Database:** MySQL 9.7 (6 tables + audit_log, foreign keys, ENUMs)
- **Payment:** PayFast South Africa (sandbox for testing)
- **Hosting:** InfinityFree (live deployment)

---

## Project Structure

```
hustlehub/
├── config/
│   ├── db.php              ← PDO connection (update credentials here)
│   └── db.example.php      ← Safe template for version control
├── includes/
│   ├── auth.php            ← Session, CSRF, requireRole(), e()
│   ├── header.php          ← Bootstrap navbar + HTML head
│   └── footer.php          ← JS includes, closing tags
├── pages/                  ← Main site pages
│   ├── browse.php          ← Service listings with filter
│   ├── service_detail.php  ← Single service + booking form
│   ├── process_booking.php ← Atomic booking + PayFast redirect
│   ├── payfast_notify.php  ← PayFast ITN handler
│   ├── booking_confirm.php ← Post-payment confirmation page
│   ├── booking_cancel.php  ← PayFast cancel URL handler
│   ├── client_dashboard.php← Client bookings + actions
│   ├── worker_dashboard.php← Worker bookings + listings
│   ├── create_listing.php  ← Worker creates service
│   ├── edit_listing.php    ← Worker edits service
│   ├── delete_listing.php  ← Worker deletes service (with guard)
│   ├── leave_review.php    ← Post-completion review form
│   ├── raise_dispute.php   ← Client raises dispute
│   ├── update_booking_status.php ← Ajax JSON endpoint
│   ├── register.php        ← Registration (worker/client)
│   ├── login.php           ← Login with role redirect
│   ├── logout.php          ← Session destroy
│   └── how_it_works.php    ← Platform explainer + FAQ
├── admin/                  ← Admin portal (RBAC protected)
│   ├── admin_header.php    ← Session guard + HTML head
│   ├── admin_nav.php       ← Sidebar navigation
│   ├── index.php           ← Admin dashboard + stats
│   ├── dashboard.php       ← Alias → index.php
│   ├── listings.php        ← Approve/reject service listings
│   ├── disputes.php        ← Dispute resolution + escrow control
│   ├── users.php           ← User management (Super Admin only)
│   └── audit_log.php       ← Admin action history
├── assets/
│   ├── css/
│   │   ├── style.css       ← HustleHub theme + CSS variables
│   │   └── admin.css       ← Admin portal styles
│   ├── js/
│   │   └── app.js          ← jQuery: filters, Ajax, review counter
│   └── images/listings/    ← Uploaded service images (auto-created)
├── database/
│   ├── schema.sql          ← CREATE TABLE statements (run first)
│   └── seed.sql            ← Test data (run second)
├── index.php               ← Landing page
├── DIAGRAMS.md             ← All 5 system diagrams with flow descriptions
└── README.md               ← This file
```

---

## Free Hosting Options

### Recommended: InfinityFree + Aiven (100% free, no card required)

The only fully free, production-capable combination in 2026.

| Service | What it hosts | Free limits |
|---|---|---|
| **InfinityFree** (infinityfree.com) | PHP files + web server | 5 GB disk, unlimited bandwidth, free subdomain |
| **Aiven** (aiven.io) | Managed MySQL database | Free tier — 1 DB, automated backups, SSL |

**Steps:**
1. Sign up at **aiven.io** → New Service → MySQL → select Free tier → note your host, port, user, password
2. In the Aiven console, open your MySQL service → use the built-in query editor to run `database/schema.sql` then `database/seed.sql`
3. Sign up at **infinityfree.com** → create a hosting account → note your FTP credentials and cPanel URL
4. Update `config/db.php` with the Aiven credentials (host, port, DB name, user, password)
5. Upload all project files to `htdocs/` via FTP — FileZilla is recommended (free at filezilla-project.org)
6. Visit your free subdomain (e.g. `hustlehub.infinityfreeapp.com`)

> **Shortcut:** InfinityFree also includes a built-in MySQL database (50 MB per DB) via cPanel → MySQL Databases + phpMyAdmin. For a demo or student project this is enough and skips the Aiven setup entirely — just follow the Deploying to InfinityFree steps below.

---

### Not free — common misconceptions (2026)

| Service | Why it is not free |
|---|---|
| **Railway** | 30-day $5 trial only — requires credit card, $5/month minimum after trial |
| **Render** | No MySQL on free tier — PostgreSQL only; PHP requires Docker |
| **PlanetScale** | Free Hobby tier removed March 2024 |
| **000webhost** | Free tier discontinued by Hostinger — no longer available |
| **FreeSQLDatabase** | 5 MB cap — development and testing only, not production |

---

## Deploying to InfinityFree — Step-by-Step

### Step 1: Database
1. Log into cPanel → MySQL Databases
2. Create database: `hustlehub` (or any name)
3. Create a database user and assign ALL PRIVILEGES
4. Open phpMyAdmin → select the database
5. Import `database/schema.sql` first, then `database/seed.sql`

### Step 2: Config
1. Copy `config/db.example.php` to `config/db.php`
2. Edit `config/db.php` — fill in your host, database name, username, password

### Step 3: PayFast (for payment testing)
1. Register at https://sandbox.payfast.co.za for a free sandbox account
2. Get your Merchant ID, Merchant Key, and Passphrase
3. Update these values in `pages/process_booking.php` (lines marked "Replace with real...")
4. Update `return_url`, `cancel_url`, and `notify_url` with your live domain
5. Set `$pfSandbox = true` while testing, `false` when going live

### Step 4: Upload files
1. Upload all files to your `public_html/` directory (or a subfolder)
2. Ensure `assets/images/listings/` is writable (chmod 755 or 775)

### Step 5: Test
Navigate to your live URL. Test with seed data credentials:
- **Admin:** admin@hustlehub.co.za / Password@123
- **Moderator:** mod@hustlehub.co.za / Password@123
- **Worker:** thabo@example.co.za / Password@123
- **Client:** amara@example.co.za / Password@123

---

## Core Booking + Escrow Flow

```
1. Client browses services (browse.php)
2. Client clicks Book Now → service_detail.php (booking form)
3. Client submits form → process_booking.php:
   a. Duplicate booking guard (SELECT COUNT)
   b. PDO beginTransaction()
   c. INSERT bookings (status='pending')
   d. INSERT transactions (escrow_status='held')
   e. PDO commit()
   f. Auto-redirect to PayFast sandbox
4. Client pays on PayFast
5. PayFast sends ITN to payfast_notify.php:
   a. Signature validated
   b. transactions updated: pf_payment_id, escrow confirmed
   c. bookings status → 'confirmed'
6. Worker accepts booking → status → 'confirmed' (Ajax)
7. Worker starts job   → status → 'in_progress' (Ajax)
8. Client confirms complete → status → 'completed' + escrow → 'released' (Ajax)
9. Both parties can leave a review

DISPUTE PATH:
- Client raises dispute while status='in_progress' and escrow='held'
- Booking status → 'disputed'
- Admin reviews in admin/disputes.php
- Admin writes resolution note (REQUIRED)
- Admin clicks Release → escrow='released', booking='completed'
  OR clicks Refund → escrow='refunded', booking='cancelled'
- All three DB updates wrapped in PDO transaction
```

---

## Security Measures Implemented

| Measure | Where |
|---|---|
| SQL Injection prevention | PDO prepared statements with `?` placeholders throughout |
| XSS prevention | `htmlspecialchars()` via `e()` helper on all output |
| CSRF protection | Token in every form + server-side validation |
| Password hashing | bcrypt via `password_hash()` / `password_verify()` |
| Session fixation | `session_regenerate_id(true)` after login |
| Session timeout | 30-minute inactivity logout |
| Broken access control | `requireRole()` on every protected page |
| File upload safety | MIME type check via `finfo`, random filename, size limit |
| Direct object reference | Ownership WHERE clause on all booking/service queries |
| Error exposure | `display_errors=Off` in production; `error_log()` only |

---

## Admin RBAC

| Page | Admin | Moderator |
|---|---|---|
| Dashboard | ✅ | ✅ |
| Listings (approve/reject) | ✅ | ✅ |
| Disputes (release/refund) | ✅ | ✅ |
| Users (manage/deactivate) | ✅ | ❌ |
| Audit Log | ✅ | ❌ |

---

## Troubleshooting

### Root cause: "Service unavailable" on every page
This means **MySQL is not running**. PHP connects to MySQL on every page load;
if MySQL is down, every page returns the generic DB error.

**Fix:** Start MySQL first, then the PHP server:
```powershell
powershell -ExecutionPolicy Bypass -File "start-mysql.ps1"
# wait for "ready for connections" then:
powershell -ExecutionPolicy Bypass -File "start-dev-server.ps1"
```

### MySQL won't start
```powershell
# Check if MySQL is already running:
netstat -ano | Select-String ":3306"

# If port 3306 is in use, MySQL is already running — don't start again
# If nothing shows, MySQL isn't running — retry start-mysql.ps1
```

### PHP showing "could not find driver" or extension errors
This happens if you run PHP manually using `php` from the terminal instead of through
the startup script. The system PATH points to Scoop's `php.exe` shim, but the wrong
`php.ini` may load. Always use `start-dev-server.ps1`.

> **Do not use `C:\php-official\php.exe`** — that installation has broken extensions
> (pdo_mysql, mysqli, gd, mbstring all fail to load) and will not connect to MySQL.

### Can't access http://localhost:8080
1. Verify PHP server is running (check the terminal window for "Development Server started")
2. Verify MySQL is running (check terminal for "ready for connections" on port 3306)
3. Check no other app is using port 8080: `netstat -ano | Select-String ":8080"`

### Worker profile page redirects to browse.php
The URL must use a worker's user ID (not admin or moderator). Worker IDs in seed data:

| ID | Name | Email |
|---|---|---|
| 3 | Thabo Nkosi | thabo@example.co.za |
| 4 | Lindiwe Dube | lindiwe@example.co.za |
| 5 | David Sithole | david@example.co.za |
| 6 | Moses Khumalo | moses@example.co.za |

Correct URL: `http://localhost:8080/pages/worker_profile.php?id=3`

### Database connection failed / reset database
```powershell
# Reset database using Scoop MySQL binary:
& "C:\Users\User\scoop\apps\mysql\current\bin\mysql.exe" -u root hustlehub < database/schema.sql
& "C:\Users\User\scoop\apps\mysql\current\bin\mysql.exe" -u root hustlehub < database/seed.sql
```

### Check config/db.php credentials
```php
define('DB_HOST', '127.0.0.1');     // Must be this — not 'localhost'
define('DB_NAME', 'hustlehub');     // Must be this
define('DB_USER', 'root');          // Must be this
define('DB_PASS', '');              // Empty — Scoop MySQL has no root password
```

---

*ITECA3-12 — Web Development and e-Commerce | NQF Level 7 | Eduvos*
