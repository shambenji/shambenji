# 🏥 **DOCTOR SHIFT SCHEDULER SYSTEM – USER MANUAL**

📄 Prepared by: A.56

 Introduction

The **Doctor Shift Scheduler System** is a PHP-MySQL based web application designed to help hospitals allocate and manage doctors’ work shifts more efficiently. Traditionally, hospital staff have relied on manual methods such as spreadsheets or paper-based systems to manage shifts, which often results in errors, time wastage, and uneven workloads.

This system brings automation to shift allocation, makes it easy for doctors to swap shifts, allows administrators to monitor staffing, and provides reports that help improve hospital operations. The system provides role-based access: admins manage scheduling and doctors manage their shifts.

This manual provides a step-by-step guide on how to install, configure, and use the Doctor Shift Scheduler System effectively. It also provides instructions for troubleshooting, system maintenance, and support contact.

System Requirements

To successfully install and run the Doctor Shift Scheduler System, your computer should meet the following requirements:

Software Requirements

* Operating System: Windows 10/11, macOS, or Linux
* XAMPP (PHP 8+, Apache, MySQL)
* Visual Studio Code (or any preferred code editor)
* Web browser (Chrome, Firefox, or Edge)
* PDF reader for documentation and reports

Optional

* Internet connection (for updates or remote support)
* Email configuration (for future notification feature)

Installing the Development Environment

Before using the system, install and set up the local server environment.

Installing XAMPP

XAMPP provides the necessary services: Apache for hosting and MySQL for the database.

**Steps:**

1. Download from: [https://www.apachefriends.org](https://www.apachefriends.org)
2. Run the installer
3. Open the XAMPP Control Panel
4. Start both **Apache** and **MySQL**
5. Test Apache by visiting `http://localhost/` on your browser

Installing Visual Studio Code

VS Code is used to edit source code (PHP, HTML, CSS, JS).

**Steps:**

1. Download from: [https://code.visualstudio.com](https://code.visualstudio.com)
2. Install and open VS Code
3. Add extensions: PHP IntelliSense, MySQL, HTML Preview

Creating the Database

1. Go to `http://localhost/phpmyadmin`
2. Click "New"
3. Enter `doctor_shift_db` as the database name
4. Click “Create”
5. Import the SQL file from the project folder

 Installing the System
Placing System Files

1. Download and extract the project ZIP file
2. Move the extracted folder to `C:\xampp\htdocs\doctor_shift_scheduler`

Configuring the Database Connection

1. Open the file `config/db.php`
2. Set the credentials as:

   ```php
   $host = 'localhost';
   $user = 'root';
   $pass = '';
   $dbname = 'doctor_shift_db';
   ```
 Launching the Application

1. Open your browser
2. Navigate to `http://localhost/doctor_shift_scheduler/`
3. The login screen should appear

System Features Overview

The system includes the following major features:

Login & User Authentication

* Secure login for Admins and Doctors
* Credentials verified from database
* Redirects user to appropriate dashboard

Automated Shift Allocation

* Admin clicks a button to auto-generate shifts for all doctors
* Ensures fair distribution and balance in workload

Manual Shift Management

* Admin can view, edit, or delete shift assignments
* Useful for emergency changes

Shift Swap Requests

* Doctors can request to exchange shifts
* Requests are reviewed and approved/rejected by Admin

 Notification System

* Displays alerts about shift swaps, approvals, and assignments
* Helps users stay informed

Reports and Analytics

* Admin can generate reports (daily, weekly, monthly)
* Includes shift history, doctor workload, swap statistics

 Using the System

Logging In

1. Visit: `http://localhost/doctor_shift_scheduler/`
2. Enter Email and Password
3. Click "Login"
4. You are redirected to your dashboard

Admin Dashboard

* Add, edit, and remove doctors
* Allocate shifts automatically
* Approve or reject shift swap requests
* View system reports and analytics

Doctor Dashboard

* View your shift schedule
* Request shift swaps
* View notifications
* Update your profile (if available)
 Reports and Analytics

 Report Types

* Daily shift summary
* Weekly doctor workload
* Monthly attendance report
* Swap history

 How to Generate Reports

1. Login as Admin
2. Go to “Reports” menu
3. Select report type and date range
4. Click “Generate Report”
5. Optionally click “Download PDF” or “Export CSV”

Reports help identify performance trends, attendance gaps, and workload distribution

 Support and Contact

If you need help, please use the contacts below:

* **Support Email:** byabatoinnocent21@gmail.com
* **Phone:** +255 688756362

Conclusion

The Doctor Shift Scheduler System provides a modern solution to managing shifts in healthcare. It reduces time, improves staff fairness, and promotes accountability. With automatic scheduling, real-time editing, and shift swapping, hospitals can ensure efficient and transparent operations.

---

🟢 **END OF USER MANUAL**


