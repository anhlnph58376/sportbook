# SportBook — System Design Specification

**Version:** 1.0  
**Date:** 2026-09-13  
**Status:** Draft — Awaiting Review  

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Functional Requirements](#2-functional-requirements)
3. [Non-Functional Requirements](#3-non-functional-requirements)
4. [User Roles](#4-user-roles)
5. [Use Cases](#5-use-cases)
6. [Main Business Workflows](#6-main-business-workflows)
7. [Business Rules](#7-business-rules)
8. [Database Entities](#8-database-entities)
9. [ERD Relationship Explanation](#9-erd-relationship-explanation)
10. [Database Schema Proposal](#10-database-schema-proposal)
11. [API Architecture](#11-api-architecture)
12. [Authentication Architecture](#12-authentication-architecture)
13. [Authorization Architecture](#13-authorization-architecture)
14. [Booking Conflict Prevention Strategy](#14-booking-conflict-prevention-strategy)
15. [Payment Architecture](#15-payment-architecture)
16. [Notification Architecture](#16-notification-architecture)
17. [Queue Architecture](#17-queue-architecture)
18. [Caching Strategy](#18-caching-strategy)
19. [Security Strategy](#19-security-strategy)
20. [Testing Strategy](#20-testing-strategy)
21. [Folder Structure](#21-folder-structure)
22. [Development Phases](#22-development-phases)
23. [Deployment Architecture](#23-deployment-architecture)
24. [Future Scalability Plan](#24-future-scalability-plan)

---

## 1. Project Overview

**SportBook** is a production-grade sports venue booking platform targeting the Vietnamese market. It connects sports enthusiasts (Players) with sports facility operators (Venue Owners) through a managed marketplace platform overseen by Administrators.

The platform enables end-to-end venue discovery, booking, payment, and review. Venue Owners can list multiple venues, each containing multiple courts with dynamic pricing. The system prevents double bookings via server-side conflict detection with database-level locking, manages deposit payments through an abstracted payment layer, and automates booking lifecycle transitions via Laravel Scheduler and Queues.

The system is designed from the start as a production SaaS product — not a tutorial demo. Every major design decision (schema, concurrency, payments, authorization, notifications) is made with correctness, security, and future scalability in mind.

### Core Goals

| Goal | Description |
|------|-------------|
| Correct double-booking prevention | Database-level locking + transactions, not UI-level checks |
| Clean role-based authorization | Policy-driven, extensible, never frontend-only |
| Abstracted payment layer | Swap providers without rewriting business logic |
| Asynchronous operations | Queues for notifications, reports, and expiry jobs |
| Portfolio quality | Real-world architecture a senior developer would defend in interview |

---

## 2. Functional Requirements

### 2.1 Authentication

- FR-AUTH-01: Users can register with name, email, phone, and password.
- FR-AUTH-02: Users can log in with email and password.
- FR-AUTH-03: Authentication tokens are issued via Laravel Sanctum.
- FR-AUTH-04: Users can log out (token revocation).
- FR-AUTH-05: Users can change password with current password verification.
- FR-AUTH-06: Email verification is enforced before booking.
- FR-AUTH-07: Administrators can lock or unlock user accounts.

### 2.2 Venue Management

- FR-VENUE-01: Venue Owners can create venue applications.
- FR-VENUE-02: Venue Owners can upload verification documents.
- FR-VENUE-03: Venues require administrator approval before going public.
- FR-VENUE-04: Venue Owners can add/edit venue information (name, address, geolocation, amenities, supported sports, operating hours).
- FR-VENUE-05: Venue Owners can manage venue images (upload, reorder, delete).
- FR-VENUE-06: Venue Owners can manage holiday / closed dates.
- FR-VENUE-07: Approved venues are publicly searchable.

### 2.3 Court Management

- FR-COURT-01: Venue Owners can add multiple courts to a venue.
- FR-COURT-02: Each court has a sport type, capacity, description, and status.
- FR-COURT-03: Each court supports configurable pricing rules per time window.
- FR-COURT-04: Venue Owners can temporarily disable a court.
- FR-COURT-05: Venue Owners can upload court images.

### 2.4 Booking

- FR-BOOK-01: Players can view available time slots for a given court and date.
- FR-BOOK-02: Players can create a booking by selecting a court, date, start time, and end time.
- FR-BOOK-03: The system calculates total price based on matching pricing rules.
- FR-BOOK-04: The system prevents overlapping bookings on the same court.
- FR-BOOK-05: Players must pay a deposit within a configurable window or the booking expires.
- FR-BOOK-06: Players can cancel bookings subject to cancellation policy.
- FR-BOOK-07: Venue Owners can confirm, check in, complete, or reject bookings.
- FR-BOOK-08: Players receive notifications on booking status transitions.

### 2.5 Payment

- FR-PAY-01: Players can pay the deposit for a booking.
- FR-PAY-02: The system records payment transaction, provider, amount, and status.
- FR-PAY-03: Payment status is verified server-side (never trusted from frontend).
- FR-PAY-04: Webhook endpoints handle payment provider callbacks.
- FR-PAY-05: Payment failures are logged with error details.

### 2.6 Reviews

- FR-REV-01: Players can leave a review (1–5 stars + comment + optional images) for a completed booking.
- FR-REV-02: Only one review per booking is allowed.
- FR-REV-03: Venue Owners can reply to reviews.
- FR-REV-04: Administrators can moderate (hide/delete) reported reviews.

### 2.7 Search & Discovery

- FR-SEARCH-01: Players can search venues by name, sport, location (province/district), price range, rating, and available date/time.
- FR-SEARCH-02: Results can be sorted by distance, rating, and price.
- FR-SEARCH-03: Players can filter by available slots on a specific date and time.
- FR-SEARCH-04: Distance filtering requires latitude/longitude.

### 2.8 Favorites

- FR-FAV-01: Players can add a venue to favorites.
- FR-FAV-02: Players can remove a venue from favorites.
- FR-FAV-03: Duplicate favorites are prevented at the database level.

### 2.9 Notifications

- FR-NOTIF-01: Players receive in-app and email notifications for booking lifecycle events.
- FR-NOTIF-02: Venue Owners receive notifications for new bookings, cancellations, and reviews.
- FR-NOTIF-03: Administrators receive notifications for new venue applications.

### 2.10 Admin Functions

- FR-ADMIN-01: Admin can view, approve, and reject venue applications.
- FR-ADMIN-02: Admin can manage sports categories.
- FR-ADMIN-03: Admin can manage users (lock/unlock).
- FR-ADMIN-04: Admin can view all bookings and payments.
- FR-ADMIN-05: Admin can view platform statistics and revenue.
- FR-ADMIN-06: Admin can view audit logs.
- FR-ADMIN-07: Admin can moderate reviews.

---

## 3. Non-Functional Requirements

| Category | Requirement |
|----------|-------------|
| **Performance** | API responses under 300 ms for cached queries; under 800 ms for complex availability checks |
| **Concurrency** | Booking creation must be safe under concurrent requests (race condition prevention) |
| **Security** | OWASP Top 10 mitigated; no sensitive data in responses; token-based auth |
| **Scalability** | Stateless API supports horizontal scaling; queue workers scale independently |
| **Maintainability** | Clean architecture; thin controllers; service layer; consistent coding style (Pint) |
| **Testability** | Core workflows covered by Feature Tests; minimum 70% coverage on critical paths |
| **Reliability** | Failed jobs retry with exponential backoff; critical operations wrapped in DB transactions |
| **Observability** | Laravel Telescope (dev), structured audit logs, queue monitoring |
| **Availability** | System handles queue failures gracefully; bookings do not depend on queue completion |
| **Data Integrity** | Foreign keys, unique constraints, cascade rules enforced at DB level |
| **Portability** | Environment-driven configuration; Docker-ready |

---

## 4. User Roles

The system uses a roles table with a role-user pivot, allowing multiple roles and future role expansion without schema changes.

```
PLAYER          → Default role on registration
VENUE_OWNER     → Granted when user applies to list a venue (or self-registers as owner)
ADMINISTRATOR   → Seeded; never self-assigned
```

> **Design Note:** Roles are stored in a `roles` table with a `role_user` pivot. The authorization layer uses Laravel Policies and Gates, not simple role-string checks in controllers. This means adding a new role (e.g., `STAFF`, `MODERATOR`) only requires a new row in `roles` and new Policy methods — no controller rewrites.

---

## 5. Use Cases

### UC-01: Player Books a Court

```
Actor: Player
Preconditions: Player is authenticated, email verified, venue is approved and active
Steps:
  1. Player searches for venues by sport and location
  2. Player selects a venue and views available courts
  3. Player selects a court, picks a date, selects start/end time
  4. System calculates price using pricing_rules
  5. System checks availability (no conflicting bookings)
  6. Player confirms booking → booking created with status PENDING
  7. System transitions booking to AWAITING_PAYMENT, starts expiry timer
  8. Player completes deposit payment via payment gateway
  9. System verifies payment server-side → booking moves to CONFIRMED
 10. Player and Owner receive confirmation notifications
Postconditions: Booking is CONFIRMED, slot is locked, payment recorded
```

### UC-02: Booking Conflict (Race Condition)

```
Actor: Two Players simultaneously
Steps:
  1. Player A and Player B both view the same available slot
  2. Both submit booking creation requests at nearly the same time
  3. System acquires row-level lock via SELECT ... FOR UPDATE on courts
  4. Only one request proceeds; the other receives HTTP 409 Conflict
Postconditions: Exactly one booking exists for that slot
```

### UC-03: Venue Owner Lists a Venue

```
Actor: Venue Owner
Steps:
  1. Owner creates a venue application (status: PENDING_REVIEW)
  2. Owner uploads verification documents
  3. Administrator reviews the application
  4. Admin approves → venue status: APPROVED, visible publicly
  5. Owner adds courts, sets pricing rules and operating hours
  6. Owner publishes the venue
Postconditions: Venue appears in public search
```

### UC-04: Booking Expiry

```
Actor: Scheduler (automated)
Steps:
  1. Player creates booking (PENDING → AWAITING_PAYMENT)
  2. Player does not pay within configured window (default: 30 minutes)
  3. Laravel Scheduler triggers ExpireUnpaidBookings job
  4. Job transitions booking to EXPIRED
  5. Slot becomes available again
  6. Player notified via notification
```

### UC-05: Player Cancels a Booking

```
Actor: Player
Steps:
  1. Player requests cancellation
  2. System checks booking is in cancellable state (AWAITING_PAYMENT or CONFIRMED)
  3. CancellationPolicyService calculates refund amount based on hours-until-booking
  4. Booking transitions to CANCELLED
  5. Refund processed (if applicable)
  6. Both parties notified
```

### UC-06: Admin Approves Venue

```
Actor: Administrator
Steps:
  1. Admin reviews venue application and uploaded documents
  2. Admin clicks Approve
  3. VenueApprovalService wraps in DB transaction:
     - venue.verification_status → APPROVED
     - venue.status → ACTIVE
     - AuditLog entry created
  4. VenueApprovedNotification dispatched to Owner (queued)
Postconditions: Venue is publicly visible, Owner notified
```

---

## 6. Main Business Workflows

### 6.1 Booking Workflow (State Machine)

```
                        [Player submits booking]
                                 │
                          ┌──────▼──────┐
                          │   PENDING   │
                          └──────┬──────┘
                                 │ System prompts payment
                          ┌──────▼──────────────┐
                          │  AWAITING_PAYMENT   │◄── Expiry timer starts
                          └──────┬──────────────┘
               ┌─────────────────┼─────────────────┐
        [Paid] │                 │ [Timeout]        │ [Cancelled by player]
        ┌──────▼──────┐    ┌─────▼──────┐    ┌─────▼──────┐
        │  CONFIRMED  │    │  EXPIRED   │    │ CANCELLED  │
        └──────┬──────┘    └────────────┘    └────────────┘
               │
       [Owner checks in player]
        ┌──────▼──────┐
        │ CHECKED_IN  │
        └──────┬──────┘
               │
       [Session complete]
        ┌──────▼──────┐
        │  COMPLETED  │◄── Player can now leave review
        └─────────────┘

Additionally:
  PENDING / AWAITING_PAYMENT / CONFIRMED → REJECTED (by owner)
  CONFIRMED → CANCELLED (by player, subject to cancellation policy)
```

### 6.2 Venue Approval Workflow

```
[Owner creates venue] → PENDING_REVIEW
                              │
                     [Admin reviews]
                    ┌──────────┴──────────┐
              [Approve]               [Reject]
           APPROVED + ACTIVE        REJECTED
                    │
           [Owner adds courts]
                    │
           [Owner publishes]
                PUBLISHED
```

### 6.3 Payment Workflow

```
[Player initiates payment]
         │
[PaymentService creates payment record: PENDING]
         │
[Redirect to payment gateway / Mock]
         │
    [Gateway callback / Webhook]
         │
[PaymentWebhookController verifies signature]
         │
    ┌────┴────┐
[SUCCESS]   [FAILED]
    │           │
[Payment: COMPLETED]  [Payment: FAILED]
[Booking: CONFIRMED]  [Booking stays AWAITING_PAYMENT]
[Notification queued] [Player notified of failure]
```

---

## 7. Business Rules

### Booking Rules

| Rule ID | Rule |
|---------|------|
| BR-BOOK-01 | Players may not book in the past. |
| BR-BOOK-02 | Start time must be before end time. Minimum booking duration: 30 minutes. |
| BR-BOOK-03 | Booking times must fall within venue operating hours. |
| BR-BOOK-04 | The court must be active (not disabled). |
| BR-BOOK-05 | The venue must be APPROVED and ACTIVE. |
| BR-BOOK-06 | No existing confirmed/pending/awaiting-payment booking may overlap the requested slot for the same court. |
| BR-BOOK-07 | Deposit must be paid within the configured payment window (default: 30 min). |
| BR-BOOK-08 | A booking on a venue holiday is rejected. |

### Cancellation Rules

| Hours Before Booking | Refund |
|----------------------|--------|
| > 24 hours | 100% of deposit |
| 12–24 hours | 50% of deposit |
| < 12 hours | 0% |

These thresholds are stored in `system_configurations` (configurable by Admin) — never hard-coded.

### Review Rules

| Rule ID | Rule |
|---------|------|
| BR-REV-01 | Only one review per booking. |
| BR-REV-02 | Booking must be in COMPLETED status. |
| BR-REV-03 | Player must be the booking owner. |
| BR-REV-04 | Admin can hide reviews; hidden reviews are not shown publicly. |

### Venue Rules

| Rule ID | Rule |
|---------|------|
| BR-VENUE-01 | Venue must be verified (documents + admin approval) before appearing publicly. |
| BR-VENUE-02 | Owner may only manage their own venues. |
| BR-VENUE-03 | Venue cannot accept bookings when status is not ACTIVE. |

### Authorization Rules

| Rule ID | Rule |
|---------|------|
| BR-AUTH-01 | All authorization is enforced server-side via Policies. |
| BR-AUTH-02 | Players cannot access admin or owner management endpoints. |
| BR-AUTH-03 | Owners cannot access another owner's venue management. |
| BR-AUTH-04 | Admin routes require the `admin` role verified via middleware. |

---

## 8. Database Entities

Below is the full entity list with justification for each:

| Entity | Purpose |
|--------|---------|
| `users` | Core user accounts for all roles |
| `roles` | Named roles (player, venue_owner, admin) |
| `role_user` | Pivot: users↔roles (many-to-many) |
| `venues` | Sports venue listings |
| `venue_images` | Multiple images per venue |
| `venue_documents` | Verification documents uploaded by owners |
| `sports` | Master list of sport categories (Football, Badminton, etc.) |
| `venue_sport` | Pivot: venues↔sports |
| `courts` | Individual bookable courts within a venue |
| `court_images` | Multiple images per court |
| `operating_hours` | Per-venue weekly schedule (Mon–Sun open/close times) |
| `pricing_rules` | Time-window pricing per court |
| `venue_holidays` | Specific dates when a venue is closed |
| `bookings` | Core booking records |
| `payments` | Payment transactions linked to bookings |
| `reviews` | Post-booking reviews by players |
| `review_images` | Optional images attached to reviews |
| `review_replies` | Owner replies to reviews |
| `favorites` | Player↔Venue favorites (with unique constraint) |
| `notifications` | Laravel database notifications |
| `audit_logs` | Admin action audit trail |
| `system_configurations` | Key-value config (payment window, cancellation policy, etc.) |
| `amenities` | Master list of amenity tags |
| `venue_amenity` | Pivot: venues↔amenities |

---

## 9. ERD Relationship Explanation

```
users ──< role_user >── roles
  │
  ├──< venues (owner_id FK)
  │       │
  │       ├──< venue_images
  │       ├──< venue_documents
  │       ├──< venue_sport >── sports
  │       ├──< venue_amenity >── amenities
  │       ├──< operating_hours (7 rows per venue, one per weekday)
  │       ├──< venue_holidays
  │       └──< courts
  │               │
  │               ├──< court_images
  │               ├──< pricing_rules
  │               └──< bookings (court_id FK)
  │                       │
  │                       ├── payments (booking_id FK)
  │                       └── reviews (booking_id FK)
  │                               │
  │                               ├──< review_images
  │                               └──< review_replies
  │
  ├──< bookings (user_id FK as player)
  ├──< favorites (user_id FK) >── venues
  ├──< notifications (notifiable_id, polymorphic)
  └──< audit_logs (user_id FK as actor)
```

### Key Relationships

| Relationship | Type | Notes |
|---|---|---|
| User → Venues | One-to-Many (as owner) | A user with `venue_owner` role owns many venues |
| User → Bookings | One-to-Many (as player) | A player makes many bookings |
| Venue → Courts | One-to-Many | Each venue contains multiple bookable courts |
| Court → Pricing Rules | One-to-Many | Multiple time windows with different prices |
| Court → Bookings | One-to-Many | Each booking is for one specific court |
| Booking → Payment | One-to-One | Each booking has one payment record |
| Booking → Review | One-to-One | Each completed booking produces at most one review |
| Venue → Sports | Many-to-Many | A venue can host multiple sports |
| User → Venues (favorites) | Many-to-Many via `favorites` | Unique constraint on (user_id, venue_id) |

---

## 10. Database Schema Proposal

### `users`
```sql
id               BIGINT UNSIGNED PK AUTO_INCREMENT
name             VARCHAR(255) NOT NULL
email            VARCHAR(255) NOT NULL UNIQUE
phone            VARCHAR(20) NULLABLE
email_verified_at TIMESTAMP NULLABLE
password         VARCHAR(255) NOT NULL
avatar           VARCHAR(255) NULLABLE
status           ENUM('active','locked') DEFAULT 'active'
remember_token   VARCHAR(100) NULLABLE
created_at       TIMESTAMP
updated_at       TIMESTAMP
INDEX(email)
INDEX(status)
```

### `roles`
```sql
id               BIGINT UNSIGNED PK AUTO_INCREMENT
name             VARCHAR(50) NOT NULL UNIQUE   -- 'player','venue_owner','admin'
display_name     VARCHAR(100) NOT NULL
created_at       TIMESTAMP
updated_at       TIMESTAMP
```

### `role_user`
```sql
user_id          BIGINT UNSIGNED FK users.id ON DELETE CASCADE
role_id          BIGINT UNSIGNED FK roles.id ON DELETE CASCADE
PRIMARY KEY (user_id, role_id)
```

### `sports`
```sql
id               BIGINT UNSIGNED PK AUTO_INCREMENT
name             VARCHAR(100) NOT NULL UNIQUE
slug             VARCHAR(100) NOT NULL UNIQUE
icon             VARCHAR(255) NULLABLE
is_active        BOOLEAN DEFAULT TRUE
created_at       TIMESTAMP
updated_at       TIMESTAMP
```

### `amenities`
```sql
id               BIGINT UNSIGNED PK AUTO_INCREMENT
name             VARCHAR(100) NOT NULL UNIQUE
icon             VARCHAR(255) NULLABLE
created_at       TIMESTAMP
updated_at       TIMESTAMP
```

### `venues`
```sql
id               BIGINT UNSIGNED PK AUTO_INCREMENT
owner_id         BIGINT UNSIGNED FK users.id
name             VARCHAR(255) NOT NULL
slug             VARCHAR(255) NOT NULL UNIQUE
description      TEXT NULLABLE
address          VARCHAR(500) NOT NULL
province         VARCHAR(100) NOT NULL
district         VARCHAR(100) NOT NULL
ward             VARCHAR(100) NULLABLE
latitude         DECIMAL(10,8) NULLABLE
longitude        DECIMAL(11,8) NULLABLE
phone            VARCHAR(20) NULLABLE
email            VARCHAR(255) NULLABLE
opening_time     TIME NOT NULL            -- e.g. '06:00:00'
closing_time     TIME NOT NULL            -- e.g. '22:00:00'
status           ENUM('draft','active','inactive','suspended') DEFAULT 'draft'
verification_status ENUM('pending_review','approved','rejected') DEFAULT 'pending_review'
rejection_reason TEXT NULLABLE
average_rating   DECIMAL(3,2) DEFAULT 0.00
total_reviews    INT UNSIGNED DEFAULT 0
created_at       TIMESTAMP
updated_at       TIMESTAMP
deleted_at       TIMESTAMP NULLABLE       -- soft deletes

INDEX(owner_id)
INDEX(status)
INDEX(verification_status)
INDEX(province, district)
INDEX(latitude, longitude)               -- for geo queries
FULLTEXT(name, description)              -- for text search
```

### `venue_images`
```sql
id               BIGINT UNSIGNED PK AUTO_INCREMENT
venue_id         BIGINT UNSIGNED FK venues.id ON DELETE CASCADE
path             VARCHAR(500) NOT NULL
is_primary       BOOLEAN DEFAULT FALSE
sort_order       INT UNSIGNED DEFAULT 0
created_at       TIMESTAMP
updated_at       TIMESTAMP
```

### `venue_documents`
```sql
id               BIGINT UNSIGNED PK AUTO_INCREMENT
venue_id         BIGINT UNSIGNED FK venues.id ON DELETE CASCADE
document_type    VARCHAR(100) NOT NULL    -- 'business_license','land_use_certificate', etc.
path             VARCHAR(500) NOT NULL
original_name    VARCHAR(255) NOT NULL
created_at       TIMESTAMP
updated_at       TIMESTAMP
```

### `venue_sport` (pivot)
```sql
venue_id         BIGINT UNSIGNED FK venues.id ON DELETE CASCADE
sport_id         BIGINT UNSIGNED FK sports.id ON DELETE CASCADE
PRIMARY KEY (venue_id, sport_id)
```

### `venue_amenity` (pivot)
```sql
venue_id         BIGINT UNSIGNED FK venues.id ON DELETE CASCADE
amenity_id       BIGINT UNSIGNED FK amenities.id ON DELETE CASCADE
PRIMARY KEY (venue_id, amenity_id)
```

### `operating_hours`
```sql
id               BIGINT UNSIGNED PK AUTO_INCREMENT
venue_id         BIGINT UNSIGNED FK venues.id ON DELETE CASCADE
day_of_week      TINYINT UNSIGNED NOT NULL   -- 0=Monday … 6=Sunday
is_closed        BOOLEAN DEFAULT FALSE
open_time        TIME NULLABLE
close_time       TIME NULLABLE
created_at       TIMESTAMP
updated_at       TIMESTAMP
UNIQUE(venue_id, day_of_week)
```

### `venue_holidays`
```sql
id               BIGINT UNSIGNED PK AUTO_INCREMENT
venue_id         BIGINT UNSIGNED FK venues.id ON DELETE CASCADE
date             DATE NOT NULL
reason           VARCHAR(255) NULLABLE
created_at       TIMESTAMP
updated_at       TIMESTAMP
UNIQUE(venue_id, date)
INDEX(venue_id, date)
```

### `courts`
```sql
id               BIGINT UNSIGNED PK AUTO_INCREMENT
venue_id         BIGINT UNSIGNED FK venues.id ON DELETE CASCADE
sport_id         BIGINT UNSIGNED FK sports.id
name             VARCHAR(255) NOT NULL
description      TEXT NULLABLE
capacity         TINYINT UNSIGNED DEFAULT 2
status           ENUM('active','inactive','under_maintenance') DEFAULT 'active'
created_at       TIMESTAMP
updated_at       TIMESTAMP
deleted_at       TIMESTAMP NULLABLE

INDEX(venue_id)
INDEX(sport_id)
INDEX(status)
```

### `court_images`
```sql
id               BIGINT UNSIGNED PK AUTO_INCREMENT
court_id         BIGINT UNSIGNED FK courts.id ON DELETE CASCADE
path             VARCHAR(500) NOT NULL
is_primary       BOOLEAN DEFAULT FALSE
sort_order       INT UNSIGNED DEFAULT 0
created_at       TIMESTAMP
updated_at       TIMESTAMP
```

### `pricing_rules`
```sql
id               BIGINT UNSIGNED PK AUTO_INCREMENT
court_id         BIGINT UNSIGNED FK courts.id ON DELETE CASCADE
name             VARCHAR(100) NULLABLE       -- e.g. 'Peak Hours'
start_time       TIME NOT NULL               -- e.g. '16:00:00'
end_time         TIME NOT NULL               -- e.g. '22:00:00'
price_per_hour   DECIMAL(12,2) NOT NULL      -- VND
day_type         ENUM('weekday','weekend','all') DEFAULT 'all'
is_active        BOOLEAN DEFAULT TRUE
created_at       TIMESTAMP
updated_at       TIMESTAMP
INDEX(court_id)
-- Application-level validation ensures no overlapping rules for same court+day_type
```

### `bookings`
```sql
id               BIGINT UNSIGNED PK AUTO_INCREMENT
user_id          BIGINT UNSIGNED FK users.id
court_id         BIGINT UNSIGNED FK courts.id
booking_code     VARCHAR(20) NOT NULL UNIQUE    -- human-readable e.g. SB-20260913-XXXX
booking_date     DATE NOT NULL
start_time       TIME NOT NULL
end_time         TIME NOT NULL
duration_hours   DECIMAL(4,2) NOT NULL          -- computed, stored for clarity
total_price      DECIMAL(12,2) NOT NULL
deposit_amount   DECIMAL(12,2) NOT NULL
deposit_percentage TINYINT UNSIGNED NOT NULL    -- e.g. 30
status           ENUM('pending','awaiting_payment','confirmed','checked_in',
                      'completed','cancelled','expired','rejected') DEFAULT 'pending'
cancelled_at     TIMESTAMP NULLABLE
cancellation_reason TEXT NULLABLE
refund_amount    DECIMAL(12,2) NULLABLE
expires_at       TIMESTAMP NULLABLE             -- deadline for payment
notes            TEXT NULLABLE
created_at       TIMESTAMP
updated_at       TIMESTAMP

INDEX(user_id)
INDEX(court_id)
INDEX(status)
INDEX(booking_date)
INDEX(court_id, booking_date, status)          -- critical composite index for conflict queries
-- No unique constraint at DB level for time overlap; enforced via SELECT FOR UPDATE
```

### `payments`
```sql
id               BIGINT UNSIGNED PK AUTO_INCREMENT
booking_id       BIGINT UNSIGNED FK bookings.id UNIQUE   -- one payment per booking
transaction_id   VARCHAR(255) NULLABLE UNIQUE             -- gateway transaction ID
amount           DECIMAL(12,2) NOT NULL
payment_method   VARCHAR(50) NOT NULL                     -- 'mock','momo','vnpay'
status           ENUM('pending','completed','failed','refunded') DEFAULT 'pending'
provider_response JSON NULLABLE                           -- raw gateway response
paid_at          TIMESTAMP NULLABLE
refunded_at      TIMESTAMP NULLABLE
refund_amount    DECIMAL(12,2) NULLABLE
created_at       TIMESTAMP
updated_at       TIMESTAMP

INDEX(booking_id)
INDEX(transaction_id)
INDEX(status)
```

### `reviews`
```sql
id               BIGINT UNSIGNED PK AUTO_INCREMENT
booking_id       BIGINT UNSIGNED FK bookings.id UNIQUE   -- one review per booking
user_id          BIGINT UNSIGNED FK users.id
venue_id         BIGINT UNSIGNED FK venues.id             -- denormalized for query speed
rating           TINYINT UNSIGNED NOT NULL                -- 1–5
comment          TEXT NOT NULL
is_visible       BOOLEAN DEFAULT TRUE                     -- admin moderation
reported_count   TINYINT UNSIGNED DEFAULT 0
created_at       TIMESTAMP
updated_at       TIMESTAMP

INDEX(venue_id)
INDEX(user_id)
INDEX(is_visible)
```

### `review_images`
```sql
id               BIGINT UNSIGNED PK AUTO_INCREMENT
review_id        BIGINT UNSIGNED FK reviews.id ON DELETE CASCADE
path             VARCHAR(500) NOT NULL
created_at       TIMESTAMP
updated_at       TIMESTAMP
```

### `review_replies`
```sql
id               BIGINT UNSIGNED PK AUTO_INCREMENT
review_id        BIGINT UNSIGNED FK reviews.id ON DELETE CASCADE
user_id          BIGINT UNSIGNED FK users.id           -- venue owner
comment          TEXT NOT NULL
created_at       TIMESTAMP
updated_at       TIMESTAMP
```

### `favorites`
```sql
id               BIGINT UNSIGNED PK AUTO_INCREMENT
user_id          BIGINT UNSIGNED FK users.id ON DELETE CASCADE
venue_id         BIGINT UNSIGNED FK venues.id ON DELETE CASCADE
created_at       TIMESTAMP
UNIQUE(user_id, venue_id)                              -- prevent duplicates
```

### `audit_logs`
```sql
id               BIGINT UNSIGNED PK AUTO_INCREMENT
user_id          BIGINT UNSIGNED FK users.id NULLABLE   -- nullable: system actions
action           VARCHAR(100) NOT NULL                   -- e.g. 'APPROVE_VENUE'
auditable_type   VARCHAR(255) NULLABLE                   -- polymorphic model class
auditable_id     BIGINT UNSIGNED NULLABLE
old_values       JSON NULLABLE
new_values       JSON NULLABLE
ip_address       VARCHAR(45) NULLABLE
user_agent       VARCHAR(500) NULLABLE
created_at       TIMESTAMP

INDEX(user_id)
INDEX(action)
INDEX(auditable_type, auditable_id)
INDEX(created_at)
```

### `system_configurations`
```sql
id               BIGINT UNSIGNED PK AUTO_INCREMENT
key              VARCHAR(100) NOT NULL UNIQUE
value            TEXT NOT NULL
type             ENUM('string','integer','decimal','boolean','json') DEFAULT 'string'
description      VARCHAR(500) NULLABLE
updated_at       TIMESTAMP

-- Example rows:
-- booking_payment_window_minutes = 30
-- cancellation_full_refund_hours = 24
-- cancellation_half_refund_hours = 12
-- default_deposit_percentage = 30
```

---

## 11. API Architecture

### Versioning

All endpoints are prefixed with `/api/v1/` to allow future API versioning without breaking existing clients.

### Response Envelope

```json
{
    "success": true,
    "message": "Human-readable message.",
    "data": {},
    "meta": {
        "pagination": {}
    }
}
```

Error response:

```json
{
    "success": false,
    "message": "The selected time slot is no longer available.",
    "errors": {
        "field": ["Validation message"]
    }
}
```

### Endpoint Reference

```
--- AUTH ---
POST   /api/v1/auth/register
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
GET    /api/v1/auth/me
PUT    /api/v1/auth/profile
PUT    /api/v1/auth/password
POST   /api/v1/auth/email/verify/{id}/{hash}

--- PUBLIC VENUE DISCOVERY ---
GET    /api/v1/venues                         (search + filter + paginate)
GET    /api/v1/venues/{slug}                  (venue detail)
GET    /api/v1/venues/{venue}/courts          (list courts)
GET    /api/v1/courts/{court}/availability    (available slots for date)
GET    /api/v1/venues/{venue}/reviews         (paginated reviews)
GET    /api/v1/sports                         (sport categories)

--- BOOKING (Player) ---
POST   /api/v1/bookings                       (create booking)
GET    /api/v1/bookings                       (player's booking history)
GET    /api/v1/bookings/{booking}
POST   /api/v1/bookings/{booking}/cancel

--- PAYMENT (Player) ---
POST   /api/v1/payments                       (initiate payment for booking)
POST   /api/v1/payments/webhook/{provider}    (gateway callback, no auth middleware)

--- REVIEWS ---
POST   /api/v1/reviews                        (create review for completed booking)
GET    /api/v1/reviews/{review}
PUT    /api/v1/reviews/{review}
DELETE /api/v1/reviews/{review}

--- FAVORITES ---
GET    /api/v1/favorites
POST   /api/v1/favorites/{venue}
DELETE /api/v1/favorites/{venue}

--- NOTIFICATIONS ---
GET    /api/v1/notifications
POST   /api/v1/notifications/{id}/read

--- VENUE OWNER ---
POST   /api/v1/owner/venues
GET    /api/v1/owner/venues
GET    /api/v1/owner/venues/{venue}
PUT    /api/v1/owner/venues/{venue}
DELETE /api/v1/owner/venues/{venue}

POST   /api/v1/owner/venues/{venue}/images
DELETE /api/v1/owner/venues/{venue}/images/{image}

POST   /api/v1/owner/venues/{venue}/documents
GET    /api/v1/owner/venues/{venue}/documents

POST   /api/v1/owner/venues/{venue}/courts
GET    /api/v1/owner/venues/{venue}/courts
GET    /api/v1/owner/courts/{court}
PUT    /api/v1/owner/courts/{court}
DELETE /api/v1/owner/courts/{court}

GET    /api/v1/owner/courts/{court}/pricing-rules
POST   /api/v1/owner/courts/{court}/pricing-rules
PUT    /api/v1/owner/pricing-rules/{rule}
DELETE /api/v1/owner/pricing-rules/{rule}

GET    /api/v1/owner/venues/{venue}/operating-hours
PUT    /api/v1/owner/venues/{venue}/operating-hours

GET    /api/v1/owner/venues/{venue}/holidays
POST   /api/v1/owner/venues/{venue}/holidays
DELETE /api/v1/owner/venues/{venue}/holidays/{holiday}

GET    /api/v1/owner/bookings
GET    /api/v1/owner/bookings/{booking}
POST   /api/v1/owner/bookings/{booking}/confirm
POST   /api/v1/owner/bookings/{booking}/reject
POST   /api/v1/owner/bookings/{booking}/check-in
POST   /api/v1/owner/bookings/{booking}/complete

GET    /api/v1/owner/reviews
POST   /api/v1/owner/reviews/{review}/reply

GET    /api/v1/owner/dashboard/stats
GET    /api/v1/owner/dashboard/revenue

--- ADMIN ---
GET    /api/v1/admin/users
GET    /api/v1/admin/users/{user}
PUT    /api/v1/admin/users/{user}/status

GET    /api/v1/admin/venues
GET    /api/v1/admin/venues/{venue}
POST   /api/v1/admin/venues/{venue}/approve
POST   /api/v1/admin/venues/{venue}/reject

GET    /api/v1/admin/bookings
GET    /api/v1/admin/payments

GET    /api/v1/admin/sports
POST   /api/v1/admin/sports
PUT    /api/v1/admin/sports/{sport}
DELETE /api/v1/admin/sports/{sport}

GET    /api/v1/admin/amenities
POST   /api/v1/admin/amenities
PUT    /api/v1/admin/amenities/{amenity}
DELETE /api/v1/admin/amenities/{amenity}

GET    /api/v1/admin/reviews
DELETE /api/v1/admin/reviews/{review}
POST   /api/v1/admin/reviews/{review}/hide

GET    /api/v1/admin/audit-logs
GET    /api/v1/admin/dashboard/stats
GET    /api/v1/admin/dashboard/revenue

GET    /api/v1/admin/configurations
PUT    /api/v1/admin/configurations/{key}
```

---

## 12. Authentication Architecture

### Technology: Laravel Sanctum (SPA + Mobile-ready)

Sanctum provides token-based authentication well-suited to both SPA (cookie-based) and mobile (bearer token) clients.

### Token Lifecycle

```
Register → Email Verification → Login → Token Issued
                                    ↓
                           Token stored (client-side)
                                    ↓
                    Authorization: Bearer {token} on every request
                                    ↓
                    Logout → Token revoked (deleted from personal_access_tokens)
```

### Password Security

- Passwords hashed with `bcrypt` (Laravel default, cost 12).
- Never stored in plain text.
- Never returned in API responses (API Resource `makeHidden(['password'])` enforced globally).

### Locked Accounts

- `users.status = 'locked'` checked via custom middleware `EnsureUserIsActive`.
- Applied after Sanctum auth middleware — locked users receive `403 Forbidden`.

### Rate Limiting

- Login endpoint: `throttle:10,1` (10 attempts per minute per IP).
- Password change: `throttle:5,1`.
- Register: `throttle:5,1`.

---

## 13. Authorization Architecture

### Principle: Policy-Driven, Backend-Enforced

Never check roles with `if ($user->role === 'admin')` inline in controllers. All authorization is expressed in dedicated Policy classes and enforced via `$this->authorize()`.

### Middleware Guards

```
api routes
└── auth:sanctum                    → all authenticated routes
    ├── role:admin                  → admin-only routes (custom middleware)
    ├── role:venue_owner            → owner-only routes
    └── (no role middleware)        → player routes (policy handles resource ownership)
```

### Policy Classes

| Policy | Model | Key Methods |
|--------|-------|-------------|
| `VenuePolicy` | `Venue` | `view`, `create`, `update`, `delete`, `manage` (owner only), `approve` (admin only) |
| `CourtPolicy` | `Court` | `create`, `update`, `delete` (venue owner only) |
| `BookingPolicy` | `Booking` | `view` (player owns or venue owner), `cancel` (player only), `confirm`/`reject`/`checkIn`/`complete` (venue owner) |
| `PaymentPolicy` | `Payment` | `view` (booking owner or admin) |
| `ReviewPolicy` | `Review` | `create` (booking owner, completed status), `delete` (admin), `reply` (venue owner) |
| `UserPolicy` | `User` | `update` (own profile), `lock`/`unlock` (admin) |

### HasRole Trait (on User model)

```php
public function hasRole(string $role): bool
public function hasAnyRole(array $roles): bool
public function isAdmin(): bool
public function isVenueOwner(): bool
public function isPlayer(): bool
```

---

## 14. Booking Conflict Prevention Strategy

This is the most critical section of the design.

### The Problem

If two users simultaneously request the same time slot:

```
User A: POST /api/v1/bookings (court 5, 2026-09-20, 18:00–20:00)
User B: POST /api/v1/bookings (court 5, 2026-09-20, 19:00–21:00)

If both pass an availability check simultaneously:
  → Both get INSERT → double booking → data corruption
```

### Solution: Pessimistic Locking inside DB Transaction

```php
// BookingService::create()
DB::transaction(function () use ($dto) {

    // 1. Lock the court row to serialize concurrent booking attempts
    $court = Court::lockForUpdate()->findOrFail($dto->courtId);

    // 2. Check for overlapping bookings WITHIN the transaction
    $conflict = Booking::where('court_id', $dto->courtId)
        ->where('booking_date', $dto->date)
        ->whereIn('status', ['pending', 'awaiting_payment', 'confirmed', 'checked_in'])
        ->where(function ($q) use ($dto) {
            // Overlap condition: existing.start < new.end AND existing.end > new.start
            $q->where('start_time', '<', $dto->endTime)
              ->where('end_time', '>', $dto->startTime);
        })
        ->lockForUpdate()  // also lock matching bookings
        ->exists();

    if ($conflict) {
        throw new BookingConflictException('The selected time slot is no longer available.');
    }

    // 3. Create the booking
    $booking = Booking::create([...]);

    // 4. Additional validations (venue open, not holiday, etc.)

    return $booking;
});
```

### Why This Works

- `DB::transaction()` wraps everything in an atomic operation.
- `lockForUpdate()` on the court row means concurrent requests on the same court are serialized — only one can proceed at a time.
- The conflict check and INSERT are in the same transaction, so no other transaction can insert a conflicting booking between the check and the insert.
- If MySQL isolation level is REPEATABLE READ (default), `lockForUpdate` (SELECT FOR UPDATE) still works correctly.

### Complementary Measures

| Layer | Mechanism |
|-------|-----------|
| Application | `BookingConflictException` → HTTP 409 response |
| Database | Composite index on `(court_id, booking_date, status)` for fast conflict queries |
| Frontend | Optimistic UI shows slot as unavailable immediately; not relied upon for correctness |
| Scheduler | `ExpireUnpaidBookings` job frees expired slots (AWAITING_PAYMENT → EXPIRED) |

---

## 15. Payment Architecture

### Interface-Based Abstraction

```php
interface PaymentGatewayInterface
{
    public function initiate(PaymentDto $dto): PaymentInitiateResult;
    public function verify(string $transactionId, array $payload): PaymentVerifyResult;
    public function refund(Payment $payment, float $amount): PaymentRefundResult;
}
```

### Implementations

| Class | Purpose |
|-------|---------|
| `MockPaymentGateway` | Development and testing; simulates success/failure |
| `MoMoGateway` | (Stub) Vietnamese e-wallet integration |
| `VNPayGateway` | (Stub) Vietnamese payment gateway |

### Service Provider Binding

```php
// Configurable in .env: PAYMENT_GATEWAY=mock
$this->app->bind(PaymentGatewayInterface::class, function ($app) {
    return match(config('payment.default')) {
        'momo'  => new MoMoGateway(...),
        'vnpay' => new VNPayGateway(...),
        default => new MockPaymentGateway(),
    };
});
```

### Payment Flow (Server-Side Verification)

1. Player calls `POST /api/v1/payments` with `booking_id`.
2. `PaymentService::initiate()` creates a `Payment` record with `status=pending`.
3. Returns a checkout URL / session.
4. Player completes payment on gateway.
5. Gateway calls `POST /api/v1/payments/webhook/{provider}` with result.
6. `PaymentWebhookController` verifies signature and delegates to `PaymentService::handleWebhook()`.
7. `PaymentService` updates `Payment.status` and transitions `Booking.status` inside a transaction.
8. Notifications dispatched via queue.

> **Security:** Webhook endpoint is excluded from `auth:sanctum` middleware but enforces provider-specific signature verification. Frontend cannot directly mark a payment as successful.

---

## 16. Notification Architecture

### Channels

| Priority | Channel | Use Case |
|----------|---------|---------|
| High | Database (in-app) | All user-facing events |
| Medium | Email (Queued) | Booking confirmation, approval, cancellation |
| Future | SMS | Booking reminders |
| Future | Push | Mobile app notifications |

### Notification Classes

```
app/Notifications/
├── BookingCreatedNotification
├── BookingConfirmedNotification
├── BookingCancelledNotification
├── BookingExpiringNotification
├── PaymentSuccessfulNotification
├── PaymentFailedNotification
├── VenueApprovedNotification
├── VenueRejectedNotification
└── ReviewReceivedNotification
```

All extend Laravel's `Notification` and implement `ShouldQueue` so they do not block the request lifecycle.

---

## 17. Queue Architecture

### Technology: Redis (via Laravel Queue)

### Queues (by priority)

| Queue | Workers | Purpose |
|-------|---------|---------|
| `high` | 2 | Payment webhooks, critical state transitions |
| `default` | 3 | Notifications, emails |
| `low` | 1 | Reports, data aggregation |

### Job Classes

```
app/Jobs/
├── SendBookingConfirmationEmail
├── SendPaymentNotification
├── SendVenueApprovalEmail
├── ExpireUnpaidBookings            ← Scheduler triggers this
├── GenerateDailyRevenueReport
└── ProcessPaymentWebhook
```

### Scheduler Configuration

```php
// app/Console/Kernel.php
$schedule->job(new ExpireUnpaidBookings)->everyFiveMinutes();
$schedule->job(new GenerateDailyRevenueReport)->dailyAt('01:00');
```

### Retry Strategy

- Max attempts: 3
- Backoff: `[60, 300, 900]` seconds (1 min → 5 min → 15 min)
- Failed jobs land in `failed_jobs` table for inspection.

---

## 18. Caching Strategy

### Technology: Redis (shared with Queue)

| Cache Key | TTL | Content |
|-----------|-----|---------|
| `venues.index.{hash}` | 5 min | Paginated venue search results |
| `venue.{id}` | 10 min | Venue detail with relations |
| `venue.{id}.availability.{date}` | 1 min | Available slots (short TTL, frequently changing) |
| `sports.all` | 1 hour | Sports list (rarely changes) |
| `amenities.all` | 1 hour | Amenities list |
| `system_config` | 10 min | Key-value system configurations |
| `venue.{id}.stats` | 15 min | Owner dashboard stats |
| `admin.dashboard.stats` | 5 min | Admin stats |

### Cache Invalidation

- Venue cache invalidated on: venue update, approval, new booking, new review.
- Availability cache invalidated on: booking created, cancelled, expired.
- Use `Cache::tags(['venue', "venue:{$id}"])` for grouped invalidation (Redis required).

---

## 19. Security Strategy

### Authentication
- Sanctum token auth; tokens stored in `personal_access_tokens`.
- Token expiry: 7 days (configurable).
- Logout revokes the current token only.

### Authorization
- Policy-driven (see Section 13).
- Never rely on frontend role display — all backend checks are enforced.

### Input Validation
- All inputs validated via FormRequest classes before reaching controllers.
- Enum values validated against PHP 8.1 backed enums.

### File Upload Security
- Allowed MIME types: `image/jpeg`, `image/png`, `image/webp` for images; `application/pdf` for documents.
- Max sizes: 5 MB for images, 10 MB for documents.
- Files stored outside `public/` using `storage/app/private/`; served via signed URLs.
- Filename sanitized before storage (UUID-based names, original extension stripped and re-applied after MIME validation).

### SQL Injection
- Eloquent ORM with parameter binding; no raw SQL with unbound user input.
- Where raw SQL is needed: `DB::select('... WHERE id = ?', [$id])`.

### Mass Assignment
- All models use `$fillable` (whitelist), never `$guarded = []`.

### Rate Limiting
- Auth endpoints: 10 req/min.
- API generally: 60 req/min per authenticated user.
- Webhook endpoints: IP-based allowlist (future: CIDR ranges for providers).

### Sensitive Data
- Passwords excluded from all API Resources.
- Payment secrets never logged or returned.
- Internal stack traces hidden in production (`APP_DEBUG=false`).

### CORS
- Configured in `config/cors.php` — only trusted frontend origins allowed.

---

## 20. Testing Strategy

### Tools
- **PHPUnit / Pest** for Feature and Unit tests.
- **Laravel's `RefreshDatabase` trait** — each test runs in a transaction that rolls back.
- **Factories** for test data generation.
- **`actingAs()`** helper for authentication in Feature tests.

### Test Structure

```
tests/
├── Feature/
│   ├── Auth/
│   │   ├── RegisterTest.php
│   │   ├── LoginTest.php
│   │   └── LogoutTest.php
│   ├── Booking/
│   │   ├── CreateBookingTest.php         ← happy path, validation, conflict
│   │   ├── CancelBookingTest.php
│   │   ├── BookingExpirationTest.php
│   │   └── BookingConflictTest.php       ← concurrent booking prevention
│   ├── Payment/
│   │   ├── InitiatePaymentTest.php
│   │   └── WebhookTest.php
│   ├── Venue/
│   │   ├── VenueApprovalTest.php
│   │   └── VenueManagementTest.php
│   ├── Review/
│   │   └── ReviewTest.php
│   └── Admin/
│       ├── UserManagementTest.php
│       └── AdminAuthorizationTest.php
└── Unit/
    ├── Services/
    │   ├── BookingServiceTest.php
    │   ├── AvailabilityServiceTest.php
    │   └── CancellationPolicyServiceTest.php
    └── Enums/
        └── BookingStatusTest.php
```

### Critical Test Cases

| Test | What it verifies |
|------|-----------------|
| `BookingConflictTest::two_simultaneous_bookings_result_in_one_success` | Core concurrency guard |
| `BookingConflictTest::overlapping_times_are_rejected` | Time overlap logic |
| `BookingExpirationTest::unpaid_booking_expires_after_window` | Scheduler + job |
| `WebhookTest::fraudulent_webhook_is_rejected` | Payment security |
| `AdminAuthorizationTest::player_cannot_access_admin_endpoint` | RBAC |
| `ReviewTest::cancelled_booking_cannot_be_reviewed` | Business rule |
| `CancellationPolicyServiceTest::full_refund_over_24_hours` | Policy math |

---

## 21. Folder Structure

```
sportbook/
├── app/
│   ├── Actions/
│   │   ├── Booking/
│   │   │   ├── CreateBookingAction.php
│   │   │   ├── CancelBookingAction.php
│   │   │   └── ExpireBookingAction.php
│   │   └── Venue/
│   │       └── ApproveVenueAction.php
│   │
│   ├── Console/
│   │   └── Commands/
│   │       └── ExpireUnpaidBookingsCommand.php
│   │
│   ├── DTOs/                                 ← Data Transfer Objects
│   │   ├── BookingDto.php
│   │   └── PaymentDto.php
│   │
│   ├── Enums/
│   │   ├── BookingStatus.php
│   │   ├── VenueStatus.php
│   │   ├── VenueVerificationStatus.php
│   │   ├── PaymentStatus.php
│   │   ├── CourtStatus.php
│   │   └── UserStatus.php
│   │
│   ├── Events/
│   │   ├── BookingCreated.php
│   │   ├── BookingCancelled.php
│   │   ├── BookingConfirmed.php
│   │   └── VenueApproved.php
│   │
│   ├── Exceptions/
│   │   ├── BookingConflictException.php
│   │   ├── InvalidBookingStateException.php
│   │   ├── PaymentException.php
│   │   └── VenueNotAvailableException.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── Auth/
│   │   │   │   │   └── AuthController.php
│   │   │   │   ├── Booking/
│   │   │   │   │   └── BookingController.php
│   │   │   │   ├── Payment/
│   │   │   │   │   ├── PaymentController.php
│   │   │   │   │   └── WebhookController.php
│   │   │   │   ├── Review/
│   │   │   │   │   └── ReviewController.php
│   │   │   │   ├── Venue/
│   │   │   │   │   └── VenueController.php
│   │   │   │   ├── Owner/
│   │   │   │   │   ├── OwnerVenueController.php
│   │   │   │   │   ├── OwnerCourtController.php
│   │   │   │   │   └── OwnerBookingController.php
│   │   │   │   └── Admin/
│   │   │   │       ├── AdminVenueController.php
│   │   │   │       ├── AdminUserController.php
│   │   │   │       └── AdminDashboardController.php
│   │   │   └── Controller.php
│   │   │
│   │   ├── Middleware/
│   │   │   ├── EnsureUserIsActive.php
│   │   │   └── EnsureHasRole.php
│   │   │
│   │   ├── Requests/
│   │   │   ├── Auth/
│   │   │   ├── Booking/
│   │   │   │   ├── CreateBookingRequest.php
│   │   │   │   └── CancelBookingRequest.php
│   │   │   ├── Venue/
│   │   │   ├── Court/
│   │   │   ├── Review/
│   │   │   └── Payment/
│   │   │
│   │   └── Resources/
│   │       ├── UserResource.php
│   │       ├── VenueResource.php
│   │       ├── VenueDetailResource.php
│   │       ├── CourtResource.php
│   │       ├── BookingResource.php
│   │       ├── PaymentResource.php
│   │       └── ReviewResource.php
│   │
│   ├── Jobs/
│   │   ├── ExpireUnpaidBookings.php
│   │   ├── SendBookingConfirmationEmail.php
│   │   ├── SendVenueApprovalEmail.php
│   │   └── GenerateDailyRevenueReport.php
│   │
│   ├── Listeners/
│   │   ├── SendBookingConfirmation.php
│   │   ├── NotifyOwnerOfNewBooking.php
│   │   └── SendVenueApprovedNotification.php
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Role.php
│   │   ├── Venue.php
│   │   ├── Court.php
│   │   ├── PricingRule.php
│   │   ├── Booking.php
│   │   ├── Payment.php
│   │   ├── Review.php
│   │   ├── Favorite.php
│   │   ├── Sport.php
│   │   ├── Amenity.php
│   │   ├── OperatingHour.php
│   │   ├── VenueHoliday.php
│   │   ├── AuditLog.php
│   │   └── SystemConfiguration.php
│   │
│   ├── Notifications/
│   │   ├── BookingCreatedNotification.php
│   │   ├── BookingConfirmedNotification.php
│   │   ├── BookingCancelledNotification.php
│   │   ├── PaymentSuccessfulNotification.php
│   │   ├── VenueApprovedNotification.php
│   │   └── ReviewReceivedNotification.php
│   │
│   ├── Policies/
│   │   ├── VenuePolicy.php
│   │   ├── CourtPolicy.php
│   │   ├── BookingPolicy.php
│   │   ├── PaymentPolicy.php
│   │   ├── ReviewPolicy.php
│   │   └── UserPolicy.php
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── AuthServiceProvider.php
│   │   └── PaymentServiceProvider.php
│   │
│   ├── Services/
│   │   ├── AvailabilityService.php
│   │   ├── BookingService.php
│   │   ├── CancellationPolicyService.php
│   │   ├── PricingService.php
│   │   ├── PaymentService.php
│   │   ├── VenueService.php
│   │   └── AuditService.php
│   │
│   └── Support/
│       └── Payment/
│           ├── PaymentGatewayInterface.php
│           ├── MockPaymentGateway.php
│           ├── MoMoGateway.php            ← stub
│           └── VNPayGateway.php           ← stub
│
├── config/
│   ├── payment.php
│   └── sportbook.php
│
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RoleSeeder.php
│       ├── UserSeeder.php
│       ├── SportSeeder.php
│       ├── AmenitySeeder.php
│       ├── VenueSeeder.php
│       ├── BookingSeeder.php
│       └── SystemConfigurationSeeder.php
│
├── routes/
│   ├── api.php
│   └── web.php                            ← Blade fallback / SPA entry
│
├── resources/
│   └── views/
│       └── app.blade.php                  ← Vue SPA shell (if Inertia or SPA)
│
└── tests/
    ├── Feature/
    └── Unit/
```

---

## 22. Development Phases

| Phase | Focus | Key Deliverables |
|-------|-------|-----------------|
| **Phase 1** | Architecture & Design | This document, ERD, API spec |
| **Phase 2** | Database | Migrations, Models, Factories, Seeders, Enums |
| **Phase 3** | Authentication | Register, Login, Logout, Sanctum, Roles, Middleware, Policies |
| **Phase 4** | Venue Management | Venue CRUD, Court CRUD, Images, Documents, Operating Hours, Pricing Rules, Admin Approval |
| **Phase 5** | Booking Engine | AvailabilityService, BookingService, conflict prevention, state machine, cancellation |
| **Phase 6** | Payment | PaymentGatewayInterface, MockGateway, PaymentService, WebhookController |
| **Phase 7** | Reviews / Favorites / Notifications | Review CRUD, Favorites, Queue-based notifications |
| **Phase 8** | Dashboards | Player, Owner, Admin dashboards — API + Frontend |
| **Phase 9** | Frontend (Vue 3 + Tailwind) | Key screens: Landing, Search, Venue Detail, Booking Flow, Dashboards |
| **Phase 10** | Testing | Feature tests for critical paths, fix failures |
| **Phase 11** | Code Quality | `./vendor/bin/pint`, N+1 review, security audit, query optimization |
| **Phase 12** | Documentation | README, ERD diagram, API docs, architecture guide |

---

## 23. Deployment Architecture

### Development

```
Docker Compose:
  - app (PHP-FPM 8.3)
  - nginx
  - mysql:8
  - redis
  - mailpit (local email testing)
  - php artisan horizon (queue monitoring)
```

### Production (recommended)

```
┌─────────────────────────────────────────────────────────┐
│                        CDN (CloudFlare)                  │
└─────────────────────────┬───────────────────────────────┘
                          │
                   ┌──────▼──────┐
                   │   Nginx     │  (SSL termination)
                   └──────┬──────┘
                          │
             ┌────────────┼────────────┐
             │            │            │
      ┌──────▼────┐ ┌─────▼─────┐ ┌──▼──────────┐
      │  PHP-FPM  │ │  PHP-FPM  │ │ Queue Worker │
      │  App #1   │ │  App #2   │ │  (Horizon)   │
      └──────┬────┘ └─────┬─────┘ └──────┬───────┘
             │            │              │
      ┌──────▼────────────▼──────────────▼───────┐
      │              Redis Cluster                │
      └───────────────────┬───────────────────────┘
                          │
      ┌───────────────────▼───────────────────────┐
      │           MySQL 8 (Primary)               │
      │           MySQL 8 (Read Replica)          │
      └───────────────────────────────────────────┘
```

### Key Deployment Checklist

- `APP_DEBUG=false`, `APP_ENV=production`
- `php artisan config:cache`, `route:cache`, `view:cache`
- `.env` never committed to Git
- Storage symlink: `php artisan storage:link`
- Queue worker managed by Supervisor
- Scheduler added to system cron: `* * * * * php artisan schedule:run`
- Laravel Horizon for queue monitoring (production)
- Laravel Telescope disabled in production (or behind auth gate)

---

## 24. Future Scalability Plan

| Area | Future Enhancement |
|------|--------------------|
| **Real Payments** | Replace `MockPaymentGateway` with `MoMoGateway` / `VNPayGateway` (interface already defined) |
| **Mobile App** | API is already mobile-ready; add push notification channel to Notifications |
| **Multi-tenancy** | Venue owners can add sub-accounts (staff role); schema supports this via `role_user` |
| **Multi-language** | Laravel localization already provides the framework; add `lang/` files |
| **Recurring Bookings** | Add `recurring_pattern` to bookings; BookingService generates instances |
| **Subscription Plans** | Add `venue_plans` table; different commission rates per plan |
| **Revenue Share** | Add `platform_commission` to payments; payout schedule to owners |
| **Advanced Search** | Swap MySQL FULLTEXT for Meilisearch / Elasticsearch (Scout driver) |
| **Geo Search** | Use MySQL spatial functions or PostGIS; architecture already stores lat/lng |
| **Real-time** | Add Laravel Reverb / Pusher for live availability updates |
| **Analytics** | Export revenue/booking data to BI tools (Metabase, Grafana) |
| **API Versioning** | `/api/v2/` prefix already planned in route structure |
| **Docker + CI/CD** | GitHub Actions → build → test → push Docker image → deploy |
| **Audit Compliance** | Export `audit_logs` to immutable cold storage |

---

*End of System Design Specification — SportBook v1.0*

*Next step: Await instruction to begin Phase 2 (Database — Migrations, Models, Factories, Seeders, Enums).*
