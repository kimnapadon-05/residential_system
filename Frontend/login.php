<?php include '../backend/security_helper.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบผู้ดูแล - ระบบบ้านพักครู</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { 
            background-color: #eef2f7; 
            font-family: 'Kanit', sans-serif; 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin: 0; /* เพิ่มเพื่อให้แน่ใจว่าไม่มี margin */
        }
        .login-card { width: 100%; max-width: 400px; border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { background: #2c3e50; color: white; padding: 30px 20px; text-align: center; border: none; }
        .btn-login { background: #f1c40f; color: #2c3e50; font-weight: bold; transition: 0.3s; }
        .btn-login:hover { background: #d4ac0d; }
    </style>
</head>
<body>

<div class="card login-card">
    <div class="card-header">
        <div class="mb-2"><i class="fas fa-school fa-3x"></i></div>
        <h5 class="mb-0">เข้าสู่ระบบหลังบ้าน</h5>
    </div>
    <div class="card-body p-4">
        <form id="loginForm">
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="mb-3">
                <label class="form-label text-muted">ชื่อผู้ใช้งาน</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" name="username" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label text-muted">รหัสผ่าน</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" name="password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-login w-100 py-2">เข้าสู่ระบบ</button>
        </form>
        <div class="text-center mt-3">
            <a href="index.php" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left"></i> กลับไปหน้าลูกบ้าน</a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('#loginForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: '../backend/auth_handler.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'ยินดีต้อนรับ',
                        text: 'กำลังเข้าสู่ระบบ...',
                        timer: 1500,
                        showConfirmButton: false,
                        heightAuto: false // ป้องกันหน้าจอขยับ
                    }).then(() => {
                        window.location.href = 'dashboard.php';
                    });
                } else {
                    // เปลี่ยนรูปแบบการเรียกใช้เพื่อให้ใส่ heightAuto ได้
                    Swal.fire({
                        icon: 'error',
                        title: 'แจ้งเตือน',
                        text: res.message,
                        heightAuto: false // ป้องกันหน้าจอขยับ
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'เกิดข้อผิดพลาดในการเชื่อมต่อ',
                    heightAuto: false // ป้องกันหน้าจอขยับ
                });
            }
        });
    });
});
</script>
</body>
</html>