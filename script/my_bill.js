// ไฟล์: script/my_bill.js

$(document).ready(function() {
    loadHouses();
});

// โหลดรายชื่อบ้าน
function loadHouses() {
    $.ajax({
        url: 'backend/my_bill_handler.php', // Path สัมพัทธ์จากหน้า index.php
        method: 'POST',
        data: { action: 'get_houses' },
        dataType: 'json',
        success: function(data) {
            let opts = '<option value="">-- กรุณาเลือกบ้าน --</option>';
            if(Array.isArray(data)){
                data.forEach(h => {
                    opts += `<option value="${h.house_id}">${h.house_name}</option>`;
                });
            }
            $('#house_id').html(opts);
        },
        error: function(xhr, status, error) {
            console.error("Load House Error:", error);
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
        url: 'backend/my_bill_handler.php', // Path สัมพัทธ์จากหน้า index.php
        method: 'POST',
        data: { action: 'get_bill', house_id: hid, month: m, year: y },
        dataType: 'json',
        success: function(res) {
            Swal.close();

            if (res.status === 'success') {
                let d = res.data;
                let c = res.calc;

                // --- ใส่ข้อมูลลง Invoice ---
                $('#disp_house').text('บ้านพัก: ' + d.house_name);
                $('#disp_period').text(mText + ' ' + y);
                $('#disp_name').text(d.fullname);

                // Rates
                $('#rate_elec').text(c.e_rate);
                $('#rate_water').text(c.w_rate);

                // ไฟฟ้า
                $('#e_prev').text(d.e_prev !== null ? d.e_prev : '-');
                $('#e_curr').text(d.e_curr !== null ? d.e_curr : '-');
                $('#e_unit').text(d.e_units !== null ? d.e_units : '0');
                $('#e_price').text(d.e_units ? c.e_total.toLocaleString() : '0.00');

                // ประปา
                $('#w_prev').text(d.w_prev !== null ? d.w_prev : '-');
                $('#w_curr').text(d.w_curr !== null ? d.w_curr : '-');
                $('#w_unit').text(d.w_units !== null ? d.w_units : '0');
                $('#w_price').text(d.w_units ? c.w_total.toLocaleString() : '0.00');

                // ยอดรวม
                $('#grand_total').text(c.grand_total.toLocaleString('th-TH', {minimumFractionDigits: 2}));

                // แสดงผล Invoice
                $('#invoiceArea').slideDown();
                
                $('html, body').animate({
                    scrollTop: $("#invoiceArea").offset().top - 50
                }, 500);

            } else {
                $('#invoiceArea').slideUp();
                Swal.fire({
                    icon: 'info',
                    title: 'ไม่พบข้อมูลบิล',
                    text: 'ไม่พบข้อมูลการจดมิเตอร์ หรือไม่มีผู้เข้าพักในช่วงเวลานี้',
                    confirmButtonText: 'ตกลง'
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.close();
            console.error(xhr.responseText); // ดู error ใน Console
            Swal.fire('Error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
        }
    });
}