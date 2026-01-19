<?php include '../Layout/layout_header.php'; ?>
<?php include '../Layout/layout_sidebar.php'; ?>

<div class="card shadow">
    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
        <h4 class="mb-0">ข้อมูลมิเตอร์น้ำ/ไฟ</h4>
        <button class="btn btn-light" onclick="openMeterModal()">
            <i class="fas fa-plus"></i> เพิ่มมิเตอร์
        </button>
    </div>
    <div class="card-body">
        
        <div class="row mb-3">
            <div class="col-md-4 ms-auto">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                    <input type="text" id="searchInput" class="form-control" placeholder="ค้นหา Serial No. หรือ ยี่ห้อ...">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="meterTable">
                <thead class="table-light">
                    <tr>
                        <th>Serial No.</th>
                        <th>ประเภทการใช้งาน</th>
                        <th>ยี่ห้อ</th>
                        <th>จัดการ</th>
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

<div class="modal fade" id="meterModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">จัดการมิเตอร์</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="meterForm">
                    <input type="hidden" name="action" id="meter_action">
                    <input type="hidden" name="meter_id" id="meter_id">
                    
                    <div class="mb-3">
                        <label>Serial Number</label>
                        <input type="text" class="form-control" name="meter_serial" id="meter_serial" required>
                    </div>

                    <div class="mb-3">
                        <label>ประเภทการใช้งาน</label>
                        <select class="form-select" name="meter_type" id="meter_type" required>
                            <option value="" selected disabled>-- กรุณาเลือกประเภท --</option>
                            <option value="electric">ไฟฟ้า (Electric)</option>
                            <option value="water">น้ำประปา (Water)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>ยี่ห้อ</label>
                        <select class="form-select" name="brand_id" id="brand_id" required></select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button class="btn btn-primary" onclick="saveMeter()">บันทึก</button>
            </div>
        </div>
    </div>
</div>

<?php include '../Layout/layout_footer.php'; ?>
<script src="../script/meter_handler.js"></script>