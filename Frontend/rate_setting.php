<?php include '../backend/auth_guard.php'; ?> <?php include '../Layout/layout_header.php'; ?>
<?php include '../Layout/layout_sidebar.php'; ?>

<div class="card shadow">
    <div class="card-header bg-secondary text-white">
        <h4 class="mb-0"><i class="fas fa-tags"></i> กำหนดอัตราค่าไฟฟ้า/น้ำประปา</h4>
    </div>
    <div class="card-body">
        
        <div class="card mb-4 bg-light border-0">
            <div class="card-body">
                <h6 class="fw-bold text-primary mb-3">ตั้งค่าราคาประจำเดือน</h6>
                <form id="rateForm" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label>เดือน</label>
                        <select name="month" class="form-select" required>
                            <?php
                            $thMonth = [1=>"มกราคม",2=>"กุมภาพันธ์",3=>"มีนาคม",4=>"เมษายน",5=>"พฤษภาคม",6=>"มิถุนายน",7=>"กรกฎาคม",8=>"สิงหาคม",9=>"กันยายน",10=>"ตุลาคม",11=>"พฤศจิกายน",12=>"ธันวาคม"];
                            $curM = date('n');
                            foreach($thMonth as $k=>$v) echo "<option value='$k' ".($k==$curM?'selected':'').">$v</option>";
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>ปี (ค.ศ.)</label>
                        <select name="year" class="form-select" required>
                            <?php 
                            $curY = date('Y');
                            for($i=$curY+1; $i>=$curY-2; $i--) {
                                $sel = ($i == $curY) ? 'selected' : '';
                                echo "<option value='$i' $sel>$i</option>"; 
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label>ค่าไฟ (บาท/หน่วย)</label>
                        <input type="number" step="0.01" class="form-control" name="elec_rate" placeholder="0.00" required>
                    </div>
                    
                    <div class="col-md-2">
                        <label>ค่าน้ำ (บาท/หน่วย)</label>
                        <input type="number" step="0.01" class="form-control" name="water_rate" placeholder="0.00" required>
                    </div>
                    
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary w-100" onclick="saveRate()">
                            <i class="fas fa-save"></i> บันทึกราคา
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <h6 class="fw-bold">ประวัติอัตราค่าบริการ</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="rateTable">
                <thead class="table-dark">
                    <tr>
                        <th>เดือน/ปี</th>
                        <th>ค่าไฟ (บาท)</th>
                        <th>ค่าน้ำ (บาท)</th>
                        <th>อัปเดตล่าสุด</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../Layout/layout_footer.php'; ?>

<script>
// ตัวแปรชื่อเดือนสำหรับแปลงตัวเลขเป็นชื่อไทยในตาราง
const thMonthNames = ["", "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];

$(document).ready(function(){ loadRates(); });

function loadRates() {
    $.post('../backend/rate_setting_handler.php', {action:'read'}, function(data){
        let rows = '';
        if(data.length > 0){
            data.forEach(r => {
                // แปลงเลขเดือนเป็นชื่อเดือนไทย
                let mName = thMonthNames[r.bill_month] || r.bill_month;
                
                rows += `<tr>
                    <td>${mName} ${r.bill_year}</td>
                    <td class="text-end">${r.elec_rate}</td>
                    <td class="text-end">${r.water_rate}</td>
                    <td class="text-center"><small>${r.updated_at}</small></td>
                </tr>`;
            });
        } else {
            rows = '<tr><td colspan="4" class="text-center text-muted">ยังไม่มีประวัติราคา</td></tr>';
        }
        $('#rateTable tbody').html(rows);
    }, 'json');
}

function saveRate() {
    $.post('../backend/rate_setting_handler.php', $('#rateForm').serialize() + '&action=save', function(res){
        if(res.status === 'success'){ 
            Swal.fire('สำเร็จ', res.message, 'success'); 
            loadRates(); 
        } else { 
            Swal.fire('Error', res.message, 'error'); 
        }
    }, 'json');
}
</script>