# Riviera Events

A bilingual (English/Spanish) community event calendar for the Puerto Aventuras / Riviera Maya area. Visitors browse and filter events; organizers submit and manage their own listings; admins curate everything through a full-featured back-office panel.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12, PHP 8.2+ |
| Admin panel | Filament 3 |
| Frontend | Blade + Tailwind CSS + Alpine.js |
| Payments | Stripe (Laravel Cashier) |
| Auth | Laravel Breeze |
| Roles | Spatie Laravel Permission |
| Queue / cache | Database driver (default) |
| iCal | eluceo/ical |
| CSV import | league/csv |

---

## Features

- **Event calendar** — month, week, and list views with live AJAX filtering by category, date range, and location
- **Event detail pages** — slug-based URLs, rich descriptions, share modal (copy link / social)
- **Featured events** — paid placement via Stripe Checkout (MXN); webhook-driven approval
- **Recurring series** — weekly, biweekly, and monthly recurrences with admin-controlled series extension; expiry notification emails
- **iCal feeds** — per-event `.ics` download and a full `/feed.ics` calendar subscription endpoint
- **Public event submission** — email-verified submission flow; admin approval queue with approve/reject emails
- **Organizer dashboard** — authenticated organizers view, edit, and delete their own events
- **Admin panel** (`/admin`) — Filament-powered CRUD for events, categories, locations, users, ads, sponsorships, and payments; CSV bulk import with background job processing
- **Embed mode** — append `?embed=1` to render the calendar without navigation headers (for iframe use)
- **Full i18n** — English and Spanish; locale toggle persisted in session; `/locale/{en|es}` switcher route

---

## Local Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Install JS dependencies and compile assets
npm install && npm run build

# 3. Create your local env file
cp .env.example .env
php artisan key:generate

# 4. Run migrations (creates a local SQLite database by default)
php artisan migrate

# 5. (Optional) seed sample data
php artisan db:seed

# 6. Start the development server
composer run dev   # runs artisan serve + queue:listen + Vite + pail in parallel
```

Open `http://localhost:8000`. The admin panel is at `/admin` — create the first admin user with:

```bash
php artisan make:filament-user
```

---

## Key Environment Variables

```dotenv
# Application
APP_NAME="Riviera Events"
APP_URL=https://your-domain.com
APP_LOCALE=en          # default locale: en | es

# Database (SQLite for local, MySQL for production)
DB_CONNECTION=sqlite
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_DATABASE=riviera_events
# DB_USERNAME=root
# DB_PASSWORD=secret

# Mail (SMTP for production)
MAIL_MAILER=smtp
MAIL_HOST=mail.your-host.com
MAIL_PORT=465
MAIL_USERNAME=noreply@your-domain.com
MAIL_PASSWORD=secret
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="Riviera Events"

# Stripe (featured event payments)
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Queue (set to sync for local, database for production)
QUEUE_CONNECTION=database
```

---

## Production Deployment

1. Set `APP_ENV=production` and `APP_DEBUG=false`.
2. Run `npm run build` and commit compiled assets, or run it on the server.
3. Run `php artisan migrate --force` after each deploy.
4. Ensure a queue worker is running (`php artisan queue:work`); the Stripe webhook and CSV import jobs depend on it.
5. Point the Stripe webhook to `https://your-domain.com/stripe/webhook`.

---

## Project Structure (highlights)

```
app/
  Filament/          — Admin panel resources and pages
  Http/Controllers/  — Calendar, submission, organizer dashboard, iCal, Stripe webhook
  Models/            — Event, RecurringEventSeries, FeaturedEvent, Payment, Ad, ...
  Services/          — RecurringEventService, AdService
  Jobs/              — ProcessCsvImport, HandleStripePaymentFailed, RecordEventView
  Mail/              — Approval, rejection, verification, and series-expiry emails
resources/
  views/             — Blade templates (calendar, event detail, submission, dashboard)
  lang/en|es/        — Translation files
database/migrations/ — Full schema history
```

---

## Running Tests

```bash
composer run test
# or
php artisan test
```
