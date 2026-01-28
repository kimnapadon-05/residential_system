$(document).ready(function() {
    loadDashboardData();
});

function loadDashboardData() {
        $.ajax({
        url: 'backend/dashboard_handler.php', // เช็ค path ไฟล์ดีๆ นะ
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'get_stats' // <--- ต้องมีตัวนี้ เป๊ะๆ ตาม PHP
        },
        success: function(response) {
            // ลอง log ออกมาดูก่อน
            console.log("Response:", response);

            if (response.status === 'success') {
                // เอา data ไปโชว์
                $('#total_houses').text(response.stats.total_houses);
                // ...
            } else {
                console.error("Error from Server:", response.message);
            }
        },
        error: function(xhr, status, error) {
            // ถ้าไม่เข้า success มาดูตรงนี้
            console.error("AJAX Error:", status, error);
            console.log(xhr.responseText); // ดู error ที่ PHP พ่นออกมา
        }
    });
}

function renderChart(chartData) {
    const ctx = document.getElementById('usageChart').getContext('2d');
    
    // เตรียมข้อมูลแกน X (เดือน) - ดึงจาก Electric Chart เป็นหลัก (สมมติว่ามีข้อมูลเท่ากัน หรือใช้ Union ใน SQL จะดีสุด แต่เอาแบบง่ายก่อน)
    let labels = chartData.electric.map(item => item.month_label);
    let elecData = chartData.electric.map(item => item.total_usage);
    let waterData = chartData.water.map(item => item.total_usage);

    new Chart(ctx, {
        type: 'line', // หรือ 'bar'
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'ไฟฟ้า (หน่วย)',
                    data: elecData,
                    borderColor: '#f6c23e', // สีเหลือง
                    backgroundColor: 'rgba(246, 194, 62, 0.1)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'น้ำประปา (หน่วย)',
                    data: waterData,
                    borderColor: '#36b9cc', // สีฟ้า
                    backgroundColor: 'rgba(54, 185, 204, 0.1)',
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
}