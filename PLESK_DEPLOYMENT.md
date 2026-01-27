# 📋 Plesk Deployment Guide

## ✅ การแก้ไข Path สำหรับ Plesk Server

### ปัญหา
- Path ในระบบ local (XAMPP) ใช้ `../` เพื่อขึ้นไปหา folder `Database/`
- เมื่อขึ้น Plesk อาจมีโครงสร้าง folder ต่างกัน เช่น public_html, private_html, etc.

### วิธีแก้ที่ทำแล้ว

#### 1. **Frontend/index.php** - ใช้ `realpath()` และ `__DIR__`
```php
$config_file = realpath(__DIR__ . '/../Database/config.php');
if (!file_exists($config_file)) {
    die('❌ ไม่พบไฟล์ config.php ที่: ' . htmlspecialchars($config_file));
}
require_once $config_file;
```

**ข้อดี:**
- ✅ ทำงานได้ทั้ง XAMPP และ Plesk
- ✅ ตรวจสอบ file exist ก่อนรัน
- ✅ Error handling ชัดเจน

---

### 2. **Database/config.php** - ตั้งค่า PDO ให้ดี
```php
try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
```

---

### 3. **Database/config_plesk.php** - Option เพิ่มเติม
ถ้า `config.php` ไม่ทำงานบน Plesk สามารถใช้ไฟล์นี้แทน

**วิธีใช้:**
```php
// แก้ไข Frontend/index.php บรรทัดที่ 2 จาก:
$config_file = realpath(__DIR__ . '/../Database/config.php');

// เป็น:
$config_file = realpath(__DIR__ . '/../Database/config_plesk.php');
```

---

## 🚀 ขั้นตอน Upload ไป Plesk

### Step 1: ตรวจสอบโครงสร้าง Plesk
- โดยปกติ Plesk มีโครงสร้าง:
```
/home/username/public_html/Project_final/
├── backend/
├── Database/
├── Frontend/
├── Layout/
└── script/
```

### Step 2: Upload Files
1. เชื่อมต่อ Plesk File Manager หรือ FTP
2. Upload folder ทั้งหมด (`Project_final/`) ไป `/public_html/`

### Step 3: แก้ไข Database Credentials
- ค้ืนที่ `Database/config.php`
- แก้ไข `$host`, `$db_name`, `$username`, `$password` ตามค่า Plesk

### Step 4: เข้าใจการทำงาน

#### ตัวอย่าง URL ถ้าโครงสร้าง Plesk เป็น:
```
https://yourdomain.com/Project_final/Frontend/index.php
```

Path ของไฟล์ จะเป็น:
- `__DIR__` = `/home/username/public_html/Project_final/Frontend`
- `realpath(__DIR__ . '/../Database/config.php')` = `/home/username/public_html/Project_final/Database/config.php`

---

## 🔍 Testing & Troubleshooting

### ทดสอบการเชื่อมต่อ
```php
// สร้างไฟล์ test.php ในโฟลเดอร์ Frontend
<?php
require_once '../Database/config.php';
echo "✅ Database connected successfully!";
?>
```

### ถ้าเกิด Error
1. **"ไม่พบไฟล์ config.php"** → ตรวจสอบ folder structure
2. **"Connection failed"** → ตรวจสอบ credentials (host, username, password, db_name)
3. **"Access Denied"** → อาจ username/password ผิด หรือ database permission ไม่ถูก

---

## 📝 สรุปการเปลี่ยนแปลง

| ไฟล์ | การเปลี่ยนแปลง | สำหรับ |
|-----|-------------|------|
| `Frontend/index.php` | เพิ่ม PHP code ดึง DB โดยตรง + ใช้ realpath | XAMPP & Plesk |
| `Database/config.php` | ไม่เปลี่ยนแปลงการทำงาน | ทั้งสองระบบ |
| `Database/config_plesk.php` | เวอร์ชันเพิ่มเติม (ถ้าต้องการ) | Plesk only |
| `script/my_bill.js` | ไม่ใช้แล้ว (เปลี่ยนมาใช้ form POST) | - |

---

## ✨ สรุป
- ✅ `index.php` ดึง DB โดยตรง (ไม่ใช้ AJAX)
- ✅ ใช้ `realpath()` + `__DIR__` ทำให้ compatible ทั้ง XAMPP และ Plesk
- ✅ Error handling ชัดเจน
- ✅ สามารถแก้ error ได้ง่าย

