# 🎓 XAMPP पर SMS Project को Run करने का Complete Guide

## ✅ पूरी Setup Process (Step-by-Step)

---

## **PART 1: XAMPP Installation & Setup**

### **Step 1: XAMPP Download करें**
1. Browser खोलें और जाएं: https://www.apachefriends.org/
2. अपने OS के लिए XAMPP Download करें:
   - **Windows**: XAMPP-7.4 या XAMPP-8.0+ download करें
   - **Mac**: DMG file download करें
   - **Linux**: Installer download करें
3. Download पूरी हो जाने दें (500MB लगभग)

### **Step 2: XAMPP Install करें**
- **Windows:**
  1. Downloaded file को double-click करें
  2. "Next" buttons दबाते जाएं
  3. Installation path: `C:\xampp` (default) रखें
  4. "Finish" दबाएं
  5. Restart करने की जरूरत हो तो करें

- **Mac:**
  1. DMG file खोलें
  2. XAMPP icon को Applications में drag करें
  3. Applications में से XAMPP को open करें

- **Linux:**
  1. Terminal खोलें
  2. यह command चलाएं:
  ```bash
  sudo chmod +x xampp-linux-installer.run
  sudo ./xampp-linux-installer.run
  ```

### **Step 3: XAMPP Control Panel खोलें**
- **Windows/Mac:** Applications में "XAMPP Control Panel" ढूंढें और खोलें
- **Linux:** Terminal में यह command दें:
```bash
sudo /opt/lampp/manager-linux-x64.run
```

### **Step 4: Apache और MySQL Start करें**
XAMPP Control Panel में:
1. **Apache** के सामने "Start" button दबाएं (Green color हो जाएगा)
2. **MySQL** के सामने "Start" button दबाएं (Green color हो जाएगा)

**✅ दोनों Green हों तो ठीक है!**

### **Step 5: सब कुछ चल रहा है Check करें**
1. Browser खोलें
2. यह URL enter करें: `http://localhost`
3. **XAMPP Dashboard** दिखना चाहिए (Success! ✅)

---

## **PART 2: Project Files को सही जगह रखना**

### **Step 1: Project Files की Location**

**Windows के लिए:**
```
C:\xampp\htdocs\SMS-1\
```

**Mac के लिए:**
```
/Applications/XAMPP/htdocs/SMS-1/
```

**Linux के लिए:**
```
/opt/lampp/htdocs/SMS-1/
```

### **Step 2: Project Files Copy करना**

**Option A: Copy-Paste करें (आसान)**
1. आपके project का folder खोलें (जहाँ सभी PHP files हैं)
2. सभी files को select करें (Ctrl+A)
3. Copy करें (Ctrl+C)
4. XAMPP की htdocs folder खोलें
5. Paste करें (Ctrl+V)
6. Folder का नाम **SMS-1** रखें

**Option B: GitHub से clone करें (Advanced)**
```bash
cd C:\xampp\htdocs
git clone https://github.com/storeahmyat-rgb/SMS-1.git
```

### **Step 3: Folder Structure Check करें**

यह structure होना चाहिए:
```
C:\xampp\htdocs\SMS-1\
├── index.php                    ← Login page
├── setup.php                    ← Setup page
├── admin/
│   ├── dashboard.php
│   ├── attendance.php
│   ├── students.php
│   ├── teachers.php
│   └── ... (तमाम admin pages)
├── includes/
│   ├── config.php               ← DATABASE CONFIG (Important!)
│   ├── db.php
│   ├── auth.php
│   ├── header.php
│   └── footer.php
├── sql/
│   ├── schema.sql               ← Database tables
│   └── sample_data.sql          ← Test data
├── teacher/
│   └── dashboard.php
├── accountant/
│   └── dashboard.php
└── README.md, SETUP.md, etc.
```

**अगर यह structure है तो ✅ सब ठीक है!**

---

## **PART 3: Database Configuration**

### **Step 1: includes/config.php File खोलें**

Location: `C:\xampp\htdocs\SMS-1\includes\config.php`

File को कोई text editor से खोलें (Notepad++, VS Code, या Sublime Text)

### **Step 2: Database की Information डालें**

आपको यह दिखेगा:
```php
<?php
define('DB_HOST', '127.0.0.1');      // Database server address
define('DB_NAME', 'sms_db');         // Database name
define('DB_USER', 'sms_user');       // Database user
define('DB_PASS', 'sms_pass');       // Database password
```

