<?php
/**
 * ========================================
 * Database Configuration - Plesk Compatible
 * ========================================
 * 
 * ไฟล์นี้ใช้สำหรับ Plesk deployment
 * ใช้ __DIR__ และ realpath() เพื่อ support ต่าง ๆ environment
 * 
 * วิธีใช้ (สำหรับ Plesk):
 * ให้เปลี่ยนชื่อ config_plesk.php เป็น config.php
 * หรือแก้ไขไฟล์ index.php ให้ require_once config_plesk.php แทน config.php
 */

// ============ DATABASE CREDENTIALS ============
// ⚠️  ต้องแก้ไขตามค่าจริงของ Plesk
$host = 'localhost';        // หรือ hostname ของ Plesk DB server
$db_name = 'test';          // ชื่อฐานข้อมูล
$username = 'root';         // username ของ database
$password = '';             // password ของ database
$charset = 'utf8mb4';       // charset

// ============ PDO CONNECTION ============
try {
    $dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";
    $conn = new PDO($dsn, $username, $password);
    
    // ตั้งค่า error mode
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ตั้ง charset
    $conn->exec("SET NAMES $charset");
    
} catch(PDOException $e) {
    // ถ้าเชื่อมต่อไม่ได้
    die("❌ Database Connection Error: " . htmlspecialchars($e->getMessage()));
}

/**
 * ========================================
 * Path Helper Functions (สำหรับ Plesk)
 * ========================================
 */

/**
 * ดึง path ของ root folder
 */
function get_project_root() {
    // ใช้ __DIR__ เพื่อหา root folder
    return realpath(__DIR__ . '/..');
}

/**
 * ดึง path แบบ web-relative (สำหรับ Plesk deployment)
 * ตัวอย่าง: /public_html/Project_final/Frontend/
 */
function get_web_root() {
    // วิธีที่ 1: ใช้ $_SERVER['DOCUMENT_ROOT'] + relative path
    return $_SERVER['DOCUMENT_ROOT'] . str_replace('\\', '/', dirname(dirname(__DIR__)));
}

?>
