$(document).ready(function() {
    loadSheet();
});

function loadSheet() {
    let m = $('#select_month').val();
    let y = $('#select_year').val();

    $('#recordingTable tbody').html('<tr><td colspan="7" class="text-center">กำลังโหลดข้อมูล...</td></tr>');

    $.ajax({
        url: '../backend/electric_reading_handler.php',
        method: 'POST',
        data: { action: 'load_sheet', month: m, year: y },
        dataType: 'json',
        success: function(data) {
            let rows = '';
            if (data.length === 0) {
                rows = '<tr><td colspan="7" class="text-center text-muted">ไม่พบรายการบ้านพักที่มีมิเตอร์ไฟฟ้า หรือไม่มีผู้เข้าพักในเดือนนี้</td></tr>';
            } else {
                data.forEach((item, index) => {
                    let bgClass = item.is_saved ? 'table-success' : '';
                    let btnClass = item.is_saved ? 'btn-success' : 'btn-outline-primary';
                    let btnText = item.is_saved ? '<i class="fas fa-check"></i> บันทึกแล้ว' : '<i class="fas fa-save"></i> บันทึก';
                    
                    rows += `
                        <tr class="${bgClass}" id="row_${item.history_id}">
                            <td>${item.house_name}</td>
                            <td><small>${item.fullname}</small></td>
                            <td><span class="badge bg-light text-dark">${item.meter_serial}</span></td>
                            <td class="text-end text-muted" id="prev_${item.history_id}">${item.prev_reading}</td>
                            <td>
                                <input type="number" class="form-control input_curr" 
                                    data-id="${item.history_id}" 
                                    id="curr_${item.history_id}" 
                                    value="${item.current_reading}" 
                                    oninput="calculateUsage(${item.history_id})">
                            </td>
                            <td class="text-end fw-bold" id="usage_${item.history_id}">
                                ${item.usage !== '' ? item.usage : '-'}
                            </td>
                            <td>
                                <button class="btn btn-sm ${btnClass}" id="btn_${item.history_id}" onclick="saveRow(${item.history_id})">
                                    ${btnText}
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }
            $('#recordingTable tbody').html(rows);
        }
    });
}

// คำนวณหน่วยทันทีที่พิมพ์
function calculateUsage(id) {
    let prev = parseFloat($(`#prev_${id}`).text());
    let curr = parseFloat($(`#curr_${id}`).val());
    
    if (!isNaN(curr)) {
        let usage = curr - prev;
        if (usage < 0) {
            $(`#usage_${id}`).html('<span class="text-danger">ผิดพลาด</span>');
            $(`#curr_${id}`).addClass('is-invalid');
        } else {
            $(`#usage_${id}`).text(usage);
            $(`#curr_${id}`).removeClass('is-invalid');
        }
    } else {
        $(`#usage_${id}`).text('-');
    }
}

// บันทึกข้อมูลทีละแถว
function saveRow(id) {
    let m = $('#select_month').val();
    let y = $('#select_year').val();
    let prev = parseFloat($(`#prev_${id}`).text());
    let curr = parseFloat($(`#curr_${id}`).val());

    if (isNaN(curr) || curr === '') {
        Swal.fire('แจ้งเตือน', 'กรุณากรอกเลขมิเตอร์ปัจจุบัน', 'warning');
        return;
    }
    if (curr < prev) {
        Swal.fire('ข้อมูลผิดพลาด', 'เลขมิเตอร์ปัจจุบันน้อยกว่าครั้งก่อนไม่ได้', 'error');
        return;
    }

    // เปลี่ยนปุ่มเป็น Loading
    let btn = $(`#btn_${id}`);
    let originalText = btn.html();
    btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

    $.ajax({
        url: '../backend/electric_reading_handler.php',
        method: 'POST',
        data: { 
            action: 'save_reading', 
            history_id: id, 
            month: m, year: y, 
            prev_reading: prev, 
            current_reading: curr 
        },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                // Update UI
                $(`#row_${id}`).addClass('table-success');
                btn.removeClass('btn-outline-primary').addClass('btn-success');
                btn.html('<i class="fas fa-check"></i> บันทึกแล้ว').prop('disabled', false);
                
                // Toast แจ้งเตือนเล็กๆ
                const Toast = Swal.mixin({
                    toast: true, position: 'top-end', showConfirmButton: false, timer: 1500
                });
                Toast.fire({ icon: 'success', title: 'บันทึกสำเร็จ' });
            } else {
                Swal.fire('Error', res.message, 'error');
                btn.html(originalText).prop('disabled', false);
            }
        }
    });
}