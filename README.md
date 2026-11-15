# CineBook

A cinema ticketing web application built with Laravel 12 and Blade. Customers browse movies, pick seats on a live seat map, pay through Stripe Checkout, and receive QR tickets. Staff scan QR codes at the door. Admins manage the catalogue, showtimes, screenings, customers, refunds and reports.

The interface uses a custom dark "midnight cinema" theme (Tailwind + Alpine.js) built from the Cinebook Figma design.

---

## 1. Requirements

| Tool | Version |
| --- | --- |
| PHP | 8.2 or newer (extensions: `pdo_mysql`, `openssl`, `mbstring`, `fileinfo`, `gd`, `curl`, `zip`, and `pdo_sqlite` for the test suite) |
| Composer | 2.x |
| Node.js | 20 or newer |
| MySQL | 8.x |
| Stripe | Test-mode account (sandbox) + Stripe CLI for local webhooks |

## 2. Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Create an empty MySQL database named `cinebook`, then point `.env` at it:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cinebook
DB_USERNAME=root
DB_PASSWORD=
```

Then:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
```

`migrate --seed` creates three verified demo accounts (password: `password`):

| Role | Email | Entry point |
| --- | --- | --- |
| Customer | `customer@cinebook.com` | `/` and `/bookings` |
| Staff | `staff@cinebook.com` | `/staff/scan` |
| Admin | `admin@cinebook.com` | `/admin` |

## 3. Environment checklist

| Key | Purpose |
| --- | --- |
| `APP_URL` | Base URL used in mails and signed links (e.g. `http://127.0.0.1:8000`) |
| `DB_*` | MySQL connection |
| `QUEUE_CONNECTION=database` | Booking emails and refunds run on the queue |
| `MAIL_MAILER` | `log` for development (mail appears in `storage/logs/laravel.log`), SMTP for a real inbox |
| `STRIPE_KEY` / `STRIPE_SECRET` | Stripe sandbox API keys (`pk_test_...`, `sk_test_...`) |
| `STRIPE_WEBHOOK_SECRET` | Signing secret printed by `stripe listen` (`whsec_...`) |
| `PAYMENT_GATEWAY` | `stripe` for the real sandbox flow, `fake` for offline demos |

Secrets belong only in `.env`. `.env` is git-ignored, and `.env.example` ships with empty values.

## 4. Running the app

Use four terminals while developing:

```bash
# 1. Web server
php artisan serve

# 2. Vite (hot reload of CSS/JS)
npm run dev

# 3. Queue worker (ticket mails, verification mails, refunds)
php artisan queue:work

# 4. Scheduler (expires unpaid bookings and stale seat holds every minute)
php artisan schedule:work
```

In production the scheduler is a single cron entry:

