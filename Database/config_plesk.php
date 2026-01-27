<?php
/**
 * ========================================
 * Database Configuration - Plesk Deployment
 * ========================================
 * 
 * ไฟล์นี้เป็น alternative สำหรับ Plesk deployment
 * สามารถเลือกใช้งาน config.php หรือ config_plesk.php ก็ได้
 * (พวกมันเหมือนกัน)
 * 
 * วิธีใช้ (เลือก 1 วิธี):
 * 
 * วิธี 1: ใช้ config.php เลย (recommended)
 * - ไม่ต้องทำอะไร config.php ทำงานได้ทั้ง XAMPP และ Plesk
 * 
 * วิธี 2: ใช้ config_plesk.php แทน
 * - เปลี่ยนชื่อ config_plesk.php เป็น config.php
 * 
 * วิธี 3: ให้ frontend include config_plesk.php โดยตรง
 * - แก้ไข Frontend/index.php ให้เรียก config_plesk.php แทน
 */

// ============ DATABASE CREDENTIALS ============
// ⚠️  IMPORTANT: แก้ไขค่าให้ตรงกับ Plesk database ของคุณ
$host = 'localhost';        // Plesk default: localhost
$db_name = 'test';          // ชื่อฐานข้อมูล - ต้องแก้ไข!
$username = 'root';         // Plesk username - ต้องแก้ไข!
$password = '';             // Plesk password - ต้องแก้ไข!
$charset = 'utf8mb4';       // ใช้ utf8mb4 เพื่อ support emoji + languages

// ============ PDO CONNECTION ============
try {
    $dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";
    $conn = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    // ตั้ง charset และ timezone
    $conn->exec("SET NAMES $charset");
    $conn->exec("SET time_zone = '+07:00'");
    
} catch(PDOException $e) {
    // ถ้าเชื่อมต่อไม่ได้
    die("❌ Database Connection Error: " . htmlspecialchars($e->getMessage()) . 
        "\n\nDebug Info:\nHost: $host\nDatabase: $db_name\nUser: $username");
}

/**
 * ========================================
 * Path Helper Functions
 * ========================================
 */

/**
 * ดึง absolute path ของ project root folder
 * ตัวอย่าง: /home/user/public_html/Project_final
 */
function get_project_root() {
    return realpath(__DIR__ . '/..');
}

/**
 * ดึง path ของ Frontend folder
 */
function get_frontend_path() {
    return realpath(__DIR__ . '/../Frontend');
}

/**
 * ดึง path ของ Backend folder
 */
function get_backend_path() {
    return realpath(__DIR__ . '/../backend');
}

/**
 * ดึง path ของ Layout folder
 */
function get_layout_path() {
    return realpath(__DIR__ . '/../Layout');
}

?>
