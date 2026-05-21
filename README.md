# Voca — Backend (Laravel 11 REST API)

REST API for **Voca**, a platform where NGOs publish opportunities (seminars,
workshops, camps, competitions, courses, mentorships, volunteering…) for young
people aged 15–30. Built with Laravel 11, Sanctum token auth and MySQL.

> Frontend (React + Vite + TS): see the `voca-front` repository.

## Requirements

- PHP 8.2+ (developed on 8.4)
- Composer 2.x
- MySQL 8 (or MariaDB). SQLite also works out of the box for quick local runs.

## 1. Install & configure

```bash
composer install
cp .env.example .env        # if .env doesn't already exist
php artisan key:generate
php artisan storage:link     # makes uploaded images publicly accessible
```

Edit `.env` and set your database + URLs:

```dotenv
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173      # used for CORS + email links

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=voca
DB_USERNAME=root
DB_PASSWORD=

# Emails are queued. "log" writes them to storage/logs/laravel.log (no SMTP needed).
MAIL_MAILER=log
QUEUE_CONNECTION=database
```

Create the database first (`CREATE DATABASE voca;`).

**Prefer zero-config SQLite?** Set `DB_CONNECTION=sqlite` and run
`touch database/database.sqlite` — then skip the DB credentials above.

## 2. Migrate & seed

```bash
php artisan migrate --seed
```

The seeder creates demo data. All accounts use the password **`password`**:

| Role  | Email            |
|-------|------------------|
| Admin | admin@voca.test  |
| NGO   | nvo@voca.test    |
| NGO   | green@voca.test  |
| NGO   | arts@voca.test   |
| Youth | ana@voca.test    |
| Youth | marko@voca.test  |
| Youth | jelena@voca.test |
| Youth | stefan@voca.test |

## 3. Run

```bash
php artisan serve              # API on http://localhost:8000
php artisan queue:work         # in a second terminal — sends queued emails
```

## API overview

Base path: `/api`. Auth via `Authorization: Bearer <token>` (Sanctum).

### Public
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/register` | Register a youth user |
| POST | `/register/nvo` | Register an NGO account |
| POST | `/login` | Log in, returns a token |
| GET | `/categories` | List categories / interests |
| GET | `/stats` | Public platform stats |
| GET | `/calls` | List calls (filters: `type`, `category`, `online`, `search`, `page`) |
| GET | `/calls/{id}` | Call detail |
| GET | `/calls/{id}/similar` | Similar calls |
| GET | `/calls/{id}/feedbacks` | Reviews for a call |

### Authenticated (any role)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/user` | Current user |
| POST | `/logout` | Revoke current token |
| PUT/POST | `/profile` | Update profile (`POST` for avatar upload) |
| GET | `/feed` | Personalized feed (matches interests) |
| POST/DELETE | `/calls/{id}/apply` | Apply / withdraw |
| GET | `/my/applications` | My applications + statuses |
| POST | `/calls/{id}/save` | Toggle wishlist |
| GET | `/my/saved` | Wishlist |
| POST | `/calls/{id}/feedbacks` | Leave a review (after completion) |
| GET | `/my/feedbacks` | My reviews |

### NGO only (`nvo` middleware)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/nvo/stats` | Dashboard stats |
| GET | `/nvo/calls` | My calls |
| POST | `/calls` | Create call (multipart for image) |
| PUT/POST | `/calls/{id}` | Update call (`POST` for image upload) |
| DELETE | `/calls/{id}` | Delete call |
| PUT | `/profile/nvo` | Update org details / intro message |
| GET | `/calls/{id}/applicants` | List applicants |
| PUT | `/applications/{id}/status` | Accept / reject / complete |
| POST | `/calls/{id}/announce` | Email all applicants |

## Architecture notes

- **Single taxonomy**: one `categories` table powers both call categories and
  user interests, so the personalized feed is a simple "shared category" match.
- **Notifications** (`Welcome`, `ApplicationReceived`, `ApplicationStatus`,
  `CallAnnouncement`) implement `ShouldQueue` — run a queue worker to deliver.
- **Roles**: `youth | nvo | admin` on the `users` table; the admin role has
  scaffolding (`adminStats`) ready to extend.
- File uploads are stored on the `public` disk (`storage/app/public`).
