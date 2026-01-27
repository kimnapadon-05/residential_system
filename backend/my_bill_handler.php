<?php
header('Content-Type: application/json');
require_once realpath(__DIR__ . '/../Database/config.php');

$action = $_POST['action'] ?? '';

if ($action == 'get_houses') {
    $stmt = $conn->query("SELECT house_id, house_name FROM house_info ORDER BY house_name ASC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($action == 'get_bill') {
    $house_id = intval($_POST['house_id'] ?? 0);
    $month = intval($_POST['month'] ?? 0);
    $year = intval($_POST['year'] ?? 0);

    if (!$house_id || !$month || !$year) {
        echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
        exit;
    }

    // ✅ 1. ดึงราคา
    $stmtRate = $conn->prepare("SELECT elec_rate, water_rate FROM utility_rates WHERE bill_month = :month AND bill_year = :year");
    $stmtRate->execute([':month' => $month, ':year' => $year]);
    $rates = $stmtRate->fetch(PDO::FETCH_ASSOC);

    $ELEC_RATE = $rates ? floatval($rates['elec_rate']) : 7.0;
    $WATER_RATE = $rates ? floatval($rates['water_rate']) : 15.0;

    // ✅ 2. ดึงข้อมูลบิล (ผู้พักอาศัย, ค่ามิเตอร์, ข้อมูลเลขปัจจุบันและก่อนหน้า)
    try {
        $sql = "SELECT h.house_name, 
                    CONCAT(p.person_fname, ' ', p.person_lname) as fullname,
                    COALESCE(er.previous_reading, 0) as e_prev, 
                    COALESCE(er.current_reading, 0) as e_curr, 
                    COALESCE(er.usage_units, 0) as e_units,
                    COALESCE(wr.previous_reading, 0) as w_prev, 
                    COALESCE(wr.current_reading, 0) as w_curr, 
                    COALESCE(wr.usage_units, 0) as w_units
                FROM residency_history rh
                INNER JOIN house_info h ON rh.house_id = h.house_id
                INNER JOIN person_info p ON rh.person_id = p.person_id
                LEFT JOIN electric_readings er ON rh.history_id = er.history_id 
                    AND er.bill_month = :month AND er.bill_year = :year
                LEFT JOIN water_readings wr ON rh.history_id = wr.history_id 
                    AND wr.bill_month = :month AND wr.bill_year = :year
                WHERE rh.house_id = :house_id
                AND (rh.move_out_date IS NULL 
                    OR (YEAR(rh.move_out_date) = :year AND MONTH(rh.move_out_date) >= :month))
                ORDER BY rh.history_id DESC 
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':house_id' => $house_id, 
            ':month' => $month, 
            ':year' => $year
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $e_units = floatval($result['e_units'] ?? 0);
            $w_units = floatval($result['w_units'] ?? 0);
            $e_total = round($e_units * $ELEC_RATE, 2);
            $w_total = round($w_units * $WATER_RATE, 2);
            
            echo json_encode([
                'status' => 'success',
                'data' => $result,
                'calc' => [
                    'e_rate' => number_format($ELEC_RATE, 2),
                    'w_rate' => number_format($WATER_RATE, 2),
                    'e_total' => $e_total,
                    'w_total' => $w_total,
                    'grand_total' => round($e_total + $w_total, 2)
                ]
            ]);
        } else {
            echo json_encode(['status' => 'not_found']);
        }
    } catch (PDOException $e) {
        error_log("Bill handler error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>