# Database Configuration - Plesk Deployment Guide

## ✅ สรุปการแก้ไข Database Files

ได้ปรับปรุง **Database/config.php** และ **Database/config_plesk.php** ให้มี:
- ✅ Better error handling
- ✅ UTF8MB4 charset (support emoji + multilingual)
- ✅ Path helper functions
- ✅ PDO configuration best practices
- ✅ Timezone setting

---

## 🔧 การเปลี่ยนแปลง

### 1. **config.php** - Main Database Config
```php
✅ เพิ่ม PDO options array:
   [
       PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
       PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
       PDO::ATTR_EMULATE_PREPARES => false,
   ]

✅ เพิ่ม timezone setting:
   $conn->exec("SET time_zone = '+07:00'");

✅ เพิ่ม path helper functions:
   - get_project_root()
   - get_frontend_path()
   - get_backend_path()
   - get_layout_path()

✅ Better error messages พร้อม debug info
```

### 2. **config_plesk.php** - Alternative for Plesk
```php
✅ เหมือนกับ config.php ทุกประการ
✅ Comment อธิบายวิธีใช้

วิธีใช้ (เลือก 1 วิธี):
- ใช้ config.php เลย (recommended)
- หรือเปลี่ยนชื่อ config_plesk.php → config.php
```

---

## 📋 ความเปลี่ยนแปลงรายละเอียด

### ❌ เดิม:
```php
$host = 'localhost';
$db_name = 'test';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
```

### ✅ ใหม่:
```php
// ============ DATABASE CREDENTIALS ============
$host = 'localhost';
$db_name = 'test';
$username = 'root';
$password = '';
$charset = 'utf8mb4';  // ← เปลี่ยนเป็น utf8mb4

// ============ PDO CONNECTION ============
try {
    $dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";
    $conn = new PDO($dsn, $username, $password, [  // ← เพิ่ม options array
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // ← default fetch mode
        PDO::ATTR_EMULATE_PREPARES => false,  // ← security
    ]);
    
    // ตั้ง charset และ timezone
    $conn->exec("SET NAMES $charset");
    $conn->exec("SET time_zone = '+07:00'");  // ← timezone
    
} catch(PDOException $e) {
    die("❌ Database Connection Error: " . htmlspecialchars($e->getMessage()) . 
        "\n\nDebug Info:\nHost: $host\nDatabase: $db_name\nUser: $username");  // ← debug info
}
```

---

## ✨ Improvements

### 1. **UTF8MB4 Charset**
```
✅ ก่อน: charset=utf8 (3-byte, limited)
✅ หลัง: charset=utf8mb4 (4-byte, emoji support)
```

### 2. **PDO Options**
```php
// ✅ ATTR_DEFAULT_FETCH_MODE = PDO::FETCH_ASSOC
// เมื่อเรียก fetch() จะได้ array โดยอัตโนมัติ
$stmt->fetch();  // ได้ array โดยไม่ต้อง fetch(PDO::FETCH_ASSOC)

// ✅ ATTR_EMULATE_PREPARES = false
// ป้องกัน SQL injection ได้ดีขึ้น
```

### 3. **Timezone Setting**
```php
$conn->exec("SET time_zone = '+07:00'");  // Bangkok timezone
// ทำให้ MySQL query เหมือนกับ PHP date()
```

### 4. **Path Helper Functions**
```php
// เพิ่มฟังก์ชันใช้ได้ในทั้ง app:
get_project_root();   // /home/user/public_html/Project_final
get_frontend_path();  // /home/user/public_html/Project_final/Frontend
get_backend_path();   // /home/user/public_html/Project_final/backend
get_layout_path();    // /home/user/public_html/Project_final/Layout
```

### 5. **Better Error Messages**
```
❌ เดิม: Connection failed: SQLSTATE[HY000] [1045] Access denied...

✅ ใหม่: ❌ Database Connection Error: ...
          Debug Info:
          Host: localhost
          Database: test
          User: root
```

---

## 🚀 For Plesk Deployment

### Step 1: แก้ไข Database Credentials
```php
// ก่อน upload ไป Plesk ต้องแก้ไขค่านี้:
$host = 'localhost';        // หรือ Plesk DB hostname
$db_name = 'my_database';   // ชื่อ DB จริง
$username = 'my_user';      // Username จริง
$password = 'my_password';  // Password จริง
```

### Step 2: Upload Files
- Upload folder `Project_final` ไป `/public_html/`

### Step 3: Test Connection
- ไปที่ `https://yourdomain.com/Project_final/Frontend/index.php`
- ตรวจสอบว่า page ขึ้นมา (อาจมี error ถ้า DB ไม่ถูก)

### Step 4: Troubleshoot (ถ้า Error)
```
Error: Access denied for user 'root'@'localhost'
→ แก้ username/password ให้ถูก

Error: Unknown database 'test'
→ แก้ db_name ให้ถูก

Error: Cannot connect to server
→ แก้ $host ให้ถูก (อาจเป็น localhost, 127.0.0.1, หรือ custom hostname)
```

---

## 📝 Quick Reference

| Item | ค่า | หมายเหตุ |
|------|-----|---------|
| Charset | `utf8mb4` | ✅ Support emoji + multilingual |
| Timezone | `+07:00` | ✅ Bangkok timezone |
| PDO Mode | `FETCH_ASSOC` | ✅ Auto array format |
| Error Mode | `ERRMODE_EXCEPTION` | ✅ Throw exceptions |
| Emulate Prepares | `false` | ✅ Better security |

---

## ✅ Ready for Deployment

- ✅ Database/config.php → Optimized for all environments
- ✅ Database/config_plesk.php → Alternative version
- ✅ Path helpers ready for use
- ✅ Error handling improved
- ✅ Timezone configured

**Next:** Edit `$host`, `$db_name`, `$username`, `$password` ตามค่าจริงของ Plesk database

