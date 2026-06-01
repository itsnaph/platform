# HustleHub — System Diagrams Reference
## ITECA3-12 | Munashe Tsikada | EDUV4881584

> **How to use this file:** This document is the single source of truth for all five
> required D2 diagrams. Every diagram reflects the **actual live system** (OTP
> verification removed; audit_log table included). Draw these at app.diagrams.net.
>
> **Arrow/line conventions used throughout:**
> - **Solid line + filled arrowhead** → directed data flow (DFD, Context)
> - **Solid line, no arrowhead** → entity–relationship line (EERD)
> - **Crow's foot end** → "many" side of a relationship (EERD)
> - **Single vertical bar end** → "one / mandatory" side (EERD)
> - **Circle end** → "optional / zero" side (EERD)
> - **Double vertical bar end** → "exactly one, total participation" (EERD)
> - **Dashed arrow** → `<<include>>` or `<<extend>>` (Use Case only)
> - **Solid line actor → ellipse** → association (Use Case only)

---

## Diagram 1: Class Responsibility Collaborator (CRC) Cards

CRC cards describe the system's objects — what each one **knows** (data), **does** (behaviour),
and which other objects it **depends on** (collaborators). HustleHub has seven entity classes
matching the seven database tables.

> **draw.io instructions:** Use a **Table** shape (Basic → Table) with three columns.
> Row 1 = class name (merged, bold, navy `#0A2342` fill, white text).
> Row 2 headers = "RESPONSIBILITIES" | "COLLABORATORS" (grey `#F4F6F9` fill).
> Subsequent rows alternate white / light grey. No arrows on CRC cards — they are
> standalone reference tables, not flow diagrams.

---

### CRC Card 1: User

```
╔══════════════════════════════════════════════════════════════╗
║                          USER                                ║
╠══════════════════════════════════════╦═══════════════════════╣
║ RESPONSIBILITIES                     ║ COLLABORATORS         ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Stores credentials (email, bcrypt    ║ Service               ║
║ password hash, phone)                ║ (worker creates)      ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Stores role ENUM:                    ║ Booking               ║
║ worker / client / admin / moderator  ║ (as client or worker) ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Tracks is_verified flag              ║ Review                ║
║ (set to 1 on registration)           ║ (as reviewer/reviewee)║
╠══════════════════════════════════════╬═══════════════════════╣
║ Authenticates login via              ║ Dispute               ║
║ password_verify() — bcrypt           ║ (as raised_by/admin)  ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Enforces role-based page access      ║ Transaction           ║
║ (requireRole() in auth.php)          ║ (released_by — admin) ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Maintains avg_rating DECIMAL(3,2)    ║ Audit Log             ║
║ recalculated after each review       ║ (admin actions trail) ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Stores optional profile_pic path     ║                       ║
║ and bio TEXT for worker profiles     ║                       ║
╚══════════════════════════════════════╩═══════════════════════╝
```

**Information flow:** Visitor submits registration form → PHP hashes password (bcrypt cost 10)
→ INSERT users with `is_verified=1` → session variables set (`user_id`, `role`, `last_active`)
→ redirect to role-specific dashboard. On every subsequent page: `requireRole()` reads
`$_SESSION['role']` and terminates with 403 if role is insufficient.

---

### CRC Card 2: Service

```
╔══════════════════════════════════════════════════════════════╗
║                         SERVICE                              ║
╠══════════════════════════════════════╦═══════════════════════╣
║ RESPONSIBILITIES                     ║ COLLABORATORS         ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Stores listing details               ║ User (worker_id FK)   ║
║ (title, description, category)       ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Stores fixed price and               ║ Booking               ║
║ estimated duration in hours          ║ (service_id FK, 1:M)  ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Stores validated image path          ║                       ║
║ (MIME-checked, randomly named)       ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Tracks admin approval status         ║                       ║
║ (pending → approved / rejected)      ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Only approved services appear        ║                       ║
║ in browse grid (WHERE clause)        ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Filters by category ENUM and         ║                       ║
║ keyword LIKE query (PDO safe)        ║                       ║
╚══════════════════════════════════════╩═══════════════════════╝
```

**Information flow:** Worker submits listing form → Service row inserted with approval_status='pending'
→ Admin views pending queue → approves or rejects → approval_status updated → Approved services
return in browse.php SELECT query → Client browses and filters client-side via jQuery.

---

### CRC Card 3: Booking

```
╔══════════════════════════════════════════════════════════════╗
║                         BOOKING                              ║
╠══════════════════════════════════════╦═══════════════════════╣
║ RESPONSIBILITIES                     ║ COLLABORATORS         ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Records each client-service          ║ User (client_id FK)   ║
║ appointment with date and notes      ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Tracks status through defined        ║ User (worker_id FK)   ║
║ lifecycle (ENUM-controlled)          ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Status path: pending → confirmed     ║ Service (service_id)  ║
║ → in_progress → completed            ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Alternative path: any active         ║ Transaction (1:1)     ║
║ status → disputed / cancelled        ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Validates transitions server-side    ║ Review (1:2 max)      ║
║ before any status UPDATE             ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Prevents duplicate active bookings   ║ Dispute (0..1)        ║
║ (COUNT guard before INSERT)          ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Links client, worker and service     ║                       ║
║ in a single normalised record        ║                       ║
╚══════════════════════════════════════╩═══════════════════════╝
```

**Information flow:** Client submits booking form → duplicate guard query → PDO beginTransaction()
→ booking INSERT (status=pending) + transaction INSERT (escrow=held) → commit → PayFast redirect
→ PayFast ITN confirms payment → booking moves through status states via Ajax calls to
update_booking_status.php → completion triggers escrow release.

---

### CRC Card 4: Transaction

