# CineBook

A single-cinema movie reservation system built with **Laravel 12**, Blade and MySQL 8. Customers hold seats and pay through Stripe Checkout (test mode), bookings are confirmed only by a signed Stripe webhook, and each confirmed seat gets a QR ticket. Staff scan tickets at the entrance; admins manage the cinema, refunds and reports.

## Demo accounts

`php artisan migrate --seed` creates these verified accounts (password: `password`):

| Role | Email | Start page |
| --- | --- | --- |
| Customer | `customer@cinebook.com` | `/` |
| Staff | `staff@cinebook.com` | `/staff/scan` |
| Admin | `admin@cinebook.com` | `/admin` |

## Requirements

- PHP 8.5 with `pdo_mysql`, `openssl`, `mbstring`, `fileinfo`, `gd`, `curl`, `zip` (and `pdo_sqlite` for tests)
- Composer 2, Node.js 20+, MySQL 8
- A Stripe test-mode account and the Stripe CLI

> `composer.json` allows PHP `^8.2`, but `composer.lock` pins `endroid/qr-code 6.2.0` (PHP `^8.5`) and Symfony 8 packages (PHP 8.4.1+). On older PHP run `composer config platform.php 8.2.0 && composer update` and commit the new lock file.

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Create an empty MySQL database named `cinebook` and set these in `.env` (the example file defaults to SQLite):

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cinebook
DB_USERNAME=root
DB_PASSWORD=
```

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
```

## Environment checklist

| Key | Purpose |
| --- | --- |
| `APP_URL` | Base URL, e.g. `http://127.0.0.1:8000` |
| `DB_*` | MySQL connection |
| `QUEUE_CONNECTION=database` | Mail and refunds are queued |
| `MAIL_MAILER` | `log` (mail goes to `storage/logs/laravel.log`) or SMTP |
| `STRIPE_KEY`, `STRIPE_SECRET` | Stripe test keys (`pk_test_...`, `sk_test_...`) |
| `STRIPE_WEBHOOK_SECRET` | `whsec_...` printed by `stripe listen` |
| `PAYMENT_GATEWAY` | `stripe`, or `fake` for offline demos |

Secrets live only in `.env`, which is git-ignored. Never commit keys or log full ticket tokens.

## Running

```bash
php artisan serve            # web server
npm run dev                  # Vite (only while editing CSS/JS)
php artisan queue:work       # mail and refund jobs
php artisan schedule:work    # runs bookings:expire every minute
```

Mail and refund jobs make 4 attempts (the first try plus 3 retries, backed off 1, 5 and 15 minutes) and then land in `failed_jobs`; the admin "Failed Jobs" page can retry or delete them. In production, use a cron entry for the scheduler and Supervisor for the worker:

```cron
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Stripe sandbox

1. Put the test keys in `.env` and set `PAYMENT_GATEWAY=stripe`.
2. Forward webhooks:
   ```bash
   stripe login
   stripe listen --forward-to http://127.0.0.1:8000/stripe/webhook
   ```
3. Copy the printed `whsec_...` into `STRIPE_WEBHOOK_SECRET` and restart `php artisan serve`.
4. Pay with card `4242 4242 4242 4242`, any future expiry, any CVC.

Without `stripe listen`, bookings stay `pending_payment`: the success page only says "Payment processing" and never confirms a booking.

## How a booking is confirmed

1. The customer holds seats (`SeatHoldService`, one transaction).
2. Checkout creates a `pending_payment` booking, a payment record and a Stripe Checkout Session. Prices are computed on the server.
3. Stripe calls `/stripe/webhook`; the signature is verified (invalid returns 400).
4. In one transaction the booking becomes `confirmed`, one ticket per seat is created with a random 40-character token, and the holds are removed.
5. The event ID is stored in `stripe_events` (unique), so a replayed event is ignored.
6. If payment arrives after the hold expired and the seats were taken, the booking is not confirmed; the payment is refunded and the customer is emailed.
7. A mail failure never changes booking or payment state.

Fixed rules are in `app/Support/BookingRules.php`: sales close 15 minutes before the show; scanning opens 60 minutes before and closes 30 minutes after the start. Hold minutes, max seats, cancellation window and premium surcharge are editable in `/admin/settings`.

## How double booking is prevented

1. Requested seats must belong to the showtime's screen, not be disabled, and the showtime must be on sale.
2. `SeatHoldService` runs in a transaction and locks the seat rows with `SELECT ... FOR UPDATE`. A hold is all-or-nothing.
3. Expired holds are deleted inside the hold transaction and never count as held, so a late scheduler does not block new holds.
4. `seat_holds` has a unique index on `(showtime_id, seat_id)`; if two requests still race, the second insert fails with "Someone just took one of those seats."
5. Before confirming a webhook, `PaymentService` locks the booking and checks that no other confirmed booking or live hold owns the seats; otherwise it refunds.
6. A scan runs `UPDATE tickets SET status='used' WHERE id=? AND status='valid'` and checks the affected rows, so two simultaneous scans cannot both succeed.

## Tests

```bash
php artisan test
```

Runs on in-memory SQLite with the fake payment gateway; no MySQL or network needed. The six required acceptance tests:

| Requirement | Test |
| --- | --- |
| A seat can't be held twice | `SeatHoldTest::test_a_seat_cannot_be_held_twice_for_the_same_showtime` |
| An expired hold releases the seat | `SeatHoldTest::test_an_expired_hold_releases_the_seat` |
| A webhook confirms exactly once | `PaymentWebhookTest::test_a_webhook_confirms_a_booking_exactly_once_even_if_delivered_twice` |
| Cancellation inside 2 hours is rejected | `CancellationTest::test_cancellation_inside_the_two_hour_window_is_rejected` |
| A duplicate QR scan is rejected | `TicketScanTest::test_a_duplicate_qr_scan_is_rejected` |
| A customer can't view another's booking | `BookingAccessTest::test_a_customer_cannot_view_another_customers_booking` |

## Backup and restore

The MySQL database and uploaded posters (`storage/app/public`) cannot be regenerated from code.

```bash
mysqldump -u root -p --single-transaction --routines cinebook > cinebook-$(date +%F).sql
tar -czf cinebook-storage-$(date +%F).tar.gz storage/app/public public/storage
```

Run both on the same schedule and keep them together. Restore with `mysql -u root -p cinebook < cinebook-YYYY-MM-DD.sql`, then unpack the storage archive. `.env` is not in the backup; keep it in a password manager. Test a restore into a scratch database now and then.

## Known limitations

- Once a customer clicks Pay, the booking is `pending_payment` and its seats stay unavailable until the scheduler expires it, even after the 10-minute hold ends. Keep `schedule:work` running.
- After confirmation, "one buyer per seat per showtime" is enforced by application checks and row locks, not by a unique database constraint.
- Ownership checks are in controllers (no Policy classes), and validation is inline (only the Breeze form requests exist).
- Customer routes need login and a verified email but are not role-restricted; staff and admin areas are.
- Partial or tiered refunds (bonus) are not implemented; every refund is 100%.