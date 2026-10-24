# University Advisor Appointment System

A web-based academic advising appointment management system. Students can browse advisors and reserve available time slots, advisors can review their schedules, and administrators can manage appointments, accounts, and advisor availability.

## Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript (ES modules), Chart.js for dashboard charts
- **Backend:** PHP (object-oriented models), PDO, PHP sessions, JSON API
- **Database:** MySQL
- **Local development:** XAMPP and phpMyAdmin

## User Roles and Features

| Role | Features |
|---|---|
| Student | Register, sign in, browse active advisors, view available time slots, book an appointment with an optional note, review appointment status, cancel eligible appointments |
| Advisor | Sign in, view a dashboard and appointments associated with their own time slots, review student details and notes |
| Admin | Sign in, view appointment statistics and charts, confirm or reject pending appointments, activate/deactivate eligible user accounts, create and delete eligible time slots |

### Appointment Statuses

The system supports four appointment statuses:

- `pending` — A newly submitted booking awaiting approval.
- `confirmed` — An appointment approved by the administrator.
- `rejected` — An appointment rejected by the administrator.
- `cancelled` — An appointment cancelled by the student.

Available capacity accounts for pending and confirmed bookings.

## Requirements

- XAMPP with Apache, PHP, and MySQL enabled
- A modern browser supporting JavaScript modules
- An existing MySQL database named `university_advisor_system`
- The `users`, `time_slots`, and `appointments` tables
- Existing accounts for testing the different user roles

**Database Note:** The project ZIP does not contain a SQL database dump. Keep your existing MySQL database and its records.

## Installation and Setup

1. Download and install XAMPP.
2. Extract the project into `C:\xampp\htdocs\university-advisor-system\`.
3. Open the XAMPP Control Panel.
4. Start Apache and MySQL.
5. Open `http://localhost/phpmyadmin/`.
6. Verify that the `university_advisor_system` database exists.
7. Check the database connection settings in `backend/config/database.php`.
8. Open the application at `http://localhost/university-advisor-system/`.
9. Sign in using an account stored in MySQL.

**Important:** Run the application through `http://localhost/`, not by opening the HTML files directly.

## Demo Accounts (Local Testing Only)

The following accounts are used to test the three application roles.

| Role | Email | Password |
|---|---|---|
| Student | `hadeel@gmail.com` | `Test12345!` |
| Admin | `admin@university.test` | `Test12345!` |
| Advisor | `michael.anderson@university.edu` | `Test12345!` |

These accounts are expected to exist in the local MySQL database. Their passwords must be stored as PHP password hashes.

**Security Note:** These credentials are intended for local testing only. Remove them before publishing the repository publicly.

## Main Pages

| Page | Path |
|---|---|
| Home | `index.html` |
| Login | `login.html` |
| Student Registration | `register.html` |
| Student Dashboard | `student/dashboard.html` |
| Advisors | `student/advisors.html` |
| Book Appointment | `student/book.html` |
| Student Appointments | `student/appointments.html` |
| Advisor Dashboard | `advisor/dashboard.html` |
| Advisor Appointments | `advisor/appointments.html` |
| Admin Dashboard | `admin/dashboard.html` |
| Manage Appointments | `admin/appointments.html` |
| Manage Users | `admin/users.html` |
| Manage Time Slots | `admin/time-slots.html` |

## Project Structure

```text
university-advisor-system/
├── index.html
├── login.html
├── register.html
├── student/
│   ├── dashboard.html
│   ├── advisors.html
│   ├── book.html
│   └── appointments.html
├── advisor/
│   ├── dashboard.html
│   └── appointments.html
├── admin/
│   ├── dashboard.html
│   ├── appointments.html
│   ├── users.html
│   └── time-slots.html
├── assets/
│   ├── css/
│   │   └── styles.css
│   ├── js/
│   │   ├── app.js
│   │   ├── services.js
│   │   ├── data.js
│   │   └── store.js
│   └── images/
├── backend/
│   ├── api.php
│   ├── config/
│   │   └── database.php
│   ├── models/
│   │   ├── User.php
│   │   ├── TimeSlot.php
│   │   └── Appointment.php
│   └── [additional PHP endpoints]
└── README.md
```

