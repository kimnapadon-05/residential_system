<?php include '../Layout/layout_header.php'; ?>
<?php include '../Layout/layout_sidebar.php'; ?>

<div class="card shadow mb-4">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">ข้อมูลผู้อยู่อาศัย</h4>
        <button class="btn btn-light text-success" onclick="openPersonModal()">
            <i class="fas fa-user-plus"></i> เพิ่มบุคลากร
        </button>
    </div>
    <div class="card-body">
        
        <div class="row mb-3">
            <div class="col-md-4 ms-auto">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                    <input type="text" id="searchInput" class="form-control" placeholder="ค้นหาชื่อ, นามสกุล หรือ ตำแหน่ง...">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="personTable">
                <thead class="table-light">
                    <tr>
                        <th>ชื่อ-นามสกุล</th>
                        <th>ตำแหน่ง</th>
                        <th>สถานะ</th>
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

<div class="modal fade" id="personModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">เพิ่มข้อมูล</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="personForm">
                    <input type="hidden" name="action" id="form_action">
                    <input type="hidden" name="person_id" id="person_id">
                    
                    <div class="mb-3">
                        <label>ชื่อจริง</label>
                        <input type="text" class="form-control" name="person_fname" id="person_fname" required>
                    </div>
                    <div class="mb-3">
                        <label>นามสกุล</label>
                        <input type="text" class="form-control" name="person_lname" id="person_lname" required>
                    </div>
                    <div class="mb-3">
                        <label>ตำแหน่ง</label>
                        <select class="form-select" name="position_id" id="position_id" required></select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-success" onclick="savePerson()">บันทึก</button>
            </div>
        </div>
    </div>
</div>

<?php include '../Layout/layout_footer.php'; ?>
<script src="../script/person_handler.js"></script>