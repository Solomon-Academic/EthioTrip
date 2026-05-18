# EthioTrip (Integrated)

This folder contains the `ethiotrip` PHP MVC app. I integrated the available `frontend` static assets and inspected the `backend` procedural scripts. Use the instructions below to get the app running locally (XAMPP).

## Quick setup (Windows + XAMPP)

1. Start XAMPP (Apache + MySQL).
2. Place the project in your XAMPP `htdocs` directory. In this workspace the app is at `IP2_travel/ethiotrip`.
3. Import the database schema:

   - Open `http://localhost/phpmyadmin`.
   - Create a new database (or import directly) using the SQL file located at `backend/sql/database.sql`.
   - The SQL creates `ethiotrip_db` and seeds sample data and an admin user (email `admin@ethiotrip.com`, password `password123`).

4. Verify `app/Config/database.php` credentials match your MySQL setup (default is `root` with empty password).

5. Access the site in your browser:

   - Frontend pages: `http://localhost/IP2_travel/ethiotrip/public/pages/home.html`
   - App entry (router): `http://localhost/IP2_travel/ethiotrip/public/`

## Notes on integration work done

- The router was updated to resolve controller methods using both `method` and `methodAction` naming patterns, and supports `showX` → `xAction` mapping. File updated: `app/Core/Router.php`.
- Frontend static pages and assets are present in `ethiotrip/public/pages`, `ethiotrip/public/css`, and `ethiotrip/public/js`.
- The procedural backend scripts under `backend/` were inspected. The app already contains MVC controllers and models under `app/Controllers` and `app/Models`, but some route-to-method naming mismatches existed (fixed in Router).

## Next recommended steps (optional)

- Fully port `backend/*.php` procedural logic into the MVC controllers where missing or enhance controllers to match `backend` functionality (booking APIs, admin pages).
- Copy image assets from the `frontend` folder's `home_images` and `dest_images` into `ethiotrip/public/images/home_images` and `ethiotrip/public/images/dest_images`.
- Test user flows: register, login, create booking, admin discount adjustments.

If you want, I can now:

- Copy remaining static assets and images into `ethiotrip/public`.
- Port `backend` endpoints into the MVC controllers and update routes.
- Run quick local checks for common PHP errors.

Tell me which of these you'd like next.
