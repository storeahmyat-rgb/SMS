# School Management System (SMS) - Installation & Setup Guide

## System Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- PDO PHP extension
- Optional: TCPDF for PDF export

## Installation Steps

### 1. Clone/Download the Project

```bash
cd /path/to/webroot
git clone <your-repo-url> sms
cd sms
```

### 2. Database Configuration

Edit `includes/config.php` with your database credentials:

```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'sms_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

**Create MySQL database and user:**

```sql
CREATE DATABASE sms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sms_user'@'localhost' IDENTIFIED BY 'sms_pass';
GRANT ALL PRIVILEGES ON sms_db.* TO 'sms_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Run Setup Script

From project root:

```bash
php setup.php
```

This will:
- Create all database tables
- Create default admin user
- Display admin credentials

**Default Admin Login:**
- Username: `admin`
- Password: `admin123`

⚠️ Change the password immediately after first login.

### 4. Web Server Configuration

**Apache .htaccess** (if needed):

```
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /sms/
</IfModule>
```

**Nginx config** (example):

```
server {
    listen 80;
    server_name sms.local;
    root /var/www/sms;
    index index.php;

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    }
}
```

### 5. Optional: Install TCPDF for PDF Export

```bash
mkdir -p vendor/tcpdf
cd vendor/tcpdf
# Download TCPDF from https://tcpdf.org or install via composer
# cp tcpdf.php /path/to/vendor/tcpdf/
```

## Project Structure

```
sms/
├── index.php                      # Login page
├── setup.php                      # Setup script
├── README.md                      # This file
├── includes/
│   ├── config.php                 # Database config
│   ├── db.php                     # PDO helper
│   ├── auth.php                   # Authentication
│   ├── header.php                 # HTML header
│   └── footer.php                 # HTML footer
├── admin/
│   ├── dashboard.php              # Admin dashboard
│   ├── dashboard_enhanced.php     # Enhanced admin dashboard
│   ├── students.php               # Student list
│   ├── student_add.php            # Add student
│   ├── teachers.php               # Teacher list
│   ├── teacher_add.php            # Add teacher
│   ├── teacher_edit.php           # Edit teacher
│   ├── classes_manage.php         # Manage classes
│   ├── sections_manage.php        # Manage sections
│   ├── subjects_manage.php        # Manage subjects
│   ├── timetable_manage.php       # Timetable setup
│   ├── attendance.php             # Mark attendance
│   ├── attendance_ajax.php        # AJAX endpoint
│   ├── attendance_edit.php        # Audit log
│   ├── attendance_report.php      # Attendance report
│   ├── teacher_attendance.php     # Teacher attendance
│   ├── fees.php                   # Fee structure
│   ├── fee_pay.php                # Collect fees
│   ├── fee_receipt.php            # Fee receipt (HTML/PDF)
│   ├── ledger.php                 # Student ledger
│   ├── pending_fees.php           # Pending fees report
│   ├── income_report.php          # Income/collection report
│   ├── salaries_manage.php        # Teacher salaries
│   ├── expenses.php               # Expense tracking
│   ├── profitloss.php             # P&L report
│   ├── exams.php                  # Exam management
│   ├── exam_marks.php             # Enter marks
│   ├── result_card.php            # View/print results (HTML/PDF)
│   ├── biometric_import.php       # Biometric CSV import
│   └── logout.php                 # Logout
├── teacher/
│   └── dashboard.php              # Teacher dashboard
├── accountant/
│   └── dashboard.php              # Accountant dashboard
├── sql/
│   └── schema.sql                 # Database schema
└── vendor/
    └── tcpdf/                     # Optional: TCPDF library
```

## User Roles & Access

### Super Admin
- Full system access
- Create/manage students, teachers, classes, subjects
- Mark and edit attendance
- Manage fees and salaries
- View all reports
- System configuration

### Teacher
- View own class attendance
- Mark student attendance
- View own salary information
- Cannot modify financial data

### Accountant
- Manage fee collection
- View payment records
- Generate income reports
- Calculate P&L
- Cannot modify student/teacher data

## Features Overview

### Student Management
- Admission system with unique admission number
- Complete profile (father name, CNIC, DOB, contact, address)
- Class & section assignment
- Status tracking (Active/Left)
- Student attendance tracking

