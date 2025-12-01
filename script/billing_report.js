$(document).ready(function() {
    loadReport();
});

function loadReport() {
    let m = $('#select_month').val();
    let y = $('#select_year').val();

    $('#reportTable tbody').html('<tr><td colspan="7" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin"></i> กำลังประมวลผล...</td></tr>');

    $.ajax({
        url: '../backend/billing_report_handler.php',
        method: 'POST',
        data: { action: 'get_report', month: m, year: y },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                
                // --- [จุดที่เพิ่ม Logic แจ้งเตือน] ---
                if (res.rates) {
                    let e_rate = parseFloat(res.rates.elec);
                    let w_rate = parseFloat(res.rates.water);
                    
                    // เช็คค่าไฟ
                    if (e_rate > 0) {
                        $('#lbl_elec_rate').text(e_rate.toFixed(2)).removeClass('text-danger fw-bold').css('font-size', '');
                    } else {
                        $('#lbl_elec_rate').text('ยังไม่กำหนด!').addClass('text-danger fw-bold').css('font-size', '1.1em');
                    }

                    // เช็คค่าน้ำ
                    if (w_rate > 0) {
                        $('#lbl_water_rate').text(w_rate.toFixed(2)).removeClass('text-danger fw-bold').css('font-size', '');
                    } else {
                        $('#lbl_water_rate').text('ยังไม่กำหนด!').addClass('text-danger fw-bold').css('font-size', '1.1em');
                    }
                }
                // ------------------------------------

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
                                <td class="fw-bold">${row.house_name}</td>
                                <td>${row.fullname}</td>
                                <td class="text-end">${row.elec_units}</td>
                                <td class="text-end text-warning-emphasis fw-bold">${e_price}</td>
                                <td class="text-end">${row.water_units}</td>
                                <td class="text-end text-info-emphasis fw-bold">${w_price}</td>
                                <td class="text-end text-success fw-bold bg-success bg-opacity-10">${total}</td>
                            </tr>
                        `;
                    });
                } else {
                    rows = '<tr><td colspan="7" class="text-center text-muted py-4">ไม่พบข้อมูลในเดือนนี้</td></tr>';
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