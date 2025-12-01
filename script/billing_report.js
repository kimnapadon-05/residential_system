$(document).ready(function() {
    loadReport();
});

function loadReport() {
    let m = $('#select_month').val();
    let y = $('#select_year').val();

    // Show Loading
    $('#reportTable tbody').html('<tr><td colspan="7" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin"></i> กำลังประมวลผล...</td></tr>');

    $.ajax({
        url: '../backend/billing_report_handler.php',
        method: 'POST',
        data: { action: 'get_report', month: m, year: y },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                // 1. Update Summary Cards
                $('#sum_elec').text(res.summary.total_elec);
                $('#sum_water').text(res.summary.total_water);
                $('#sum_total').text(res.summary.total_grand_total); // Note: PHP ส่งมาเป็น grand_total แก้ไข key ให้ตรงกัน

                // แก้ไขเล็กน้อย: ใน PHP key คือ 'grand_total'
                $('#sum_total').text(res.summary.grand_total);

                // 2. Render Table
                let rows = '';
                if (res.data.length > 0) {
                    res.data.forEach(row => {
                        // Format numbers
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