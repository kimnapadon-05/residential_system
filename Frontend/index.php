<?php include '../Layout/layout_header.php'; ?>
<?php include '../Layout/layout_sidebar.php'; ?>

<h3 class="mt-4 mb-4">Dashboard</h3>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary h-100 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-0">บ้านพักทั้งหมด</h6>
                    <h2 class="mb-0 fw-bold" id="stat_total_houses">...</h2>
                </div>
                <i class="fas fa-home fa-3x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success h-100 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-0">มีผู้พักอาศัย</h6>
                    <h2 class="mb-0 fw-bold" id="stat_occupied">...</h2>
                </div>
                <i class="fas fa-user-check fa-3x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger h-100 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-0">บ้านว่าง</h6>
                    <h2 class="mb-0 fw-bold" id="stat_vacant">...</h2>
                </div>
                <i class="fas fa-door-open fa-3x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info h-100 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-0">ประชากรทั้งหมด</h6>
                    <h2 class="mb-0 fw-bold" id="stat_people">...</h2>
                </div>
                <i class="fas fa-users fa-3x opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-area"></i> สถิติการใช้ไฟฟ้า/น้ำ (6 เดือนล่าสุด)</h6>
            </div>
            <div class="card-body">
                <canvas id="usageChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-history"></i> การย้ายเข้าล่าสุด</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush" id="recent_list">
                    <li class="list-group-item text-center text-muted">กำลังโหลด...</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../Layout/layout_footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../script/dashboard.js"></script>