```
╔══════════════════════════════════════════════════════════════╗
║                       TRANSACTION                            ║
╠══════════════════════════════════════╦═══════════════════════╣
║ RESPONSIBILITIES                     ║ COLLABORATORS         ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Holds one financial record           ║ Booking               ║
║ per booking (UNIQUE booking_id)      ║ (booking_id FK, 1:1)  ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Tracks escrow_status                 ║ User (admin/client    ║
║ (held / released / refunded)         ║ who triggers release) ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Records PayFast pf_payment_id        ║                       ║
║ for payment audit trail              ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Records which user released/         ║                       ║
║ refunded escrow (released_by FK)     ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Validates state: held → released     ║                       ║
║ OR held → refunded (never backward)  ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Ensures dispute cannot be raised     ║                       ║
║ once escrow is already released      ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Timestamps released_at for           ║                       ║
║ financial audit records              ║                       ║
╚══════════════════════════════════════╩═══════════════════════╝
```

**Information flow (PayFast):** process_booking.php → INSERT transaction (escrow=held) →
redirect to PayFast → PayFast ITN POST to payfast_notify.php → ITN validated (signature check)
→ transaction updated with pf_payment_id → booking status → confirmed. Then: client confirms
completion → update_booking_status.php → escrow_status → released + released_by set.
OR: admin resolves dispute → escrow → released or refunded.

---

### CRC Card 5: Review

```
╔══════════════════════════════════════════════════════════════╗
║                          REVIEW                              ║
╠══════════════════════════════════════╦═══════════════════════╣
║ RESPONSIBILITIES                     ║ COLLABORATORS         ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Stores star rating 1–5               ║ Booking               ║
║ (CHECK constraint enforced in DB)    ║ (booking_id FK)       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Stores written comment (up to 500    ║ User (reviewer_id FK) ║
║ characters, XSS-escaped on output)   ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Links to booking that generated it   ║ User (reviewee_id FK) ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Enforces one review per party per    ║                       ║
║ booking via UNIQUE(booking_id,       ║                       ║
║ reviewer_id) DB constraint           ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Only available after booking         ║                       ║
║ status = 'completed'                 ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Triggers recalculation of worker     ║                       ║
║ avg_rating via UPDATE users          ║                       ║
╚══════════════════════════════════════╩═══════════════════════╝
```

**Information flow:** Booking reaches 'completed' → client dashboard shows "Leave Review" link
→ leave_review.php checks booking belongs to client AND status=completed AND no existing review
→ INSERT reviews row → UPDATE users SET avg_rating = (SELECT AVG(rating)) WHERE id=reviewee_id.

---

### CRC Card 6: Dispute

```
╔══════════════════════════════════════════════════════════════╗
║                         DISPUTE                              ║
╠══════════════════════════════════════╦═══════════════════════╣
║ RESPONSIBILITIES                     ║ COLLABORATORS         ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Records which booking is disputed    ║ Booking (booking_id)  ║
║ and by whom (raised_by FK)           ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Stores reason text provided          ║ User (raised_by FK)   ║
║ by the disputing party               ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Tracks status                        ║ User (admin_id FK)    ║
║ (open → under_review → resolved)     ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Links to admin who resolved it       ║ Transaction           ║
║ (admin_id FK, nullable)              ║ (status updated on    ║
║                                      ║  resolution)          ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Requires non-empty resolution_note   ║                       ║
║ before admin can resolve             ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Records resolved_at timestamp        ║                       ║
║ as audit trail evidence              ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Can only be raised if transaction    ║                       ║
║ escrow_status = 'held'               ║                       ║
╚══════════════════════════════════════╩═══════════════════════╝
```

**Information flow:** Client clicks "Dispute" on in_progress booking → raise_dispute.php checks
escrow=held → INSERT disputes (status=open) + UPDATE bookings (status=disputed) → Admin sees
open disputes list → clicks "View Detail" → reads booking history and dispute reason → writes
resolution_note (required server-side) → clicks "Release" or "Refund" → disputes.php wraps
three UPDATEs in PDO transaction: transaction escrow status, booking status, dispute resolved.

---

### CRC Card 7: Audit Log

```
╔══════════════════════════════════════════════════════════════╗
║                        AUDIT LOG                             ║
╠══════════════════════════════════════╦═══════════════════════╣
║ RESPONSIBILITIES                     ║ COLLABORATORS         ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Records every significant admin      ║ User (admin_id FK)    ║
║ action with a timestamp              ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Stores action label VARCHAR(60)      ║                       ║
║ (e.g. 'listing_approved')            ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Stores target_type VARCHAR(30) and   ║                       ║
║ target_id (which row was affected)   ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Stores optional notes TEXT           ║                       ║
║ (admin comment on the action)        ║                       ║
╠══════════════════════════════════════╬═══════════════════════╣
║ Immutable — rows are INSERT-only,    ║                       ║
║ never updated or deleted             ║                       ║
╚══════════════════════════════════════╩═══════════════════════╝
```

**Information flow:** Admin performs any action (approve listing, resolve dispute, deactivate user)
→ audit_log INSERT with admin_id + action label + target_type + target_id + timestamp →
admin/audit_log.php runs SELECT of all entries ordered by created_at DESC → read-only display.

---

## Diagram 2: Enhanced Entity Relationship Diagram (EERD)

> **draw.io instructions:**
> 1. Open app.diagrams.net → New → Blank Diagram.
> 2. Enable the **Entity Relation** shape library (search "ERD" in the left panel shapes search).
> 3. **Entity** = rectangle (single border). **Attribute** = ellipse connected by a solid line.
> 4. **Primary key attributes** = underlined text inside the ellipse.
> 5. **Multi-valued attribute** = double-border ellipse (avg_rating computed from reviews).
> 6. **Relationship diamond** = rhombus/diamond shape labelled with the verb.
> 7. **Crow's foot lines:**
>    - Mandatory one end: single vertical bar `|` — set connector end to `ERDOne`
>    - Mandatory many end: crow's foot `<` — set connector end to `ERDMany`
>    - Optional (zero-or-one): circle + bar `o|` — set to `ERDZeroToOne`
>    - Optional many: circle + crow's foot `o<` — set to `ERDZeroToMany`
> 8. All connectors are **solid lines** — no dashed lines in an EERD (dashed = derived attribute only).
> 9. **User specialisation** (enhanced element): draw a circle between the parent USER entity
>    and the three subtype rectangles. Label it `d` (disjoint). Lines from circle to subtypes
>    are solid with no arrowhead.