### Teacher Management
- Teacher profile with CNIC, qualification, salary
- Attendance in/out time tracking
- Assignment to classes
- Salary calculation and payment

### Attendance
- Daily student & teacher attendance
- Mark as Present/Absent/Leave/Late
- Edit with audit trail (who changed, when, reason)
- Monthly reports
- Class-wise attendance

### Academic Module
- Classes (Playgroup to 10th)
- Sections (A, B, C, etc.)
- Subjects management
- Timetable per class/section

### Fees Module
- Multiple fee types (Admission, Monthly, Exam, Transport, Custom)
- Student-wise fee collection
- Partial payment support
- Payment method tracking (Cash, Bank, Card)
- Fee receipt generation (HTML/PDF)
- Student ledger view
- Pending fees report
- Income/collection reports

### Salary & Expenses
- Teacher salary recording & payment tracking
- Expense categories
- Monthly profit/loss calculation
- Financial reports

### Exams & Results
- Exam creation and scheduling
- Subject-wise marks entry
- Auto-grading (A+, A, B, C, D, F)
- Result card generation (HTML/PDF)

### Biometric Integration
- CSV import for biometric logs
- Map device users to students/teachers
- Attendance sync support
- Manual override option

## Database Tables

- `users` - Login credentials & roles
- `students` - Student information
- `student_attendance` - Daily attendance with audit
- `attendance_edits` - Attendance change log
- `teachers` - Teacher information
- `teacher_attendance` - Teacher daily attendance
- `classes` - Class definitions
- `sections` - Section definitions
- `subjects` - Subject definitions
- `timetable` - Class schedule
- `fees` - Fee types & amounts
- `fee_payments` - Payment records
- `salaries` - Salary records
- `expenses` - Expense tracking
- `exams` - Exam definitions
- `results` - Student exam results
- `biometric_logs` - Biometric attendance logs

## Security Features

- Password hashing with PHP's `password_hash()`
- SQL prepared statements (parameterized queries)
- Session-based authentication
- Session timeout (30 minutes)
- Role-based access control (RBAC)
- CSRF protection ready
- XSS protection via `htmlspecialchars()`

## Quick Start Guide

1. **Login**: Open `http://localhost/sms/` (or your domain)
2. **First Login**: Use admin/admin123
3. **Create Classes**: Admin > Classes
4. **Create Sections**: Admin > Sections
5. **Create Subjects**: Admin > Subjects
6. **Add Teachers**: Admin > Add Teacher
7. **Add Students**: Admin > Add Student
8. **Mark Attendance**: Admin > Student Attendance
9. **Collect Fees**: Admin > Collect Fee
10. **View Reports**: Admin > Income Report, etc.

## API Endpoints (AJAX)

All AJAX endpoints are in `admin/attendance_ajax.php`:

- `GET /admin/attendance_ajax.php?action=sections&class_id=X` - Get sections for class
- `GET /admin/attendance_ajax.php?action=load&class_id=X&section_id=Y&date=YYYY-MM-DD` - Load students & attendance
- `POST /admin/attendance_ajax.php?action=save` - Save attendance

## PDF Export

Result cards and fee receipts can be exported as PDF if TCPDF is installed.

To install TCPDF:
```bash
composer require tecnickcom/tcpdf
```

Or manually:
1. Download from https://tcpdf.org
2. Extract to `/vendor/tcpdf/`

## Troubleshooting

**Database Connection Error**
- Verify credentials in `includes/config.php`
- Check MySQL is running
- Ensure database and user are created

**Setup Script Fails**
- Check database permissions
- Ensure config.php is readable/writable
- Verify tables don't already exist

**Attendance AJAX Not Working**
- Check browser console for errors
- Verify `attendance_ajax.php` exists
- Ensure POST data is being sent

**Missing PDF Export**
- TCPDF is optional; install if needed
- Fall back to HTML view if TCPDF unavailable

## Support & Maintenance

- Backup database regularly
- Monitor disk space for uploaded files
- Update passwords periodically
- Review audit logs in `attendance_edits`
- Clear old biometric logs periodically

## License

This SMS is provided as-is for educational and school management purposes.

---

**Created:** January 2026  
**Version:** 1.0
