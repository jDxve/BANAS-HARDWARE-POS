# Bañas Hardware POS

A simple point-of-sale and inventory system for a hardware store — checkout, stock tracking, weekly sales reports, and clerk accounts, built with plain PHP, MySQL, and no frontend frameworks.

## Features

- **POS** — category-filtered catalog, live cart, payment/change calculation, printable receipts
- **Inventory** — add, restock, and delete products, with low-stock warnings
- **Void / refund** — void a completed sale and automatically restore its stock
- **Sales reports** — weekly dashboard, sales trend chart, and weekly archive/reset
- **Roles** — admins manage inventory, sales, and clerks; clerks only see the POS
- **Secure by default** — hashed PINs, CSRF protection, prepared statements, no CDN dependencies

## Tech Stack

- PHP 8+ with `mysqli`
- MySQL / MariaDB
- Hand-written HTML/CSS/JS — no frameworks or build step

## Project Structure

```
.
├── login.php, dashboard.php, inventory.php, sales.php, pos.php, logout.php
├── api/            JSON endpoints (checkout, sales chart, inventory history)
├── assets/         css, js, and images
├── config/         database connection settings
├── includes/       shared PHP (db connection, auth/CSRF, helpers, sidebar)
└── database/       schema.sql and seed.php
```

## Getting Started

**1. Set your database credentials** (defaults to `localhost` / `root` / no password / `banas_hardware_pos`). Override with `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, `DB_PORT` environment variables if needed.

**2. Create and seed the database:**

```bash
php database/seed.php
```

This creates the database, applies the schema, and seeds a default admin account — Login ID `627-999-726`, PIN `000000` (change it after your first login).

**3. Run the app:**

```bash
php -S localhost:8000
```

Then open `http://localhost:8000/login.php`.

## License

No license has been specified for this project.
