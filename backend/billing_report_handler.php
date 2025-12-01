<?php
header('Content-Type: application/json');
require_once '../Database/config.php';

$action = $_POST['action'] ?? '';

if ($action == 'get_report') {
    $month = $_POST['month'];
    $year = $_POST['year'];

    // สร้างวันที่เริ่มต้นเดือน
    $startDate = "$year-$month-01";

    // 1. ดึงราคาต่อหน่วยของเดือนนั้นๆ
    $stmtRate = $conn->prepare("SELECT elec_rate, water_rate FROM utility_rates WHERE bill_month = :m AND bill_year = :y");
    $stmtRate->execute([':m' => $month, ':y' => $year]);
    $rates = $stmtRate->fetch(PDO::FETCH_ASSOC);

    // ถ้าไม่มี ให้ใช้ค่า Default
    $ELEC_RATE = $rates ? $rates['elec_rate'] : 0;
    $WATER_RATE = $rates ? $rates['water_rate'] : 0;

    try {
        $sql = "SELECT rh.history_id, h.house_name, 
                    CONCAT(p.person_fname, ' ', p.person_lname) as fullname,
                    er.usage_units as elec_units,
                    wr.usage_units as water_units
                FROM residency_history rh
                JOIN house_info h ON rh.house_id = h.house_id
                JOIN person_info p ON rh.person_id = p.person_id
                LEFT JOIN electric_readings er ON rh.history_id = er.history_id AND er.bill_month = :m AND er.bill_year = :y
                LEFT JOIN water_readings wr ON rh.history_id = wr.history_id AND wr.bill_month = :m AND wr.bill_year = :y
                WHERE 
                    rh.move_in_date <= LAST_DAY(:start_date)
                    AND (rh.move_out_date IS NULL OR rh.move_out_date >= :start_date)
                ORDER BY h.house_id ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':m' => $month, ':y' => $year, ':start_date' => $startDate]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $reportData = [];
        $total_elec = 0; $total_water = 0; $grand_total = 0;

        foreach ($rows as $r) {
            $e_units = intval($r['elec_units'] ?? 0);
            $w_units = intval($r['water_units'] ?? 0);
            
            $e_price = $e_units * $ELEC_RATE;
            $w_price = $w_units * $WATER_RATE;
            $total = $e_price + $w_price;

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
            'rates' => [
                'elec' => $ELEC_RATE,
                'water' => $WATER_RATE
            ],
            
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