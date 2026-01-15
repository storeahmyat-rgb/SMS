# QUICK START GUIDE - SMS (School Management System)

## ⚡ 5-Minute Setup

### Prerequisites
- PHP 8.0+
- MySQL 5.7+
- Apache/Nginx
- PDO PHP extension

### Step 1: Database Setup

```bash
# Login to MySQL
mysql -u root -p

# Create database and user
CREATE DATABASE sms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sms_user'@'localhost' IDENTIFIED BY 'sms_pass';
GRANT ALL PRIVILEGES ON sms_db.* TO 'sms_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 2: Configure Database

Edit `includes/config.php`:

```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'sms_db');
define('DB_USER', 'sms_user');
define('DB_PASS', 'sms_pass');
```

### Step 3: Run Setup Script

```bash
cd /path/to/sms
php setup.php
```

**Output:**
```
Schema created and default admin inserted.
Default admin credentials:
  username: admin
  password: admin123
Please change the password after first login.
```

### Step 4: Load Sample Data (Optional)

```bash
mysql -u sms_user -p sms_db < sql/sample_data.sql
```

### Step 5: Access Application

Open browser:
```
http://localhost/sms/
```

**Login:**
- Username: `admin`
- Password: `admin123`

---

## 📋 Complete Feature List

### Authentication & Security
✅ Secure login with password hashing  
✅ Session-based authentication  
✅ Role-based access control (Super Admin, Teacher, Accountant)  
✅ Session timeout (30 minutes)  
✅ SQL injection prevention (prepared statements)  
✅ XSS protection (htmlspecialchars)  

### Student Management (100% Complete)
✅ Admission system with unique admission number  
✅ Complete student profile:
   - Full name, father name, CNIC/B-Form
   - Class, section, roll number
   - Gender, date of birth
   - Contact number, address
   - Admission date, status (Active/Left)

✅ Student attendance (Daily):
   - Mark as Present/Absent/Leave/Late
   - Edit with audit trail
   - Monthly attendance reports
   - Class-wise attendance
   - Attendance edit history view

### Teacher Management (100% Complete)
✅ Teacher profile:
   - Teacher ID, full name, CNIC
   - Qualification, assigned subjects, classes
   - Contact number, salary
   - Joining date, status (Active/Left)

✅ Teacher attendance:
   - In-time / Out-time tracking
   - Status marking (Present/Absent/Leave/Late)
   - Monthly attendance reports

✅ Teacher CRUD:
   - Add new teacher
   - Edit teacher information
   - View teacher list

### Academic Module (100% Complete)
✅ Classes management (Playgroup to Class 10)  
✅ Sections (A, B, C per class)  
✅ Subjects management  
✅ Timetable:
   - Class/section-wise schedule
   - Day and period-wise arrangement
   - Subject and teacher assignment
   - Time slots (start/end time)

### Fees & Accounts Module (100% Complete)
✅ Fee structure:
   - Admission fee, Monthly fee, Exam fee
   - Transport fee, Custom fees
   - Description for each fee type

✅ Fee collection:
   - Student-wise fee payment recording
   - Partial payment support
   - Payment method tracking (Cash/Bank/Card)
   - Automatic fee receipt generation

✅ Reports:
   - Student ledger (all payments)
   - Pending fees list
   - Daily collection report
   - Monthly/class-wise collection breakdown
   - Income vs expenses analysis

### Salary & Expenses (100% Complete)
✅ Teacher salary:
   - Monthly salary recording
   - Paid/Unpaid status tracking
   - Payment history

✅ School expenses:
   - Expense category tracking
   - Amount and date recording
   - Description field

✅ Financial reports:
   - Profit & Loss statement
   - Monthly income calculation
   - Expense summary
   - Net profit/loss display

### Exams & Results (100% Complete)
✅ Exam management:
   - Create exams with start/end dates
   - Subject-wise marks entry
   - Multiple student marks in one exam

✅ Result features:
   - Automatic grade calculation (A+, A, B, C, D, F)
   - Percentage calculation
   - Result cards (HTML & PDF)
   - Student-wise result view

✅ PDF export (with TCPDF):
   - Result card printing
   - Fee receipt printing

### Biometric Integration (100% Complete)
✅ CSV import:
   - Device user ID mapping
   - Student/teacher assignment
   - Timestamp logging

✅ Manual override:
   - Override biometric attendance
   - Reason tracking

### Admin Dashboards (100% Complete)
✅ Super Admin Dashboard:
   - Total students, teachers, classes count
   - Monthly income & salary overview
   - Quick links to all modules
   - Statistics cards

✅ Teacher Dashboard:
   - Personalized welcome
   - Quick access to attendance

✅ Accountant Dashboard:
   - Income report view
   - Payment tracking
   - Collection summary

---

## 🗂️ Directory Structure

```
sms/
├── index.php                      # Main login page
├── setup.php                      # Initial setup script
├── README.md                      # Project overview
├── SETUP.md                       # Detailed setup guide
├── QUICKSTART.md                  # This file
│
├── includes/                      # Shared utilities
│   ├── config.php                 # Database configuration
│   ├── db.php                     # PDO connection helper
│   ├── auth.php                   # Authentication functions
│   ├── header.php                 # HTML header template
│   └── footer.php                 # HTML footer template
│
├── admin/                         # Super admin pages (36 files)
│   ├── dashboard_enhanced.php     # Enhanced admin dashboard
│   ├── students.php               # Student list & search
│   ├── student_add.php            # Add student form
│   ├── teachers.php               # Teacher list
│   ├── teacher_add.php            # Add teacher form
│   ├── teacher_edit.php           # Edit teacher form
│   ├── classes_manage.php         # Manage classes
│   ├── sections_manage.php        # Manage sections
│   ├── subjects_manage.php        # Manage subjects
│   ├── timetable_manage.php       # Setup timetable
│   ├── attendance.php             # Mark student attendance
│   ├── attendance_ajax.php        # AJAX endpoint for attendance
│   ├── attendance_edit.php        # View attendance edit audit log
│   ├── attendance_report.php      # Student monthly attendance
│   ├── teacher_attendance.php     # Mark teacher attendance
│   ├── fees.php                   # Fee structure management
│   ├── fee_pay.php                # Collect fees from students
│   ├── fee_receipt.php            # View/print fee receipt
│   ├── ledger.php                 # Student fee ledger
│   ├── pending_fees.php           # Pending fees report
│   ├── income_report.php          # Income & collection report
│   ├── salaries_manage.php        # Teacher salary management
│   ├── expenses.php               # Record school expenses
│   ├── profitloss.php             # Profit & loss report
│   ├── exams.php                  # Create/manage exams
│   ├── exam_marks.php             # Enter student marks
│   ├── result_card.php            # View/print result card
│   ├── biometric_import.php       # Import biometric CSV
│   └── logout.php                 # Logout handler
│
├── teacher/                       # Teacher specific pages
│   └── dashboard.php              # Teacher dashboard
│
├── accountant/                    # Accountant specific pages
│   └── dashboard.php              # Accountant dashboard
│
├── sql/                           # Database files
│   ├── schema.sql                 # Complete database schema
│   └── sample_data.sql            # Sample data for testing
│
└── vendor/                        # (Optional) TCPDF library
    └── tcpdf/                     # For PDF generation
