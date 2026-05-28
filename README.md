# EthioTrip: TOUR MANAGEMENT SYSTEM

## Group 4

| Name | ID | GitHub Username |
|------|----|--------------------|
| Solomon Kahsay | ETS1308/16 | sele01 |
| Suheil Ali | ETS1315/16 | Suheil7 |
| Yordanos Abebe | ETS1521/16 | yordanos777 |
| Zemedkun Workalem | ETS1557/16 | zemedkunworkalem32 |
| Zerihun Berhanu | ETS1563/16 | zerub0 |

---

## 🌍 EthioTrip Web Platform

### A Premium Digital Gateway to Ethiopia – *The Land of Origins*

---

## 📌 Project Overview

**EthioTrip** is a modern, full-stack travel and tourism booking platform designed to showcase Ethiopia as the **"Land of Origins."**

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
|-----------|------------|
| Backend | PHP 8+ (MySQLi) |
| Database | MySQL |
| Frontend | HTML5, CSS3, JavaScript (ES6+) |
| Styling | Flexbox, CSS Grid, CSS Variables |
| Icons | Font Awesome |
| Fonts | Google Fonts (Poppins) |
| Email | PHPMailer with Gmail SMTP |
| Authentication | PHP Sessions |
| Security | bcrypt, CSRF Tokens, Prepared Statements |
| Storage | LocalStorage + MySQL |
| Animations | CSS 3D + requestAnimationFrame |

---

## 📁 Project Structure

```text
ethiotrip/
├── backend/
│   ├── Config/
│   ├── Controllers/
│   ├── Core/
│   ├── Models/
│   ├── Services/
│   └── Views/
├── public/
│   ├── css/
│   ├── images/
│   ├── js/
│   ├── pages/
│   └── uploads/
├── routes/
├── sql/
├── vendor/
├── .env.example
├── composer.json
└── README.md
```

---

## 🗄️ Database Overview

### Core Tables

| Table | Purpose |
|-------|---------|
| users | User accounts & roles |
| discount_tiers | Loyalty discount system |
| destinations | Travel destinations |
| destination_highlights | Destination highlights |
| destination_attractions | Attractions |
| packages | Travel packages |
| bookings | Booking records |
| reviews | User feedback |
| user_destinations | Visit tracking |

---

## 🎯 Core Features

### 🌟 Interactive Homepage
- 3D carousel animations
- Auto-rotating destination cards

### 🗺️ Destination Explorer
- Dynamic API-based loading
- Ethiopian destinations with details

### 📦 Packages
- Filtered travel packages
- Flexible pricing system

### 📅 Booking System
- Date-based booking
- Auto price calculation

### 💎 Loyalty System

| Tier | Trips | Discount |
|------|-------|----------|
| Bronze | 0–2 | 0% |
| Silver | 3–4 | 3% |
| Gold | 5–7 | 5% |
| Platinum | 8–10 | 8% |
| Diamond | 11+ | 12% |

### 👤 Authentication
- Secure login/register
- Session-based auth
- Password hashing

### 📧 Email Notifications
- Booking confirmation
- Payment updates
- Gmail SMTP integration

### 🛠️ Admin Panel
- Manage bookings
- Manage destinations
- Manage packages
- Approvals system

---

## 🔄 User Flow

```
Home → Destination → Packages → Payment → Booking → Dashboard
```

---

## 📡 API Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| /api/check-login | GET | Login status |
| /api/destinations | GET | Destinations list |
| /api/packages | GET | Packages list |
| /api/save-booking | POST | Save booking |

---

## 🚀 Setup

1. Start XAMPP  
2. Import database  
3. Configure `database.php`  
4. Run project on localhost  

---

## 👥 Team Contributions

| Member | Role |
|--------|------|
| Solomon | Core System |
| Suheil | Authentication |
| Yordanos | Booking System |
| Zemedkun | Admin Module |
| Zerihun | API & Email |

---

## 📄 License

Educational use only.

---
