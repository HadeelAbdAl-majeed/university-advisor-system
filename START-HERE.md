# University Advisor System — Connected PHP + MySQL

## Install on XAMPP (Windows)
1. Back up your old project folder first.
2. Extract this ZIP into `C:\xampp\htdocs\` so the folder is `C:\xampp\htdocs\university-advisor-system\`.
3. Start **Apache** and **MySQL** in XAMPP.
4. In phpMyAdmin, ensure the database `university_advisor_system` exists and has tables `users`, `time_slots`, `appointments` from your existing backend. This ZIP does **not** include your live database or passwords; keep your existing MySQL database.
5. If your MySQL credentials differ from `root` with blank password, update `backend/config/database.php`.
6. Open **http://localhost/university-advisor-system/login.html** (do not double-click the HTML file).
7. Sign in using a real account from your MySQL `users` table, or create a student account from Register.

## Pages
- Student: dashboard, advisors, booking, appointments.
- Advisor: dashboard and schedule, restricted to own slots and appointments.
- Admin: analytics, appointments approval/rejection, user activation, and slot creation/deletion.

## What changed
- `backend/api.php`: JSON API using PDO, PHP sessions, CSRF token, and server-side role checks.
- `assets/js/services.js`: fetches real server data; no localStorage mock writes.
- `assets/js/app.js`: asynchronous login/register, booking, cancellation, admin actions, data refresh.
- Existing CSS and HTML page layout preserved.

## Notes and limitations
- Admin appointment cancellation is not supported by the existing PHP model; admin can confirm/reject pending requests. Students can cancel their own upcoming pending/confirmed bookings.
- Slots with any appointment history cannot be deleted, preserving relational integrity.
- `assets/js/data.js` and `store.js` are unused legacy demo modules and are not imported by the live application.
- Existing `backend/*.php` pages are retained for backward compatibility; the redesigned frontend uses `backend/api.php`.
- This package was syntax-checked locally, but a full live MySQL + browser integration test must be done on your XAMPP installation.
- Do not deploy with the default XAMPP database password settings to a public server.
