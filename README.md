# School Management System (SMS)

Simple Core-PHP School Management System scaffold.

Requirements:
- PHP 8+, PDO, MySQL
- Apache/Nginx
- TCPDF (for PDF exports)

Quick setup:

1. Edit `includes/config.php` with your DB credentials.
2. Run `php setup.php` from the project root to create tables and default admin.
3. Open the app in your browser and login with the default admin credentials shown after setup.

Files of interest:
- `includes/config.php` - DB config
- `includes/db.php` - PDO connection
- `includes/auth.php` - authentication helpers
- `admin/` - admin pages
- `sql/schema.sql` - full SQL schema
# SMS-1