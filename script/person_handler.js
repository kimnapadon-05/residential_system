// ตัวแปร Global สำหรับจัดการหน้า
let currentPage = 1;
const itemsPerPage = 10; // จำนวนรายการต่อหน้า

$(document).ready(function() {
    loadPersonTable(currentPage);
    loadPositions();

    // [Real-time Search] ค้นหาทันทีที่พิมพ์ (ใช้ event 'input')
    $('#searchInput').on('input', function() {
        let query = $(this).val();
        currentPage = 1; // รีเซ็ตกลับไปหน้า 1 เสมอเมื่อเริ่มค้นหาใหม่
        loadPersonTable(currentPage, query);
    });
});

// ฟังก์ชันโหลดตาราง (รับค่า page และ search)
function loadPersonTable(page, search = '') {
    currentPage = page;
    
    // ถ้าไม่ได้ส่ง search มา ให้ลองดึงจาก input (เผื่อกรณีกดปุ่มเปลี่ยนหน้า)
    if(search === '') search = $('#searchInput').val();

    $.ajax({
        url: '../backend/person_handler.php', 
        method: 'POST', 
        data: { 
            action: 'read', 
            page: page, 
            limit: itemsPerPage, 
            search: search 
        }, 
        dataType: 'json',
        success: function(res) {
            // เช็คว่ามี error หรือไม่
            if (res.status === 'error') {
                console.error(res.message);
                return;
            }

            let rows = '';
            // ตรวจสอบข้อมูลที่ส่งกลับมา
            if (res.data && res.data.length > 0) {
                res.data.forEach(item => {
                    rows += `
                        <tr>
                            <td>${item.person_fname} ${item.person_lname}</td>
                            <td><span class="badge bg-info text-dark">${item.position_name}</span></td>
                            <td><span class="badge bg-success">ปกติ</span></td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editPerson(${item.person_id}, '${item.person_fname}', '${item.person_lname}', ${item.position_id})">
                                    <i class="fas fa-edit"></i> แก้ไข
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deletePerson(${item.person_id})">
                                    <i class="fas fa-trash"></i> ลบ
                                </button>
                            </td>
                        </tr>`;
                });
            } else {
                rows = '<tr><td colspan="4" class="text-center text-muted">ไม่พบข้อมูล</td></tr>';
            }
            $('#personTable tbody').html(rows);
            
            // เรียกฟังก์ชันสร้างปุ่ม Pagination
            if(res.pagination) {
                renderPagination(res.pagination);
            }
        }
    });
}

// ฟังก์ชันสร้างปุ่ม Pagination
function renderPagination(paging) {
    let html = '';
    let total = paging.total_pages;
    let current = parseInt(paging.current_page);
    let totalRows = paging.total_rows;

    // แสดงข้อความ "แสดง X ถึง Y จาก Z รายการ"
    let start = totalRows === 0 ? 0 : ((current - 1) * itemsPerPage) + 1;
    let end = Math.min(current * itemsPerPage, totalRows);
    $('#pageInfo').text(`แสดง ${start} ถึง ${end} จาก ${totalRows} รายการ`);

    // ปุ่ม "ก่อนหน้า"
    html += `<li class="page-item ${current === 1 ? 'disabled' : ''}">
                <button class="page-link" onclick="loadPersonTable(${current - 1})">ก่อนหน้า</button>
             </li>`;

    // ปุ่มตัวเลขหน้า
    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || (i >= current - 1 && i <= current + 1)) {
            let active = (i === current) ? 'active' : '';
            html += `<li class="page-item ${active}">
                        <button class="page-link" onclick="loadPersonTable(${i})">${i}</button>
                     </li>`;
        } else if (i === current - 2 || i === current + 2) {
             html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    // ปุ่ม "ถัดไป"
    html += `<li class="page-item ${current === total || total === 0 ? 'disabled' : ''}">
                <button class="page-link" onclick="loadPersonTable(${current + 1})">ถัดไป</button>
             </li>`;

    $('#paginationControls').html(html);
}

// โหลดตำแหน่งใส่ Dropdown
function loadPositions() {
    $.ajax({
        url: '../backend/person_handler.php', method: 'POST', data: { action: 'get_positions' }, dataType: 'json',
        success: function(data) {
            let opts = '<option value="">เลือกตำแหน่ง</option>';
            data.forEach(p => {
                opts += `<option value="${p.position_id}">${p.position_name}</option>`;
            });
            $('#position_id').html(opts);
        }
    });
}

// เปิด Modal เพิ่มข้อมูล
function openPersonModal() {
    $('#personForm')[0].reset();
    $('#form_action').val('create');
    $('#modalTitle').text('เพิ่มบุคลากร');
    $('#personModal').modal('show');
}

// เปิด Modal แก้ไขข้อมูล
function editPerson(id, fname, lname, posId) {
    $('#person_id').val(id);
    $('#person_fname').val(fname);
    $('#person_lname').val(lname);
    $('#position_id').val(posId);
    $('#form_action').val('update');
    $('#modalTitle').text('แก้ไขข้อมูล');
    $('#personModal').modal('show');
}

// บันทึกข้อมูล
function savePerson() {
    $.ajax({
        url: '../backend/person_handler.php', method: 'POST', data: $('#personForm').serialize(), dataType: 'json',
        success: function(res) {
            if(res.status === 'success') {
                Swal.fire('สำเร็จ', res.message, 'success');
                $('#personModal').modal('hide');
                loadPersonTable(currentPage); // โหลดหน้าเดิม
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }
    });
}

// ลบข้อมูล
function deletePerson(id) {
    Swal.fire({
        title: 'ยืนยันลบ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก'
    }).then((r) => {
        if(r.isConfirmed) {
            $.post('../backend/person_handler.php', { action: 'delete', person_id: id }, function(res) {
                if(res.status === 'success') {
                    loadPersonTable(currentPage);
                    Swal.fire('Deleted', 'ลบข้อมูลสำเร็จ', 'success');
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }, 'json');
        }
    });
}