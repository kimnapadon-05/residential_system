// ตัวแปร Global สำหรับจัดการหน้า
let currentPage = 1;
const itemsPerPage = 10; // จำนวนรายการต่อหน้า

$(document).ready(function() {
    loadMeterTable(currentPage);
    loadOptions();

    // [Real-time Search] ค้นหาทันทีที่พิมพ์ (ใช้ event 'input')
    $('#searchInput').on('input', function() {
        let query = $(this).val();
        currentPage = 1; // รีเซ็ตกลับไปหน้า 1 เสมอเมื่อเริ่มค้นหาใหม่
        loadMeterTable(currentPage, query);
    });
});

// โหลดข้อมูลใส่ตาราง (รับค่า page และ search)
function loadMeterTable(page, search = '') {
    currentPage = page;
    
    // กันเหนียว: ถ้าไม่ได้ส่ง search มา ให้ลองดึงจาก input
    if(search === '') search = $('#searchInput').val();

    $.ajax({
        url: '../backend/meter_handler.php', 
        method: 'POST', 
        data: { 
            action: 'read',
            page: page,
            limit: itemsPerPage,
            search: search 
        }, 
        dataType: 'json',
        success: function(res) {
            // เช็คสถานะการตอบกลับ
            if (res.status === 'error') {
                console.error(res.message);
                return;
            }

            let rows = '';
            // เข้าถึงข้อมูลผ่าน res.data
            if (res.data && res.data.length > 0) {
                res.data.forEach(m => {
                    let typeBadge = m.meter_type == 'electric' 
                        ? '<span class="badge bg-warning text-dark"><i class="fas fa-bolt"></i> ไฟฟ้า</span>' 
                        : '<span class="badge bg-primary"><i class="fa-solid fa-droplet"></i> น้ำ</span>';
                    
                    let brandId = m.brand_id || ''; 

                    rows += `<tr>
                        <td>${m.meter_serial}</td>
                        <td>${typeBadge}</td>
                        <td>${m.brand_name || '-'}</td>
                        <td>
                            <button class="btn btn-sm btn-info" 
                                onclick="editMeter(${m.meter_id}, '${m.meter_serial}', '${m.meter_type}', '${brandId}')">
                                <i class="fas fa-edit"></i> แก้ไข
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteMeter(${m.meter_id})">
                                <i class="fas fa-trash"></i> ลบ
                            </button>
                        </td>
                    </tr>`;
                });
            } else {
                rows = '<tr><td colspan="4" class="text-center text-muted">ไม่พบข้อมูล</td></tr>';
            }
            $('#meterTable tbody').html(rows);

            // สร้างปุ่มเปลี่ยนหน้า ถ้ามีข้อมูล Pagination ส่งกลับมา
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
                <button class="page-link" onclick="loadMeterTable(${current - 1})">ก่อนหน้า</button>
             </li>`;

    // ปุ่มตัวเลขหน้า (Logic แสดงแบบย่อ)
    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || (i >= current - 1 && i <= current + 1)) {
            let active = (i === current) ? 'active' : '';
            html += `<li class="page-item ${active}">
                        <button class="page-link" onclick="loadMeterTable(${i})">${i}</button>
                     </li>`;
        } else if (i === current - 2 || i === current + 2) {
             html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    // ปุ่ม "ถัดไป"
    html += `<li class="page-item ${current === total || total === 0 ? 'disabled' : ''}">
                <button class="page-link" onclick="loadMeterTable(${current + 1})">ถัดไป</button>
             </li>`;

    $('#paginationControls').html(html);
}

// โหลดตัวเลือกยี่ห้อใส่ Dropdown
function loadOptions() {
    $.ajax({
        url: '../backend/meter_handler.php', method: 'POST', data: { action: 'get_options' }, dataType: 'json',
        success: function(res) {
            if(res.error) {
                console.error(res.error);
                return;
            }
            
            let bOps = '<option value="" selected disabled>-- เลือกยี่ห้อ --</option>';
            res.brands.forEach(b => bOps += `<option value="${b.brand_id}">${b.brand_name}</option>`);
            $('#brand_id').html(bOps);
        }
    });
}

// เปิด Modal เพิ่มข้อมูล
function openMeterModal() {
    $('#meterForm')[0].reset(); 
    $('#meter_action').val('create'); 
    $('#meterModal').modal('show');
}

// เปิด Modal แก้ไขข้อมูล
function editMeter(id, serial, mType, bId) {
    $('#meter_id').val(id); 
    $('#meter_serial').val(serial); 
    $('#meter_type').val(mType); 
    $('#brand_id').val(bId); 
    
    $('#meter_action').val('update'); 
    $('#meterModal').modal('show');
}

// บันทึกข้อมูล
function saveMeter() {
    if(!$('#meterForm')[0].checkValidity()) {
        $('#meterForm')[0].reportValidity();
        return;
    }
    $.ajax({
        url: '../backend/meter_handler.php', method: 'POST', data: $('#meterForm').serialize(), dataType: 'json',
        success: function(res) {
            if(res.status == 'success') { 
                $('#meterModal').modal('hide'); 
                loadMeterTable(currentPage); // โหลดหน้าเดิมหลังบันทึก
                Swal.fire('Saved!', 'บันทึกข้อมูลเรียบร้อย', 'success'); 
            } else { 
                Swal.fire('Error', res.message, 'error'); 
            }
        }
    });
}

// ลบข้อมูล
function deleteMeter(id) {
    Swal.fire({ title: 'ยืนยันการลบ?', icon: 'warning', showCancelButton: true, confirmButtonText: 'ลบ', cancelButtonText: 'ยกเลิก' }).then((r) => {
        if(r.isConfirmed) {
            $.post('../backend/meter_handler.php', {action:'delete', meter_id:id}, (res) => {
                if (res.status === 'success') {
                    loadMeterTable(currentPage); // โหลดหน้าเดิมหลังลบ
                    Swal.fire('Deleted','ลบข้อมูลเรียบร้อย','success'); 
                } else {
                    Swal.fire('Error', res.message || 'เกิดข้อผิดพลาดในการลบ', 'error');
                }
            }, 'json');
        }
    });
}