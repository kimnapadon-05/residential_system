// ✅ house_handler.js - Handle CRUD operations for house management
// Backend: ../backend/house_handler.php

let currentPage = 1;
const itemsPerPage = 10;

$(document).ready(function() {
    loadTable(currentPage);
    loadLocations();

    // ✅ Real-time search as user types
    $('#searchInput').on('input', function() {
        let query = $(this).val();
        currentPage = 1; // Reset to first page
        loadTable(currentPage, query);
    });
});

function loadTable(page, search = '') {
    currentPage = page;
    if(search === '') search = $('#searchInput').val();

    $.ajax({
        url: '../backend/house_handler.php',
        method: 'POST',
        data: { action: 'read', page: page, limit: itemsPerPage, search: search },
        dataType: 'json',
        success: function(res) {
            if (!res || res.status === 'error') {
                console.error('Error loading houses:', res ? res.message : 'Unknown error');
                $('#houseTable tbody').html('<tr><td colspan="4" class="text-center text-danger">ไม่สามารถโหลดข้อมูล</td></tr>');
                return;
            }

            let rows = '';
            if (res.data.length > 0) {
                res.data.forEach(row => {
                    rows += `<tr>
                        <td>${row.house_id}</td>
                        <td>${row.house_name}</td>
                        <td>${row.location_name || '-'}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="editHouse(${row.house_id}, '${row.house_name}', '${row.location_id}')"><i class="fas fa-edit"></i> แก้ไข</button>
                            <button class="btn btn-danger btn-sm" onclick="deleteHouse(${row.house_id})"><i class="fas fa-trash"></i> ลบ</button>
                        </td>
                    </tr>`;
                });
            } else {
                rows = '<tr><td colspan="4" class="text-center text-muted">ไม่พบข้อมูล</td></tr>';
            }
            $('#houseTable tbody').html(rows);
            
            if(res.pagination) {
                renderPagination(res.pagination);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            $('#houseTable tbody').html('<tr><td colspan="4" class="text-center text-danger">เกิดข้อผิดพลาดในการเชื่อมต่อ</td></tr>');
        }
    });
}

function renderPagination(paging) {
    let html = '';
    let total = paging.total_pages;
    let current = parseInt(paging.current_page);
    let totalRows = paging.total_rows;

    let start = totalRows === 0 ? 0 : ((current - 1) * itemsPerPage) + 1;
    let end = Math.min(current * itemsPerPage, totalRows);
    $('#pageInfo').text(`แสดง ${start} ถึง ${end} จาก ${totalRows} รายการ`);

    html += `<li class="page-item ${current === 1 ? 'disabled' : ''}"><button class="page-link" onclick="loadTable(${current - 1})">ก่อนหน้า</button></li>`;
    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || (i >= current - 1 && i <= current + 1)) {
            let active = (i === current) ? 'active' : '';
            html += `<li class="page-item ${active}"><button class="page-link" onclick="loadTable(${i})">${i}</button></li>`;
        } else if (i === current - 2 || i === current + 2) {
             html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    html += `<li class="page-item ${current === total || total === 0 ? 'disabled' : ''}"><button class="page-link" onclick="loadTable(${current + 1})">ถัดไป</button></li>`;
    $('#paginationControls').html(html);
}

function loadLocations() {
    $.post('../backend/house_handler.php', {action:'get_locations'}, function(data){
        let opts = '<option value="">-- เลือกโซน --</option>';
        data.forEach(item => {
            opts += `<option value="${item.location_id}">${item.location_name}</option>`;
        });
        $('#location_id').html(opts);
    }, 'json');
}

function saveHouse() {
    if(!$('#house_name').val() || !$('#location_id').val()) {
        Swal.fire('แจ้งเตือน', 'กรุณากรอกข้อมูลให้ครบ', 'warning');
        return;
    }

    $.post('../backend/house_handler.php', $('#houseForm').serialize(), function(res){
        if(res.status === 'success'){
            $('#houseModal').modal('hide');
            Swal.fire('สำเร็จ', res.message, 'success');
            loadTable(currentPage);
        } else {
            Swal.fire('ผิดพลาด', res.message, 'error');
        }
    }, 'json');
}

function editHouse(id, name, location_id) {
    $('#house_id').val(id);
    $('#house_name').val(name);
    $('#location_id').val(location_id);
    $('#form_action').val('update');
    $('#modalTitle').text('แก้ไขข้อมูลบ้านพัก');
    $('#houseModal').modal('show');
}

function resetForm() {
    $('#houseForm')[0].reset();
    $('#form_action').val('create');
    $('#house_id').val('');
    $('#modalTitle').text('เพิ่มบ้านพัก');
}

function deleteHouse(id) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: "คุณจะไม่สามารถกู้คืนข้อมูลนี้ได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'ใช่, ลบเลย!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('../backend/house_handler.php', {action:'delete', house_id:id}, function(res){
                if (res.status === 'success') {
                    Swal.fire('ลบสำเร็จ!', res.message, 'success');
                    loadTable(currentPage);
                } else {
                    Swal.fire('ผิดพลาด', res.message, 'error');
                }
            }, 'json');
        }
    });
}