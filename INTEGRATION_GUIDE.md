# Frontend-Backend Integration Guide

## ✅ Status: Integration Complete

The EthioTrip travel booking system now has **full end-to-end integration** between frontend and backend.

---

## Complete User Flow

```
Home (home.html) 
  ↓
Destination (Destination.html) [Saves: selectedDestinationName → localStorage]
  ↓
Packages (packages.html) [Loads: selectedDestinationName from localStorage]
  ↓
Payment (Payment.html) [Reads: selectedDestinationName, selectedPrice from localStorage]
  ↓
Backend API Calls:
  1. POST → save-booking.php [Creates booking in database]
  2. Loyalty tier updated in users table
  3. Destination tracked in user_destinations table
  ↓
Receipt & Confirmation (in Payment.html)
  ↓
My Bookings (bookings.php) [Retrieves saved bookings from database]
```

---

## API Integration Architecture

### 1. Session Management
**Endpoint:** `backend/check-login.php`
**Method:** GET  
**Purpose:** Verify user login status
```javascript
const response = await fetch('../backend/check-login.php');
const data = await response.json();
// Returns: { logged_in: true/false, user_name: string, user_id: int }
```

### 2. Loyalty Discount Lookup
**Endpoint:** `backend/get-loyalty-discount.php`
**Method:** GET  
**Parameters:**
- `?name={userName}` (if not logged in)  
- Uses `$_SESSION['user_id']` (if logged in)

**Purpose:** Get user's current loyalty tier and discount percentage
```javascript
const apiUrl = '../backend/get-loyalty-discount.php?name=' + encodeURIComponent(userName);
const response = await fetch(apiUrl);
const data = await response.json();
// Returns: {
//   success: true,
//   discount_percent: 0-12,
//   discount_decimal: 0-0.12,
//   tier_name: "Bronze|Silver|Gold|Platinum|Diamond",
//   trips_completed: int,
//   total_spent: decimal
// }
```

### 3. Next Loyalty Tier Info
**Endpoint:** `backend/get-next-tier.php`
**Method:** GET  
**Parameters:** `?trips={completedTrips}`

**Purpose:** Show user progress toward next loyalty tier
```javascript
const response = await fetch('../backend/get-next-tier.php?trips=' + tripsCompleted);
const data = await response.json();
// Returns: {
//   success: true,
//   trips_needed: int,
//   tier_name: string,
//   discount_percent: int
// }
```

### 4. Save Booking to Database
**Endpoint:** `backend/save-booking.php`
**Method:** POST  
**Content-Type:** `application/json`

**Request Body:**
```json
{
  "package_name": "EthioTrip Package",
  "package_price": 300,
  "destination": "Addis Ababa",
  "start_date": "2026-06-01",
  "end_date": "2026-06-04",
  "travelers": 1,
  "payment_method": "Credit Card",
  "transaction_id": "ET-123456",
  "final_amount": 315,
  "user_name": "John Doe"
}
```

**Response on Success:**
```json
{
  "success": true,
  "booking_id": 42,
  "message": "Booking saved successfully!",
  "trips_completed": 1,
  "total_spent": 315.00,
  "final_amount": 315,
  "destination": "Addis Ababa",
  "duration_days": 3,
  "start_date": "2026-06-01",
  "end_date": "2026-06-04"
}
```

**What happens in the backend:**
1. User lookup/creation (creates temp account if new customer)
2. Loyalty tier calculation based on trips_completed
3. Booking insertion with all details
4. user_destinations tracking (visit counts)
5. Loyalty discount recalculation
6. User stats update (trips_completed, total_spent, loyalty_discount)

---

## Data Flow: localStorage

### Destination → Packages → Payment

| Key | Set By | Read By | Purpose |
|-----|--------|---------|---------|
| `selectedDestination` | Destination.html | - | Full destination object (JSON) |
| `selectedDestinationName` | Destination.html | Payment.html | Destination name for display |
| `selectedPackage` | packages.js | Payment.html | Package name |
| `selectedPrice` | packages.js | Payment.html | Base package price |

### Payment → Receipt

| Key | Set By | Purpose |
|-----|--------|---------|
| `finalAmount` | Payment.html | Total amount after discount+tax |
| `discountApplied` | Payment.html | Discount rate (0-0.12) |
| `discountPercent` | Payment.html | Discount percentage (0-12) |
| `userName` | Payment.html | Customer name |
| `tierName` | Payment.html | Loyalty tier (Bronze/Silver/Gold/etc) |
| `tripsCompleted` | Payment.html | Trips before current booking |
| `baseAmount` | Payment.html | Amount before discount |
| `durationDays` | Payment.html | Travel duration |
| `startDate` | Payment.html | Travel start date |
| `endDate` | Payment.html | Travel end date |

---

## Database Tables

### users
Stores user accounts and loyalty tracking
```sql
SELECT id, name, email, trips_completed, total_spent, loyalty_discount FROM users;
```

### discount_tiers
Loyalty tier definitions
```sql
SELECT * FROM discount_tiers;
-- Bronze: 0-2 trips, 0% discount
-- Silver: 3-4 trips, 3% discount
-- Gold: 5-7 trips, 5% discount
-- Platinum: 8-10 trips, 8% discount
-- Diamond: 11+ trips, 12% discount
```

### bookings
All booking records with dates and amounts
```sql
SELECT id, user_id, package_name, destination, start_date, end_date, 
       final_amount, payment_method, status FROM bookings;
```

### user_destinations
Track which destinations users have visited and how many times
```sql
SELECT user_id, destination, visit_count, last_visited FROM user_destinations;
```

---

## Testing the Integration

