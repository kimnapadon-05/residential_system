<?php include '../Layout/layout_header.php'; ?>
<?php include '../Layout/layout_sidebar.php'; ?>

<div class="card shadow">
    <div class="card-header bg-warning text-dark">
        <h4 class="mb-0"><i class="fas fa-bolt"></i> จดบันทึกค่าไฟฟ้า</h4>
    </div>
    <div class="card-body">
        <div class="row mb-4 g-2 align-items-end">
            <div class="col-md-3">
                <label>เดือน</label>
                <select id="select_month" class="form-select">
                    <?php
                    $thai_months = [1=>"มกราคม",2=>"กุมภาพันธ์",3=>"มีนาคม",4=>"เมษายน",5=>"พฤษภาคม",6=>"มิถุนายน",7=>"กรกฎาคม",8=>"สิงหาคม",9=>"กันยายน",10=>"ตุลาคม",11=>"พฤศจิกายน",12=>"ธันวาคม"];
                    $curMonth = date('n');
                    foreach($thai_months as $k=>$v){
                        $sel = ($k == $curMonth) ? 'selected' : '';
                        echo "<option value='$k' $sel>$v</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>ปี (ค.ศ.)</label>
                <select id="select_year" class="form-select">
                    <?php
                    $curYear = date('Y');
                    for($i=$curYear; $i>=$curYear-2; $i--){
                        echo "<option value='$i'>$i</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" onclick="loadSheet()">
                    <i class="fas fa-search"></i> ค้นหา
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="recordingTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 15%;">บ้านพัก</th>
                        <th style="width: 20%;">ผู้พักอาศัย</th>
                        <th style="width: 15%;">มิเตอร์</th>
                        <th style="width: 10%;">ครั้งก่อน</th>
                        <th style="width: 15%;">ครั้งนี้ (จด)</th>
                        <th style="width: 10%;">หน่วยใช้</th>
                        <th style="width: 15%;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../Layout/layout_footer.php'; ?>
<script src="../script/electric_recording_handler.js"></script>