**XAMPP में default settings हैं:**
- **DB_HOST**: `127.0.0.1` (यह पहले से ठीक है)
- **DB_NAME**: `sms_db` (यह रखें)
- **DB_USER**: `root` (CHANGE करें - XAMPP में default "root" है)
- **DB_PASS**: `` (CHANGE करें - XAMPP में EMPTY है)

### **Step 3: सही Configuration लिखें**

अपने config.php में यह लिखें:

```php
<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', '127.0.0.1');          // localhost
define('DB_NAME', 'sms_db');             // Database name
define('DB_USER', 'root');               // ← CHANGE: XAMPP में "root" होता है
define('DB_PASS', '');                   // ← CHANGE: XAMPP में password खाली होता है
define('DB_CHARSET', 'utf8mb4');

// Session Configuration
define('SESSION_TIMEOUT', 1800);          // 30 minutes
define('BASE_URL', 'http://localhost/SMS-1/');

// Error Handling
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
```

**File को Save करें (Ctrl+S)**

---

## **PART 4: Database बनाना और Tables Create करना**

### **Option A: Automatic Setup (आसान - Recommended) ✅**

1. Browser खोलें
2. यह URL enter करें:
```
http://localhost/SMS-1/setup.php
```

3. आपको एक page दिखेगा with **"Setup Database"** button
4. Button दबाएं
5. Wait करें... (कुछ seconds लगेंगे)
6. Success message दिखेगा:
```
✅ Database created successfully!
✅ All tables created!
✅ Default admin user created!
Username: admin
Password: admin123
```

**बस! Database तैयार है! ✅**

### **Option B: Manual Setup (Advanced)**

अगर Option A काम न करे:

1. Browser खोलें: `http://localhost/phpmyadmin`
2. Login करें (username: root, password: खाली छोड़ें)
3. **New** button दबाएं (बाईं तरफ)
4. Database name: `sms_db` enter करें
5. **Create** दबाएं

अब SQL files चलाएं:

1. Browser में phpmyadmin पर जाएं
2. अभी बनाया हुआ **sms_db** click करें
3. **Import** tab पर क्लिक करें
4. **Choose File** दबाएं
5. यह file select करें: `sql/schema.sql`
6. **Go** दबाएं
7. Wait करें...
8. **Success!** message दिखेगा

Repeat करें **sample_data.sql** के लिए (optional - test data के लिए):
1. Same steps
2. File select करें: `sql/sample_data.sql`
3. **Go** दबाएं

---

## **PART 5: Application को Access करना**

### **Step 1: Login Page खोलें**

Browser में यह URL enter करें:
```
http://localhost/SMS-1/
```

या

```
http://localhost/SMS-1/index.php
```

### **Step 2: Default Credentials से Login करें**

Login form में यह enter करें:
- **Username**: `admin`
- **Password**: `admin123`

**"Login" button दबाएं**

### **Step 3: Dashboard दिखना चाहिए**

Login successful हो तो आप **Admin Dashboard** पर पहुंचेंगे:

```
Welcome to School Management System
Dashboard
├── Students
├── Teachers
├── Attendance
├── Fees
├── Exams
├── Reports
└── ... (ओर बहुत कुछ)
```

**✅ Setup Complete! System चल रहा है!**

---

## **PART 6: Troubleshooting (अगर कोई Error आए)**

### **Error 1: "localhost refused to connect"**
**Solution:**
- Check करें कि Apache Green है या नहीं (XAMPP Control Panel में)
- Apache को Stop करें फिर Start करें
- Browser cache clear करें (Ctrl+Shift+Delete)

### **Error 2: "MySQL connection failed"**
**Solution:**
- Check करें: MySQL Green है या नहीं (XAMPP Control Panel में)
- config.php में database details सही हैं या नहीं
- MySQL को Restart करें

### **Error 3: "Cannot access the file"**
**Solution:**
- Check करें: File `C:\xampp\htdocs\SMS-1\` में है या नहीं
- Folder नाम exactly **SMS-1** हो (capital S)
- Browser में URL सही enter करें

### **Error 4: "Access Denied" (Database login में)**
**Solution:**
- phpmyadmin खोलें: `http://localhost/phpmyadmin`
- Check करें कि mysql running है
- Username change करें from `sms_user` to `root` in config.php