### Prerequisites
1. **Backend running:** PHP server on localhost
2. **Database:** MySQL with ethiotrip_db created and schema loaded
3. **Environment:** .env file configured (see `.env` in project root)

### Step 1: Database Setup
```bash
# Import database schema
mysql -u root < backend/sql/database.sql
```

### Step 2: Start PHP Server
```bash
cd backend
php -S localhost:8000
```

### Step 3: Test Complete Flow
1. **Open browser:** `http://localhost:8000/../frontend/home.html`
2. **Click on a destination** (e.g., "Addis Ababa")
3. **Destination details show** → click "Select This Destination"
4. **Packages page loads** → select a package by clicking price
5. **Payment page loads** with:
   - Destination displayed
   - Package price shown
   - Date range inputs available
6. **Enter travel dates** (start date and end date)
7. **Click "Check Discount & Proceed"**
   - System checks backend for loyalty tier
   - Shows loyalty info modal (if member)
8. **Select payment method** (Credit Card, Telebirr, PayPal, etc.)
9. **Click "Pay Securely"** (or equivalent for chosen method)
10. **Receipt displays** with:
    - Booking confirmation
    - Applied discount percentage
    - Total amount paid
    - Booking ID from database
11. **Click "My Bookings"** to verify booking saved

### Step 4: Verify Database
```sql
-- Check new user created/updated
SELECT * FROM users WHERE name LIKE 'Test%';

-- Check new booking
SELECT * FROM bookings ORDER BY created_at DESC LIMIT 1;

-- Check destination tracking
SELECT * FROM user_destinations WHERE user_id = 1;

-- Check loyalty tier progression
SELECT 
  u.name, u.trips_completed, u.loyalty_discount,
  dt.tier_name, dt.discount_percent
FROM users u
LEFT JOIN discount_tiers dt ON 
  u.trips_completed >= dt.min_trips AND 
  (u.trips_completed <= dt.max_trips OR dt.max_trips IS NULL)
WHERE u.name LIKE 'Test%';
```

---

## Frontend Files Structure

```
frontend/
├── home.html              # Landing page with 3D carousel
├── Destination.html       # Destination detail pages (saves destination → localStorage)
├── packages.html          # Package selection (saves package → localStorage, redirects to Payment.html)
├── Payment.html           # Complete payment integration
│   ├── Session check (check-login.php)
│   ├── Loyalty lookup (get-loyalty-discount.php)
│   ├── Next tier info (get-next-tier.php)
│   └── Save booking (POST to save-booking.php)
├── about.html             # About page
├── about.js               # About animations
├── home.js                # Homepage 3D carousel logic
├── packages.js            # Package selection handler
├── js/
│   └── validation.js      # Form validation + session management
├── css/
│   ├── *.css              # Styling for each page
└── dest_images/           # Destination photos
```

### Removed (Dead Code)
- ❌ `payment.js` - Old unused implementation with hardcoded data
- ❌ `payment.css` - Unused by Payment.html (which has inline styles)

---

## Backend Files Structure

```
backend/
├── config/
│   └── database.php       # Database connection (uses .env)
├── includes/
│   ├── auth.php           # Auth functions
│   ├── header.php         # Page header
│   ├── footer.php         # Page footer
│   └── functions.php      # Utility functions
├── sql/
│   └── database.sql       # Database schema
├── admin/
│   ├── discounts.php      # Admin: Edit loyalty tiers
│   └── adjust-discounts.php # Admin: Adjust discounts
├── check-login.php        # ✓ GET session status [WORKING]
├── get-loyalty-discount.php # ✓ GET user loyalty tier [WORKING]
├── get-next-tier.php      # ✓ GET next tier progress [WORKING]
├── save-booking.php       # ✓ POST save booking [WORKING]
├── login.php              # User login
├── registration.php       # User registration
├── dashboard.php          # User dashboard
├── bookings.php           # View user's bookings
├── delete-booking.php     # Cancel booking
└── logout.php             # Logout
```

---

## Troubleshooting

### "Backend API returns 404"
- **Check:** PHP server running on correct port
- **Fix:** Ensure backend server is running: `php -S localhost:8000`

### "Booking not saved to database"
- **Check:** MySQL connection in .env file
- **Check:** Database `ethiotrip_db` exists
- **Check:** Tables created (run database.sql)
- **Fix:** `mysql -u root < backend/sql/database.sql`

### "Loyalty discount not showing"
- **Check:** User exists in database OR backend creates new user
- **Check:** get-loyalty-discount.php returns success: true
- **Check:** Discount tiers populated in database
- **Fix:** Verify discount_tiers table has entries

### "Session not persisting across pages"
- **Check:** Browser allows localStorage
- **Check:** Session cookies enabled
- **Fix:** Check browser console for errors

### "Dates not calculating correctly"
- **Check:** Date format is YYYY-MM-DD
- **Check:** End date is after start date
- **Fix:** Payment.html validates dates before sending

---

## Key Integration Points

1. **Session Bridge:** check-login.php validates session across frontend
2. **Loyalty Persistence:** get-loyalty-discount.php queries database in real-time
3. **Booking Persistence:** save-booking.php writes to database with all relationships
4. **Data Isolation:** localStorage handles page-to-page data passing safely
5. **Error Handling:** Notifications show user feedback for all API calls

---

## What Was Fixed

✅ **Removed:** Dead code (payment.js, payment.css)  
✅ **Verified:** Complete flow from Destination → Payment → Database  
✅ **Confirmed:** All API endpoints working  
✅ **Ensured:** Data properly passed through localStorage  
✅ **Validated:** Database schema matches API expectations  

---

Last Updated: 2026-05-19