---

### Overview

The EERD models HustleHub's **seven** database tables with full attribute listings, PK/FK
notation, cardinality, and one enhanced element: User specialisation (disjoint, total).

### User Specialisation (Enhanced Element)

```
                        ┌─────────────────────┐
                        │        USER          │  ← double border = total participation
                        │─────────────────────│
                        │ PK id               │
                        │ full_name           │
                        │ email (UNIQUE)      │
                        │ phone               │
                        │ password VARCHAR(255)│
                        │ role ENUM(4 values) │  ← discriminator attribute
                        │ is_verified TINY(1) │
                        │ otp_code            │  ← stored in schema; currently inactive
                        │ otp_expires         │  ← stored in schema; currently inactive
                        │ profile_pic         │
                        │ bio TEXT            │
                        │ avg_rating DEC(3,2) │  ← derived: AVG(reviews.rating)
                        │ created_at          │
                        │ updated_at          │
                        └──────────┬──────────┘
                                   │
                              ◯ d  │   ← disjoint specialisation circle
              ┌────────────────────┼────────────────────┐
              ▼                    ▼                     ▼
    ┌──────────────────┐ ┌──────────────────┐ ┌──────────────────────┐
    │     WORKER       │ │     CLIENT       │ │  ADMIN / MODERATOR   │
    │  role='worker'   │ │  role='client'   │ │ role='admin'/'mod'   │
    │  Creates Services│ │  Makes Bookings  │ │  Manages System      │
    └──────────────────┘ └──────────────────┘ └──────────────────────┘
```

---

### All Relationships with Crow's Foot Notation

```
Notation key:   ||  = mandatory one       |<  = mandatory many
                o|  = optional one        o<  = optional many

users  ||────────────────────────|<  services
       (1 worker creates 0..M services — ON DELETE CASCADE)

users  ||────────────────────────|<  bookings   [as client_id]
       (1 client places 0..M bookings — ON DELETE CASCADE)

users  ||────────────────────────|<  bookings   [as worker_id]
       (1 worker receives 0..M bookings — ON DELETE CASCADE)

services  ||─────────────────────|<  bookings
          (1 service is referenced in 0..M bookings — ON DELETE CASCADE)

bookings  ||─────────────────────||  transactions
          (1 booking has EXACTLY 1 transaction — UNIQUE FK — ON DELETE CASCADE)

bookings  ||─────────────────────o<  reviews
          (1 booking has 0..2 reviews — UNIQUE(booking_id, reviewer_id))

users  ||────────────────────────|<  reviews   [as reviewer_id]
       (1 user writes 0..M reviews)

users  ||────────────────────────|<  reviews   [as reviewee_id]
       (1 user receives 0..M reviews)

bookings  ||─────────────────────o|  disputes
          (1 booking has 0 or 1 dispute — ON DELETE CASCADE)

users  ||────────────────────────|<  disputes  [as raised_by]
       (1 user raises 0..M disputes — ON DELETE CASCADE)

users  o|────────────────────────o<  disputes  [as admin_id — nullable]
       (0 or 1 admin resolves 0..M disputes — ON DELETE SET NULL)

users  o|────────────────────────o<  transactions  [as released_by — nullable]
       (0 or 1 user releases 0..M transactions — ON DELETE SET NULL)

users  ||────────────────────────|<  audit_log  [as admin_id]
       (1 admin produces 0..M audit entries — ON DELETE CASCADE)
```

---

### Full Table Schemas (EERD Attribute Detail)

> In draw.io: each table's attributes appear as ellipses connected to their entity rectangle
> by solid lines. **Underline** primary key text. Use a **double-border ellipse** for `avg_rating`
> (derived). Columns marked NULL are optional attributes — draw with a dashed-border ellipse.

**TABLE: users**
| Column | Type | Constraints |
|---|---|---|
| **id** | INT UNSIGNED | PK AUTO_INCREMENT |
| full_name | VARCHAR(120) | NOT NULL |
| email | VARCHAR(180) | NOT NULL UNIQUE |
| phone | VARCHAR(20) | NULL |
| password | VARCHAR(255) | NOT NULL (bcrypt hash) |
| role | ENUM('worker','client','admin','moderator') | NOT NULL DEFAULT 'client' |
| is_verified | TINYINT(1) | NOT NULL DEFAULT 1 |
| otp_code | VARCHAR(10) | NULL (legacy field — inactive) |
| otp_expires | DATETIME | NULL (legacy field — inactive) |
| profile_pic | VARCHAR(255) | NULL |
| bio | TEXT | NULL |
| avg_rating | DECIMAL(3,2) | NOT NULL DEFAULT 0.00 (derived) |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP |

**TABLE: services**
| Column | Type | Constraints |
|---|---|---|
| **id** | INT UNSIGNED | PK AUTO_INCREMENT |
| worker_id | INT UNSIGNED | FK → users.id ON DELETE CASCADE |
| title | VARCHAR(160) | NOT NULL |
| description | TEXT | NOT NULL |
| category | ENUM('cleaning','gardening','painting','moving','repairs','other') | NOT NULL |
| price | DECIMAL(10,2) | NOT NULL |
| duration_hours | TINYINT UNSIGNED | NOT NULL DEFAULT 1 |
| image_path | VARCHAR(255) | NULL |
| approval_status | ENUM('pending','approved','rejected') | NOT NULL DEFAULT 'pending' |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP |

