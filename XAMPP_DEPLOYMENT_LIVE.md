# SMS Online Deployment Guide (Roman Urdu/Hindi)

Bhai, project ko online live krne k liye ye 4 asaan steps follow krey:

### 1. Files Upload Krein
Aapne hosting khareedi hogi (CPanel/Shared Hosting).
- Hosting k **File Manager** me jayein.
- `public_html` folder me pura `SMS` ka folder upload krdein.
- Agar aap chahte hain k project direct link (e.g., `www.apkiwebsite.com`) pr khuley, to saari files folder se bahar nikaal kr `public_html` me rakh dein.

### 2. Database Live Krein
1. Localhost (XAMPP) k PHPMyAdmin se database ko **Export** krein (SQL file download hogi).
2. Live hosting k CPanel me **MySQL Databases** me ja kr aik naya Database aur User banayein.
3. User ko Database se link (Add) krein aur saari permissions allow krein.
4. Live hosting k **PHPMyAdmin** me ja kr wahan naye database me SQL file ko **Import** krdein.

### 3. Configuration Update Krein
Ye step sab se zaruri hai. Folder me `includes/config.php` file ko edit krein:

```php
// Nye Database ki details yahan likhein
define('DB_HOST', 'localhost'); // Aksar localhost hi hota hai
define('DB_NAME', 'apka_new_db_name');
define('DB_USER', 'apka_new_db_user');
define('DB_PASS', 'apka_new_db_password');

// Base URL change krein
// Agar subfolder me hai to '/SMS/' wrna sirf '/'
define('BASE_URL', '/'); 
```

### 4. Error Reporting Band Krein (Security)
Jab project online chala jaye, to `config.php` me errors ko chhupa dein:

```php
error_reporting(0);
ini_set('display_errors', 0);
```

### Pro Tip:
Hmesha **HTTPS** (SSL) use krein taakey login details secure rahein. Aksar hosting me "AutoSSL" free hota hai.

---
Abh aapka project online chalne k liye tayyar hai! 😊
