# HustleHub — Gap Analysis
**Student:** Munashe Tsikada | **Number:** EDUV4881584 | **Module:** ITECA3-12
**Last updated:** 2026-05-29 (Session 7 — D3 section added, all code verified, RBAC confirmed)

> **Legend:** ✅ Done and code-verified · ⚠️ Built, your action needed · ❌ Not yet done

---

## Deliverable 1 — Project Proposal (30 marks)
> Status: ✅ **SUBMITTED** (PDF on file)

| Section | Marks | Status |
|---|---|---|
| 1.1 Introduction | 5 | ✅ Submitted |
| 1.2 Needs/Problems | 6 | ✅ Submitted |
| 1.3 Goals/Objectives | 6 | ✅ Submitted |
| 1.4 Procedures/Scope | 5 | ✅ Submitted |
| 1.5 Timetable | 3 | ✅ Submitted |
| 1.6 Conclusion | 5 | ✅ Submitted |
| **Total** | **30** | ✅ |

---

## Deliverable 2 — Design + Build (50 marks)

### ✅ VERIFIED — All code confirmed working

Code was read and verified against the live files. No further coding needed.

| Feature | File | Verified |
|---|---|---|
| Register / Login / Logout | `pages/register.php`, `login.php`, `logout.php` | ✅ bcrypt, CSRF, `is_verified=1` on register |
| Browse + Search | `pages/browse.php`, `pages/search.php` | ✅ Only `approval_status='approved'` listings shown |
| Service Detail | `pages/service_detail.php` | ✅ |
| Book a Service (escrow) | `pages/process_booking.php` | ✅ PDO transaction: booking + transaction `held` atomically |
| PayFast IPN handler | `pages/payfast_notify.php` | ✅ Signature verified, amount checked, sets booking `confirmed` + escrow `held` |
| Booking status lifecycle | `pages/update_booking_status.php` | ✅ AJAX: pending→confirmed→in_progress (worker) → completed (client) |
| Booking cancel | `pages/booking_cancel.php` | ✅ |
| Booking confirm | `pages/booking_confirm.php` | ✅ |
| Leave Review (two-way) | `pages/leave_review.php` | ✅ Both `client` AND `worker` roles accepted; reviewee auto-detected; avg_rating recalculated |
| Raise Dispute | `pages/raise_dispute.php` | ✅ Linked to booking |
| Worker Dashboard | `pages/worker_dashboard.php` | ✅ |
| Client Dashboard | `pages/client_dashboard.php` | ✅ |
| Worker Public Profile | `pages/worker_profile.php` | ✅ |
| Create / Edit / Delete Listing | `pages/create_listing.php`, `edit_listing.php`, `delete_listing.php` | ✅ Image upload, approval required |
| My Account | `pages/my_account.php` | ✅ |
| How It Works | `pages/how_it_works.php` | ✅ Covers help/FAQ — replaces onboarding walkthrough (see note below) |
| Admin Dashboard | `admin/dashboard.php` | ✅ |
| Admin: User Management | `admin/users.php` | ✅ Super Admin only (`$isSuperAdmin` gate); role change + deactivate; audit logged |
| Admin: Listing Approval | `admin/listings.php` | ✅ Both admin + moderator; approve/reject; audit logged |
| Admin: Dispute Resolution | `admin/disputes.php` | ✅ Both admin + moderator; release/refund escrow; resolution note required |
| Admin: Audit Log | `admin/audit_log.php` | ✅ Super Admin only; last 100 entries; INSERT-only table |
| RBAC — 4 roles | `includes/auth.php` + `admin/admin_header.php` | ✅ `requireRole()` used on every page; `$isSuperAdmin` separates admin vs moderator |
| Moderator restriction | `admin/users.php`, `admin/audit_log.php` | ✅ `$isSuperAdmin = false` blocks moderator from User Mgmt and Audit Log |
| CSRF on every form | `includes/auth.php` → `verifyCsrfToken()` | ✅ All POST handlers call `verifyCsrfToken()` |
| Session timeout (30 min) | `includes/auth.php` | ✅ |
| 7-table schema | `database/schema.sql` | ✅ users, services, bookings, transactions, reviews, disputes, audit_log |
| Seed data | `database/seed.sql` | ✅ 9 test users across all 4 roles; password: `Password@123` |
| Bootstrap 5 mobile-first | `assets/css/style.css` | ✅ |
| jQuery AJAX | `assets/js/app.js` | ✅ Booking status updates + star rating UI + char counter |

