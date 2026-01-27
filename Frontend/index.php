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
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">บ้านพัก</label>
                        <select id="house_id" class="form-select">
                            <option value="">-- กำลังโหลดรายชื่อ --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">เดือน</label>
                        <select id="month" class="form-select">
                            <?php
                            $thMonth = [1=>"มกราคม",2=>"กุมภาพันธ์",3=>"มีนาคม",4=>"เมษายน",5=>"พฤษภาคม",6=>"มิถุนายน",7=>"กรกฎาคม",8=>"สิงหาคม",9=>"กันยายน",10=>"ตุลาคม",11=>"พฤศจิกายน",12=>"ธันวาคม"];
                            $curM = date('n');
                            foreach($thMonth as $k=>$v) echo "<option value='$k' ".($k==$curM?'selected':'').">$v</option>";
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">ปี (ค.ศ.)</label>
                        <select id="year" class="form-select">
                            <?php 
                            $curY = date('Y');
                            for($i=$curY;$i>=$curY-2;$i--) echo "<option value='$i'>$i</option>"; 
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100 fw-bold" onclick="checkBill()">
                            <i class="fas fa-search"></i> ค้นหา
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="invoice-wrapper mb-5">
        <div id="invoiceArea" class="invoice-box"> 
            
            <div class="invoice-header d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="fw-bold text-dark">ใบแจ้งหนี้ / Invoice</h4>
                    <p class="mb-0 text-muted">วิทยาลัยเทคนิคลพบุรี</p>
                    <small class="text-muted">งานบ้านพักครูและสวัสดิการ</small>
                </div>
                <div class="text-end">
                    <h5 class="mb-1 text-primary fw-bold" id="disp_house">บ้านพัก: -</h5>
                    <span class="badge bg-light text-dark border">ประจำเดือน: <b id="disp_period">-</b></span>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-8">
                    <strong>ผู้พักอาศัย:</strong> <span id="disp_name" class="ms-2 border-bottom pb-1 px-3">-</span>
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
                                    <small class="text-muted">อัตรา <span id="rate_elec">0</span> บ./หน่วย</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center" id="e_prev">-</td>
                        <td class="text-center" id="e_curr">-</td>
                        <td class="text-center fw-bold" id="e_unit">-</td>
                        <td class="text-end fw-bold" id="e_price">-</td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 p-2 rounded me-2"><i class="fas fa-tint text-primary"></i></div>
                                <div>
                                    <strong>ค่าน้ำประปา</strong><br>
                                    <small class="text-muted">อัตรา <span id="rate_water">0</span> บ./หน่วย</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center" id="w_prev">-</td>
                        <td class="text-center" id="w_curr">-</td>
                        <td class="text-center fw-bold" id="w_unit">-</td>
                        <td class="text-end fw-bold" id="w_price">-</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end border-0 pt-4 align-middle">ยอดรวมสุทธิ (Grand Total)</td>
                        <td class="text-end border-0 pt-4">
                            <div class="d-inline-flex align-items-baseline">
                                <span class="h3 fw-bold text-success mb-0" id="grand_total">0.00</span>
                                <span class="text-muted ms-2" style="font-size: 1.1rem;">บาท</span>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <div class="mt-5 p-3 bg-light border rounded text-center">
                <!-- <p class="mb-1 text-danger fw-bold"><i class="fas fa-exclamation-circle"></i> กรุณาชำระเงินภายในวันที่ 5 ของเดือนถัดไป</p> -->
                <small class="text-muted">หากมีข้อสงสัยกรุณาติดต่อเจ้าหน้าที่ดูแลระบบ</small>
            </div>

            <div class="text-center mt-4 no-print">
                <button class="btn btn-secondary btn-lg me-2 shadow-sm" onclick="window.print()">
                    <i class="fas fa-print"></i> พิมพ์ใบแจ้งหนี้
                </button>
                <button class="btn btn-light text-danger btn-lg shadow-sm" onclick="$('#invoiceArea').slideUp()">
                    ปิดหน้าต่าง
                </button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../script/my_bill.js"></script>
</body>
</html>