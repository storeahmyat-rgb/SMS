# XAMPP Par SMS Project Ko Run Karna - Roman English Guide

## **YEH GUIDE COMPLETELY SIMPLE HAI - BAS STEPS FOLLOW KARO!**

---

## **STEP 1: XAMPP Download Karo**

### Kya karna hai:
1. Google mein likho: `XAMPP download`
2. Ya directly jaao: `www.apachefriends.org`
3. Apne computer OS ke liye download karo:
   - **Windows** → XAMPP-7.4 ya 8.0 download karo
   - **Mac** → DMG file download karo
   - **Linux** → Linux installer download karo

4. Download poora ho jaao dene do (500 MB lagbhag)

---

## **STEP 2: XAMPP Install Karo**

### Windows mein:
1. Jo file download hui, usko double-click karo
2. "Next" buttons dabate raho
3. Installation path: `C:\xampp` rakhna (default hi theek hai)
4. "Finish" daba de
5. Khatam!

### Mac mein:
1. DMG file khol de
2. XAMPP ko Applications mein drag kar de
3. Khatam!

### Linux mein:
Terminal mein yeh likho:
```bash
sudo chmod +x xampp-linux-installer.run
sudo ./xampp-linux-installer.run
```

---

## **STEP 3: XAMPP Khol De**

### Windows/Mac mein:
- Applications mein "XAMPP" khoj aur khol de
- Ya "XAMPP Control Panel" search kar de

### Linux mein:
Terminal mein likho:
```bash
sudo /opt/lampp/manager-linux-x64.run
```

---

## **STEP 4: Apache Aur MySQL Start Karo**

XAMPP Control Panel mein:

```
Apache ke saamne      → "Start" button daba
MySQL ke saamne       → "Start" button daba
```

Jab dono **GREEN** color ho jayen, toh sab theek hai! ✅

---

## **STEP 5: Check Karo Sab Kuch Chal Raha Hai**

1. Browser khol (Chrome, Firefox, kuch bhi)
2. Yeh likho: `http://localhost`
3. XAMPP ka dashboard dikhai de toh **SUCCESS!** ✅

---

## **STEP 6: Apne SMS Project Files Ko Sahi Jagah Rakh**

### Files Kaun Si Location Mein Dalne Hain:

**Windows mein:**
```
C:\xampp\htdocs\SMS-1\
```

**Mac mein:**
```
/Applications/XAMPP/htdocs/SMS-1/
```

**Linux mein:**
```
/opt/lampp/htdocs/SMS-1/
```

### Kaise Copy Kare:
1. Apne SMS project ka folder khol (jisme saari PHP files hain)
2. Sab files select karo (Ctrl+A)
3. Copy karo (Ctrl+C)
4. XAMPP ke htdocs folder mein jaao
5. Paste karo (Ctrl+V)
6. Folder ka naam **SMS-1** rakh de

---

## **STEP 7: Folder Structure Check Karo**

Yeh folder structure hona chahiye:

```
C:\xampp\htdocs\SMS-1\
│
├── index.php                    (Login page)
├── setup.php                    (Setup ke liye - IMPORTANT!)
│
├── admin/                       (Admin ka folder)
│   ├── dashboard.php
│   ├── attendance.php
│   ├── students.php
│   ├── teachers.php
│   └── aur baki sab files
│
├── includes/                    (Important files)
│   ├── config.php               (DATABASE CONFIG - ZAROORI!)
│   ├── db.php
│   ├── auth.php
│   ├── header.php
│   └── footer.php
│
├── sql/                         (Database files)
│   ├── schema.sql
│   └── sample_data.sql
│
├── teacher/
│   └── dashboard.php
│
├── accountant/
│   └── dashboard.php
│
└── README.md, SETUP.md, aur docs
```

Agar yeh structure hai toh **SHUKRIYA!** ✅

---

## **STEP 8: Database Config File Ko Edit Karo (ZAROORI!)**

### File Ko Khol:
Location: `C:\xampp\htdocs\SMS-1\includes\config.php`

Koi bhi text editor use karo (Notepad++, VS Code, ya Sublime Text)

### File Mein Yeh Dega Dikhaii:
```php
<?php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'sms_db');
define('DB_USER', 'sms_user');
define('DB_PASS', 'sms_pass');
```

### XAMPP Ke Liye Sahi Settings:

**DB_USER** ko change karo:
```php
define('DB_USER', 'root');        // ← XAMPP mein "root" hota hai
```

**DB_PASS** ko empty rakh de:
```php
define('DB_PASS', '');            // ← XAMPP mein password nahi hota (empty)
```

### Sahi Version (XAMPP Ke Liye):
```php
<?php
session_start();

// Database Configuration
define('DB_HOST', '127.0.0.1');    // localhost
define('DB_NAME', 'sms_db');       // Database ka naam
define('DB_USER', 'root');         // ← CHANGE YEH
define('DB_PASS', '');             // ← YEH EMPTY RAKH
define('DB_CHARSET', 'utf8mb4');

// Session Configuration
define('SESSION_TIMEOUT', 1800);   // 30 minutes
define('BASE_URL', 'http://localhost/SMS-1/');

// Error Handling
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
```

**File save karo (Ctrl+S)**

---

## **STEP 9: Database Banao**

### EASY TARIKA (Recommended) - Sabse Aasan!

1. Browser khol
2. Yeh likho:
```
http://localhost/SMS-1/setup.php
```