> **Descoped feature — Worker onboarding walkthrough:** The D1 proposal mentioned a JavaScript step-by-step modal on first login for new workers. During development this was replaced by the dedicated `how_it_works.php` page which covers escrow, booking flow, and platform rules in full. This is a normal scope adjustment — the core C2C functionality was prioritised over a tutorial overlay. Mention this in your D2 Conclusion.

---

### YOUR ACTION REQUIRED — D2 Document Assembly (50 marks)

Everything is built. The remaining marks are document tasks only — no more coding.

#### A) Diagrams (21 marks)

| Diagram | Marks | Code status | Your action |
|---|---|---|---|
| CRC Cards | 3 | ✅ 7 cards spec'd in `DIAGRAMS.md` | Open draw.io → follow "CRC Cards" section in DIAGRAMS.md → export PNG |
| EERD | 3 | ✅ All 7 tables, 13 relationships, User Specialisation in `DIAGRAMS.md` | Open draw.io → follow "EERD" section in DIAGRAMS.md → export PNG |
| Context Diagram | 3 | ✅ 4 external entities, F1–F17 flows in `DIAGRAMS.md` | Open draw.io → follow "Context Diagram" section → export PNG |
| DFD Level 1 | 3 | ✅ 5 processes, 7 data stores in `DIAGRAMS.md` | Open draw.io → follow "DFD Level 1" section → export PNG |
| Use Case Diagram | 3 | ✅ 4 actors, full RBAC table, all relationships in `DIAGRAMS.md` | Open draw.io → follow "Use Case" section → export PNG |
| Database Design | 3 | ✅ All 7 tables in `database/schema.sql` | Open phpMyAdmin → click each table → Structure tab → screenshot |

> Use https://app.diagrams.net — free, no install needed. See "Quick draw.io Reconstruction Guide" at the bottom of `DIAGRAMS.md`.

#### B) Prototypes (12 marks)

Use Chrome DevTools (F12 → Toggle Device Toolbar) on http://localhost:8080 to capture screenshots.

| Page | Marks | Breakpoints needed |
|---|---|---|
| **Main site** | 6 | 375px (mobile), 768px (tablet), 1024px (desktop) |
| Register & Login | — | All 3 |
| Browse / Search | — | All 3 |
| Service Detail | — | All 3 |
| Book a Service | — | All 3 |
| Client Dashboard | — | All 3 |
| Worker Dashboard | — | All 3 |
| **Admin site** | 6 | All 3 breakpoints |
| Admin Dashboard | — | All 3 |
| User Management | — | All 3 |
| Listing Approval | — | All 3 |
| Dispute Resolution | — | All 3 |
| Audit Log | — | All 3 |

#### C) Coding Section (16 marks)

| Item | Marks | Where to find it |
|---|---|---|
| Platform Screenshots (annotated) | 2 | Screenshots of live pages with arrows/labels describing each element |
| Sample PHP Code | 3 | Copy lines 50–100 from `pages/process_booking.php` — the PDO transaction block |
| Sample HTML Code | 3 | Copy the booking form HTML from `pages/service_detail.php` or `create_listing.php` |
| Sample JavaScript Code | 3 | Copy the AJAX status update block from `assets/js/app.js` |
| Sample CSS Code | 3 | Copy the media query / card section from `assets/css/style.css` |
| MySQL Table Screenshots | 2 | phpMyAdmin → each of the 7 tables → Structure tab → screenshot all 7 |

#### D) Written Sections (4 marks)

| Item | Marks | Instruction |
|---|---|---|
| D2 Introduction | 2 | ≤200 words. Mention: C2C model, PHP/MySQL/Bootstrap/jQuery/PayFast, purpose of HustleHub |
| D2 Conclusion | 2 | ≤150 words. Summarise: 7 tables, 22 pages, RBAC, escrow, PayFast sandbox, diagrams drawn |

---

### D2 Mark Summary

