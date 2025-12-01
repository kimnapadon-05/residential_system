<?php
$host = 'localhost';
$db_name = 'residential_system';
$username = 'root'; // หรือตามที่คุณตั้ง
$password = '';     // หรือตามที่คุณตั้ง

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>