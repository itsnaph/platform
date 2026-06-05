-- HustleHub Database Schema
-- Stack: MySQL 8.x


-- TABLE 1: users
-- Stores all users. Role column controls access level.
-- OTP verification status tracked via is_verified column.

CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED     NOT NULL AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(120)     NOT NULL,
  email         VARCHAR(180)     NOT NULL UNIQUE,
  phone         VARCHAR(20)      DEFAULT NULL,
  password      VARCHAR(255)     NOT NULL,
  role          ENUM('worker','client','admin','moderator') NOT NULL DEFAULT 'client',
  is_verified   TINYINT(1)       NOT NULL DEFAULT 0,
  otp_code      VARCHAR(10)      DEFAULT NULL,
  otp_expires   DATETIME         DEFAULT NULL,
  profile_pic   VARCHAR(255)     DEFAULT NULL,
  bio           TEXT             DEFAULT NULL,
  avg_rating    DECIMAL(3,2)     NOT NULL DEFAULT 0.00,
  created_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- TABLE 2: services
-- Worker service listings. Admin must approve before visible.

CREATE TABLE IF NOT EXISTS services (
  id              INT UNSIGNED     NOT NULL AUTO_INCREMENT PRIMARY KEY,
  worker_id       INT UNSIGNED     NOT NULL,
  title           VARCHAR(160)     NOT NULL,
  description     TEXT             NOT NULL,
  category        ENUM('cleaning','gardening','painting','moving','repairs','other') NOT NULL,
  price           DECIMAL(10,2)    NOT NULL,
  duration_hours  TINYINT UNSIGNED NOT NULL DEFAULT 1,
  image_path      VARCHAR(255)     DEFAULT NULL,
  approval_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_services_worker
    FOREIGN KEY (worker_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- TABLE 3: bookings
-- One row per client booking. Status follows defined lifecycle.
-- pending -> confirmed -> in_progress -> completed
-- OR: any active status -> disputed / cancelled

CREATE TABLE IF NOT EXISTS bookings (
  id           INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
  service_id   INT UNSIGNED  NOT NULL,
  client_id    INT UNSIGNED  NOT NULL,
  worker_id    INT UNSIGNED  NOT NULL,
  booking_date DATE          NOT NULL,
  status       ENUM('pending','confirmed','in_progress','completed','disputed','cancelled')
               NOT NULL DEFAULT 'pending',
  notes        TEXT          DEFAULT NULL,
  created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_bookings_service
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
  CONSTRAINT fk_bookings_client
    FOREIGN KEY (client_id)  REFERENCES users(id)    ON DELETE CASCADE,
  CONSTRAINT fk_bookings_worker
    FOREIGN KEY (worker_id)  REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB;


-- TABLE 4: transactions
-- One financial record per booking. Escrow lifecycle:

CREATE TABLE IF NOT EXISTS transactions (
  id             INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
  booking_id     INT UNSIGNED  NOT NULL UNIQUE,
  amount         DECIMAL(10,2) NOT NULL,
  escrow_status  ENUM('held','released','refunded') NOT NULL DEFAULT 'held',
  payfast_id     VARCHAR(100)  DEFAULT NULL,
  released_by    INT UNSIGNED  DEFAULT NULL,
  released_at    DATETIME      DEFAULT NULL,
  created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_transactions_booking
    FOREIGN KEY (booking_id)  REFERENCES bookings(id) ON DELETE CASCADE,
  CONSTRAINT fk_transactions_admin
    FOREIGN KEY (released_by) REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB;


-- TABLE 5: reviews
-- Post-completion ratings 1-5. One review per party per booking.

CREATE TABLE IF NOT EXISTS reviews (
  id           INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
  booking_id   INT UNSIGNED  NOT NULL,
  reviewer_id  INT UNSIGNED  NOT NULL,
  reviewee_id  INT UNSIGNED  NOT NULL,
  rating       TINYINT UNSIGNED NOT NULL,
  comment      TEXT          DEFAULT NULL,
  created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5),
  UNIQUE KEY unique_review (booking_id, reviewer_id),
  CONSTRAINT fk_reviews_booking
    FOREIGN KEY (booking_id)  REFERENCES bookings(id) ON DELETE CASCADE,
  CONSTRAINT fk_reviews_reviewer
    FOREIGN KEY (reviewer_id) REFERENCES users(id)    ON DELETE CASCADE,
  CONSTRAINT fk_reviews_reviewee
    FOREIGN KEY (reviewee_id) REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB;


-- TABLE 6: disputes
-- Raised when a booking goes wrong. Admin resolves.

CREATE TABLE IF NOT EXISTS disputes (
  id              INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
  booking_id      INT UNSIGNED  NOT NULL,
  raised_by       INT UNSIGNED  NOT NULL,
  reason          TEXT          NOT NULL,
  status          ENUM('open','under_review','resolved') NOT NULL DEFAULT 'open',
  admin_id        INT UNSIGNED  DEFAULT NULL,
  resolution_note TEXT          DEFAULT NULL,
  resolved_at     DATETIME      DEFAULT NULL,
  created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_disputes_booking
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  CONSTRAINT fk_disputes_raised_by
    FOREIGN KEY (raised_by)  REFERENCES users(id)    ON DELETE CASCADE,
  CONSTRAINT fk_disputes_admin
    FOREIGN KEY (admin_id)   REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB;

-- TABLE 7: audit_log (admin actions trail)
CREATE TABLE IF NOT EXISTS audit_log (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  admin_id    INT UNSIGNED NOT NULL,
  action      VARCHAR(60)  NOT NULL,
  target_type VARCHAR(30),
  target_id   INT UNSIGNED,
  notes       TEXT,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- payfast_id is already defined inline in the transactions table above.
-- (No ALTER needed — column is included in CREATE TABLE statement)