| Section | Marks | Status |
|---|---|---|
| Introduction | 2 | ❌ Write in doc |
| Prototype — Main site | 6 | ⚠️ Site built → take screenshots |
| Prototype — Admin site | 6 | ⚠️ Site built → take screenshots |
| CRC Cards | 3 | ⚠️ Spec done → draw in draw.io |
| EERD | 3 | ⚠️ Spec done → draw in draw.io |
| Context Diagram | 3 | ⚠️ Spec done → draw in draw.io |
| DFD Level 1 | 3 | ⚠️ Spec done → draw in draw.io |
| Use Case Diagram | 3 | ⚠️ Spec done → draw in draw.io |
| Database Design | 3 | ⚠️ Schema done → MySQL screenshot |
| Platform Screenshots | 2 | ⚠️ Site built → annotated screenshots |
| PHP Code sample | 3 | ⚠️ Code exists → copy + explain |
| HTML Code sample | 3 | ⚠️ Code exists → copy + explain |
| JavaScript sample | 3 | ⚠️ Code exists → copy + explain |
| CSS Code sample | 3 | ⚠️ Code exists → copy + explain |
| MySQL Table Screenshots | 2 | ⚠️ Tables exist → screenshot 7 tables |
| Conclusion | 2 | ❌ Write in doc |
| **TOTAL** | **50** | **No new coding required — document assembly only** |

---

## Deliverable 3 — Presentation + User Manual (20 marks)
> **Due date:** Block 2, Summative Assessment Schedule
> **Hard requirement:** Site must be hosted on a live server (NOT localhost) 1 week before presentation.

### What D3 requires (from the brief)

> *"You are required to present your final project (the complete platform) to your lecturer. The web applications must be hosted using a live hosting provider. Along with the presentation, you must submit a comprehensive User Manual."*

The User Manual must contain:
1. **Technical Stack** — detailed list of technologies used
2. **System Features** — breakdown of all platform features per role
3. **Operational Guide** — step-by-step instructions for every feature, each with a screenshot

---

### 3.1 User Manual (5 marks)

Everything for the manual is already built. You write the text and take the screenshots — no coding needed.

#### Section A — Technical Stack

List the following (these are all confirmed in use):

| Technology | Version / Detail |
|---|---|
| PHP | 8.3 — server-side logic, routing, session management |
| MySQL | 9.7 — 7-table relational database |
| HTML5 | Semantic markup across all 22 pages |
| CSS3 | Custom stylesheet + Bootstrap 5.3 |
| Bootstrap | 5.3.3 — responsive grid, components |
| JavaScript | ES6 — client-side validation and UI |
| jQuery | 3.x — AJAX booking status updates, star rating UI |
| PayFast | Sandbox IPN — payment gateway with escrow |
| Apache/PHP built-in | Development server; InfinityFree for live hosting |

#### Section B — System Features by Role

| Role | Features to document |
|---|---|
| **Guest (not logged in)** | Browse listings, view service detail, how it works, register, login |
| **Client** | Book a service, pay via PayFast, track booking status, leave review, raise dispute, client dashboard, my account |
| **Worker** | Create/edit/delete listings, accept bookings, update status (confirmed → in_progress), worker dashboard, worker profile, my account |
| **Moderator** | Admin login, view/approve/reject listings, resolve disputes |
| **Admin (Super)** | Everything moderator can do + manage users (role change, deactivate) + view audit log |

#### Section C — Operational Guide (step-by-step with screenshots)

Each step below needs 1–2 screenshots. Minimum 15 screenshots total for the manual.

| # | Feature | Steps to document |
|---|---|---|
| 1 | Register as a Client | Go to /pages/register.php → fill form → select Client → submit → lands on browse.php |
| 2 | Register as a Worker | Same form → select Worker → submit → lands on worker_dashboard.php |
| 3 | Login | Go to /pages/login.php → email + password → submit |
| 4 | Browse & Search | Go to browse.php → keyword/category → service cards appear |
| 5 | View Service Detail | Click a service card → service_detail.php → shows price, worker, booking form |
| 6 | Book a Service | Fill booking date + notes → submit → redirected to PayFast sandbox |
| 7 | Client Dashboard | client_dashboard.php → shows bookings with current status, review/dispute buttons |
| 8 | Leave a Review | Click "Leave Review" on completed booking → 1–5 stars + comment → submit |
| 9 | Raise a Dispute | Click "Raise Dispute" → reason form → submit → appears in admin panel |
| 10 | Worker Dashboard | worker_dashboard.php → shows incoming bookings, status update buttons |
| 11 | Create a Listing | create_listing.php → title, description, price, category, image → submit → pending approval |
| 12 | Worker accepts booking | Worker clicks "Accept" on pending booking → status becomes confirmed |
| 13 | Worker updates to In Progress | Worker clicks "Start Job" → status becomes in_progress |
| 14 | Client marks Complete | Client clicks "Mark Complete" → status becomes completed → review prompt shown |
| 15 | Admin: Approve Listing | Admin login → admin/listings.php → click Approve on a pending listing |
| 16 | Admin: Resolve Dispute | admin/disputes.php → click Release to Worker or Refund to Client → type resolution note |
| 17 | Admin: Manage Users | admin/users.php (Super Admin only) → change role or deactivate a user |
| 18 | Admin: Audit Log | admin/audit_log.php (Super Admin only) → view last 100 admin actions with timestamp |

