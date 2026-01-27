<?php
require_once realpath(__DIR__ . '/security_helper.php');

// ถ้าไม่มี Session หรือไม่ได้ Login ให้ดีดกลับไปหน้า Login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../Frontend/login.php");
    exit;
}

// (Option) ระบบ Auto Logout หากไม่มีการใช้งานเกิน 30 นาที
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}
$_SESSION['last_activity'] = time(); // อัปเดตเวลาล่าสุด
?>