### **Error 5: "Table doesn't exist"**
**Solution:**
- setup.php फिर से run करें: `http://localhost/SMS-1/setup.php`
- या manually Import करें (Option B से)

---

## **PART 7: Daily Use के लिए Tips**

### **हर दिन शुरू करने में:**
```
1. XAMPP Control Panel खोलें
2. Apache Start करें
3. MySQL Start करें
4. Browser में यह open करें: http://localhost/SMS-1/
5. admin/admin123 से login करें
```

### **काम ख़त्म करने में:**
```
1. Apache को Stop करें (Control Panel से)
2. MySQL को Stop करें (Control Panel से)
3. Browser को Close करें
```

### **Localhost से बाहर Access करने के लिए:**
- अपना IP address पता करें: `ipconfig` (Windows) या `ifconfig` (Mac/Linux)
- फिर दूसरे computer पर यह enter करें:
```
http://आपका-IP-ADDRESS/SMS-1/
```

---

## **PART 8: Features के साथ परिचय**

अब जब application खुल गया है, तो ये सब कर सकते हो:

### **Admin के लिए:**
- ✅ Students add/view/edit
- ✅ Teachers add/view/edit
- ✅ Attendance mark करें (AJAX - real-time)
- ✅ Fees collect करें
- ✅ Exams create करें
- ✅ Results देखें
- ✅ Reports generate करें
- ✅ Salary track करें

### **Teacher के लिए:**
- ✅ अपना Attendance देख सकते हैं
- ✅ Basic dashboard

### **Accountant के लिए:**
- ✅ Fee collection देख सकते हैं
- ✅ Financial reports
- ✅ Basic dashboard

---

## **PART 9: Database को Reset करना**

अगर गलती हो जाए और फिर से शुरू करना हो:

### **Option 1: Complete Reset**
1. phpmyadmin खोलें: `http://localhost/phpmyadmin`
2. **sms_db** पर right-click करें
3. **Drop** दबाएं
4. Confirm करें
5. फिर setup.php फिर से run करें

### **Option 2: Data को Delete करना (Tables रखते हुए)**
1. phpmyadmin खोलें
2. **sms_db** select करें
3. सभी tables select करें
4. Bottom से **Empty** select करें
5. **Go** दबाएं

---

## **PART 10: Production के लिए (भविष्य में)**

अगर आप इसे hosting पर deploy करना चाहते हो:

1. **FTP client** use करें (FileZilla)
2. Project files को hosting के `public_html` में upload करें
3. Database को hosting के MySQL में बनाएं
4. config.php को update करें (hosting की details से)
5. setup.php को run करें hosting पर

---

## **✅ Quick Reference Checklist**

- [ ] XAMPP installed है
- [ ] Apache चल रहा है (Green)
- [ ] MySQL चल रहा है (Green)
- [ ] Files `C:\xampp\htdocs\SMS-1\` में हैं
- [ ] config.php में database details सही हैं (root, empty password)
- [ ] setup.php से database create किया गया है
- [ ] `http://localhost/SMS-1/` खुल रहा है
- [ ] admin/admin123 से login हो सकते हैं
- [ ] Dashboard दिख रहा है

**सब Done हो तो आप All Set हो!** ✅

---

## **Support/Help**

अगर कोई problem हो:

1. **Error message को carefully पढ़ें** - अक्सर solution वहीं लिखा होता है
2. **Browser की F12 key दबाएं** - Console में detailed error देखने को मिलेगी
3. **XAMPP logs check करें** - Control Panel में "Logs" button है
4. **Try करें फिर से restart XAMPP को**

---

## **मुबारक! आप Ready हो! 🎉**

अब आपके पास एक complete School Management System है जो:
- ✅ Students को manage करता है
- ✅ Teachers को track करता है
- ✅ Attendance automatic mark होती है
- ✅ Fees को collection करता है
- ✅ Exams और Results को handle करता है
- ✅ Reports generate करता है
- ✅ Financial data को organize करता है

**Enjoy! Happy Learning! 📚**

---

*Last Updated: January 15, 2026*  
*XAMPP Version: 7.4+ या 8.0+*  
*PHP Version: 7.4+*  
*MySQL Version: 5.7+*
