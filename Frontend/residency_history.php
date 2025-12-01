<?php include '../Layout/layout_header.php'; ?>
<?php include '../Layout/layout_sidebar.php'; ?>

<div class="card shadow">
    <div class="card-header bg-info text-white d-flex justify-content-between">
        <h4 class="mb-0">ประวัติการเข้าพัก (Residency)</h4>
        <button class="btn btn-light text-info" onclick="openMoveInModal()"><i class="fas fa-suitcase"></i> แจ้งย้ายเข้า</button>
    </div>
    <div class="card-body">
        <table class="table table-striped" id="historyTable">
            <thead>
                <tr>
                    <th>ผู้พักอาศัย</th>
                    <th>บ้านเลขที่</th>
                    <th>มิเตอร์ไฟ</th>
                    <th>มิเตอร์น้ำ</th>
                    <th>วันที่เข้า</th>
                    <th>สถานะ</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="moveInModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">บันทึกการย้ายเข้า</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="moveInForm">
                    <input type="hidden" name="action" value="move_in">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>ผู้พักอาศัย</label>
                            <select class="form-select" name="person_id" id="person_select" required></select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>บ้านพัก</label>
                            <select class="form-select" name="house_id" id="house_select" required></select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>มิเตอร์ไฟฟ้า</label>
                            <select class="form-select" name="electric_meter_id" id="elec_select"></select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>มิเตอร์น้ำ</label>
                            <select class="form-select" name="water_meter_id" id="water_select"></select>
                        </div>
                    </div>
                    <div class="row">
                         <div class="col-md-4 mb-3">
                            <label>วันที่ย้ายเข้า</label>
                            <input type="date" class="form-control" name="move_in_date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>เลขมิเตอร์ไฟเริ่มต้น</label>
                            <input type="number" class="form-control" name="starting_electric_reading" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>เลขมิเตอร์น้ำเริ่มต้น</label>
                            <input type="number" class="form-control" name="starting_water_reading" value="0">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="saveMoveIn()">บันทึกย้ายเข้า</button>
            </div>
        </div>
    </div>
</div>
<?php include '../Layout/layout_footer.php'; ?>
<script src="../script/residency_handler.js"></script>