**TABLE: bookings**
| Column | Type | Constraints |
|---|---|---|
| **id** | INT UNSIGNED | PK AUTO_INCREMENT |
| service_id | INT UNSIGNED | FK → services.id ON DELETE CASCADE |
| client_id | INT UNSIGNED | FK → users.id ON DELETE CASCADE |
| worker_id | INT UNSIGNED | FK → users.id ON DELETE CASCADE |
| booking_date | DATE | NOT NULL |
| status | ENUM('pending','confirmed','in_progress','completed','disputed','cancelled') | NOT NULL DEFAULT 'pending' |
| notes | TEXT | NULL |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP |

**TABLE: transactions**
| Column | Type | Constraints |
|---|---|---|
| **id** | INT UNSIGNED | PK AUTO_INCREMENT |
| booking_id | INT UNSIGNED | FK → bookings.id **UNIQUE** ON DELETE CASCADE |
| amount | DECIMAL(10,2) | NOT NULL |
| escrow_status | ENUM('held','released','refunded') | NOT NULL DEFAULT 'held' |
| payfast_id | VARCHAR(100) | NULL (PayFast pf_payment_id) |
| released_by | INT UNSIGNED | FK → users.id ON DELETE SET NULL (nullable) |
| released_at | DATETIME | NULL |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP |

**TABLE: reviews**
| Column | Type | Constraints |
|---|---|---|
| **id** | INT UNSIGNED | PK AUTO_INCREMENT |
| booking_id | INT UNSIGNED | FK → bookings.id ON DELETE CASCADE |
| reviewer_id | INT UNSIGNED | FK → users.id ON DELETE CASCADE |
| reviewee_id | INT UNSIGNED | FK → users.id ON DELETE CASCADE |
| rating | TINYINT UNSIGNED | NOT NULL CHECK (rating BETWEEN 1 AND 5) |
| comment | TEXT | NULL |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP |
| | | UNIQUE KEY (booking_id, reviewer_id) |

**TABLE: disputes**
| Column | Type | Constraints |
|---|---|---|
| **id** | INT UNSIGNED | PK AUTO_INCREMENT |
| booking_id | INT UNSIGNED | FK → bookings.id ON DELETE CASCADE |
| raised_by | INT UNSIGNED | FK → users.id ON DELETE CASCADE |
| reason | TEXT | NOT NULL |
| status | ENUM('open','under_review','resolved') | NOT NULL DEFAULT 'open' |
| admin_id | INT UNSIGNED | FK → users.id ON DELETE SET NULL (nullable) |
| resolution_note | TEXT | NULL |
| resolved_at | DATETIME | NULL |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP |

**TABLE: audit_log**
| Column | Type | Constraints |
|---|---|---|
| **id** | INT UNSIGNED | PK AUTO_INCREMENT |
| admin_id | INT UNSIGNED | FK → users.id ON DELETE CASCADE |
| action | VARCHAR(60) | NOT NULL |
| target_type | VARCHAR(30) | NULL |
| target_id | INT UNSIGNED | NULL |
| notes | TEXT | NULL |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |

---

## Diagram 3: Context Diagram (Level 0 DFD)

> **draw.io instructions:**
> - External entities: **rectangle** (Flowchart → Process shape, or just a plain rectangle).
>   Label each with the actor name. Place Workers + Client on the left; Admin on the right;
>   PayFast at the top or bottom.
> - System process: **circle** or rounded rectangle in the centre labelled "0 / HUSTLEHUB SYSTEM".
> - All arrows: **solid lines with a filled arrowhead** pointing in the direction of data flow.
> - Label every arrow with the data it carries (keep labels concise — 3–6 words).
> - No internal processes or data stores in a Level 0 diagram.

The Context Diagram defines the entire HustleHub system as a **single process bubble** (Level 0).
No internal processing detail is shown — only external entities and the data that flows between
them and the system boundary. There are **four** external entities: Worker, Client, Admin,
and PayFast Payment Gateway.

```
┌──────────────┐  Service listing data ─────────►│                         ┌───────────────────┐
│              │  Accept / start booking ────────►│  ◄── Dispute decision   │                   │
│    WORKER    │                                  │  ◄── Listing decision   │  ADMINISTRATOR    │
│              │  ◄── Booking notification        │  ◄── Manage users       │  (admin/mod)      │
│              │  ◄── Escrow released notice      │  ──► Open disputes ────►│                   │
└──────────────┘                                  │  ──► Pending listings ──►│                   │
                                                  │  ──► Audit log ─────────►│                   │
                                                  │                         └───────────────────┘
                         ┌────────────────────────┴────────────────────────┐
                         │                   0                              │
                         │           HUSTLEHUB SYSTEM                       │
                         │        C2C Service Marketplace                   │
                         └────────────────────────┬────────────────────────┘
                                                  │
┌──────────────┐  Register / Login ──────────────►│                         ┌───────────────────┐
│              │  Booking request + notes ────────►│ ──► Payment initiation  │                   │
│    CLIENT    │  Job completion confirm ─────────►│ ◄── ITN confirmation   │  PAYFAST           │
│              │  Dispute reason ─────────────────►│     (pf_payment_id)    │  (Sandbox)        │
│              │  Star rating + review ───────────►│                         │                   │
│              │  ◄── Booking confirmation         │                         └───────────────────┘
│              │  ◄── Dispute status update        │
└──────────────┘
```

### Data Flow Descriptions

| Flow | From | To | Data |
|---|---|---|---|
| F1 | Worker | System | Service listing (title, price, category, description, image) |
| F2 | Worker | System | Booking status update (accept / mark started) |
| F3 | System | Worker | Booking notification, escrow release confirmation |
| F4 | Client | System | Registration form (name, email, password, role) |
| F5 | Client | System | Login credentials (email, password) |
| F6 | Client | System | Booking request (service_id, date, notes) + PayFast payment |
| F7 | Client | System | Job completion confirmation |
| F8 | Client | System | Dispute reason text |
| F9 | Client | System | Star rating (1–5) + review comment |
| F10 | System | Client | Booking confirmation + transaction ID |
| F11 | System | Client | Dispute status update / resolution outcome |
| F12 | Admin | System | Listing approval or rejection decision |
| F13 | Admin | System | Dispute resolution (release / refund) + resolution note |
| F14 | Admin | System | User management action (role change / deactivate) |
| F15 | System | Admin | Pending listings queue, open disputes, audit log, user list |
| F16 | System | PayFast | Payment initiation (amount, booking_id, merchant data) |
| F17 | PayFast | System | ITN confirmation (pf_payment_id, payment_status=COMPLETE) |

