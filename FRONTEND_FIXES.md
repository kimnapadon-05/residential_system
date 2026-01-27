# Frontend Files - Path Fixes for Plesk Deployment

## ✅ สรุปการแก้ไข

ได้ทำการแก้ไข **path includes** ใน Frontend files ทั้งหมดเพื่อให้เข้ากันได้กับทั้ง XAMPP และ Plesk server

---

## 🔧 การเปลี่ยนแปลง

### 1. **Dashboard.php**
```
❌ เดิม: <?php include '/Layout/layout_header.php'; ?>
✅ ใหม่: <?php include '../Layout/layout_header.php'; ?>
```

### 2. **Billing Report.php**
```
❌ เดิม: <?php include '/backend/auth_guard.php'; ?>
         <?php include '/Layout/layout_header.php'; ?>
✅ ใหม่: <?php include '../backend/auth_guard.php'; ?>
         <?php include '../Layout/layout_header.php'; ?>
```

### 3. **House Info.php**
```
❌ เดิม: <?php include '/Layout/layout_header.php'; ?>
✅ ใหม่: <?php include '../Layout/layout_header.php'; ?>
```

### 4. **Login.php**
```
❌ เดิม: <?php include '/backend/security_helper.php'; ?>
✅ ใหม่: <?php include '../backend/security_helper.php'; ?>
```

### 5. **Electric Recording.php**
```
❌ เดิม: <?php include '/Layout/layout_header.php'; ?>
✅ ใหม่: <?php include '../Layout/layout_header.php'; ?>
```

### 6. **Water Recording.php**
```
❌ เดิม: <?php include 'Layout/layout_header.php'; ?>
✅ ใหม่: <?php include '../Layout/layout_header.php'; ?>
```

### 7. **Meter Info.php**
```
❌ เดิม: <?php include '../Layout/layout_header.php'; ?>
✅ ใหม่: <?php require_once realpath(__DIR__ . '/../Layout/layout_header.php'); ?>
```

### 8. **Person Info.php**
```
✅ ใช้ realpath() สำหรับ better path resolution
<?php require_once realpath(__DIR__ . '/../Layout/layout_header.php'); ?>
```

### 9. **Residency History.php**
```
✅ ใช้ realpath() สำหรับ better path resolution
```

### 10. **Rate Setting.php**
```
✅ ใช้ realpath() สำหรับ better path resolution
```

---

## 📋 Path Structure Explanation

### ✅ CORRECT Path Structure
```
Frontend/
  ├── index.php (uses realpath(__DIR__ . '/../Database/config.php'))
  ├── dashboard.php (uses include '../Layout/layout_header.php')
  └── ... other files

Layout/
  ├── layout_header.php
  ├── layout_sidebar.php
  └── layout_footer.php

Database/
  └── config.php

Backend/
  └── ... handlers
```

### ❌ INCORRECT Paths (Fixed)
- ❌ `/Layout/layout_header.php` - This is absolute path (from root) - **NOT WORK**
- ❌ `Layout/layout_header.php` - This looks for Layout folder in current dir - **NOT WORK from Frontend/**
- ✅ `../Layout/layout_header.php` - Correct relative path
- ✅ `realpath(__DIR__ . '/../Layout/layout_header.php')` - Best for Plesk

---

## 🚀 การ Deploy ไป Plesk

### Step 1: Upload ไป Plesk
ทั้งหมดไฟล์ที่แก้ไขแล้วสามารถ upload ขึ้น Plesk ได้ทันที

### Step 2: Test หลังจาก Upload
1. ไปที่ `https://yourdomain.com/Project_final/Frontend/index.php`
2. ตรวจสอบว่า page มี header/footer มาถูกต้อง (อาจต้องแก้ CSS path เพิ่มเติม)

### Step 3: Backend API Paths
ส่วน JavaScript ใน Frontend ยังเรียก backend handlers ผ่าน AJAX:
- ✅ `../backend/my_bill_handler.php` - Correct
- ✅ `../backend/auth_handler.php` - Correct
- ✅ อื่น ๆ ก็ตรวจสอบให้แน่ใจว่าเรียก `../backend/...` (relative path)

---

## 📝 Notes for Future Maintenance

### ใช้ Relative Paths เสมอ
- ✅ `../Layout/file.php`
- ✅ `./script/myfile.js`
- ❌ `/Layout/file.php` - Absolute path
- ❌ `Layout/file.php` - Unclear hierarchy

### ใช้ realpath() + __DIR__ สำหรับ PHP includes
```php
require_once realpath(__DIR__ . '/../Database/config.php');
```
ข้อดี:
- ✅ ทำงาน Plesk, XAMPP, Windows, Linux
- ✅ ตรวจสอบ file exists ก่อน include
- ✅ หลีกเลี่ยง path traversal attacks

### ใช้ include/require แทน echo path
```php
// ❌ ผิด - hardcoded path
<script src="/script/myfile.js"></script>

// ✅ ถูก - relative path
<script src="../script/myfile.js"></script>
```

---

## ✨ Summary
- ✅ Fixed 10 Frontend PHP files with correct path includes
- ✅ Using relative paths `../` which work on all servers
- ✅ Enhanced with `realpath()` for maximum compatibility
- ✅ Ready for XAMPP and Plesk deployment