---

### 3.2 Presentation — Live Demo (15 marks)

The presentation is a live walkthrough of the hosted site. The site is fully built — you prepare the demo flow below.

#### Pre-presentation checklist

| Item | Action |
|---|---|
| ⚠️ Host on InfinityFree | Upload all files via FTP + import `schema.sql` + `seed.sql` into their MySQL panel |
| ⚠️ Test hosted site | Login with each role, complete a full booking flow, test admin panel |
| ⚠️ PayFast live URL | Update PayFast `return_url`, `cancel_url`, `notify_url` in `process_booking.php` to your InfinityFree domain |
| ⚠️ Uploads folder | Make `/uploads/` writable on InfinityFree (chmod 755) |
| ⚠️ config/db.php | Update host/user/password/dbname to InfinityFree database credentials |

#### Suggested demo flow (covers all mark criteria)

```
1. Open hosted site on live URL
2. Register a new Client account → show it logs in immediately
3. Browse listings → search by keyword/category
4. Open a service detail → show booking form
5. Submit a booking → PayFast sandbox → complete payment → return to site
6. Switch to Worker account → accept booking → update to in_progress
7. Switch back to Client → mark completed → leave a review
8. Raise a dispute on a different booking
9. Login as Moderator → admin panel → approve a pending listing → resolve dispute
10. Login as Super Admin → user management → audit log (show all recorded actions)
```

#### Hosting steps (InfinityFree)

1. Go to https://www.infinityfree.net → create free account
2. Create a hosting account → note the FTP credentials and MySQL credentials
3. Update `config/db.php` with InfinityFree MySQL host/user/pass/dbname
4. Update PayFast `$notifyUrl`, `$returnUrl`, `$cancelUrl` in `pages/process_booking.php` with your live domain
5. Upload all platform files via FTP (FileZilla recommended)
6. Open phpMyAdmin on InfinityFree → import `database/schema.sql` → then import `database/seed.sql`
7. Visit your live URL and test the complete booking flow

---

### D3 Mark Summary

| Section | Marks | Status |
|---|---|---|
| 3.1 User Manual | 5 | ❌ Write manual (tech stack + feature list + 18-step guide with screenshots) |
| 3.2 Presentation — live demo | 15 | ⚠️ Site built → host on InfinityFree → practice demo flow above |
| **TOTAL** | **20** | **No new coding required — hosting + writing + demo only** |

---

## Complete Priority Order — What You Do Next

Everything code-related is done. Your remaining tasks in order:

1. **Host on InfinityFree** — required for both D2 submission and D3 presentation. Do this first.
2. **Take site screenshots** — Chrome DevTools at 375px / 768px / 1024px for every page (used in both D2 prototypes and D3 User Manual)
3. **Screenshot all 7 MySQL tables** — phpMyAdmin Structure tab (used in D2 Database Design + D3 manual)
4. **Draw 5 diagrams in draw.io** — use specs in `DIAGRAMS.md`. Export each as PNG.
5. **Write D2 Introduction** (≤200 words) and **D2 Conclusion** (≤150 words)
6. **Copy 4 code samples** into D2 doc — PHP (process_booking.php), HTML (service_detail.php form), JS (app.js AJAX block), CSS (style.css media queries) — one paragraph of explanation each
7. **Write D3 User Manual** — use the 18-step Operational Guide above as your structure
8. **Practise the D3 presentation** — demo flow is written above, 10 steps covering all mark criteria
