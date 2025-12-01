<?php
header('Content-Type: application/json');
require_once '../Database/config.php';

$action = $_POST['action'] ?? '';

// --- ตั้งค่าราคาต่อหน่วย (แก้ไขตรงนี้) ---
$ELEC_RATE = 7;   // ค่าไฟหน่วยละ 7 บาท
$WATER_RATE = 15; // ค่าน้ำหน่วยละ 15 บาท
// -------------------------------------

if ($action == 'get_report') {
    $month = $_POST['month'];
    $year = $_POST['year'];

    try {
        // ดึงข้อมูลผู้พักอาศัย พร้อม Join ตารางค่าไฟ (er) และค่าน้ำ (wr) ตามเดือนที่เลือก
        $sql = "SELECT 
                    rh.history_id, h.house_name, 
                    CONCAT(p.person_fname, ' ', p.person_lname) as fullname,
                    er.current_reading as elec_curr, er.previous_reading as elec_prev, er.usage_units as elec_units,
                    wr.current_reading as water_curr, wr.previous_reading as water_prev, wr.usage_units as water_units
                FROM residency_history rh
                JOIN house_info h ON rh.house_id = h.house_id
                JOIN person_info p ON rh.person_id = p.person_id
                
                -- Join ค่าไฟ
                LEFT JOIN electric_readings er 
                    ON rh.history_id = er.history_id 
                    AND er.bill_month = :m AND er.bill_year = :y
                
                -- Join ค่าน้ำ
                LEFT JOIN water_readings wr 
                    ON rh.history_id = wr.history_id 
                    AND wr.bill_month = :m AND wr.bill_year = :y
                
                WHERE rh.move_out_date IS NULL 
                   OR (MONTH(rh.move_out_date) = :m AND YEAR(rh.move_out_date) = :y)
                ORDER BY h.house_id ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':m' => $month, ':y' => $year]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $reportData = [];
        $total_elec = 0;
        $total_water = 0;
        $grand_total = 0;

        foreach ($rows as $r) {
            // คำนวณค่าใช้จ่าย (ถ้าไม่มีหน่วยใช้ ให้เป็น 0)
            $e_units = intval($r['elec_units'] ?? 0);
            $w_units = intval($r['water_units'] ?? 0);

            $e_price = $e_units * $ELEC_RATE;
            $w_price = $w_units * $WATER_RATE;
            $total = $e_price + $w_price;

            // บวกยอดรวมทั้งหมด
            $total_elec += $e_price;
            $total_water += $w_price;
            $grand_total += $total;

            $reportData[] = [
                'house_name' => $r['house_name'],
                'fullname' => $r['fullname'],
                'elec_units' => $e_units,
                'elec_price' => $e_price,
                'water_units' => $w_units,
                'water_price' => $w_price,
                'total_price' => $total
            ];
        }

        echo json_encode([
            'status' => 'success',
            'data' => $reportData,
            'summary' => [
                'total_elec' => number_format($total_elec, 2),
                'total_water' => number_format($total_water, 2),
                'grand_total' => number_format($grand_total, 2)
            ]
        ]);

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>