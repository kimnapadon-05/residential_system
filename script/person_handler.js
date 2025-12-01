$(document).ready(function() {
    loadPersonTable();
    loadPositions();
});

function loadPersonTable() {
    $.ajax({
        url: '../backend/person_handler.php', method: 'POST', data: { action: 'read' }, dataType: 'json',
        success: function(data) {
            let rows = '';
            data.forEach(item => {
                rows += `
                    <tr>
                        <td>${item.person_fname} ${item.person_lname}</td>
                        <td><span class="badge bg-info text-dark">${item.position_name}</span></td>
                        <td><span class="badge bg-success">ปกติ</span></td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="editPerson(${item.person_id}, '${item.person_fname}', '${item.person_lname}', ${item.position_id})"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="deletePerson(${item.person_id})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>`;
            });
            $('#personTable tbody').html(rows);
        }
    });
}

function loadPositions() {
    $.ajax({
        url: '../backend/person_handler.php', method: 'POST', data: { action: 'get_positions' }, dataType: 'json',
        success: function(data) {
            let opts = '<option value="">เลือกตำแหน่ง</option>';
            data.forEach(p => opts += `<option value="${p.position_id}">${p.position_name}</option>`);
            $('#position_id').html(opts);
        }
    });
}

function openPersonModal() {
    $('#personForm')[0].reset();
    $('#form_action').val('create');
    $('#modalTitle').text('เพิ่มบุคลากร');
    $('#personModal').modal('show');
}

function editPerson(id, fname, lname, posId) {
    $('#person_id').val(id);
    $('#person_fname').val(fname);
    $('#person_lname').val(lname);
    $('#position_id').val(posId);
    $('#form_action').val('update');
    $('#modalTitle').text('แก้ไขข้อมูล');
    $('#personModal').modal('show');
}

function savePerson() {
    $.ajax({
        url: '../backend/person_handler.php', method: 'POST', data: $('#personForm').serialize(), dataType: 'json',
        success: function(res) {
            if(res.status === 'success') {
                Swal.fire('สำเร็จ', res.message, 'success');
                $('#personModal').modal('hide');
                loadPersonTable();
            } else { Swal.fire('Error', res.message, 'error'); }
        }
    });
}

function deletePerson(id) {
    Swal.fire({ title: 'ยืนยันลบ?', icon: 'warning', showCancelButton: true, confirmButtonText: 'ลบ' }).then((r) => {
        if(r.isConfirmed) {
            $.ajax({ url: '../backend/person_handler.php', method: 'POST', data: { action: 'delete', person_id: id }, dataType: 'json',
                success: function(res) { loadPersonTable(); Swal.fire('Deleted', '', 'success'); }
            });
        }
    });
}