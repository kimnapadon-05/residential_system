<?php
// ป้องกัน Session Hijacking และ Fixation
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1); // JavaScript เข้าถึง Cookie ไม่ได้ (กัน XSS)
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // **แก้เป็น 1 ถ้าใช้ HTTPS (Production)**
    session_start();
}

// สร้าง CSRF Token (ป้องกันการยิง Form จากเว็บอื่น)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>