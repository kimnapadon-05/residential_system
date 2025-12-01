<?php 
// หาชื่อไฟล์ปัจจุบัน
$current_page = basename($_SERVER['PHP_SELF']); 
?>

<div id="sidebar-wrapper">
    <div class="sidebar-heading">
        <i class="fas fa-school me-2 text-warning"></i> ระบบบ้านพักครูฯ
    </div>
    <div class="list-group list-group-flush mt-2">
        
        <a href="index.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
            <i class="fas fa-chart-pie me-2 fixed-width-icon"></i> แดชบอร์ด
        </a>
        
        <small class="text-muted ms-4 mt-3 mb-1 text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">ข้อมูลพื้นฐาน</small>
        
        <a href="house_info.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'house_info.php') ? 'active' : ''; ?>">
            <i class="fas fa-home me-2"></i> ข้อมูลบ้านพัก
        </a>
        <a href="person_info.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'person_info.php') ? 'active' : ''; ?>">
            <i class="fas fa-users me-2"></i> ข้อมูลครู/บุคลากร
        </a>
        <a href="meter_info.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'meter_info.php') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt me-2"></i> ข้อมูลมิเตอร์
        </a>
        <a href="residency_history.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'residency_history.php') ? 'active' : ''; ?>">
            <i class="fas fa-history me-2"></i> ประวัติการเข้าพัก
        </a>
        
        <small class="text-muted ms-4 mt-3 mb-1 text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">การจดบันทึก</small>
        
        <a href="electric_recording.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'electric_recording.php') ? 'active' : ''; ?>">
            <i class="fas fa-bolt me-2"></i> จดค่าไฟฟ้า
        </a>
        <a href="water_recording.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'water_recording.php') ? 'active' : ''; ?>">
            <i class="fas fa-tint me-2"></i> จดค่าน้ำประปา
        </a>
        
        <small class="text-muted ms-4 mt-3 mb-1 text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">รายงาน</small>
        
        <a href="billing_report.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'billing_report.php') ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice-dollar me-2"></i> รายงานค่าใช้จ่าย
        </a>

        <small class="text-muted ms-4 mt-3 mb-1 text-uppercase" style="font-size: 0.7rem;">การตั้งค่า</small>
        <a href="rate_setting.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'rate_setting.php') ? 'active' : ''; ?>">
            <i class="fas fa-tags me-2"></i> กำหนดราคาต่อหน่วย
        </a>
    </div>
</div>

<div id="page-content-wrapper">
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <button class="btn btn-light btn-sm border" id="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <span class="ms-3 fw-bold text-secondary d-none d-md-block">
                ระบบบันทึกค่าไฟฟ้าและน้ำประปา
            </span>

            <div class="ms-auto">
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle text-dark d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="text-end me-2 d-none d-md-block">
                            <small class="d-block fw-bold">เจ้าหน้าที่ดูแลระบบ</small>
                            <small class="text-muted" style="font-size: 0.75rem;">Admin</small>
                        </div>
                        <i class="fas fa-user-circle fa-2x text-secondary"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="#" onclick="logout()">
                                <i class="fas fa-sign-out-alt me-2"></i> ออกจากระบบ
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4 flex-grow-1">

    <script>
        function logout() {
            $.ajax({
                url: '../backend/auth_handler.php',
                method: 'POST',
                data: { action: 'logout' },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        window.location.href = 'login.php';
                    }
                }
            });
        }
    </script>