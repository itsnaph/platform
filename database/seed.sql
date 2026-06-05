-- HustleHub Seed Data — run AFTER schema.sql
-- Passwords are bcrypt of "Password@123"

-- ── USERS ─────────────────────────────────────────────────────
-- Super Admin
INSERT INTO users (full_name, email, phone, password, role, is_verified) VALUES
('Sipho Dlamini',   'admin@hustlehub.co.za',     '0821001001',
 '$2y$10$8twf91YEyKwHuxIRHo7fdOQb19F8dgMYxBVi0hNGbtSpRYNsNbkMa', 'admin',     1),

-- Moderator
('Naledi Khumalo',  'mod@hustlehub.co.za',        '0822002002',
 '$2y$10$8twf91YEyKwHuxIRHo7fdOQb19F8dgMYxBVi0hNGbtSpRYNsNbkMa', 'moderator', 1),

-- Workers
('Thabo Nkosi',     'thabo@example.co.za',        '0831001001',
 '$2y$10$8twf91YEyKwHuxIRHo7fdOQb19F8dgMYxBVi0hNGbtSpRYNsNbkMa', 'worker',    1),
('Lindiwe Dube',    'lindiwe@example.co.za',      '0832002002',
 '$2y$10$8twf91YEyKwHuxIRHo7fdOQb19F8dgMYxBVi0hNGbtSpRYNsNbkMa', 'worker',    1),
('David Sithole',   'david@example.co.za',        '0833003003',
 '$2y$10$8twf91YEyKwHuxIRHo7fdOQb19F8dgMYxBVi0hNGbtSpRYNsNbkMa', 'worker',    1),
('Moses Khumalo',   'moses@example.co.za',        '0834004004',
 '$2y$10$8twf91YEyKwHuxIRHo7fdOQb19F8dgMYxBVi0hNGbtSpRYNsNbkMa', 'worker',    1),

-- Clients
('Amara Osei',      'amara@example.co.za',        '0841001001',
 '$2y$10$8twf91YEyKwHuxIRHo7fdOQb19F8dgMYxBVi0hNGbtSpRYNsNbkMa', 'client',    1),
('Priya Naidoo',    'priya@example.co.za',        '0842002002',
 '$2y$10$8twf91YEyKwHuxIRHo7fdOQb19F8dgMYxBVi0hNGbtSpRYNsNbkMa', 'client',    1),
('James Mokoena',   'james@example.co.za',        '0843003003',
 '$2y$10$8twf91YEyKwHuxIRHo7fdOQb19F8dgMYxBVi0hNGbtSpRYNsNbkMa', 'client',    1);

-- ── SERVICES ──────────────────────────────────────────────────
INSERT INTO services (worker_id, title, description, category, price, duration_hours, approval_status) VALUES
(3, 'Deep Home Cleaning',
 'Professional deep clean of your entire home. Includes kitchen, bathrooms, bedrooms, and living areas. I bring my own eco-friendly supplies.',
 'cleaning', 250.00, 4, 'approved'),

(4, 'Garden Maintenance',
 'Full garden service: mowing, trimming, weeding, and pruning. I handle small to medium gardens and leave your yard looking pristine.',
 'gardening', 180.00, 3, 'approved'),

(5, 'Interior Wall Painting',
 'Smooth, professional interior painting with quality paint. I cover furniture and clean up after. Price is per room.',
 'painting', 350.00, 6, 'approved'),

(6, 'Local Furniture Moving',
 'Safe and reliable furniture moving within the same area. I bring straps and blankets to protect your items during transit.',
 'moving', 500.00, 4, 'approved'),

(3, 'Office Cleaning',
 'End-of-day office cleaning including desks, floors, bathrooms, and kitchen area. Available after 5pm.',
 'cleaning', 200.00, 3, 'approved'),

(4, 'Plumbing Repairs',
 'Fix leaking taps, blocked drains, and replace bathroom fixtures. I carry common parts so most jobs are done same day.',
 'repairs', 300.00, 2, 'pending');

-- ── BOOKINGS ──────────────────────────────────────────────────
-- Booking 1: COMPLETED — Amara booked Thabo's cleaning
INSERT INTO bookings (service_id, client_id, worker_id, booking_date, status, notes) VALUES
(1, 7, 3, DATE_ADD(CURDATE(), INTERVAL -5 DAY), 'completed',
 'Please focus on the kitchen first — we had a party last night.');

