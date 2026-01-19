<?php include '../Layout/layout_header.php'; ?>
<?php include '../Layout/layout_sidebar.php'; ?>

<div class="card shadow mb-4">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="fas fa-history"></i> ประวัติการเข้าพัก (Residency History)</h4>
        <button class="btn btn-light text-info fw-bold" onclick="openMoveInModal()">
            <i class="fas fa-plus-circle"></i> แจ้งย้ายเข้า
        </button>
    </div>
    <div class="card-body">
        
        <div class="row mb-3">
            <div class="col-md-4 ms-auto">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control border-start-0" placeholder="ค้นหาชื่อผู้พัก หรือ บ้านเลขที่...">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="historyTable" width="100%" cellspacing="0">
                <thead class="table-light">
                    <tr>
                        <th>ผู้พักอาศัย</th>
                        <th>บ้านเลขที่</th>
                        <th>มิเตอร์ไฟ / มิเตอร์น้ำ</th>
                        <th>วันที่เข้าพัก</th>
                        <th>สถานะ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <span class="text-muted" id="pageInfo">กำลังโหลด...</span>
            <nav>
                <ul class="pagination justify-content-end mb-0" id="paginationControls"></ul>
            </nav>
        </div>

    </div>
</div>

<div class="modal fade" id="moveInModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-suitcase"></i> บันทึกการย้ายเข้า</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="moveInForm">
                    <input type="hidden" name="action" value="move_in">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">เลือกบ้านพัก <span class="text-danger">*</span></label>
                            <select class="form-select" name="house_id" id="house_select" required>
                                <option value="">-- กำลังโหลด --</option>
                            </select>
                            <small class="text-muted">ระบบจะดึงเลขมิเตอร์ล่าสุดมาให้อัตโนมัติ</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">เลือกผู้พักอาศัย <span class="text-danger">*</span></label>
                            <select class="form-select" name="person_id" id="person_select" required>
                                <option value="">-- กำลังโหลด --</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <h6 class="text-primary">ข้อมูลมิเตอร์เริ่มต้น</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">เลขมิเตอร์ไฟเริ่มต้น</label>
                            <input type="number" class="form-control" name="starting_electric_reading" id="start_elec" value="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">เลขมิเตอร์น้ำเริ่มต้น</label>
                            <input type="number" class="form-control" name="starting_water_reading" id="start_water" value="0" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ระบุมิเตอร์ไฟ (ถ้ามี)</label>
                            <select class="form-select" name="electric_meter_id" id="elec_select"><option value="">-- เลือกมิเตอร์ --</option></select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ระบุมิเตอร์น้ำ (ถ้ามี)</label>
                            <select class="form-select" name="water_meter_id" id="water_select"><option value="">-- เลือกมิเตอร์ --</option></select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">วันที่ย้ายเข้า</label>
                        <input type="date" class="form-control" name="move_in_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" onclick="saveMoveIn()">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="moveOutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-sign-out-alt"></i> ยืนยันการย้ายออก</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="moveOutForm">
                    <input type="hidden" name="action" value="move_out">
                    <input type="hidden" name="history_id" id="out_history_id">
                    
                    <input type="hidden" id="chk_start_elec">
                    <input type="hidden" id="chk_start_water">

                    <div class="mb-3">
                        <label class="form-label">วันที่ย้ายออก</label>
                        <input type="date" class="form-control" name="move_out_date" id="move_out_date" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">เลขมิเตอร์ไฟฟ้า (ล่าสุด)</label>
                        <input type="number" class="form-control" name="final_electric_reading" id="final_elec" required>
                        <small class="text-muted" id="hint_elec"></small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">เลขมิเตอร์น้ำประปา (ล่าสุด)</label>
                        <input type="number" class="form-control" name="final_water_reading" id="final_water" required>
                        <small class="text-muted" id="hint_water"></small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger" onclick="saveMoveOut()">ยืนยันย้ายออก</button>
            </div>
        </div>
    </div>
</div>

<?php include '../Layout/layout_footer.php'; ?>
<script src="../script/residency_handler.js"></script>