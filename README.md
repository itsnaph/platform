# HustleHub — C2C Service Marketplace

**Live site:** https://hustlehub.freepage.cc
**Hosted on:** InfinityFree (PHP + MySQL)

---

## Running Locally

Double-click `start-all.ps1` or run:

```powershell
powershell -ExecutionPolicy Bypass -File "start-all.ps1"
```

This starts MySQL and the PHP dev server, then opens the site at **http://localhost:8080** automatically.

To reset the database:

```powershell
& "C:\Users\User\scoop\apps\mysql\current\bin\mysql.exe" -u root hustlehub < database/schema.sql
& "C:\Users\User\scoop\apps\mysql\current\bin\mysql.exe" -u root hustlehub < database/seed.sql
```

---

## Test Accounts

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

---

## Pages

| Page | File |
|---|---|
| Homepage | index.php |
| Browse services | pages/browse.php |
| Service detail + booking | pages/service_detail.php |
| Register | pages/register.php |
| Login | pages/login.php |
| Client dashboard | pages/client_dashboard.php |
| Worker dashboard | pages/worker_dashboard.php |
| Create listing | pages/create_listing.php |
| Edit listing | pages/edit_listing.php |
| Leave a review | pages/leave_review.php |
| Raise a dispute | pages/raise_dispute.php |
| How it works | pages/how_it_works.php |
| Help & FAQ | pages/help.php |
| Admin dashboard | admin/index.php |
| Admin — listings | admin/listings.php |
| Admin — disputes | admin/disputes.php |
| Admin — users | admin/users.php |
| Admin — audit log | admin/audit_log.php |

---

## How Booking and Escrow Works

1. A client browses services and clicks **Book Now** on a listing.
2. They pick a date, add any notes, and are taken to PayFast to pay.
3. The money is held in escrow — the worker does not receive it yet.
4. The worker sees the booking on their dashboard and accepts it.
5. The worker marks the job as started when they begin.
6. Once the job is done, the client confirms completion on their dashboard.
7. Confirming releases the escrow and the worker gets paid.
8. Both sides can leave a star rating and review after completion.

**If something goes wrong:**
The client can raise a dispute from their dashboard. An admin reviews both sides, writes a resolution note, and decides whether to release the payment to the worker or refund it to the client.

---

## Tech Stack

- PHP 8.5, MySQL 9.7
- Bootstrap 5.3, jQuery 3
- PayFast (sandbox) for payments
- Hosted on InfinityFree
