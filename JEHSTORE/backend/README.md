# JEH Store Backend

## Setup

1. Install dependencies:
   ```bash
   composer install
   ```

2. Configure environment variables in `backend/.env`.
3. Serve the API from `backend/public/index.php`.

## Structure

- `public/` — web-accessible entry point and API router.
- `src/Config.php` — environment and database configuration.
- `src/Database/Database.php` — PDO connection helper.
- `src/Controllers/` — controller classes for auth, product, cart, order, admin.
- `routes.php` — endpoint definitions.
