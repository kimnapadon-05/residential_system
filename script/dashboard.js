$(document).ready(function() {
    loadDashboardData();
});

function loadDashboardData() {
    $.ajax({
        url: '../backend/dashboard_handler.php',
        method: 'POST',
        data: { action: 'get_stats' },
        dataType: 'json',
        success: function(data) {
            if (data.status === 'success') {
                // 1. Update Cards
                $('#stat_total_houses').text(data.stats.total_houses);
                $('#stat_occupied').text(data.stats.occupied);
                $('#stat_vacant').text(data.stats.vacant);
                $('#stat_people').text(data.stats.people);

                // 2. Update List
                let listHtml = '';
                if (data.recent_movein.length > 0) {
                    data.recent_movein.forEach(item => {
                        listHtml += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${item.person_fname} ${item.person_lname}</strong><br>
                                    <small class="text-muted">บ้าน ${item.house_name}</small>
                                </div>
                                <span class="badge bg-light text-dark">${item.move_in_date}</span>
                            </li>
                        `;
                    });
                } else {
                    listHtml = '<li class="list-group-item text-center text-muted">ยังไม่มีข้อมูล</li>';
                }
                $('#recent_list').html(listHtml);

                // 3. Render Chart
                renderChart(data.chart);
            }
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