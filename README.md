<<<<<<< HEAD
# EthioTrip: TOUR MANAGEMENT SYSTEM
## Group 4


| Name              | ID         | GitHub Username        |
|-------------------|-----------|------------------------|
| Solomon Kahsay    | ETS1308/16 | sele01                      |
| Suheil Ali        | ETS1315/16 | Suheil7                |
| Yordanos Abebe    | ETS1521/16 | yordanos777                     |
| Zemedkun Workalem | ETS1557/16 | zemedkunworkalem32      |
| Zerihun Berhanu   | ETS1563/16 | zerub0                     |

# 🌍 EthioTrip Web Platform
### A Premium Digital Gateway to Ethiopia – *The Land of Origins*

---

## 📌 Project Overview

**EthioTrip** is a modern, full-stack travel and tourism booking platform designed to showcase Ethiopia as the **“Land of Origins.”**  

It delivers a seamless digital experience for travelers by integrating:
- Curated destination discovery
- Dynamic package selection
- Custom travel date booking
- Loyalty-based discounts
- Secure booking management
- Interactive user engagement

---

## 🏗️ System Architecture

The platform is divided into two main parts:

### 🔹 Frontend
- User-friendly interface
- Destination browsing & package selection
- Booking & payment interface

### 🔹 Backend
- Built with PHP & MySQL
- Handles authentication, bookings, loyalty system, and admin controls

---

## 🛠️ Technology Stack

| Component | Technology |
|----------|-----------|
| Backend | PHP 7+ (MySQLi) |
| Database | MySQL |
| Frontend | HTML5, CSS3, JavaScript (ES6+) |
| Styling | Flexbox, CSS Grid, CSS Variables |
| Icons | Font Awesome |
| Fonts | Google Fonts (Poppins) |
| Authentication | PHP Sessions |
| Security | bcrypt, CSRF Tokens, Prepared Statements |
| Storage | LocalStorage + MySQL |
| Animations | CSS 3D + requestAnimationFrame |

---

## 📁 Project Structure


```text
IP2_travel/
├── backend/
│   ├── admin/
│   │   ├── adjust-discounts.php
│   │   └── discounts.php
│   ├── config/
│   │   └── database.php
│   ├── css/
│   ├── includes/
│   │   ├── auth.php
│   │   ├── footer.php
│   │   ├── functions.php
│   │   └── header.php
│   ├── sql/
│   │   └── database.sql
│   ├── bookings.php
│   ├── check-login.php
│   ├── check-table.php
│   ├── create-booking.php
│   ├── dashboard.php
│   ├── delete-booking.php
│   ├── edit-booking.php
│   ├── get-loyalty-discount.php
│   ├── get-next-tier.php
│   ├── login.php
│   ├── logout.php
│   ├── registration.php
│   ├── save-booking.php
│   ├── test-booking.php
│   ├── test-connection.php
│   ├── test_registration.php
│   └── testdb.php
├── frontend/
│   ├── css/
│   ├── dest_images/
│   ├── home_images/
│   ├── js/
│   │   └── validation.js
│   ├── .gitignore
│   ├── about.css
│   ├── about.html
│   ├── about.js
│   ├── destination.css
│   ├── Destination.html
│   ├── Home.css
│   ├── home.html
│   ├── home.js
│   ├── packages.css
│   ├── packages.html
│   ├── packages.js
│   ├── payment.css
│   ├── Payment.html
│   └── payment.js
├── .gitignore
└── README.md

---

## 🗄️ Database Overview

### Core Tables

- **users** – User accounts & loyalty tracking  
- **discount_tiers** – Loyalty levels  
- **packages** – Travel packages  
- **destinations** – Ethiopian destinations  
- **bookings** – Booking records  
- **reviews** – User feedback  
- **user_destinations** – Visit tracking  

---

## 🎯 Core Features

### 🌟 Interactive Homepage
- 3D card carousel showcasing Ethiopian destinations
- Smooth animations with auto-rotation

### 🗺️ Destination Explorer
- 8+ Ethiopian destinations
- Travel tips, activities, best seasons

### 📦 Package Customization
- Filtered by destination
- Flexible pricing (per day)

### 📅 Dynamic Booking System
- Custom start/end dates
- Automatic duration & pricing calculation

### 💎 Loyalty Discount System

| Tier | Trips | Discount |
|------|------|----------|
| Bronze | 0–2 | 0% |
| Silver | 3–4 | 3% |
| Gold | 5–7 | 5% |
| Platinum | 8–10 | 8% |
| Diamond | 11+ | 12% |

---

### 👤 Authentication System
- Secure login & registration
- Session-based authentication
- Admin role support

---

### 📊 User Dashboard
- Total bookings & spending
- Loyalty progress
- Destination tracking

---

### 💳 Payment Methods
- Credit Card
- Telebirr
- PayPal
- Bank Transfer
- Cash at Office

---

### 🛠️ Admin Panel
- Manage loyalty tiers
- Adjust discounts globally
- View system statistics

---

## 🔄 User Flow
Home → Destination → Packages → Payment → Booking → Dashboard

---

## 🔐 Security Features

- Password hashing (bcrypt)
- SQL Injection protection
- XSS prevention
- CSRF tokens
- Secure session handling

---

## 🧪 Test Credentials

| Role | Email | Password |
|------|------|----------|
| Admin | admin@ethiotrip.com | password123 |
| User | test@example.com | password123 |

---

## 🚀 Future Enhancements

- Payment gateway integration (Chapa, CBE Birr)
- Email confirmations
- Reviews & ratings system
- Multi-language support (Amharic & English)
- Mobile application
- SMS/Email booking reminders
- Advanced analytics dashboard

---

## 🌱 Sustainability Vision

EthioTrip promotes:

- Local community empowerment  
- Cultural preservation  
- Responsible tourism  
- Ethical travel practices  

Inspired by the Ethiopian value of **Medemer (togetherness)**

---

## 📄 License

This project is for educational and demonstration purposes.

---

## 👨‍💻 Author

**EthioTrip Web Platform**  
Crafted to celebrate Ethiopia through technology 🇪🇹

---
=======
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
>>>>>>> development
