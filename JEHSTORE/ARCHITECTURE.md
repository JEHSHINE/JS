# JEH Store Architecture

This repository now includes a modular architecture for the JEH Store e-commerce system.

## Components

- `backend/` — PHP REST API microservice with secure authentication, product, cart, order, and admin endpoints.
- `database/` — MySQL schema definition for normalized tables and referential integrity.
- `admin_panel/` — PHP-based admin dashboard shell with login, dashboard, and analytics page skeletons.
- `flutter_app/` — Flutter frontend shell for shopping, login, product browsing, cart, and checkout.

## Architecture Principles

- Separation of concerns between frontend, backend, database, and admin UI.
- Reusable components and service layers.
- Secure API design with password hashing and prepared statements.
- Normalized relational schema with foreign keys.
- Organized image storage and dynamic path mapping.

## Next steps

1. Implement backend endpoint logic in `backend/src/Controllers/`.
2. Add admin dashboard pages in `admin_panel/pages/`.
3. Build Flutter UI flows in `flutter_app/lib/screens/`.
4. Deploy MySQL schema from `database/schema.sql`.