---

## Diagram 4: Data Flow Diagram — Level 1

> **draw.io instructions:**
> - **External entity** = rectangle (plain, no rounded corners). Label with actor name.
> - **Process** = rounded rectangle (or circle). Label: number in top-left corner, name centred.
>   Use orange `#FF6B35` fill for escrow-critical processes (3.0, 4.0).
> - **Data store** = open-ended rectangle (two parallel horizontal lines, open on left and right).
>   Label: D-number + table name.
> - **All arrows** = solid lines with filled arrowhead pointing in the direction of data flow.
>   Label every arrow. Arrows between an external entity and a process, or between a process
>   and a data store. **No arrow goes directly between two data stores or two external entities.**
> - Arrow between two processes is allowed only when the first must feed data into the second
>   (e.g. approved services flowing from 2.0 into 3.0).

Level 1 decomposes the HustleHub system into **five numbered processes**, data flows between
them, external entities, and **seven data stores** (one per database table).

```
EXTERNAL ENTITIES        PROCESSES                      DATA STORES
(rectangles)             (rounded rectangles)           (open rectangles)

WORKER ─────────────►  ┌────────────────────┐
CLIENT ─────────────►  │  1.0               │ ◄─── D1: users (read: validate)
                       │  USER AUTH         │ ───► D1: users (write: register)
                       └─────────────────-──┘
                              ▲ session validated → feeds into 2.0, 3.0, 4.0, 5.0

WORKER ─────────────►  ┌────────────────────┐
ADMIN  ─────────────►  │  2.0               │ ◄─── D2: services (read)
                       │  SERVICE           │ ───► D2: services (write)
                       │  MANAGEMENT        │ ───► D7: audit_log (write: approval action)
                       └────────┬───────────┘
                                │ approved service data
                                ▼
CLIENT ─────────────►  ┌────────────────────┐
PAYFAST ────────────►  │  3.0               │ ◄─── D3: bookings (read/write)
                       │  BOOKING &         │ ───► D3: bookings (write)
                       │  ESCROW            │ ───► D4: transactions ★ (write: held/released)
                       └────────┬───────────┘
                                │ dispute raised
                                ▼
CLIENT ─────────────►  ┌────────────────────┐
WORKER ─────────────►  │  4.0               │ ◄─── D5: disputes (read)
ADMIN  ─────────────►  │  DISPUTE           │ ───► D5: disputes (write: resolved)
                       │  RESOLUTION        │ ───► D4: transactions ★ (write: released/refunded)
                       │                    │ ───► D3: bookings (write: status update)
                       └────────────────────┘ ───► D7: audit_log (write: resolution action)

CLIENT ─────────────►  ┌────────────────────┐
WORKER ─────────────►  │  5.0               │ ◄─── D3: bookings (read: verify completed)
                       │  REVIEWS           │ ───► D6: reviews (write)
                       │                    │ ───► D1: users (write: update avg_rating)
                       └────────────────────┘

★ D4: transactions = escrow-critical data store
```

### Data Stores

| ID | Table | Description |
|---|---|---|
| D1 | users | All users across all roles; bcrypt passwords; avg_rating |
| D2 | services | Worker listings; approval_status controls visibility |
| D3 | bookings | Every booking; status ENUM drives the lifecycle |
| D4 | transactions | One per booking; escrow_status: held → released / refunded |
| D5 | disputes | Raised disputes with admin resolution note and outcome |
| D6 | reviews | Post-completion star ratings; UNIQUE per party per booking |
| D7 | audit_log | Immutable admin action trail; INSERT-only |

### Process Descriptions

**1.0 User Authentication**
- Receives registration data (name, email, password, role) from Worker or Client
- Writes new user to D1 with bcrypt-hashed password, `is_verified=1` immediately
- On login: reads D1, validates via `password_verify()`, regenerates session ID, writes role to session
- Every subsequent page checks role via `requireRole()` reading `$_SESSION['role']`

**2.0 Service Management**
- Worker submits listing form → writes to D2 with `approval_status='pending'`
- Admin views pending queue from D2 → sends approve/reject → D2 updated
- Approval action → D7 (audit_log) INSERT with admin_id and action label
- Approved services readable by 3.0 (browse / booking flow)

**3.0 Booking and Escrow**
- Client selects service (from 2.0 approved output), submits date and notes
- Duplicate booking guard: SELECT COUNT from D3 → reject if active booking exists
- PDO transaction: INSERT D3 (status=`pending`) + INSERT D4 (escrow_status=`held`)
- Redirects to PayFast → PayFast ITN POST received → signature validated
- D4 updated with `payfast_id`; D3 status → `confirmed`
- Worker updates D3: `confirmed` → `in_progress`
- Client confirms completion: D3 → `completed`, D4 escrow_status → `released`, `released_by` set
- If dispute raised: D3 → `disputed`, data passed to 4.0

**4.0 Dispute Resolution**
- Admin reads open disputes from D5 + booking history from D3
- Admin writes `resolution_note` (required field validated server-side)
- Release path: D4 → `released`, D3 → `completed`, D5 → `resolved`, D7 INSERT
- Refund path: D4 → `refunded`, D3 → `cancelled`, D5 → `resolved`, D7 INSERT
- All three D-store writes wrapped in single PDO transaction (atomic)

