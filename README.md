# CineBook

Single-cinema movie reservation system built with **Laravel 12**, Blade and MySQL 8.

Customers hold seats and pay through Stripe Checkout (test mode). A booking is confirmed **only** by a signature-verified Stripe webhook, and every confirmed seat receives a QR ticket. Staff scan tickets at the entrance; admins manage the cinema, refunds and reports.

## Features

**Public**

- Home page with featured-movie hero, "What's on" schedule for the next seven days, now-showing and coming-soon lists
- Movie catalogue with search, genre filters and a movie detail page with trailer and showtimes

**Customer**

- Live seat map with premium rows, held/sold states and a hold countdown
- Stripe Checkout in test mode; pricing is computed on the server
- Booking history, booking detail, cancellation inside the allowed window, and QR tickets

**Staff (read-only)**

- Today's showtimes with live ticket counts
- QR token lookup and scan: valid, already used, wrong showtime, outside the scan window
- Read-only list of bookings for the selected show

**Admin**

- Movies (poster upload or URL, genres, featured flag), screens with seat layout and per-seat disable, showtimes with overlap protection and cancellation, genres
- Bookings with detail and cancellation, customers with role changes, settings, audit log
- Reports with CSV export, plus a Failed Jobs page with retry/delete

## Tech stack

| Layer | Choice |
| --- | --- |
| Framework | Laravel 12 (PHP 8.5) |
| Database | MySQL 8 / InnoDB; SQLite `:memory:` for tests |
| Views | Blade + Alpine.js, Tailwind CSS 3.4 via Vite 7 |
| Auth | Laravel Breeze (email verification, password reset) |
| Payments | Stripe Checkout + signed webhook; `PAYMENT_GATEWAY=fake` for offline demos |
| Tickets | QR code that encodes only an opaque 40-character token |
| Queue | Database driver for mail and refunds |

## Demo accounts

`php artisan migrate --seed` creates these verified accounts (password: `password`):

| Role | Email | Start page |
| --- | --- | --- |
| Customer | `customer@cinebook.com` | `/` |
| Staff | `staff@cinebook.com` | `/staff/scan` |
| Admin | `admin@cinebook.com` | `/admin` |

Seeded movies use artwork bundled in `public/images/posters` and `public/images/hero`, so the demo works without internet access or `storage:link`.

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

`storage:link` is only needed for posters uploaded through the admin panel; seeded artwork is served straight from `public/images`.

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

Four terminals keep the full flow alive:

```bash
php artisan serve            # 1. web server
php artisan queue:work       # 2. mail and refund jobs
php artisan schedule:work    # 3. runs bookings:expire every minute
npm run dev                  # 4. only while editing CSS or JS
```

Mail and refund jobs make 4 attempts (first try plus 3 retries, backed off 60, 300 and 900 seconds) and then land in `failed_jobs`; the admin "Failed Jobs" page can retry or delete them. In production, use a cron entry for the scheduler and Supervisor for the worker:

```cron
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Deploy for free (Render + Neon)

`Dockerfile` and `docker/entrypoint.sh` are included, so Render builds and runs the app without extra setup. Neon provides a free Postgres database (1 GB).

1. **Neon**: create a project and copy the connection details (host, database, user, password).
2. **Render**: New → Web Service → connect this repository → Language **Docker** → Instance type **Free**.
3. **Environment variables**:

   | Key | Value |
   | --- | --- |
   | `APP_ENV` | `production` |
   | `APP_DEBUG` | `false` |
   | `APP_KEY` | `php artisan key:generate --show` output |
   | `APP_URL` | `https://<service>.onrender.com` |
   | `LOG_CHANNEL` | `stderr` |
   | `DB_CONNECTION` | `pgsql` |
   | `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Neon values |
   | `DB_SSLMODE` | `require` |
   | `SESSION_DRIVER` | `database` |
   | `CACHE_STORE` | `database` |
   | `QUEUE_CONNECTION` | `sync` |
   | `MAIL_MAILER` | `log` |
   | `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` | Stripe test keys |
   | `PAYMENT_GATEWAY` | `stripe` |
   | `CRON_TOKEN` | any long random string |
   | `RUN_SEED` | `true` for the first deploy only |

4. The container runs `migrate` on every boot. The first deploy with `RUN_SEED=true` creates the demo accounts and showtimes; set it to `false` afterwards.
5. **Cron**: free services have no scheduler. Add a job on cron-job.org that opens `https://<service>.onrender.com/cron/expire/<CRON_TOKEN>` every 5 minutes.
6. **Stripe webhook**: in the Stripe dashboard add an endpoint for `https://<service>.onrender.com/stripe/webhook`, select the checkout events, and copy the signing secret into `STRIPE_WEBHOOK_SECRET`.

Free-plan notes: the service sleeps after 15 minutes without traffic, so the first request can take about a minute; admin-uploaded posters live on ephemeral disk and disappear on redeploy (seeded artwork is inside the repository); queued mail is sent inline because `QUEUE_CONNECTION=sync`.

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

## Business rules

Fixed rules live in `app/Support/BookingRules.php`:

- Sales close 15 minutes before the show.
- Scanning opens 60 minutes before the start and closes 30 minutes after.
- Cancellation is allowed up to 2 hours before the show.

