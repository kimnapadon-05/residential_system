$(document).ready(function() {
    loadHouses();
});

// โหลดรายชื่อบ้านใส่ Dropdown
function loadHouses() {
    $.ajax({
        url: '../backend/my_bill_handler.php',
        method: 'POST',
        data: { action: 'get_houses' },
        dataType: 'json',
        success: function(data) {
            let opts = '<option value="">-- กรุณาเลือกบ้าน --</option>';
            data.forEach(h => {
                opts += `<option value="${h.house_id}">${h.house_name}</option>`;
            });
            $('#house_id').html(opts);
        }
    });
}

// กดค้นหาบิล
function checkBill() {
    let hid = $('#house_id').val();
    let m = $('#month').val();
    let y = $('#year').val();
    let mText = $('#month option:selected').text();

    if (!hid) {
        Swal.fire('แจ้งเตือน', 'กรุณาเลือกบ้านพัก', 'warning');
        return;
    }

    // แสดง Loading
    Swal.fire({
        title: 'กำลังค้นหา...',
        text: 'กรุณารอสักครู่',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        url: '../backend/my_bill_handler.php',
        method: 'POST',
        data: { action: 'get_bill', house_id: hid, month: m, year: y },
        dataType: 'json',
        success: function(res) {
            Swal.close();

            if (res.status === 'success') {
                let d = res.data;
                let c = res.calc;

                // --- 1. ใส่ข้อมูลลงใน Invoice ---
                $('#disp_house').text('บ้านพัก: ' + d.house_name);
                $('#disp_period').text(mText + ' ' + y);
                $('#disp_name').text(d.fullname);

                // Rates
                $('#rate_elec').text(c.e_rate);
                $('#rate_water').text(c.w_rate);

                // ข้อมูลไฟฟ้า
                $('#e_prev').text(d.e_prev !== null ? d.e_prev : '-');
                $('#e_curr').text(d.e_curr !== null ? d.e_curr : '-');
                $('#e_unit').text(d.e_units !== null ? d.e_units : '0');
                $('#e_price').text(d.e_units ? c.e_total.toLocaleString() : '0.00');

                // ข้อมูลน้ำประปา
                $('#w_prev').text(d.w_prev !== null ? d.w_prev : '-');
                $('#w_curr').text(d.w_curr !== null ? d.w_curr : '-');
                $('#w_unit').text(d.w_units !== null ? d.w_units : '0');
                $('#w_price').text(d.w_units ? c.w_total.toLocaleString() : '0.00');

                // ยอดรวม
                $('#grand_total').text(c.grand_total.toLocaleString('th-TH', {minimumFractionDigits: 2}));

                // --- 2. แสดงผล Invoice ---
                $('#invoiceArea').slideDown();
                
                // เลื่อนหน้าจอลงมาที่ใบเสร็จ
                $('html, body').animate({
                    scrollTop: $("#invoiceArea").offset().top - 50
                }, 500);

            } else {
                $('#invoiceArea').slideUp();
                Swal.fire({
                    icon: 'info',
                    title: 'ไม่พบข้อมูลบิล',
                    text: 'ยังไม่มีการจดบันทึกค่าใช้จ่ายในเดือนนี้ หรือไม่มีผู้เข้าพักในช่วงเวลาดังกล่าว',
                    confirmButtonText: 'ตกลง'
                });
            }
        },
        error: function() {
            Swal.close();
            Swal.fire('Error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
        }
    });
}