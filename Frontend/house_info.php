<?php include '../Layout/layout_header.php'; ?>
<?php include '../Layout/layout_sidebar.php'; ?>

<div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">จัดการข้อมูลบ้านพัก (House Info)</h4>
        <button class="btn btn-light text-primary" data-bs-toggle="modal" data-bs-target="#houseModal" onclick="resetForm()">
            <i class="fas fa-plus"></i> เพิ่มบ้านพัก
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="houseTable">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>ชื่อบ้านพัก/เลขที่</th>
                        <th>โซนที่พัก (Location)</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="houseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">เพิ่มบ้านพัก</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="houseForm">
                    <input type="hidden" id="house_id" name="house_id">
                    <input type="hidden" name="action" id="form_action" value="create">
                    
                    <div class="mb-3">
                        <label>ชื่อบ้านพัก / เลขที่บ้าน</label>
                        <input type="text" class="form-control" name="house_name" id="house_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>โซนที่พัก</label>
                        <select class="form-select" name="location_id" id="location_id" required>
                            </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" onclick="saveHouse()">บันทึก</button>
            </div>
        </div>
    </div>
</div>

<?php include '../Layout/layout_footer.php'; ?>
<script src="../script/house_handler.js"></script>