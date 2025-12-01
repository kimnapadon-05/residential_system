<?php
require_once '../Database/config.php';
require_once 'security_helper.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'login') {
    // 1. ตรวจสอบ CSRF Token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Security Token (CSRF)']);
        exit;
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    try {
        // 2. ดึงข้อมูล User
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = :user");
        $stmt->execute([':user' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // ตอบกลับแบบคลุมเครือเพื่อป้องกัน User Enumeration
            echo json_encode(['status' => 'error', 'message' => 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง']);
            exit;
        }

        // 3. ตรวจสอบสถานะการล็อค (Brute Force Protection)
        if ($user['locked_until'] && new DateTime() < new DateTime($user['locked_until'])) {
            $diff = (new DateTime($user['locked_until']))->diff(new DateTime());
            echo json_encode(['status' => 'error', 'message' => "บัญชีถูกระงับชั่วคราว กรุณารอ " . $diff->i . " นาที"]);
            exit;
        }

        // 4. ตรวจสอบรหัสผ่าน
        if (password_verify($password, $user['password_hash'])) {
            // --- LOGIN SUCCESS ---
            
            // Reset การนับครั้งที่ผิด
            $conn->prepare("UPDATE admin_users SET login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE admin_id = :id")
                 ->execute([':id' => $user['admin_id']]);

            // Regenerate Session ID (ป้องกัน Session Fixation)
            session_regenerate_id(true);
            
            $_SESSION['admin_id'] = $user['admin_id'];
            $_SESSION['admin_name'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();

            echo json_encode(['status' => 'success', 'message' => 'เข้าสู่ระบบสำเร็จ']);

        } else {
            // --- LOGIN FAILED ---
            
            // เพิ่มจำนวนครั้งที่ผิด
            $attempts = $user['login_attempts'] + 1;
            $sql = "UPDATE admin_users SET login_attempts = :att WHERE admin_id = :id";
            $params = [':att' => $attempts, ':id' => $user['admin_id']];

            // ถ้าผิดเกิน 5 ครั้ง ล็อค 15 นาที
            if ($attempts >= 5) {
                $sql = "UPDATE admin_users SET login_attempts = 0, locked_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE admin_id = :id";
                $msg = "คุณใส่รหัสผิดเกินกำหนด บัญชีถูกระงับ 15 นาที";
            } else {
                $remaining = 5 - $attempts;
                $msg = "ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง (เหลือโอกาส $remaining ครั้ง)";
            }
            
            $conn->prepare($sql)->execute($params);
            echo json_encode(['status' => 'error', 'message' => $msg]);
        }

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'System Error']);
    }
    exit;
}

if ($action === 'logout') {
    session_destroy();
    echo json_encode(['status' => 'success']);
    exit;
}
?>