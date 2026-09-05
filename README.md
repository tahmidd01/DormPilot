<p align="center">
  <img src="logo.png" alt="DormPilot logo" width="320">
</p>

<h1 align="center">DormPilot</h1>

<p align="center">
  A role-based hostel and dormitory management system built with PHP and MySQL.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8%2B-777BB4?logo=php&logoColor=white" alt="PHP 8+">
  <img src="https://img.shields.io/badge/MySQL-MariaDB-4479A1?logo=mysql&logoColor=white" alt="MySQL or MariaDB">
  <img src="https://img.shields.io/badge/Environment-XAMPP-FB7A24?logo=apache&logoColor=white" alt="XAMPP">
</p>

## About the project

DormPilot brings the main operations of a hostel into one web application. Students can apply for accommodation, view room information, submit complaints, request room changes, and communicate with their assigned supervisor. Supervisors can manage assigned students, complaints, tasks, announcements, and room-change requests. Administrators can manage rooms, supervisors, student assignments, admissions, repair costs, and reports.

## Features

### Student portal

- Student registration and role-based login
- Hostel admission application with document uploads
- Assigned room and roommate information
- Hostel room browsing
- Complaint submission with optional photo evidence
- Room-change requests with status tracking
- Announcements
- Direct messaging with an assigned supervisor

### Supervisor portal

- Dashboard with student, complaint, room-change, and task statistics
- Access to assigned student profiles
- Complaint review, status updates, and resolution details
- Room-change request approval or rejection
- Task creation, assignment, tracking, and resolution
- Repair-cost recording
- Announcements and student messaging

### Administrator portal

- Dashboard with room, student, supervisor, admission, and complaint statistics
- Room, capacity, and room-image management
- Supervisor account management
- Student-to-room and student-to-supervisor assignment
- Supervisor task assignment
- Admission approval and rejection
- Repair-cost history
- Operational reports for rooms, complaints, tasks, assignments, and room changes
- Optional PDF report generation with TCPDF

## Technology stack

| Layer | Technology |
| --- | --- |
| Backend | PHP with MySQLi |
| Database | MySQL or MariaDB |
| Frontend | HTML and CSS with lightweight JavaScript |
| Authentication | PHP sessions and password hashing |
| Local server | Apache through XAMPP |
| Optional reports | TCPDF |

## Getting started

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) with Apache and MySQL/MariaDB
- PHP 8 or newer recommended
- Git
- A modern web browser

### 1. Clone the repository

Open PowerShell and clone DormPilot into XAMPP's `htdocs` directory:

```powershell
cd C:\xampp\htdocs
git clone https://github.com/tahmidd01/DormPilot.git
cd DormPilot
```

### 2. Start the local services

Open the XAMPP Control Panel and start:

- Apache
- MySQL

### 3. Set up the database

The project includes `setup_database.php`, which creates a database named `dormpilot` and imports the base `dormpilot.sql` file.

Open this address once:

```text
http://localhost/DormPilot/setup_database.php
```

The repository also contains incremental SQL files for features added after the base schema. Import the following through phpMyAdmin when setting up a fresh database:

1. `add_supervisor_assignments.sql`
2. `update_complaints_tasks_repair_costs_safe.sql`

Other SQL files are repair scripts or database snapshots and should only be applied after reviewing their contents. Never publish or import a populated database dump without checking it for personal information.

### 4. Configure the database connection

The default `db.php` configuration expects XAMPP's standard local MySQL setup:

```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dormpilot";
```

Update these values if your database uses different credentials. Do not commit production passwords or secrets to GitHub.

### 5. Create the first accounts

Open the account setup page:

```text
http://localhost/DormPilot/create_admin.php
```

Use it to create the first administrator and supervisor accounts. Students can create their own accounts from `register.php`. Disable or remove `create_admin.php` after the initial setup so unauthorized visitors cannot create privileged accounts.

### 6. Open DormPilot

```text
http://localhost/DormPilot/
```

All users sign in through:

```text
http://localhost/DormPilot/login.php
```

After authentication, DormPilot redirects users to the correct dashboard for their role.

## Optional PDF reports

PDF generation in `admin_pdf_reports.php` requires TCPDF, which is not bundled with the project.

1. Download [TCPDF](https://github.com/tecnickcom/TCPDF).
2. Extract it into the DormPilot project root.
3. Make sure the library entry file is available at `tcpdf/tcpdf.php`.

Without TCPDF, the rest of DormPilot will still run, but PDF report links will display an error.

## Upload directories

DormPilot stores uploaded admission documents, complaint photos, identity images, profile photos, and room images below `uploads/`. The web server must be able to write to these directories.

Runtime uploads should not be committed. Keep empty directories with placeholder files if the application needs them, and add generated files to `.gitignore`.

## Security and privacy

This project handles personal information and uploaded identity documents. Before using it outside a local development environment:

- Remove real NID images, profile photos, and student documents from the repository and its Git history.
- Replace populated SQL dumps with a sanitized, schema-only database export.
- Keep database credentials outside version control.
- Validate file type, size, and filename for every upload.
- Add CSRF protection to state-changing forms.
- Review authorization checks on every admin, supervisor, and student page.
- Disable `setup_database.php` and `create_admin.php` after installation.
- Use HTTPS and secure session-cookie settings in production.

## Project structure

```text
DormPilot/
├── admin_*.php          # Administrator pages
├── supervisor_*.php     # Supervisor pages
├── student_*.php        # Student pages
├── db.php               # Database connection
├── login.php            # Shared role-based login
├── register.php         # Student registration
├── setup_database.php   # Local database setup
├── dormpilot.sql        # Base database schema
├── icons/               # Interface icons
├── room/                # Bundled room images
└── uploads/             # Runtime-uploaded files
```

## Development status

DormPilot is currently intended for learning, demonstration, and local XAMPP development. Perform a full security review and consolidate the included database schemas and migrations before deploying it in production.

## Contributing

1. Fork the repository.
2. Create a feature branch: `git checkout -b feature/your-feature`.
3. Commit your changes: `git commit -m "Add your feature"`.
4. Push the branch: `git push origin feature/your-feature`.
5. Open a pull request.
