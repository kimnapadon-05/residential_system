<?php include 'backend/auth_guard.php'; ?>
<?php include 'Layout/layout_header.php'; ?>
<?php include 'Layout/layout_sidebar.php'; ?>

<div class="card shadow">
    <div class="card-header bg-secondary text-white">
        <h4 class="mb-0"><i class="fas fa-tags"></i> กำหนดอัตราค่าไฟฟ้า/น้ำประปา (แยกตามโซน)</h4>
    </div>
    <div class="card-body">
        
        <div class="card mb-4 bg-light border-0">
            <div class="card-body">
                <h6 class="fw-bold text-primary mb-3">ตั้งค่าราคาประจำเดือน</h6>
                <form id="rateForm" class="row g-3 align-items-end">
                    
                    <div class="col-md-3">
                        <label class="fw-bold">โซนที่พัก (Location)</label>
                        <select name="location_id" id="location_select" class="form-select" required>
                            <option value="">-- กำลังโหลด --</option>
                        </select>
                    </div>

                    <div class="col-md-2">
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
                        <label>ค่าไฟ (บาท)</label>
                        <input type="number" step="0.01" class="form-control" name="elec_rate" placeholder="0.00" required>
                    </div>
                    
                    <div class="col-md-2">
                        <label>ค่าน้ำ (บาท)</label>
                        <input type="number" step="0.01" class="form-control" name="water_rate" placeholder="0.00" required>
                    </div>
                    
                    <div class="col-md-1"> <button type="button" class="btn btn-primary w-100" onclick="saveRate()">
                            <i class="fas fa-save"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <h6 class="fw-bold">ประวัติอัตราค่าบริการ</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="rateTable">
                <thead class="table-dark">
                    <tr>
                        <th width="20%">โซนที่พัก</th> <th width="20%">เดือน/ปี</th>
                        <th width="15%" class="text-end">ค่าไฟ (บาท)</th>
                        <th width="15%" class="text-end">ค่าน้ำ (บาท)</th>
                        <th width="20%" class="text-center">อัปเดตล่าสุด</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../Layout/layout_footer.php'; ?>

<script>
const thMonthNames = ["", "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];

$(document).ready(function(){ 
    loadLocations(); // โหลดรายชื่อโซนก่อน
    loadRates(); 
});

// ฟังก์ชันโหลดรายชื่อโซนใส่ Dropdown
function loadLocations() {
    $.post('../backend/rate_setting_handler.php', {action: 'get_locations'}, function(data){
        let opts = '<option value="">-- เลือกโซน --</option>';
        data.forEach(item => {
            opts += `<option value="${item.location_id}">${item.location_name}</option>`;
        });
        $('#location_select').html(opts);
    }, 'json');
}

function loadRates() {
    $.post('../backend/rate_setting_handler.php', {action:'read'}, function(data){
        let rows = '';
        if(data.length > 0){
            data.forEach(r => {
                let mName = thMonthNames[r.bill_month] || r.bill_month;
                
                rows += `<tr>
                    <td><span class="badge bg-info text-dark">${r.location_name}</span></td>
                    <td>${mName} ${r.bill_year}</td>
                    <td class="text-end text-danger fw-bold">${r.elec_rate}</td>
                    <td class="text-end text-primary fw-bold">${r.water_rate}</td>
                    <td class="text-center"><small>${r.updated_at || '-'}</small></td>
                </tr>`;
            });
        } else {
            rows = '<tr><td colspan="5" class="text-center text-muted">ยังไม่มีการกำหนดราคา</td></tr>';
        }
        $('#rateTable tbody').html(rows);
    }, 'json');
}

function saveRate() {
    // เช็คว่าเลือกโซนหรือยัง
    if(!$('#location_select').val()) {
        Swal.fire('แจ้งเตือน', 'กรุณาเลือกโซนที่พัก', 'warning');
        return;
    }

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