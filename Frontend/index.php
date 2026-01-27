<?php
// ==================== DATABASE CONNECTION ====================
// ใช้ __DIR__ เพื่อ support Plesk และ environments ต่าง ๆ
$config_file = realpath(__DIR__ . '/../Database/config.php');
if (!file_exists($config_file)) {
    die('❌ ไม่พบไฟล์ config.php ที่: ' . htmlspecialchars($config_file));
}
require_once $config_file;

// ==================== GET HOUSES (dropdown list) ====================
$houses = [];
try {
    $stmt = $conn->query("SELECT house_id, house_name FROM house_info ORDER BY house_name ASC");
    $houses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error loading houses: " . $e->getMessage());
}

// ==================== GET BILL DATA (if form submitted) ====================
$bill_data = null;
$bill_calc = null;
$bill_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $house_id = isset($_POST['house_id']) ? intval($_POST['house_id']) : 0;
    $month = isset($_POST['month']) ? intval($_POST['month']) : 0;
    $year = isset($_POST['year']) ? intval($_POST['year']) : 0;

    if ($house_id && $month && $year) {
        try {
            // ดึงราคา
            $stmtRate = $conn->prepare("SELECT elec_rate, water_rate FROM utility_rates WHERE bill_month = :m AND bill_year = :y");
            $stmtRate->execute([':m' => $month, ':y' => $year]);
            $rates = $stmtRate->fetch(PDO::FETCH_ASSOC);

            $ELEC_RATE = $rates ? floatval($rates['elec_rate']) : 7.0;
            $WATER_RATE = $rates ? floatval($rates['water_rate']) : 15.0;

            // ดึงข้อมูลบิล
            $sql = "SELECT h.house_name, CONCAT(p.person_fname, ' ', p.person_lname) as fullname,
                        er.previous_reading as e_prev, er.current_reading as e_curr, er.usage_units as e_units,
                        wr.previous_reading as w_prev, wr.current_reading as w_curr, wr.usage_units as w_units
                    FROM residency_history rh
                    JOIN house_info h ON rh.house_id = h.house_id
                    JOIN person_info p ON rh.person_id = p.person_id
                    LEFT JOIN electric_readings er ON rh.history_id = er.history_id AND er.bill_month = :m AND er.bill_year = :y
                    LEFT JOIN water_readings wr ON rh.history_id = wr.history_id AND wr.bill_month = :m AND wr.bill_year = :y
                    WHERE rh.house_id = :hid
                    AND (rh.move_out_date IS NULL OR (MONTH(rh.move_out_date) >= :m AND YEAR(rh.move_out_date) = :y))
                    ORDER BY rh.history_id DESC LIMIT 1";

            $stmt = $conn->prepare($sql);
            $stmt->execute([':hid' => $house_id, ':m' => $month, ':y' => $year]);
            $bill_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($bill_data) {
                $e_units = intval($bill_data['e_units'] ?? 0);
                $w_units = intval($bill_data['w_units'] ?? 0);
                $e_total = $e_units * $ELEC_RATE;
                $w_total = $w_units * $WATER_RATE;

                $bill_calc = [
                    'e_rate' => $ELEC_RATE,
                    'w_rate' => $WATER_RATE,
                    'e_total' => $e_total,
                    'w_total' => $w_total,
                    'grand_total' => $e_total + $w_total,
                    'month_text' => ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'][$month]
                ];
            } else {
                $bill_error = "ไม่พบข้อมูลบิลสำหรับบ้านพัก/เดือนนี้";
            }
        } catch (PDOException $e) {
            $bill_error = "เกิดข้อผิดพลาดในการดึงข้อมูล: " . htmlspecialchars($e->getMessage());
            error_log("Bill query error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตรวจสอบค่าสาธารณูปโภค - บ้านพักครู</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body { 
            font-family: 'Kanit', sans-serif; 
            background-color: #f0f2f5; 
            min-height: 100vh; /* ให้หน้าจอมีความสูงอย่างน้อยเต็มจอ */
            display: flex;
            flex-direction: column;
        }
        
        .container-search { max-width: 900px; margin: 40px auto; }
        
        .invoice-box {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            display: none; 
            width: 100%;          /* ยืดให้เต็ม Wrapper */
            max-width: 210mm;     /* แต่ไม่เกิน A4 */
        }
        
        .invoice-header { border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 30px; }
        .table-invoice th { background-color: #f8f9fa; text-align: center; vertical-align: middle; }
        .table-invoice td { vertical-align: middle; }
        
        /* จัดให้อยู่กึ่งกลางหน้าจอ */
        .invoice-wrapper {
            display: flex;
            justify-content: center; /* กึ่งกลางแนวนอน */
            width: 100%;
            padding: 0 15px; /* เว้นขอบซ้ายขวาเล็กน้อยกันชิดขอบจอเกินไปในมือถือ */
        }

        @media print {

            @page { 
            size: A4 portrait;
            /* กำหนดขอบ: บน ขวา ล่าง ซ้าย */
            /* ตัวอย่าง: บน 20mm, ขวา 10mm, ล่าง 10mm, ซ้าย 10mm */
            margin: 20mm 10mm 10mm 10mm; 
        }
            /* 1. ซ่อนส่วนที่ไม่ต้องการ (ปุ่ม, เมนูค้นหา) */
            .no-print { display: none !important; }
            
            /* 2. รีเซ็ตพื้นหลังเป็นสีขาว */
            body, html { 
                background-color: white !important; 
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* 3. จัด Wrapper ให้ยึดตำแหน่งหัวกระดาษและจัดกึ่งกลาง */
            .invoice-wrapper {
                position: absolute; /* ยึดกับมุมกระดาษ */
                top: 0;
                left: 0;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                
                /* ใช้ Flexbox จัดกึ่งกลางแนวนอน */
                display: flex !important;
                justify-content: center !important; 
                align-items: flex-start !important; 
            }

            /* 4. ปรับกล่องใบแจ้งหนี้ให้พอดีกระดาษ */
            .invoice-box {
                display: block !important;
                
                /* กำหนดขนาดให้พอดี A4 */
                width: 100% !important;
                max-width: 210mm !important; 
                
                /* คำสั่งจัดกึ่งกลาง (สำคัญ) */
                margin: 0 auto !important;   
                
                /* ยกเลิกการบังคับตำแหน่งแบบเดิม */
                position: static !important; 
                left: auto !important;
                top: auto !important;
                
                /* ตกแต่งเล็กน้อย */
                padding: 20px !important;
                box-shadow: none !important;
                border: 1px solid #ddd !important; /* ใส่ขอบบางๆ ให้ดูเป็นเอกสาร */
            }
        }
    </style>
</head>
<body>

    <div class="position-absolute top-0 end-0 p-3 no-print">
        <a href="login.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-lock"></i> เจ้าหน้าที่</a>
    </div>

    <div class="container container-search no-print">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-primary"><i class="fas fa-home"></i> ระบบตรวจสอบค่าใช้จ่ายบ้านพักครู</h2>
            <p class="text-muted">กรุณาเลือกบ้านเลขที่และเดือนที่ต้องการตรวจสอบ</p>
        </div>

        <div class="card shadow border-0">
            <div class="card-body p-4 bg-white rounded">
                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">บ้านพัก</label>
                            <select name="house_id" id="house_id" class="form-select" required>
                                <option value="">-- กรุณาเลือกบ้าน --</option>
                                <?php foreach ($houses as $h): ?>
                                    <option value="<?php echo htmlspecialchars($h['house_id']); ?>" <?php echo (isset($_POST['house_id']) && $_POST['house_id'] == $h['house_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($h['house_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">เดือน</label>
                            <select name="month" id="month" class="form-select" required>
                                <?php
                                $thMonth = [1=>"มกราคม",2=>"กุมภาพันธ์",3=>"มีนาคม",4=>"เมษายน",5=>"พฤษภาคม",6=>"มิถุนายน",7=>"กรกฎาคม",8=>"สิงหาคม",9=>"กันยายน",10=>"ตุลาคม",11=>"พฤศจิกายน",12=>"ธันวาคม"];
                                $curM = isset($_POST['month']) ? intval($_POST['month']) : date('n');
                                foreach($thMonth as $k=>$v):
                                    echo "<option value='$k' " . ($k==$curM?'selected':'') . ">$v</option>";
                                endforeach;
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">ปี (ค.ศ.)</label>
                            <select name="year" id="year" class="form-select" required>
                                <?php 
                                $curY = isset($_POST['year']) ? intval($_POST['year']) : date('Y');
                                for($i=$curY;$i>=$curY-2;$i--):
                                    echo "<option value='$i' " . ($i==$curY?'selected':'') . ">$i</option>";
                                endfor;
                                ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" name="search" value="1" class="btn btn-primary w-100 fw-bold">
                                <i class="fas fa-search"></i> ค้นหา
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- =============== แสดงข้อความแจ้งข้อผิดพลาด =============== -->
    <?php if ($bill_error): ?>
        <div class="container mt-4">
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($bill_error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="invoice-wrapper mb-5">
        <div id="invoiceArea" class="invoice-box" <?php echo (!$bill_data) ? 'style="display: none;"' : ''; ?>> 
            
            <div class="invoice-header d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="fw-bold text-dark">ใบแจ้งหนี้ / Invoice</h4>
                    <p class="mb-0 text-muted">วิทยาลัยเทคนิคลพบุรี</p>
                    <small class="text-muted">งานบ้านพักครูและสวัสดิการ</small>
                </div>
                <div class="text-end">
                    <h5 class="mb-1 text-primary fw-bold" id="disp_house">
                        บ้านพัก: <?php echo $bill_data ? htmlspecialchars($bill_data['house_name']) : '-'; ?>
                    </h5>
                    <span class="badge bg-light text-dark border">
                        ประจำเดือน: <b id="disp_period">
                            <?php 
                            if ($bill_data && $bill_calc) {
                                echo htmlspecialchars($bill_calc['month_text']) . ' ' . (isset($_POST['year']) ? htmlspecialchars($_POST['year']) : '-');
                            } else {
                                echo '-';
                            }
                            ?>
                        </b>
                    </span>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-8">
                    <strong>ผู้พักอาศัย:</strong> 
                    <span id="disp_name" class="ms-2 border-bottom pb-1 px-3">
                        <?php echo $bill_data ? htmlspecialchars($bill_data['fullname']) : '-'; ?>
                    </span>
                </div>
                <div class="col-md-4 text-end">
                    <strong>วันที่ออกบิล:</strong> <?php echo date('d/m/Y'); ?>
                </div>
            </div>

            <table class="table table-bordered table-invoice">
                <thead>
                    <tr>
                        <th width="40%">รายการ (Description)</th>
                        <th width="15%">เลขก่อน</th>
                        <th width="15%">เลขหลัง</th>
                        <th width="15%">หน่วยที่ใช้</th>
                        <th width="15%">รวมเงิน (บาท)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 p-2 rounded me-2"><i class="fas fa-bolt text-warning"></i></div>
                                <div>
                                    <strong>ค่ากระแสไฟฟ้า</strong><br>
                                    <small class="text-muted">อัตรา <span id="rate_elec">
                                        <?php echo $bill_calc ? number_format($bill_calc['e_rate'], 2) : '0'; ?>
                                    </span> บ./หน่วย</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center" id="e_prev">
                            <?php echo $bill_data && $bill_data['e_prev'] !== null ? htmlspecialchars($bill_data['e_prev']) : '-'; ?>
                        </td>
                        <td class="text-center" id="e_curr">
                            <?php echo $bill_data && $bill_data['e_curr'] !== null ? htmlspecialchars($bill_data['e_curr']) : '-'; ?>
                        </td>
                        <td class="text-center fw-bold" id="e_unit">
                            <?php echo $bill_data && $bill_data['e_units'] !== null ? htmlspecialchars($bill_data['e_units']) : '0'; ?>
                        </td>
                        <td class="text-end fw-bold" id="e_price">
                            <?php 
                            if ($bill_data && $bill_calc && intval($bill_data['e_units'] ?? 0) > 0) {
                                echo number_format($bill_calc['e_total'], 2);
                            } else {
                                echo '0.00';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 p-2 rounded me-2"><i class="fas fa-tint text-primary"></i></div>
                                <div>
                                    <strong>ค่าน้ำประปา</strong><br>
                                    <small class="text-muted">อัตรา <span id="rate_water">
                                        <?php echo $bill_calc ? number_format($bill_calc['w_rate'], 2) : '0'; ?>
                                    </span> บ./หน่วย</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center" id="w_prev">
                            <?php echo $bill_data && $bill_data['w_prev'] !== null ? htmlspecialchars($bill_data['w_prev']) : '-'; ?>
                        </td>
                        <td class="text-center" id="w_curr">
                            <?php echo $bill_data && $bill_data['w_curr'] !== null ? htmlspecialchars($bill_data['w_curr']) : '-'; ?>
                        </td>
                        <td class="text-center fw-bold" id="w_unit">
                            <?php echo $bill_data && $bill_data['w_units'] !== null ? htmlspecialchars($bill_data['w_units']) : '0'; ?>
                        </td>
                        <td class="text-end fw-bold" id="w_price">
                            <?php 
                            if ($bill_data && $bill_calc && intval($bill_data['w_units'] ?? 0) > 0) {
                                echo number_format($bill_calc['w_total'], 2);
                            } else {
                                echo '0.00';
                            }
                            ?>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end border-0 pt-4 align-middle">ยอดรวมสุทธิ (Grand Total)</td>
                        <td class="text-end border-0 pt-4">
                            <div class="d-inline-flex align-items-baseline">
                                <span class="h3 fw-bold text-success mb-0" id="grand_total">
                                    <?php 
                                    if ($bill_data && $bill_calc) {
                                        echo number_format($bill_calc['grand_total'], 2);
                                    } else {
                                        echo '0.00';
                                    }
                                    ?>
                                </span>
                                <span class="text-muted ms-2" style="font-size: 1.1rem;">บาท</span>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <div class="mt-5 p-3 bg-light border rounded text-center">
                <small class="text-muted">หากมีข้อสงสัยกรุณาติดต่อเจ้าหน้าที่ดูแลระบบ</small>
            </div>

            <div class="text-center mt-4 no-print">
                <button class="btn btn-secondary btn-lg me-2 shadow-sm" onclick="window.print()">
                    <i class="fas fa-print"></i> พิมพ์ใบแจ้งหนี้
                </button>
                <button class="btn btn-light text-danger btn-lg shadow-sm" onclick="document.getElementById('invoiceArea').style.display='none'">
                    ปิดหน้าต่าง
                </button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ไม่จำเป็นต้องมี JavaScript สำหรับการค้นหาเพราะใช้ PHP POST โดยตรง
    </script>
</body>
</html>