**5.0 Reviews**
- Available only once D3 booking status = `completed`
- Client or Worker submits rating (1–5) + comment
- UNIQUE(booking_id, reviewer_id) guard prevents duplicate reviews
- INSERT D6; then UPDATE D1 `avg_rating` = SELECT AVG(rating) WHERE reviewee_id = target user

---

## Diagram 5: Use Case Diagram

> **draw.io instructions:**
> - **Actor** = stick figure (UML shape library → Actor). Place Worker on far left, Client
>   below Worker, Admin on far right.
> - **System boundary** = large rectangle enclosing all use cases. Label "HustleHub System"
>   at the top.
> - **Use case** = ellipse. Label with the action. Group logically inside the boundary.
> - **Association** (actor uses a use case) = **solid line, no arrowhead**.
> - **<<include>>** = **dashed arrow pointing TO the included use case**, label `<<include>>`.
> - **<<extend>>** = **dashed arrow pointing FROM the extension TO the base use case**,
>   label `<<extend>>`.
> - **Generalisation** (sub-actor inherits) = solid line with hollow triangle at parent.
>   Use this for Moderator ← Admin.

### Actors and System Boundary

```
                    ┌─────────────────────────────────────────────────────────────────┐
                    │                    HustleHub System                              │
                    │                                                                  │
 🧑 WORKER          │  SHARED (Worker + Client):                                       │
    │               │  ◉ Register                                                      │
    ├──────────────►│  ◉ Login         ◄──────────── <<include>> from Register        │
    │               │  ◉ Logout                                                        │
    │               │                                                                  │
    │  WORKER ONLY: │                                                                  │
    ├──────────────►│  ◉ Create Service Listing                                        │
    ├──────────────►│  ◉ Edit Service Listing                                          │
    ├──────────────►│  ◉ Delete Service Listing                                        │
    ├──────────────►│  ◉ View Incoming Bookings (Worker Dashboard)                     │
    ├──────────────►│  ◉ Accept Booking   ◄──────── <<include>> Login                  │
    ├──────────────►│  ◉ Mark Job Started                                              │
    └──────────────►│  ◉ View Worker Profile (public — avg_rating + reviews)           │
                    │                                                                  │
 🧑 CLIENT          │  CLIENT ONLY:                                                    │
    │               │  ◉ Browse Services                                               │
    ├──────────────►│  ◉ Search / Filter by Category or Price                          │
    ├──────────────►│  ◉ View Service Detail                                           │
    ├──────────────►│  ◉ Book Service ───────────── <<include>> Login                  │
    │               │        │                                                         │
    │               │        └──────────────────── <<include>> Pay via PayFast         │
    ├──────────────►│  ◉ Track Booking Status (Client Dashboard)                       │
    ├──────────────►│  ◉ Confirm Job Completion ── triggers escrow release             │
    ├──────────────►│  ◉ Leave Review (1–5 stars) ◄ <<extend>> from Confirm           │
    ├──────────────►│  ◉ Raise Dispute ◄──────────── <<extend>> from Track Status     │
    └──────────────►│  ◉ View Booking History                                          │
                    │                                                                  │
                    │  ┌─ Super Admin only (role='admin') ──────────────────────────┐ │
 🧑 ADMIN           │  │  ◉ Manage Users (change role / deactivate)                 │ │
    │               │  │  ◉ View Audit Log                                          │ │
    │               │  └────────────────────────────────────────────────────────────┘ │
    │               │                                                                  │
    │  ADMIN+MOD:   │                                                                  │
    ├──────────────►│  ◉ Login — Admin Portal (separate requireRole check)             │
    ├──────────────►│  ◉ Approve Listing ─────────────── <<include>> Write Audit Log  │
    ├──────────────►│  ◉ Reject Listing ──────────────── <<include>> Write Audit Log  │
    ├──────────────►│  ◉ View Open Disputes                                            │
    ├──────────────►│  ◉ Review Dispute Detail                                         │
    ├──────────────►│  ◉ Release Escrow to Worker ─── <<include>> Write Resolution    │
    └──────────────►│  ◉ Refund Escrow to Client ──── <<include>> Write Resolution    │
                    │                                                                  │
 🧑 MODERATOR       │  (inherits all Admin+Mod use cases above via generalisation)     │
    │               │  Cannot access: Manage Users, View Audit Log                    │
    └──────────────►│                                                                  │
                    └─────────────────────────────────────────────────────────────────┘
```

### Relationship Key

| Relationship | Meaning in HustleHub |
|---|---|
| `<<include>>` Register → Login | Registration calls the Login flow to set the session immediately |
| `<<include>>` Book Service → Login | Must be logged in as client to book |
| `<<include>>` Book Service → Pay via PayFast | Payment is mandatory as part of booking |
| `<<include>>` Approve/Reject Listing → Write Audit Log | Every listing decision is logged |
| `<<include>>` Release/Refund → Write Resolution | Resolution note is mandatory before escrow action |
| `<<extend>>` Raise Dispute from Track Status | Dispute is an optional extension of the booking journey |
| `<<extend>>` Leave Review from Confirm | Review is optional after job completion |
| Generalisation: Moderator ← Admin | Moderator inherits all Admin+Mod use cases; cannot access User Mgmt or Audit Log |

### RBAC Summary

| Role | Can Access |
|---|---|
| Worker | Register, login, listing management (create/edit/delete), worker dashboard, accept booking, mark job started, view profile |
| Client | Register, login, browse services, filter/search, view detail, book + pay, track status, confirm completion, leave review, raise dispute, booking history |
| Moderator | Admin portal login, disputes panel (view + release/refund), listings panel (approve/reject) |
| Admin (Super) | All Moderator permissions + user management (role change / deactivate) + view audit log |

---

## Quick draw.io Reconstruction Guide

All diagrams are built at **https://app.diagrams.net**

### Step-by-step per diagram

