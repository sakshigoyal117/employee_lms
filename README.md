# Employee Leave Management System

## Project Description
This is a web-based Employee Leave Management System developed using Core PHP and MySQL. The system is designed to manage employee leave requests, track histories, and allow administrators to control leave type configurations dynamically. It includes role-based access control, session-based authentication, and strong server-side validation to manage application guidelines properly.

## Key Features
- Separate dashboards for Administrator and Employee roles.
- Dynamic Leave Type Activation/Deactivation panel without rigid hardcoding.
- Server-side validations for preventing overlapping leave dates.
- Mandatory 10-day operational gap check between leave requests.
- Secure password hashing using PHP `password_hash()` function.
- Responsive user interface built with Bootstrap 5.

## Technologies Used
- Core PHP (Procedural style with MySQLi connection)
- MySQL Database
- HTML5 & CSS3
- Bootstrap 5 (Frontend framework)
- JavaScript (Client-side validation and duration tracking)

## Database Name
`leave_management_db`

## Setup and Installation Instructions

1. **Download and Extract:**
   Download the project source code ZIP file and extract it inside your local server directory (e.g., `C:/xampp/htdocs/` for XAMPP users).

2. **Database Import:**
   - Open your browser and go to `http://localhost/phpmyadmin/`.
   - Create a new database named `leave_management_db`.
   - Click on the **Import** tab, select the provided `database.sql` file from the project directory, and click **Go**.

3. **Configure Database Connection:**
   - Open the file `config/db.php` in a text editor.
   - Update the database credentials (hostname, database username, password) to match your local server environment if required.

4. **Run the Project:**
   - Start Apache and MySQL from your XAMPP/WAMP control panel.
   - Open your browser and navigate to `http://localhost/Employee_lms/ems.html` (change folder name according to your setup).

## Default Login Credentials

### 1. Administrator Account
- **Email:** adminnexus@tech.com
- **Password:** 123

### 2. Employee Account
- **Email:** sakshigoyal@gmail.com
- **Password:** sakshi1234

## Features Completed
- User authentication and secure logout process.
- Dynamic tracking of core leave types (Sick Leave, Paid Leave, Unpaid Leave, Urgent Leave).
- Overlap detection and minimum duration checks on leave submission form.
- Status management (Approve/Reject) along with admin feedback comments.