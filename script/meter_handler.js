$(document).ready(function() {
    loadMeterTable();
    loadOptions();
});

function loadMeterTable() {
    $.ajax({
        url: '../backend/meter_handler.php', method: 'POST', data: { action: 'read' }, dataType: 'json',
        success: function(data) {
            let rows = '';
            data.forEach(m => {
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
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteMeter(${m.meter_id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });
            $('#meterTable tbody').html(rows);
        }
    });
}

function loadOptions() {
    $.ajax({
        url: '../backend/meter_handler.php', method: 'POST', data: { action: 'get_options' }, dataType: 'json',
        success: function(res) {
            if(res.error) {
                console.error(res.error);
                return;
            }
            
            // Dropdown ยี่ห้อ
            let bOps = '<option value="" selected disabled>-- เลือกยี่ห้อ --</option>';
            res.brands.forEach(b => bOps += `<option value="${b.brand_id}">${b.brand_name}</option>`);
            $('#brand_id').html(bOps);
        }
    });
}

function openMeterModal() {
    $('#meterForm')[0].reset(); 
    $('#meter_action').val('create'); 
    $('#meterModal').modal('show');
}

function editMeter(id, serial, mType, bId) {
    $('#meter_id').val(id); 
    $('#meter_serial').val(serial); 
    $('#meter_type').val(mType); 
    $('#brand_id').val(bId); 
    
    $('#meter_action').val('update'); 
    $('#meterModal').modal('show');
}

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
                loadMeterTable(); 
                Swal.fire('Saved!', 'บันทึกข้อมูลเรียบร้อย', 'success'); 
            } else { 
                Swal.fire('Error', res.message, 'error'); 
            }
        }
    });
}

function deleteMeter(id) {
    Swal.fire({ title: 'ยืนยันการลบ?', icon: 'warning', showCancelButton: true }).then((r) => {
        if(r.isConfirmed) $.post('../backend/meter_handler.php', {action:'delete', meter_id:id}, () => { 
            loadMeterTable(); 
            Swal.fire('Deleted','ลบข้อมูลเรียบร้อย','success'); 
        });
    });
}