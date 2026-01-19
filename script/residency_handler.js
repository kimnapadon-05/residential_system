// ตัวแปร Global
let currentPage = 1;
const itemsPerPage = 10;

$(document).ready(function() {
    loadHistory(currentPage);
    loadFormData();

    // [Real-time Search] ค้นหาทันทีที่พิมพ์ (Event 'input')
    $('#searchInput').on('input', function() {
        let query = $(this).val();
        currentPage = 1; // รีเซ็ตไปหน้า 1
        loadHistory(currentPage, query);
    });

    // Event Listener สำหรับ Dropdown เลือกบ้าน (ดึงค่ามิเตอร์ล่าสุด)
    $('#house_select').change(function() {
        let houseId = $(this).val();
        if (houseId) {
            $.ajax({
                url: '../backend/residency_handler.php',
                method: 'POST',
                data: { action: 'get_last_reading', house_id: houseId },
                dataType: 'json',
                success: function(res) {
                    $('#start_elec').val(res.final_electric_reading);
                    $('#start_water').val(res.final_water_reading);
                },
                error: function() { console.log('Error fetching readings'); }
            });
        } else {
            $('#start_elec').val(0);
            $('#start_water').val(0);
        }
    });
});

// ฟังก์ชันโหลดตาราง
function loadHistory(page, search = '') {
    currentPage = page;
    // ดึงค่า search จาก input ถ้าไม่ได้ส่งมา
    if(search === '') search = $('#searchInput').val();

    $.ajax({
        url: '../backend/residency_handler.php', 
        method: 'POST', 
        data: { 
            action: 'read',
            page: page,
            limit: itemsPerPage,
            search: search
        }, 
        dataType: 'json',
        success: function(res) {
            if(res.status === 'error') {
                console.error(res.message);
                return;
            }

            let rows = '';
            if (res.data.length > 0) {
                res.data.forEach(r => {
                    let status = r.move_out_date ? 
                        `<span class="badge bg-secondary">ย้ายออก (${r.move_out_date})</span>` : 
                        `<span class="badge bg-success">กำลังพัก</span>`;
                    
                    let btn = r.move_out_date ? 
                        '-' : 
                        `<button class="btn btn-danger btn-sm" 
                            onclick="openMoveOutModal(${r.history_id}, ${r.starting_electric_reading}, ${r.starting_water_reading})">
                            <i class="fas fa-sign-out-alt"></i> แจ้งย้ายออก
                        </button>`;
                    
                    rows += `<tr>
                        <td>${r.fullname}</td>
                        <td>${r.house_name}</td>
                        <td>
                            <small class="text-muted">ไฟ:</small> ${r.elec_serial || '-'}<br>
                            <small class="text-muted">น้ำ:</small> ${r.water_serial || '-'}
                        </td>
                        <td>${r.move_in_date}</td>
                        <td>${status}</td>
                        <td class="text-center">${btn}</td>
                    </tr>`;
                });
            } else {
                rows = '<tr><td colspan="6" class="text-center text-muted">ไม่พบข้อมูล</td></tr>';
            }
            $('#historyTable tbody').html(rows);

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

    let start = totalRows === 0 ? 0 : ((current - 1) * itemsPerPage) + 1;
    let end = Math.min(current * itemsPerPage, totalRows);
    $('#pageInfo').text(`แสดง ${start} ถึง ${end} จาก ${totalRows} รายการ`);

    // ปุ่มก่อนหน้า
    html += `<li class="page-item ${current === 1 ? 'disabled' : ''}">
                <button class="page-link" onclick="loadHistory(${current - 1})">ก่อนหน้า</button>
             </li>`;

    // ปุ่มตัวเลข
    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || (i >= current - 1 && i <= current + 1)) {
            let active = (i === current) ? 'active' : '';
            html += `<li class="page-item ${active}">
                        <button class="page-link" onclick="loadHistory(${i})">${i}</button>
                     </li>`;
        } else if (i === current - 2 || i === current + 2) {
             html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    // ปุ่มถัดไป
    html += `<li class="page-item ${current === total || total === 0 ? 'disabled' : ''}">
                <button class="page-link" onclick="loadHistory(${current + 1})">ถัดไป</button>
             </li>`;

    $('#paginationControls').html(html);
}

// โหลดข้อมูลใส่ Dropdown
function loadFormData() {
    $.ajax({
        url: '../backend/residency_handler.php', method: 'POST', data: { action: 'get_form_data' }, dataType: 'json',
        success: function(res) {
            let pOpt = '<option value="">-- เลือกผู้พัก --</option>';
            res.people.forEach(p => pOpt += `<option value="${p.person_id}">${p.name}</option>`);
            $('#person_select').html(pOpt);

            let hOpt = '<option value="">-- เลือกบ้าน --</option>';
            res.houses.forEach(h => hOpt += `<option value="${h.house_id}">${h.house_name}</option>`);
            $('#house_select').html(hOpt);

            let eOpt = '<option value="">-- ไม่ระบุ --</option>';
            res.elecMeters.forEach(e => eOpt += `<option value="${e.meter_id}">${e.meter_serial}</option>`);
            $('#elec_select').html(eOpt);

            let wOpt = '<option value="">-- ไม่ระบุ --</option>';
            res.waterMeters.forEach(w => wOpt += `<option value="${w.meter_id}">${w.meter_serial}</option>`);
            $('#water_select').html(wOpt);
        }
    });
}

// เปิด Modal ย้ายเข้า
function openMoveInModal() {
    $('#moveInForm')[0].reset();
    $('input[name="move_in_date"]').val(new Date().toISOString().split('T')[0]);
    $('#moveInModal').modal('show');
}

// เปิด Modal ย้ายออก
function openMoveOutModal(id, startElec, startWater) {
    $('#out_history_id').val(id);
    $('#moveOutForm')[0].reset();
    $('#move_out_date').val(new Date().toISOString().split('T')[0]);
    $('#chk_start_elec').val(startElec);
    $('#chk_start_water').val(startWater);
    $('#hint_elec').text(`(ต้องไม่ต่ำกว่า: ${startElec})`);
    $('#hint_water').text(`(ต้องไม่ต่ำกว่า: ${startWater})`);
    $('#moveOutModal').modal('show');
}

// บันทึกย้ายเข้า
function saveMoveIn() {
    if(!$('#house_select').val() || !$('#person_select').val()) {
        Swal.fire('แจ้งเตือน', 'กรุณาเลือกบ้านพักและผู้พักอาศัย', 'warning');
        return;
    }
    $.ajax({
        url: '../backend/residency_handler.php', method: 'POST', data: $('#moveInForm').serialize(), dataType: 'json',
        success: function(res) {
            if(res.status == 'success') {
                $('#moveInModal').modal('hide');
                loadHistory(currentPage); // โหลดหน้าเดิม
                Swal.fire('สำเร็จ', res.message, 'success');
            } else { Swal.fire('ผิดพลาด', res.message, 'error'); }
        },
        error: function() { Swal.fire('Error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error'); }
    });
}

// บันทึกย้ายออก
function saveMoveOut() {
    let finalElec = parseFloat($('#final_elec').val()) || 0;
    let finalWater = parseFloat($('#final_water').val()) || 0;
    let startElec = parseFloat($('#chk_start_elec').val()) || 0;
    let startWater = parseFloat($('#chk_start_water').val()) || 0;

    if(finalElec < startElec) { Swal.fire('ข้อมูลไม่ถูกต้อง', `มิเตอร์ไฟ (${finalElec}) ต่ำกว่าค่าเริ่มต้น (${startElec})`, 'warning'); return; }
    if(finalWater < startWater) { Swal.fire('ข้อมูลไม่ถูกต้อง', `มิเตอร์น้ำ (${finalWater}) ต่ำกว่าค่าเริ่มต้น (${startWater})`, 'warning'); return; }

    $.ajax({
        url: '../backend/residency_handler.php', method: 'POST', data: $('#moveOutForm').serialize(), dataType: 'json',
        success: function(res) {
            if(res.status == 'success') {
                $('#moveOutModal').modal('hide');
                loadHistory(currentPage);
                Swal.fire('สำเร็จ', res.message, 'success');
            } else { Swal.fire('ผิดพลาด', res.message, 'error'); }
        }
    });
}