-- Booking 2: COMPLETED — Priya booked David's painting
INSERT INTO bookings (service_id, client_id, worker_id, booking_date, status, notes) VALUES
(3, 8, 5, DATE_ADD(CURDATE(), INTERVAL -3 DAY), 'completed',
 'Two rooms: bedroom and lounge. Colour is Plascon White Cotton.');

-- Booking 3: DISPUTED — James booked Moses's moving service
INSERT INTO bookings (service_id, client_id, worker_id, booking_date, status, notes) VALUES
(4, 9, 6, DATE_ADD(CURDATE(), INTERVAL -1 DAY), 'disputed',
 'Moving from Soweto to Sandton. 3 rooms of furniture.');

-- Booking 4: IN_PROGRESS — Amara booked Lindiwe's gardening
INSERT INTO bookings (service_id, client_id, worker_id, booking_date, status, notes) VALUES
(2, 7, 4, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'in_progress', NULL);

-- Booking 5: CONFIRMED — Priya booked office cleaning
INSERT INTO bookings (service_id, client_id, worker_id, booking_date, status, notes) VALUES
(5, 8, 3, DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'confirmed', 'After 5:30pm please.');

-- ── TRANSACTIONS (escrow) ──────────────────────────────────────
-- Booking 1: escrow released (completed job)
INSERT INTO transactions (booking_id, amount, escrow_status, released_by, released_at) VALUES
(1, 250.00, 'released', 7, DATE_ADD(NOW(), INTERVAL -4 DAY));

-- Booking 2: escrow released
INSERT INTO transactions (booking_id, amount, escrow_status, released_by, released_at) VALUES
(2, 350.00, 'released', 8, DATE_ADD(NOW(), INTERVAL -2 DAY));

-- Booking 3: escrow HELD (disputed — cannot release yet)
INSERT INTO transactions (booking_id, amount, escrow_status) VALUES
(3, 500.00, 'held');

-- Booking 4: escrow held
INSERT INTO transactions (booking_id, amount, escrow_status) VALUES
(4, 180.00, 'held');

-- Booking 5: escrow held
INSERT INTO transactions (booking_id, amount, escrow_status) VALUES
(5, 200.00, 'held');

-- ── REVIEWS ───────────────────────────────────────────────────
-- Review for Booking 1: Amara reviews Thabo (completed)
INSERT INTO reviews (booking_id, reviewer_id, reviewee_id, rating, comment) VALUES
(1, 7, 3, 5, 'Thabo was punctual, thorough, and very professional. Kitchen looked brand new. Highly recommend!');

-- Thabo reviews Amara
INSERT INTO reviews (booking_id, reviewer_id, reviewee_id, rating, comment) VALUES
(1, 3, 7, 4, 'Easy-going client, clear instructions, and was home to let me in on time. Good experience.');

-- Review for Booking 2: Priya reviews David
INSERT INTO reviews (booking_id, reviewer_id, reviewee_id, rating, comment) VALUES
(2, 8, 5, 5, 'Absolutely beautiful paint finish. David took care to protect all furniture and cleaned up perfectly.');

-- ── DISPUTES ──────────────────────────────────────────────────
-- Dispute on Booking 3 (moving service — James disputes)
INSERT INTO disputes (booking_id, raised_by, reason, status) VALUES
(3, 9, 'The worker arrived 3 hours late and only moved half the furniture before claiming the job was done. The quoted price was for a full move. I am requesting a partial refund.', 'open');

-- ── AUDIT LOG ─────────────────────────────────────────────────
INSERT INTO audit_log (admin_id, action, target_type, target_id, notes) VALUES
(1, 'LISTING_APPROVED', 'service', 1, 'Deep Home Cleaning approved after review.'),
(1, 'LISTING_APPROVED', 'service', 2, 'Garden Maintenance approved.'),
(1, 'LISTING_APPROVED', 'service', 3, 'Interior Wall Painting approved.'),
(1, 'LISTING_APPROVED', 'service', 4, 'Local Furniture Moving approved.'),
(1, 'LISTING_APPROVED', 'service', 5, 'Office Cleaning approved.');

-- ── UPDATE USER RATINGS ────────────────────────────────────────
UPDATE users SET avg_rating = 4.50 WHERE id = 3;  -- Thabo (worker)
UPDATE users SET avg_rating = 4.90 WHERE id = 5;  -- David (worker)
UPDATE users SET avg_rating = 4.00 WHERE id = 7;  -- Amara (client — rated by worker)
