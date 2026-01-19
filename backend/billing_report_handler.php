<?php
header('Content-Type: application/json');
require_once '../Database/config.php';

$action = $_POST['action'] ?? '';

if ($action == 'get_report') {
    $month = $_POST['month'];
    $year = $_POST['year'];
    $startDate = "$year-$month-01";

    try {
        // แก้ไข SQL: JOIN ตาราง utility_rates ให้ตรงกับ location_id ของบ้าน
        $sql = "SELECT 
                    rh.history_id, 
                    h.house_name, 
                    l.location_name, -- เพิ่มชื่อโซน
                    CONCAT(p.person_fname, ' ', p.person_lname) as fullname,
                    er.usage_units as elec_units,
                    wr.usage_units as water_units,
                    ur.elec_rate,  -- ดึงราคาจากตาราง rates โดยตรง
                    ur.water_rate
                FROM residency_history rh
                JOIN house_info h ON rh.house_id = h.house_id
                JOIN house_location l ON h.location_id = l.location_id -- เชื่อมบ้านกับโซน
                JOIN person_info p ON rh.person_id = p.person_id
                -- ดึงหน่วยที่จด
                LEFT JOIN electric_readings er ON rh.history_id = er.history_id AND er.bill_month = :m AND er.bill_year = :y
                LEFT JOIN water_readings wr ON rh.history_id = wr.history_id AND wr.bill_month = :m AND wr.bill_year = :y
                -- ดึงราคาตามโซน (สำคัญมาก!)
                LEFT JOIN utility_rates ur ON l.location_id = ur.location_id AND ur.bill_month = :m AND ur.bill_year = :y
                WHERE 
                    rh.move_in_date <= LAST_DAY(:start_date)
                    AND (rh.move_out_date IS NULL OR rh.move_out_date >= :start_date)
                ORDER BY l.location_id ASC, h.house_id ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':m' => $month, ':y' => $year, ':start_date' => $startDate]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $reportData = [];
        $total_elec = 0; $total_water = 0; $grand_total = 0;
        $missing_rates = false;

        foreach ($rows as $r) {
            $e_units = intval($r['elec_units'] ?? 0);
            $w_units = intval($r['water_units'] ?? 0);
            
            // ใช้ราคาตามโซนของแต่ละบ้าน
            $e_rate = floatval($r['elec_rate'] ?? 0);
            $w_rate = floatval($r['water_rate'] ?? 0);
            
            // เช็คว่าลืมตั้งราคาหรือไม่
            if ($r['elec_rate'] === null) $missing_rates = true;

            $e_price = $e_units * $e_rate;
            $w_price = $w_units * $w_rate;
            $total = $e_price + $w_price;

            $total_elec += $e_price;
            $total_water += $w_price;
            $grand_total += $total;

            $reportData[] = [
                'location_name' => $r['location_name'], // ส่งชื่อโซนไปด้วย
                'house_name' => $r['house_name'],
                'fullname' => $r['fullname'],
                'elec_units' => $e_units,
                'elec_rate' => $e_rate, // ส่งราคาต่อหน่วยของบ้านนี้
                'elec_price' => $e_price,
                'water_units' => $w_units,
                'water_rate' => $w_rate, // ส่งราคาต่อหน่วยของบ้านนี้
                'water_price' => $w_price,
                'total_price' => $total
            ];
        }

        echo json_encode([
            'status' => 'success',
            'missing_rates' => $missing_rates, // บอก Frontend ว่ามีโซนไหนลืมตั้งราคาไหม
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