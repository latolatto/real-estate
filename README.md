# Real Estate Agency Website

A full-stack real estate website built with PHP, MySQL, HTML, CSS, JavaScript, and Bootstrap.

## What it includes

- Public property listing site
- Property detail pages with multi-image galleries
- Admin dashboard with sign in
- Major admin vs regular admin roles
- Property ownership rules per admin
- Local image uploads for listings
- Editable site content
- Admin account management

## Quick Start

1. Import [database.sql](/C:/Users/User/Documents/New%20project/database.sql) into a fresh MySQL database named `real_estate_agency`.
2. If you are upgrading the earlier version of this project instead of starting fresh, import [database_migration_v2.sql](/C:/Users/User/Documents/New%20project/database_migration_v2.sql) into the existing `real_estate_agency` database.
3. Update [config/database.php](/C:/Users/User/Documents/New%20project/config/database.php) if needed.
4. Make sure [uploads](/C:/Users/User/Documents/New%20project/uploads) is writable by PHP.
5. Start the app from the project root:

```bash
php -S localhost:8000
```

6. Open [http://localhost:8000](http://localhost:8000)

## Default Major Admin

- Email: `admin@harborhomes.com`
- Password: `Admin123!`

## Full Manual

See [MANUAL.md](/C:/Users/User/Documents/New%20project/MANUAL.md) for the full operating guide.
