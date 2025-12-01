$(document).ready(function() {
    loadHistory();
    loadFormData();
});

function loadHistory() {
    $.ajax({
        url: '../backend/residency_handler.php', method: 'POST', data: { action: 'read' }, dataType: 'json',
        success: function(data) {
            let rows = '';
            data.forEach(r => {
                let status = r.move_out_date ? `<span class="badge bg-secondary">ย้ายออกแล้ว (${r.move_out_date})</span>` : '<span class="badge bg-success">กำลังพักอาศัย</span>';
                let btn = r.move_out_date ? '' : `<button class="btn btn-danger btn-sm" onclick="moveOut(${r.history_id})">แจ้งย้ายออก</button>`;
                
                rows += `<tr>
                    <td>${r.fullname}</td>
                    <td>${r.house_name}</td>
                    <td>${r.elec_serial || '-'}</td>
                    <td>${r.water_serial || '-'}</td>
                    <td>${r.move_in_date}</td>
                    <td>${status}</td>
                    <td>${btn}</td>
                </tr>`;
            });
            $('#historyTable tbody').html(rows);
        }
    });
}

function loadFormData() {
    $.ajax({
        url: '../backend/residency_handler.php', method: 'POST', data: { action: 'get_form_data' }, dataType: 'json',
        success: function(res) {
            let pOpt = '<option value="">เลือกผู้พัก</option>';
            res.people.forEach(p => pOpt += `<option value="${p.person_id}">${p.name}</option>`);
            $('#person_select').html(pOpt);

            let hOpt = '<option value="">เลือกบ้าน</option>';
            res.houses.forEach(h => hOpt += `<option value="${h.house_id}">${h.house_name}</option>`);
            $('#house_select').html(hOpt);

            let eOpt = '<option value="">เลือกมิเตอร์ไฟ</option>';
            res.elecMeters.forEach(e => eOpt += `<option value="${e.meter_id}">${e.meter_serial}</option>`);
            $('#elec_select').html(eOpt);

            let wOpt = '<option value="">เลือกมิเตอร์น้ำ</option>';
            res.waterMeters.forEach(w => wOpt += `<option value="${w.meter_id}">${w.meter_serial}</option>`);
            $('#water_select').html(wOpt);
        }
    });
}

function openMoveInModal() {
    $('#moveInForm')[0].reset();
    $('#moveInModal').modal('show');
}

function saveMoveIn() {
    $.ajax({
        url: '../backend/residency_handler.php', method: 'POST', data: $('#moveInForm').serialize(), dataType: 'json',
        success: function(res) {
            if(res.status == 'success') {
                $('#moveInModal').modal('hide');
                loadHistory();
                Swal.fire('Success', res.message, 'success');
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }
    });
}

function moveOut(id) {
    Swal.fire({
        title: 'ยืนยันการแจ้งย้ายออก?',
        text: "สถานะจะถูกเปลี่ยนเป็นย้ายออกในวันนี้",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'ยืนยันย้ายออก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../backend/residency_handler.php',
                method: 'POST',
                data: { action: 'move_out', history_id: id },
                dataType: 'json',
                success: function(res) {
                    loadHistory();
                    Swal.fire('เรียบร้อย', res.message, 'success');
                }
            });
        }
    });
}