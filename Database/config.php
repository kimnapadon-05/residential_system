<?php
$host = 'localhost';
$db_name = 'test'; // <-- เช็คชื่อฐานข้อมูลตรงนี้ให้ตรงกับ phpMyAdmin
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // ถ้าเชื่อมต่อไม่ได้ ให้หยุดทำงานและแสดง Error ทันที
    die("Connection failed: " . $e->getMessage());
}
?>