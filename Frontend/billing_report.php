<?php include '../Layout/layout_header.php'; ?>
<?php include '../Layout/layout_sidebar.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
        <h4 class="mb-0"><i class="fas fa-file-invoice-dollar"></i> รายงานค่าใช้จ่ายรายเดือน</h4>
        <button class="btn btn-secondary" onclick="window.print()">
            <i class="fas fa-print"></i> พิมพ์รายงาน
        </button>
    </div>

    <div class="card shadow-sm mb-4 d-print-none"> <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-auto"><label>ประจำเดือน:</label></div>
                <div class="col-md-3">
                    <select id="select_month" class="form-select form-select-sm">
                        <?php
                        $months = [1=>"มกราคม",2=>"กุมภาพันธ์",3=>"มีนาคม",4=>"เมษายน",5=>"พฤษภาคม",6=>"มิถุนายน",7=>"กรกฎาคม",8=>"สิงหาคม",9=>"กันยายน",10=>"ตุลาคม",11=>"พฤศจิกายน",12=>"ธันวาคม"];
                        $curM = date('n');
                        foreach($months as $k=>$v) echo "<option value='$k' ".($k==$curM?'selected':'').">$v</option>";
                        ?>
                    </select>
                </div>
                <div class="col-md-auto"><label>ปี:</label></div>
                <div class="col-md-2">
                    <select id="select_year" class="form-select form-select-sm">
                        <?php
                        $curY = date('Y');
                        for($i=$curY; $i>=$curY-2; $i--) echo "<option value='$i'>$i</option>";
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-sm w-100" onclick="loadReport()">
                        <i class="fas fa-search"></i> ดูรายงาน
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-warning shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small>ยอดรวมค่าไฟ (7บ./หน่วย)</small>
                            <h3 class="mb-0 fw-bold" id="sum_elec">0.00</h3>
                        </div>
                        <i class="fas fa-bolt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small>ยอดรวมค่าน้ำ (15บ./หน่วย)</small>
                            <h3 class="mb-0 fw-bold" id="sum_water">0.00</h3>
                        </div>
                        <i class="fas fa-tint fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small>ยอดรวมทั้งหมดที่ต้องเก็บ</small>
                            <h3 class="mb-0 fw-bold" id="sum_total">0.00</h3>
                        </div>
                        <i class="fas fa-coins fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">ตารางสรุปค่าใช้จ่าย</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="reportTable" width="100%">
                    <thead class="table-light text-center">
                        <tr>
                            <th rowspan="2" class="align-middle">บ้านพัก</th>
                            <th rowspan="2" class="align-middle">ผู้พักอาศัย</th>
                            <th colspan="2" class="bg-warning bg-opacity-10 text-warning-emphasis">ไฟฟ้า</th>
                            <th colspan="2" class="bg-info bg-opacity-10 text-info-emphasis">น้ำประปา</th>
                            <th rowspan="2" class="align-middle bg-success bg-opacity-10">รวมสุทธิ (บาท)</th>
                        </tr>
                        <tr>
                            <th class="text-warning-emphasis"><small>หน่วย</small></th>
                            <th class="text-warning-emphasis"><small>จำนวนเงิน</small></th>
                            <th class="text-info-emphasis"><small>หน่วย</small></th>
                            <th class="text-info-emphasis"><small>จำนวนเงิน</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    /* CSS สำหรับหน้า Print */
    @media print {
        body * { visibility: hidden; }
        .container-fluid, .container-fluid * { visibility: visible; }
        .container-fluid { position: absolute; left: 0; top: 0; width: 100%; }
        .d-print-none { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        .card-header { display: none; }
    }
</style>

<?php include '../Layout/layout_footer.php'; ?>
<script src="../script/billing_report.js"></script>