```cron
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

Run `php artisan queue:work` under Supervisor (or `queue:listen` while developing) so queued mails are delivered. Failed jobs are listed at `/admin/failed-jobs`, where an admin can retry or delete them.

## 5. Stripe sandbox setup

1. Put the test keys in `.env`:
   ```dotenv
   STRIPE_KEY=pk_test_...
   STRIPE_SECRET=sk_test_...
   PAYMENT_GATEWAY=stripe
   ```
2. Forward Stripe events to the local app:
   ```bash
   stripe login
   stripe listen --forward-to http://127.0.0.1:8000/stripe/webhook
   ```
3. Copy the printed `whsec_...` value into `STRIPE_WEBHOOK_SECRET` and restart `php artisan serve`.
4. Pay with the test card `4242 4242 4242 4242`, any future expiry, any CVC.

A booking is confirmed **only** by the signature-verified `checkout.session.completed` webhook with `payment_status = paid`. The `/checkout/success` page never confirms anything; it only shows "Payment processing" until the webhook lands. Duplicate webhook deliveries are ignored through the unique `stripe_events.event_id`, and a payment that arrives after the seat hold expired is refunded automatically instead of double-booking the seat.

## 6. Tests

```bash
php artisan test
```

The suite uses an in-memory SQLite database (`phpunit.xml`) and the `fake` payment gateway, so it needs no MySQL or network access. 40 tests / 97 assertions currently pass.

The six acceptance tests required by the Phase 2 specification:

| Requirement | Test |
| --- | --- |
| A seat cannot be held twice for the same showtime | `tests/Feature/SeatHoldTest.php` — `test_a_seat_cannot_be_held_twice_for_the_same_showtime` |
| An expired hold releases the seat | `tests/Feature/SeatHoldTest.php` — `test_an_expired_hold_releases_the_seat` |
| A webhook confirms a booking exactly once, even if delivered twice | `tests/Feature/PaymentWebhookTest.php` — `test_a_webhook_confirms_a_booking_exactly_once_even_if_delivered_twice` |
| Cancellation inside the 2-hour window is rejected | `tests/Feature/CancellationTest.php` — `test_cancellation_inside_the_two_hour_window_is_rejected` |
| A duplicate QR scan is rejected | `tests/Feature/TicketScanTest.php` — `test_a_duplicate_qr_scan_is_rejected` |
| A customer cannot view another customer's booking | `tests/Feature/BookingAccessTest.php` — `test_a_customer_cannot_view_another_customers_booking` |

Additional coverage: invalid webhook signature, sales closing 15 minutes before a showtime, refunds outside the window, scanned tickets blocking cancellation, wrong-showtime scans, scans before the entry window, and ticket ownership.

> Windows note: the suite needs `extension=pdo_sqlite` enabled in `php.ini`.

## 7. Business rules

Fixed rules live in `app/Support/BookingRules.php`:

| Rule | Value |
| --- | --- |
| Online sales close before a showtime | 15 minutes |
| QR scanning opens before a showtime | 60 minutes |
| QR scanning closes after a showtime | 30 minutes |
| Self-service cancellation cutoff | 2 hours (configurable setting) |

Editable settings (`/admin/settings`): seat-hold duration, maximum seats per booking, cancellation cutoff, premium-seat surcharge and currency. Seat prices are computed server-side by `PricingService`; the browser never sends prices.

## 8. How double booking is prevented

1. **Validation** — requested seats must belong to the showtime's screen, must not be disabled, and the showtime must still be on sale.
2. **Transaction + row locks** — `SeatHoldService` runs inside a database transaction and locks the requested seat rows with `SELECT ... FOR UPDATE`.
3. **Expired holds are purged first** — expired rows are deleted inside the same transaction, so the scheduler is not required for correctness.
4. **Database uniqueness** — `seat_holds` has a unique index on `(showtime_id, seat_id)`. If two requests still race, the second insert throws, and the service converts the constraint error into "Someone just took one of those seats."
5. **Confirmation check** — before confirming a webhook, `PaymentService` verifies that no other confirmed booking or live hold owns those seats. If the hold expired while the customer was paying, the payment is refunded and the booking is marked `refunded`.
6. **Atomic admission** — a scan is `UPDATE tickets SET status = 'used' WHERE id = ? AND status = 'valid'`. The row count decides whether the holder is admitted, so the same QR cannot be used twice.

## 9. Security notes

- CSRF protection is enabled on every form; Stripe is the only route excluded, and it is authenticated by its signature instead.
- All user data is output through Blade escaping. Queries are built with the query builder/Eloquent (bound parameters), never by string concatenation.
- Route groups are protected by `auth`, `verified` and `role:staff,admin` / `role:admin` middleware. Controllers additionally check ownership (a customer can only reach their own bookings and tickets).
- The QR code contains a random 40-character opaque token, never a URL and never personal data.
- Card numbers never touch the application: payment happens on Stripe's hosted Checkout page. Logs contain event types only, never secrets or tokens.
- Audit entries (`audit_logs`) record actor, action, target, before/after values and IP for sensitive admin actions.

## 10. Project map

```
app/
  Console/Commands/ExpireStaleBookings.php   scheduler: expire holds + unpaid bookings
  Contracts/PaymentGateway.php               gateway abstraction
  Http/Controllers/                          public, customer, staff and Admin/* controllers
  Jobs/RefundBookingJob.php                  retried, idempotent refunds
  Services/                                  PaymentService, SeatHoldService, PricingService,
                                             RefundService, TicketScanService, SettingsService,
                                             Payments/StripeGateway, Payments/FakeGateway
  Support/BookingRules.php                   fixed time-window constants
resources/views/
  components/layouts/                        public / admin / app shells
  admin/                                     admin screens
  staff/scan.blade.php                       scanner
  booking/, checkout/, tickets/, bookings/   customer journey
tests/Feature/                               acceptance + regression tests
```

## 11. Troubleshooting

- **419 Page Expired** — refresh the page; the CSRF token rotates after login.
- **Webhook returns 400** — the signature did not match. Re-run `stripe listen`, copy the new `whsec_...` into `.env`, and restart the server.
- **Payment done but booking still pending** — no webhook reached the app. Check `stripe listen`, the queue worker, and `storage/logs/laravel.log`.
- **Seats stay "held"** — make sure `php artisan schedule:work` (or cron) is running.
- **No emails** — with `MAIL_MAILER=log` they are written to `storage/logs/laravel.log`; with SMTP, check the credentials and that the queue worker is running.
- **Posters do not load** — run `php artisan storage:link`.