```

---

## 🎯 Main Features at a Glance

| Feature | Status | Pages |
|---------|--------|-------|
| Authentication | ✅ Complete | index.php, logout |
| Student CRUD | ✅ Complete | students.php, student_add.php |
| Student Attendance | ✅ Complete (AJAX) | attendance.php, attendance_ajax.php |
| Teacher CRUD | ✅ Complete | teachers.php, teacher_add.php, teacher_edit.php |
| Teacher Attendance | ✅ Complete | teacher_attendance.php |
| Classes/Sections/Subjects | ✅ Complete | classes_manage.php, sections_manage.php, subjects_manage.php |
| Timetable | ✅ Complete | timetable_manage.php |
| Fee Management | ✅ Complete | fees.php, fee_pay.php, fee_receipt.php |
| Student Ledger | ✅ Complete | ledger.php |
| Pending Fees | ✅ Complete | pending_fees.php |
| Income Reports | ✅ Complete | income_report.php |
| Salary Management | ✅ Complete | salaries_manage.php |
| Expenses Tracking | ✅ Complete | expenses.php |
| P&L Report | ✅ Complete | profitloss.php |
| Exams | ✅ Complete | exams.php, exam_marks.php |
| Results | ✅ Complete | result_card.php (HTML/PDF) |
| Biometric Import | ✅ Complete | biometric_import.php |
| Dashboards | ✅ Complete | dashboard.php, dashboard_enhanced.php |
| Attendance Reports | ✅ Complete | attendance_report.php |
| Attendance Audit | ✅ Complete | attendance_edit.php |

---

## 🚀 Usage Examples

### Add a New Student
1. Login as admin
2. Click "Manage" → Students → "Add Student"
3. Fill in all required fields
4. Click "Save"

### Mark Attendance
1. Go to Admin → Student Attendance
2. Select Class and Date
3. Click "Load Students"
4. Select attendance status for each student
5. Click "Save Attendance"

### Collect Fees
1. Go to Accountant → Collect Fee
2. Select student and fee type
3. Enter amount paid
4. Select payment method
5. Click "Receive Payment"
6. Print receipt if needed

### Create Exam & Enter Results
1. Go to Admin → Exams
2. Click "Create Exam" and fill details
3. Click "Enter Marks"
4. Select student and subject
5. Enter marks and total
6. Click "Save Marks"
7. View result card (HTML or PDF)

### View Reports
- **Income Report**: Admin → Income Report (shows daily, monthly, class-wise)
- **Pending Fees**: Admin → Pending Fees (shows students who haven't paid)
- **Student Ledger**: Admin → Student Ledger (shows all payments for one student)
- **Attendance**: Admin → Attendance Report (monthly attendance summary)
- **P&L Report**: Admin → Profit & Loss (shows net profit/loss)

---

## 🔐 Default Credentials

**Admin Account:**
- Username: `admin`
- Password: `admin123`

⚠️ **Change immediately after first login!**

---

## 📱 Browser Support

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ✅ Mobile browsers (responsive design)

---

## 🛠️ Troubleshooting

### Q: Database connection error
**A:** Check:
- MySQL is running
- Database exists: `sms_db`
- User exists: `sms_user`
- Credentials in `includes/config.php` match

### Q: Attendance AJAX not working
**A:** Check:
- `attendance_ajax.php` exists in `/admin/`
- Browser console for errors (F12)
- POST method is being used

### Q: PDF export not working
**A:** 
- TCPDF is optional
- HTML view will show if TCPDF not installed
- To enable: install TCPDF in `/vendor/tcpdf/`

### Q: Session expires too quickly
**A:** 
- Increase `SESSION_TIMEOUT` in `includes/config.php`
- Default is 30 minutes (1800 seconds)

---

## 📚 Database Tables (17 Total)

```
✅ users              - Login & roles
✅ students           - Student info
✅ student_attendance - Daily attendance (with audit)
✅ attendance_edits   - Attendance change log
✅ teachers           - Teacher info
✅ teacher_attendance - Teacher daily attendance
✅ classes            - Class definitions
✅ sections           - Section definitions
✅ subjects           - Subject definitions
✅ timetable          - Class schedule
✅ fees               - Fee types & amounts
✅ fee_payments       - Payment records
✅ salaries           - Salary records
✅ expenses           - Expense tracking
✅ exams              - Exam definitions
✅ results            - Exam results
✅ biometric_logs     - Biometric attendance
```

---

## 🔄 Sample Data Included

The `sql/sample_data.sql` file includes:
- 12 sample classes (Playgroup to Class 10)
- 24 sample sections
- 10 sample subjects
- 8 sample teachers with salaries
- 10 sample students (classes 1-2)
- Sample fees, payments, expenses, exams, results
- Sample timetable entries
- Sample attendance records
- Sample biometric logs

Load with:
```bash
mysql -u sms_user -p sms_db < sql/sample_data.sql
```

---

## 📞 Support

For issues:
1. Check SETUP.md for detailed documentation
2. Review error messages in browser console (F12)
3. Check MySQL logs for database errors
4. Verify file permissions on server

---

## 📝 Notes

- All transactions use prepared statements (SQL injection safe)
- Passwords hashed with `password_hash()` (bcrypt)
- XSS protection via `htmlspecialchars()`
- Session timeout set to 30 minutes
- Timezone: Server default (set in PHP if needed)

---

## 🎓 Educational Use

This SMS is perfect for:
- Learning school management concepts
- Training on web application development
- Understanding CRUD operations
- Role-based access control
- Database design (normalized)
- Reporting and analytics

---

**Version:** 1.0  
**Created:** January 2026  
**Last Updated:** January 15, 2026
