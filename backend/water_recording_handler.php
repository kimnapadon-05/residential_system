<?php
header('Content-Type: application/json');
require_once realpath(__DIR__ . '/../Database/config.php');

$action = $_POST['action'] ?? '';

if ($action == 'load_sheet') {
    $month = $_POST['month'];
    $year = $_POST['year'];
    $startDate = "$year-$month-01";
    
    // 1. ดึงข้อมูลผู้เช่าที่มีมิเตอร์น้ำ (Water)
    $sql = "SELECT rh.history_id, rh.person_id, rh.house_id, rh.starting_water_reading,
            h.house_name, 
            CONCAT(p.person_fname, ' ', p.person_lname) as fullname,
            m.meter_serial
            FROM residency_history rh
            JOIN house_info h ON rh.house_id = h.house_id
            JOIN person_info p ON rh.person_id = p.person_id
            JOIN meter_info m ON rh.water_meter_id = m.meter_id
            WHERE rh.water_meter_id IS NOT NULL 
            AND (rh.move_out_date IS NULL OR rh.move_out_date >= :startDate)
            ORDER BY h.house_id ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':startDate' => $startDate]);
    $residents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];
    foreach ($residents as $row) {
        // 2. ตรวจสอบว่าเดือนนี้จดไปหรือยัง? (ตาราง water_readings)
        $stmt_curr = $conn->prepare("SELECT * FROM water_readings WHERE history_id = :history_id AND bill_month = :month AND bill_year = :year");
        $stmt_curr->execute([':history_id' => $row['history_id'], ':month' => $month, ':year' => $year]);
        $current_record = $stmt_curr->fetch(PDO::FETCH_ASSOC);

        // 3. หาเลขมิเตอร์ครั้งก่อน
        $prev_reading = 0;
        if ($current_record) {
            $prev_reading = $current_record['previous_reading'];
        } else {
            // หา reading ล่าสุดก่อนหน้านี้
            $stmt_prev = $conn->prepare("SELECT current_reading FROM water_readings WHERE history_id = :history_id ORDER BY reading_date DESC LIMIT 1");
            $stmt_prev->execute([':history_id' => $row['history_id']]);
            $last_bill = $stmt_prev->fetch(PDO::FETCH_ASSOC);

            if ($last_bill) {
                $prev_reading = $last_bill['current_reading'];
            } else {
                $prev_reading = $row['starting_water_reading']; // ใช้เลขเริ่มต้นตอนย้ายเข้า
            }
        }

        $data[] = [
            'history_id' => $row['history_id'],
            'house_name' => $row['house_name'],
            'fullname'   => $row['fullname'],
            'meter_serial' => $row['meter_serial'],
            'prev_reading' => $prev_reading,
            'current_reading' => $current_record ? $current_record['current_reading'] : '',
            'usage' => $current_record ? $current_record['usage_units'] : '',
            'reading_id' => $current_record ? $current_record['reading_id'] : null,
            'is_saved' => $current_record ? true : false
        ];
    }

    echo json_encode($data);
    exit;
}

if ($action == 'save_reading') {
    try {
        $history_id = $_POST['history_id'];
        $month = $_POST['month'];
        $year = $_POST['year'];
        $prev = $_POST['prev_reading'];
        $curr = $_POST['current_reading'];
        $usage = $curr - $prev;
        $date = date('Y-m-d');

        if ($curr < $prev) {
            echo json_encode(['status' => 'error', 'message' => 'เลขมิเตอร์ปัจจุบันต้องไม่ต่ำกว่าครั้งก่อน']);
            exit;
        }

        // Check Update or Insert (ตาราง water_readings)
        $check = $conn->prepare("SELECT reading_id FROM water_readings WHERE history_id = :history_id AND bill_month = :month AND bill_year = :year");
        $check->execute([':history_id' => $history_id, ':month' => $month, ':year' => $year]);
        $existing = $check->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $stmt = $conn->prepare("UPDATE water_readings SET current_reading = :current_reading, usage_units = :usage_units, reading_date = :reading_date WHERE reading_id = :reading_id");
            $stmt->execute([':current_reading' => $curr, ':usage_units' => $usage, ':reading_date' => $date, ':reading_id' => $existing['reading_id']]);
        } else {
            $stmt = $conn->prepare("INSERT INTO water_readings (history_id, bill_month, bill_year, reading_date, previous_reading, current_reading, usage_units) 
                                    VALUES (:history_id, :month, :year, :date, :prev_reading, :current_reading, :usage_units)");
            $stmt->execute([
                ':history_id' => $history_id, ':month' => $month, ':year' => $year, 
                ':date' => $date, ':prev_reading' => $prev, ':current_reading' => $curr, ':usage_units' => $usage
            ]);
        }

        echo json_encode(['status' => 'success', 'message' => 'บันทึกค่าน้ำเรียบร้อย', 'usage' => $usage]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
?>