## How the System Works

1. The browser loads the HTML and CSS interface.
2. JavaScript renders the pages and handles user interactions.
3. The frontend communicates with `backend/api.php` using HTTP requests.
4. PHP validates requests and checks user permissions.
5. PDO communicates with the MySQL database.
6. PHP sessions maintain user authentication.
7. Updated appointment and account information is retrieved from the database.

The connected application uses server-side data for its core functionality rather than browser-stored demo data.

## Student Features

### Student Registration and Login

Students can create accounts and sign in using their registered email addresses and passwords.

### Browse Advisors

Students can view active academic advisors and explore available appointment slots.

### Book Appointments

Students can select an advisor, choose an available date and time, and optionally provide a reason for the visit.

New appointments are initially created with a `pending` status.

### Manage Appointments

Students can review their appointment history, check booking statuses, and cancel eligible appointments.

## Advisor Features

### Advisor Dashboard

Advisors can access a dashboard showing appointment statistics and information related to their schedules.

### View Appointments

Advisors can review appointments associated with their assigned time slots, including student information, appointment dates, statuses, and visit notes.

Appointment approval is handled by administrators.

## Admin Features

### Admin Dashboard

Administrators can review system statistics and appointment charts.

### Manage Appointments

Administrators can review pending appointments and confirm or reject eligible requests.

### Manage Users

Administrators can view registered users and activate or deactivate eligible accounts.

### Manage Time Slots

Administrators can create available appointment periods, assign advisors, define capacity, and delete eligible time slots.

## Database Overview

The system uses three main database tables.

### Users

Stores account information, including name, email, hashed password, role, active status, and creation date.

### Time Slots

Stores advisor availability, including advisor ID, date, start time, end time, and capacity.

### Appointments

Stores student reservations, including student ID, time slot ID, optional note, appointment status, and creation date.

Relationships between these tables allow the system to connect students, advisors, and appointments.

## Security

The project uses the following security mechanisms:

- Password hashing and verification
- PHP session-based authentication
- Role-based authorization
- PDO prepared statements
- Server-side request validation
- CSRF protection for modifying API requests

For public deployment, additional server configuration, HTTPS, secure session cookies, and production security testing are required.

## Known Limitations

- The package does not include the live MySQL database or a SQL export.
- Older standalone PHP pages remain alongside the connected API.
- Administrative cancellation is not implemented in the connected API.
- Time slots with appointment history cannot be deleted.
- Advisor accounts are added through the database rather than through an administrator registration form.
- Chart.js may require an internet connection.
- Complete end-to-end testing against the local XAMPP database has not yet been verified.

## Testing Checklist

- [ ] Register a new student.
- [ ] Log in as a student.
- [ ] Log in as an advisor.
- [ ] Log in as an administrator.
- [ ] Verify role-based access.
- [ ] Create a new time slot.
- [ ] Book an appointment.
- [ ] Verify that the appointment is saved in MySQL.
- [ ] Confirm an appointment.
- [ ] Reject an appointment.
- [ ] Cancel an eligible appointment.
- [ ] Review advisor appointment information.
- [ ] Activate and deactivate an eligible user.
- [ ] Verify appointment statistics and charts.

## Troubleshooting

| Problem | Suggested Solution |
|---|---|
| Database connection failed | Verify that MySQL is running and check the credentials in `backend/config/database.php`. |
| Login failed | Verify the email, password hash, account role, and active status. |
| API request failed | Make sure Apache is running and the website is opened through localhost. |
| No advisors displayed | Verify that active advisor accounts exist in MySQL. |
| No available time slots | Create future time slots with available capacity. |
| Charts not displayed | Check the browser console and Chart.js availability. |

## Project Purpose

The University Advisor Appointment System demonstrates the development of a role-based web application using HTML, CSS, JavaScript, PHP, and MySQL.

It provides an organized academic advising workflow where students can schedule appointments, advisors can review their schedules, and administrators can manage the system through a centralized dashboard.

The project also demonstrates practical concepts including object-oriented PHP, database relationships, authentication, authorization, appointment management, and frontend/backend integration.