**Diagram 1 — CRC Cards (7 cards)**
1. Blank diagram. Enable Basic shapes.
2. For each card: drag in a **Table** shape. Set 3 columns.
3. Row 1 = class name, merged, fill `#0A2342`, white text, bold.
4. Row 2 = headers "RESPONSIBILITIES" | "COLLABORATORS", fill `#F4F6F9`.
5. Add one row per responsibility entry. No connecting arrows between cards.

**Diagram 2 — EERD**
1. Blank diagram. Enable **Entity Relation** library.
2. Place 7 entity rectangles: users, services, bookings, transactions, reviews, disputes, audit_log.
3. Add attribute ellipses. Underline PK text. Double-border ellipse for `avg_rating` (derived).
4. Add relationship diamonds between connected entities.
5. Connect with crow's foot lines: set connector Start/End style in Format panel:
   - "One" end = `ERDOne`; "Many" end = `ERDMany`; "Zero-to-One" = `ERDZeroToOne`; "Zero-to-Many" = `ERDZeroToMany`
6. Draw User specialisation: circle between USER and three subtype boxes, label `d`.

**Diagram 3 — Context Diagram**
1. Blank diagram. Enable Flowchart shapes.
2. Centre: large circle labelled `0 / HUSTLEHUB SYSTEM`.
3. Surrounding rectangles for 4 external entities: Worker (left), Client (left-below), Admin (right), PayFast (bottom).
4. Add solid directed arrows (filled arrowhead) between entities and the centre circle.
5. Label every arrow from the F1–F17 table above.

**Diagram 4 — DFD Level 1**
1. Blank diagram. Enable Flowchart shapes.
2. External entity rectangles on left/right margins.
3. Process rounded rectangles numbered 1.0–5.0 in centre column.
4. Data store open-rectangles (two parallel lines) on right: D1–D7.
5. Solid arrows with labels for every data flow.
6. Color processes 3.0 and 4.0 orange `#FF6B35` (escrow-critical).

**Diagram 5 — Use Case**
1. Blank diagram. Enable UML shapes.
2. Draw large system boundary rectangle, label "HustleHub System".
3. Place stick-figure actors: Worker (left), Client (left-below), Admin (right), Moderator (right-below).
4. Draw use case ellipses inside the boundary — group by actor section.
5. Solid lines (no arrowheads) for actor ↔ use case associations.
6. Dashed arrows with `<<include>>` or `<<extend>>` labels for dependencies.
7. Hollow triangle (generalisation line) from Moderator to Admin actor.

### Colour palette (consistent across all diagrams)

| Colour | Hex | Usage |
|---|---|---|
| Navy | `#0A2342` | Headers, borders, actor labels |
| Orange | `#FF6B35` | Escrow processes, CTA highlights |
| White | `#FFFFFF` | Text on dark backgrounds |
| Light grey | `#F4F6F9` | Alternate rows, subtype boxes |
| Mid grey | `#8896AB` | Secondary labels, data store fills |

---

## Project Completion Status — D2 Gap Analysis

> **Last updated:** 2026-05-29
> **Legend:** ✅ Done and verified · ⚠️ Built but needs documentation/screenshot · ❌ Not yet done

---

### Section 2.1 — Introduction (2 marks)

| Item | Marks | Status | Evidence |
|---|---|---|---|
| Introduction ≤200 words, C2C model, tech stack overview | 2 | ❌ Write for submission doc | Must be in the Word/PDF document |

---

### Section 2.2 — Prototyping (12 marks)

> Screenshots must show 3 breakpoints: **mobile (320–375px)**, **tablet (768px)**, **desktop (1024px+)**
> Use Chrome DevTools → Toggle Device Toolbar to capture each.

#### Main Website (6 marks)

| Page | Built? | Screenshot taken? |
|---|---|---|
| Register / Login | ✅ `pages/register.php`, `login.php` | ❌ Need screenshot |
| Browse Services | ✅ `pages/browse.php` | ❌ Need screenshot |
| Service Detail | ✅ `pages/service_detail.php` | ❌ Need screenshot |
| Book Service (process_booking) | ✅ `pages/process_booking.php` | ❌ Need screenshot |
| Client Dashboard | ✅ `pages/client_dashboard.php` | ❌ Need screenshot |
| Worker Dashboard | ✅ `pages/worker_dashboard.php` | ❌ Need screenshot |
| Worker Public Profile | ✅ `pages/worker_profile.php` | ❌ Need screenshot |
| Create Listing | ✅ `pages/create_listing.php` | ❌ Need screenshot |
| How It Works | ✅ `pages/how_it_works.php` | ❌ Need screenshot |

#### Admin Website (6 marks)

| Page | Built? | Screenshot taken? |
|---|---|---|
| Admin Dashboard | ✅ `admin/dashboard.php` | ❌ Need screenshot |
| User Management | ✅ `admin/users.php` | ❌ Need screenshot |
| Listing Approval | ✅ `admin/listings.php` | ❌ Need screenshot |
| Dispute Resolution | ✅ `admin/disputes.php` | ❌ Need screenshot |
| Audit Log | ✅ `admin/audit_log.php` | ❌ Need screenshot |

---

### Section 2.3 — Design Diagrams (21 marks)

| Diagram | Marks | Spec in DIAGRAMS.md? | Drawn in draw.io? |
|---|---|---|---|
| CRC Cards (7 cards) | 3 | ✅ Cards 1–7 complete, all accurate | ❌ Not yet drawn |
| EERD (7 tables, attributes, FK, cardinality) | 3 | ✅ User specialisation + all 13 relationships + full schemas | ❌ Not yet drawn |
| Context Diagram (Level 0) | 3 | ✅ 4 external entities, F1–F17 flows, no OTP/Email | ❌ Not yet drawn |
| DFD Level 1 (5 processes, 7 data stores) | 3 | ✅ Processes 1.0–5.0, D1–D7, all descriptions | ❌ Not yet drawn |
| Use Case Diagram | 3 | ✅ 4 actors, all use cases, <<include>>/<<extend>>, RBAC | ❌ Not yet drawn |
| Database Design (MySQL schema screenshots) | 3 | ✅ All 7 table schemas in Full Table Schemas section | ❌ Need MySQL screenshots |

