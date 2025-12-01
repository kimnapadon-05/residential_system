$(document).ready(function() {
    loadTable();
    loadLocations();
});

// โหลดข้อมูลใส่ตาราง
function loadTable() {
    $.ajax({
        url: '../backend/house_handler.php',
        method: 'POST',
        data: { action: 'read' },
        dataType: 'json',
        success: function(data) {
            let rows = '';
            data.forEach(row => {
                rows += `
                    <tr>
                        <td>${row.house_id}</td>
                        <td>${row.house_name}</td>
                        <td>${row.location_name || '-'}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="editHouse(${row.house_id}, '${row.house_name}', ${row.location_id})">
                                <i class="fas fa-edit"></i> แก้ไข
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteHouse(${row.house_id})">
                                <i class="fas fa-trash"></i> ลบ
                            </button>
                        </td>
                    </tr>
                `;
            });
            $('#houseTable tbody').html(rows);
        }
    });
}

// โหลดตัวเลือก Location
function loadLocations() {
    $.ajax({
        url: '../backend/house_handler.php',
        method: 'POST',
        data: { action: 'get_locations' },
        dataType: 'json',
        success: function(data) {
            let options = '<option value="">เลือกโซนที่พัก</option>';
            data.forEach(loc => {
                options += `<option value="${loc.location_id}">${loc.location_name}</option>`;
            });
            $('#location_id').html(options);
        }
    });
}

// บันทึกข้อมูล (แยกกรณี เพิ่ม/แก้ไข ตาม form_action)
function saveHouse() {
    let formData = $('#houseForm').serialize(); // ดึงค่าทั้งหมดในฟอร์ม

    $.ajax({
        url: '../backend/house_handler.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire('สำเร็จ!', response.message, 'success');
                $('#houseModal').modal('hide');
                loadTable(); // โหลดตารางใหม่
            } else {
                Swal.fire('เกิดข้อผิดพลาด!', response.message, 'error');
            }
        }
    });
}

// เตรียมฟอร์มสำหรับแก้ไข
function editHouse(id, name, location_id) {
    $('#modalTitle').text('แก้ไขข้อมูลบ้านพัก');
    $('#form_action').val('update');
    $('#house_id').val(id);
    $('#house_name').val(name);
    $('#location_id').val(location_id);
    $('#houseModal').modal('show');
}

// เตรียมฟอร์มสำหรับเพิ่มใหม่ (เคลียร์ค่าเก่า)
function resetForm() {
    $('#modalTitle').text('เพิ่มบ้านพัก');
    $('#form_action').val('create');
    $('#houseForm')[0].reset();
    $('#house_id').val('');
}

// ลบข้อมูล
function deleteHouse(id) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: "คุณจะไม่สามารถกู้คืนข้อมูลนี้ได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../backend/house_handler.php',
                method: 'POST',
                data: { action: 'delete', house_id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('ลบสำเร็จ!', response.message, 'success');
                        loadTable();
                    } else {
                        Swal.fire('เกิดข้อผิดพลาด', response.message, 'error');
                    }
                }
            });
        }
    });
}