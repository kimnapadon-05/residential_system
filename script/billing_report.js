$(document).ready(function() {
    loadReport();
    
    // อัปเดตหัวกระดาษเวลาสั่งพิมพ์
    $('#select_month, #select_year').change(function() {
        let m = $('#select_month option:selected').text();
        let y = $('#select_year option:selected').text();
        $('#print_month_year').text(m + ' ' + y);
    });
});

function loadReport() {
    let m = $('#select_month').val();
    let y = $('#select_year').val();

    // ตั้งค่าหัวกระดาษสำหรับพิมพ์
    let mText = $('#select_month option:selected').text();
    $('#print_month_year').text(mText + ' ' + y);

    $('#reportTable tbody').html('<tr><td colspan="8" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin"></i> กำลังประมวลผล...</td></tr>');

    $.ajax({
        url: '../backend/billing_report_handler.php',
        method: 'POST',
        data: { action: 'get_report', month: m, year: y },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                
                // --- [แก้ไข Logic ใหม่: เช็คว่ามีโซนไหนลืมตั้งค่าราคาไหม] ---
                if (res.missing_rates) {
                    // ถ้ามีบ้านที่ไม่มีราคา (ราคาเป็น 0) ให้แจ้งเตือนสีแดง
                    $('#lbl_elec_rate, #lbl_water_rate').text('ข้อมูลไม่ครบ!').addClass('text-danger fw-bold');
                    Swal.fire('แจ้งเตือน', 'พบโซนที่ยังไม่ได้กำหนดราคาค่าไฟ/น้ำ ระบบจะคำนวณเป็น 0 บาท', 'warning');
                } else {
                    // ถ้าครบถ้วน ให้ขึ้นว่า "ตามพื้นที่"
                    $('#lbl_elec_rate, #lbl_water_rate').text('ตามพื้นที่').removeClass('text-danger fw-bold').css('color', '');
                }
                // -----------------------------------------------------

                $('#sum_elec').text(res.summary.total_elec);
                $('#sum_water').text(res.summary.total_water);
                $('#sum_total').text(res.summary.grand_total);

                let rows = '';
                if (res.data.length > 0) {
                    res.data.forEach(row => {
                        let e_price = parseFloat(row.elec_price).toLocaleString('th-TH', {minimumFractionDigits: 2});
                        let w_price = parseFloat(row.water_price).toLocaleString('th-TH', {minimumFractionDigits: 2});
                        let total = parseFloat(row.total_price).toLocaleString('th-TH', {minimumFractionDigits: 2});

                        rows += `
                            <tr>
                                <td><span class="badge bg-light text-dark border">${row.location_name}</span></td>
                                <td class="fw-bold">${row.house_name}</td>
                                <td>${row.fullname}</td>
                                <td class="text-end">
                                    ${row.elec_units} <br> 
                                    <small class="text-muted" style="font-size:0.8em">(@${row.elec_rate})</small>
                                </td>
                                <td class="text-end text-warning-emphasis fw-bold">${e_price}</td>
                                <td class="text-end">
                                    ${row.water_units} <br> 
                                    <small class="text-muted" style="font-size:0.8em">(@${row.water_rate})</small>
                                </td>
                                <td class="text-end text-info-emphasis fw-bold">${w_price}</td>
                                <td class="text-end text-success fw-bold bg-success bg-opacity-10">${total}</td>
                            </tr>
                        `;
                    });
                } else {
                    rows = '<tr><td colspan="8" class="text-center text-muted py-4">ไม่พบข้อมูลในเดือนนี้</td></tr>';
                }
                $('#reportTable tbody').html(rows);
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้', 'error');
        }
    });
}