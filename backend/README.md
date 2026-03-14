# IP2_travel
## Group 4
topic :-**TOUR MANAGEMENT SYSTEM**

| Name              | ID         | GitHub Username        |
|-------------------|-----------|------------------------|
| Solomon Kahsay    | ETS1308/16 | sele01                      |
| Suheil Ali        | ETS1315/16 | Suheil7                |
| Yordanos Abebe    | ETS1521/16 | yordanos777                     |
| Zemedkun Workalem | ETS1557/16 | zemedkunworkalem32      |
| Zerihun Berhanu   | ETS1563/16 | zerub0                     |

# title:-EthioTrip Web Platform
**A Premium Digital Gateway to Ethiopia – The Land of Origins**

## Project Overview
EthioTrip is a modern, responsive travel and tourism platform designed to showcase Ethiopia as the “Land of Origins.” The platform provides a seamless digital experience for travelers, integrating curated destination information, cultural activities, user accounts, bookings, and interactive feedback. The system consists of: **Frontend** – User interface for browsing destinations, submitting inquiries, and interacting with travel content. **Backend** – Server-side system built with PHP, managing authentication, data processing, and communication with the database.

## Technology Stack
**Frontend**: HTML, CSS, JavaScript, responsive design, interactive UI components.  
**Backend**: PHP – server-side scripting, MySQL/MariaDB – relational database, JWT (JSON Web Tokens) – secure authentication, bcrypt – password hashing.  
**API & Communication**: RESTful API endpoints, JSON-based data exchange between frontend and backend.

## Core Features
**User Authentication**: Registration and login, password encryption with bcrypt, JWT-based session management, role-based access (Traveler/Administrator), logout functionality.  
**Destination Management**: Create, update, retrieve, and search destinations; store data such as name, region, description, activities, travel season, images, and cultural highlights.  
**Travel Experience Data**: Cultural activities and local guides, recommended travel routes, safety tips, travel recommendations.  
**Contact & Feedback**: Submit inquiries via contact form, store messages for administrative review.  
**Administrative Management**: Manage destinations and user accounts, review contact messages, monitor platform statistics.

## Database Structure (Conceptual)
**Users**: User ID, Name, Email, Password (hashed), Role, Date created.  
**Destinations**: Destination ID, Name, Location, Description, Activities, Best travel season, Images.  
**Messages**: Message ID, User name, Email, Content, Date submitted.

## API Endpoints
| Endpoint | Method | Purpose |
| --- | --- | --- |
| `/api/auth/register.php` | POST | Create new user |
| `/api/auth/login.php` | POST | Authenticate user |
| `/api/destinations.php` | GET | Retrieve all destinations |
| `/api/destinations.php?id=` | GET | Retrieve a specific destination |
| `/api/messages.php` | POST | Submit contact form |

## Security Considerations
Password hashing using bcrypt, JWT authentication for secure sessions, input validation and sanitization, protected admin routes, secure handling of sensitive data.

## Future Enhancements
Online booking system, payment integration, multi-language support, AI-driven travel recommendations, analytics dashboard for tourism insights.

## Conclusion
EthioTrip combines a robust frontend interface with a secure PHP backend to provide travelers with an immersive and interactive experience of Ethiopia’s cultural heritage. The platform is scalable, secure, and ready to evolve into a complete digital tourism solution.