Hold minutes, maximum seats per booking, cancellation cutoff and the premium surcharge are editable in `/admin/settings`.

## How double booking is prevented

1. Requested seats must belong to the showtime's screen, not be disabled, and the showtime must be on sale.
2. `SeatHoldService` runs in a transaction and locks the seat rows with `SELECT ... FOR UPDATE`. A hold is all-or-nothing.
3. Expired holds are deleted inside the hold transaction and never count as held, so a late scheduler does not block new holds.
4. `seat_holds` has a unique index on `(showtime_id, seat_id)`; if two requests still race, the second insert fails with "Someone just took one of those seats."
5. Before confirming a webhook, `PaymentService` locks the booking and checks that no other confirmed booking or live hold owns the seats; otherwise it refunds.
6. A scan runs `UPDATE tickets SET status='used' WHERE id=? AND status='valid'` and checks the affected rows, so two simultaneous scans cannot both succeed.

## Security

- Role middleware (`role:staff,admin`, `role:admin`) guards staff and admin routes; customers can only book.
- Policies (`BookingPolicy`, `TicketPolicy`) stop a customer from opening another customer's booking or ticket.
- Every write goes through a Form Request in `app/Http/Requests` (NFR-10), including the seat-hold request.
- CSRF protection is on for every form; only `/stripe/webhook` is exempt, and it is authenticated by the Stripe signature instead.
- The QR code contains only the opaque ticket token, never customer details.
- Card data never reaches the application; Stripe Checkout hosts the payment form.
- `verification.verify` keeps `auth`, `signed` and `throttle:6,1`, so verification links are signed and rate-limited.
- Admin changes are written to `audit_logs` with before/after values and the actor's IP.

## Tests

```bash
php artisan test
```

47 tests and 118 assertions run on in-memory SQLite with the fake payment gateway; no MySQL, Stripe or network access is needed. The six required acceptance tests:

| Requirement | Test |
| --- | --- |
| A seat can't be held twice | `SeatHoldTest::test_a_seat_cannot_be_held_twice_for_the_same_showtime` |
| An expired hold releases the seat | `SeatHoldTest::test_an_expired_hold_releases_the_seat` |
| A webhook confirms exactly once | `PaymentWebhookTest::test_a_webhook_confirms_a_booking_exactly_once_even_if_delivered_twice` |
| Cancellation inside 2 hours is rejected | `CancellationTest::test_cancellation_inside_the_two_hour_window_is_rejected` |
| A duplicate QR scan is rejected | `TicketScanTest::test_a_duplicate_qr_scan_is_rejected` |
| A customer can't view another's booking | `BookingAccessTest::test_a_customer_cannot_view_another_customers_booking` |

## Project structure

```
app/
  Http/Controllers/        public, customer, staff and Admin/ controllers
  Http/Requests/           Form Requests (NFR-10)
  Models/                  Movie, Screen, Seat, Showtime, Booking, Ticket, Payment, ...
  Policies/                BookingPolicy, TicketPolicy
  Services/                SeatHoldService, PaymentService, RefundService, PricingService, TicketScanService, AuditLogger
  Support/                 BookingRules, Qr
database/
  migrations/              schema
  seeders/                 demo users, screens, movies, showtimes, settings
public/images/
  posters/                 bundled poster artwork used by the seeder
  hero/                    bundled hero backdrop
resources/views/           Blade layouts, public pages, staff and admin pages
tests/Feature/             acceptance and security tests
```

## Troubleshooting

| Symptom | Fix |
| --- | --- |
| Bookings never confirm | Start `stripe listen` and check `STRIPE_WEBHOOK_SECRET`; the webhook is the only confirmation source |
| Seats stay blocked after closing the tab | Run `php artisan schedule:work` (or `php artisan bookings:expire`) to release expired holds |
| Emails are not delivered | Set `QUEUE_CONNECTION=database` and run `php artisan queue:work`; mail is written to the log when `MAIL_MAILER=log` |
| Admin-uploaded posters 404 | Run `php artisan storage:link` |
| Styles look unstyled after pulling | Run `npm install && npm run build` |
| Scanner says "Wrong showtime" | The ticket belongs to another date or screen; the scanner lists today's showtimes only |

## Backup and restore

The MySQL database and admin-uploaded posters (`storage/app/public`) cannot be regenerated from code. Seeded artwork in `public/images` comes from the repository.

```bash
mysqldump -u root -p --single-transaction --routines cinebook > cinebook-$(date +%F).sql
tar -czf cinebook-storage-$(date +%F).tar.gz storage/app/public public/storage
```

Run both on the same schedule and keep them together. Restore with `mysql -u root -p cinebook < cinebook-YYYY-MM-DD.sql`, then unpack the storage archive. `.env` is not in the backup; keep it in a password manager. Test a restore into a scratch database now and then.

## Known limitations

- Once a customer clicks Pay, the booking is `pending_payment` and its seats stay unavailable until the scheduler expires it, even after the 10-minute hold ends. Keep `schedule:work` running.
- After confirmation, "one buyer per seat per showtime" is enforced by application checks and row locks, not by a unique database constraint.
- Partial or tiered refunds (bonus) are not implemented; every refund is 100%.
- The staff scanner lists today's showtimes only.
