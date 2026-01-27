<?php
/**
 * ========================================
 * Database Configuration File
 * ========================================
 * 
 * ใช้ได้ทั้ง XAMPP และ Plesk
 * 
 * ⚠️  IMPORTANT: เปลี่ยน credentials ให้ตรงกับ DB ของคุณ
 *     - $host: database server (localhost, 127.0.0.1, หรือ hostname)
 *     - $db_name: ชื่อฐานข้อมูล
 *     - $username: MySQL username
 *     - $password: MySQL password
 */

// ============ DATABASE CREDENTIALS ============
$host = 'localhost';
$db_name = 'test';          // <-- เช็คชื่อฐานข้อมูลให้ตรงกับ phpMyAdmin
$username = 'root';         // XAMPP default: root
$password = '';             // XAMPP default: empty string
$charset = 'utf8mb4';       // ใช้ utf8mb4 เพื่อ support emoji + multi-language

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