3. Ek page khul jaega "Setup Database" button ke sath
4. Button daba de
5. Thodi der ruko... (5-10 seconds)
6. Yeh message dikhaai de:
```
✅ Database created successfully!
✅ All tables created!
✅ Default admin user created!
Username: admin
Password: admin123
```

**Bas! Database tayyar hai!** ✅

---

## **STEP 10: Application Ko Open Karo**

### Login Page Khol:

1. Browser mein likho:
```
http://localhost/SMS-1/
```

2. Ek login form dikhaai de:
```
Username: ___________
Password: ___________
        [LOGIN]
```

---

## **STEP 11: Login Karo**

### Default Username Aur Password:

```
Username: admin
Password: admin123
```

Dono fields mein yeh likho aur LOGIN button daba de!

---

## **STEP 12: Dashboard Khul Jaega**

Success! Ab tum dekho ge:

```
WELCOME TO SCHOOL MANAGEMENT SYSTEM

Dashboard
├── Students (Students ko add/view karo)
├── Teachers (Teachers ko manage karo)
├── Attendance (Students ki attendance mark karo)
├── Fees (Fees collect karo)
├── Exams (Exams create aur results dekho)
├── Reports (Sab reports)
├── Salaries (Teachers ki salary track karo)
└── Aur baki sab features
```

**COMPLETE! System chal gaya!** 🎉

---

## **AGAR KUCH PROBLEM HO TO?**

### Problem 1: "localhost refused to connect"
**Solution:**
- XAMPP Control Panel khol
- Check karo Apache GREEN hai ya nahi?
- Agar GREEN nahi hai, toh START karo
- Browser ko refresh karo (F5)

### Problem 2: "MySQL connection failed"
**Solution:**
- XAMPP Control Panel mein MySQL check karo
- Agar GREEN nahi hai, toh START karo
- STOP phir START karo (restart karo)
- config.php check karo - sahi likha hai?

### Problem 3: "File not found"
**Solution:**
- Check karo SMS-1 folder `C:\xampp\htdocs\` mein hai?
- Folder ka naam exactly "SMS-1" hona chahiye (capital S)
- URL sahi likho: `http://localhost/SMS-1/`

### Problem 4: "Access Denied" database mein
**Solution:**
- config.php khol
- DB_USER ko "root" likho
- DB_PASS ko empty rakh (kuch likho mat)
- File save karo
- XAMPP ko restart karo

### Problem 5: "Table doesn't exist"
**Solution:**
- Browser mein jaao: `http://localhost/SMS-1/setup.php`
- Setup button daba de
- Database fir se banao

---

## **ROZ SHURU KARNE KE LIYE:**

Jab subah kam karna start karo:

```
1. XAMPP Control Panel khol
2. Apache ka START button daba
3. MySQL ka START button daba
4. Dono GREEN ho jayen
5. Browser mein likho: http://localhost/SMS-1/
6. admin/admin123 se login karo
7. Kam shuru karo!
```

---

## **KAM KHATAM KARTE HOTE:**

```
1. XAMPP Control Panel mein Apache ko STOP karo
2. MySQL ko STOP karo
3. Browser band karo
4. XAMPP band karo
5. Computer band kar
```

---

## **YEH FEATURES USE KAR SAKTE HO:**

### **Admin Log In Karo Toh:**
✅ Students add karo  
✅ Teachers add karo  
✅ Attendance mark karo (Ek click mein sab!)  
✅ Fees collect karo  
✅ Exams create karo  
✅ Results dekho  
✅ Reports banao  
✅ Salary track karo  

### **Teacher Log In Kare Toh:**
✅ Apna dashboard dekhe  
✅ Status check kare  

### **Accountant Log In Kare Toh:**
✅ Fees dekhne ka  
✅ Financial reports dekhne ka  

---

## **AGAR DATABASE RESET KARNA HO (Galti Gadbad Ho Gayi To):**

### Option 1: Sab Kuch Naya Banao:
1. Browser mein khol: `http://localhost/phpmyadmin`
2. **sms_db** database par right-click karo
3. **Drop** likho
4. Confirm karo
5. Phir setup.php fir se run karo

### Option 2: Sirf Data Delete Karo (Tables rakho):
1. phpmyadmin khol
2. **sms_db** select karo
3. Sab tables select karo
4. **Empty** likho
5. **Go** daba de

---

## **CHECKLIST - SHUKAR DO DEKHO SABB THEEK HAI:**

- [ ] XAMPP download aur install hai?
- [ ] XAMPP Control Panel khul gaya?
- [ ] Apache GREEN hai?
- [ ] MySQL GREEN hai?
- [ ] SMS-1 folder `C:\xampp\htdocs\` mein hai?
- [ ] config.php file change kardi (root, empty password)?
- [ ] setup.php se database banaya?
- [ ] `http://localhost/SMS-1/` khul raha hai?
- [ ] admin/admin123 se login ho sakte ho?
- [ ] Dashboard dikha raha hai?

**Sab done? Toh BADHAYI HO!** 🎉

---

## **AGAR PHIR BHI SAMAJ NAHI AYA:**

1. **Step 1 aur 2 ko repeat karo** - XAMPP aur MySQL
2. **Step 6 ko dhyan se dekho** - Files sahi folder mein hain?
3. **Step 8 ko dhyan se dekho** - config.php sahi set hai?
4. **Step 9 ko dobara try karo** - setup.php se database banao

**Agar aur problem ho toh batao!** 👍

---

**MUSHKIL NAHI HAI! BAS STEPS FOLLOW KARO!**

*Last Updated: January 15, 2026*
