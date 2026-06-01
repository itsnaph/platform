# HustleHub — C2C Service Marketplace
## ITECA3-12 | Munashe Tsikada | EDUV4881584

> **Deliverable 2:** Prototype + Diagrams + Working Code + Documentation

---

## Local Development — Quick Start

### Start the server
```powershell
C:\php83\php.exe -S localhost:8080 -t "C:\Users\User\Documents\platform"
```
Then open **http://localhost:8080** in your browser.

> **Requirements:** PHP 8.3 at `C:\php83` with pdo_mysql, mbstring, openssl, gd, mysqli, fileinfo enabled.  
> MySQL 9 running on `localhost:3306` (root / no password), database `hustlehub`.

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
| Worker profile | http://localhost:8080/pages/worker_profile.php?id=2 |
| My account | http://localhost:8080/pages/my_account.php |
| Admin login | http://localhost:8080/pages/admin_login.php |
| Admin dashboard | http://localhost:8080/admin/dashboard.php |
| Server health | http://localhost:8080/admin/server_health.php |

### Database setup (first time)
```powershell
mysql -u root hustlehub < database/schema.sql
mysql -u root hustlehub < database/seed.sql
```

---

## Tech Stack
- **Frontend:** HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons
- **JavaScript:** ES6 + jQuery 3.x (dynamic filters, Ajax status updates)
- **Backend:** PHP 8.x (PDO, bcrypt, sessions, CSRF, RBAC)
- **Database:** MySQL 8.x (6 tables + audit_log, foreign keys, ENUMs)
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

## Setup on InfinityFree (or any PHP host)

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

*ITECA3-12 — Web Development and e-Commerce | NQF Level 7 | Eduvos*