> **Action for diagrams:** Open https://app.diagrams.net → follow per-diagram steps in "Quick draw.io Reconstruction Guide" above.
> **Action for DB:** Open phpMyAdmin (or run `DESCRIBE tablename;` in MySQL Workbench) → screenshot each of the 7 tables.

---

### Section 2.4 — Coding (16 marks)

| Item | Marks | Code exists? | In submission doc? |
|---|---|---|---|
| Platform Screenshots (annotated) | 2 | ✅ Site is running on localhost:8080 | ❌ Need annotated screenshots |
| Sample PHP Code — escrow + PDO transaction | 3 | ✅ `pages/process_booking.php` lines ~40–90 | ❌ Paste + explain in doc |
| Sample HTML Code — booking or listing form | 3 | ✅ `pages/process_booking.php` or `create_listing.php` | ❌ Paste + explain in doc |
| Sample JavaScript Code — AJAX status update | 3 | ✅ `assets/js/app.js` booking status AJAX block | ❌ Paste + explain in doc |
| Sample CSS Code — mobile-first responsive | 3 | ✅ `assets/css/style.css` media queries section | ❌ Paste + explain in doc |
| Sample MySQL Table Screenshots | 2 | ✅ 7 tables in `database/schema.sql` | ❌ Need DB screenshots |

---

### Section 2.5 — Conclusion (2 marks)

| Item | Marks | Status |
|---|---|---|
| Conclusion ≤150 words — design/development phase summary | 2 | ❌ Write for submission doc |

---

### Full D2 Mark Summary

| Section | Marks Available | Status |
|---|---|---|
| 2.1 Introduction | 2 | ❌ Not written |
| 2.2 Prototype — Main site | 6 | ⚠️ Built; screenshots needed |
| 2.2 Prototype — Admin site | 6 | ⚠️ Built; screenshots needed |
| 2.3 CRC Cards | 3 | ⚠️ Spec done in DIAGRAMS.md; draw.io export needed |
| 2.3 EERD | 3 | ⚠️ Spec done in DIAGRAMS.md; draw.io export needed |
| 2.3 Context Diagram | 3 | ⚠️ Spec done in DIAGRAMS.md; draw.io export needed |
| 2.3 DFD Level 1 | 3 | ⚠️ Spec done in DIAGRAMS.md; draw.io export needed |
| 2.3 Use Case Diagram | 3 | ⚠️ Spec done in DIAGRAMS.md; draw.io export needed |
| 2.3 Database Design | 3 | ⚠️ Schema complete; MySQL screenshot needed |
| 2.4 Platform Screenshots | 2 | ⚠️ Site live on localhost; annotated screenshots needed |
| 2.4 PHP Code sample | 3 | ⚠️ Code exists; copy + annotate in doc |
| 2.4 HTML Code sample | 3 | ⚠️ Code exists; copy + annotate in doc |
| 2.4 JavaScript sample | 3 | ⚠️ Code exists; copy + annotate in doc |
| 2.4 CSS Code sample | 3 | ⚠️ Code exists; copy + annotate in doc |
| 2.4 MySQL screenshots | 2 | ⚠️ Tables exist; screenshot needed |
| 2.5 Conclusion | 2 | ❌ Not written |
| **TOTAL** | **50** | **0 marks locked in — all assembly pending** |

---

### What is DONE vs what REMAINS

#### ✅ DONE (no more coding needed)

- All **22 PHP pages** built and working (`pages/` + `admin/`)
- All **7 MySQL tables** created with correct schema, FKs, constraints
- **RBAC** via `requireRole()` — 4 roles: worker, client, admin, moderator
- **Escrow** lifecycle: held → released / refunded (PDO transaction)
- **PayFast** IPN handler (`payfast_notify.php`)
- **Dispute** system: raise → admin resolve → escrow outcome
- **Reviews**: post-completion, UNIQUE guard, triggers `avg_rating` recalc
- **Audit log**: INSERT-only, all admin actions recorded
- **CSRF** on all forms
- **Bootstrap 5** mobile-first layout
- **jQuery AJAX** booking status updates and star rating
- **Seed data**: 9 test users across all roles (`database/seed.sql`)
- **All 5 diagram specs** written and verified against live schema in this file
- **DIAGRAMS.md**: no OTP/Email Service references, audit_log fully included

#### ❌ REMAINING (document assembly — no new code needed)

1. **Draw the 5 diagrams** in draw.io using specs above → export as PNG
2. **Take screenshots** of every site page at 3 breakpoints (Chrome DevTools)
3. **Take MySQL screenshots** of all 7 tables (DESCRIBE or phpMyAdmin)
4. **Write Introduction** (≤200 words) in Word doc
5. **Write Conclusion** (≤150 words) in Word doc
6. **Paste code samples** (PHP + HTML + JS + CSS) with one-paragraph explanations each
7. **Assemble into D2 submission document** (PDF)
8. **Host on InfinityFree** (required before D3 — localhost NOT permitted for presentation)

#### ⚠️ VERIFY BEFORE SUBMISSION

| Item | Risk | Check |
|---|---|---|
| Two-way reviews | Worker should also be able to review the client | Test `leave_review.php` as a worker after a completed booking |
| Moderator access restriction | Moderator must NOT see User Management or Audit Log | Test login with `role='moderator'` |
| Listing visibility | Pending listings must NOT appear on `browse.php` | Test: create listing as worker, check browse before admin approves |
| PayFast IPN | `payfast_notify.php` must set `escrow_status='held'` and booking to `confirmed` | Review code at lines ~50–80 |
| CSRF tokens | Every POST form must include `<?= $_SESSION['csrf_token'] ?>` and server must validate | Grep for `csrf_token` across all forms |
