<?php
header('Content-Type: application/json');
require_once 'Database/config.php';

$action = $_POST['action'] ?? '';

if ($action == 'get_houses') {
    try {
        $stmt = $conn->query("SELECT house_id, house_name FROM house_info ORDER BY house_name ASC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action == 'get_bill') {
    $house_id = $_POST['house_id'];
    $month = $_POST['month'];
    $year = $_POST['year'];

    // 1. ดึงราคา
    $stmtRate = $conn->prepare("SELECT elec_rate, water_rate FROM utility_rates WHERE bill_month = :m AND bill_year = :y");
    $stmtRate->execute([':m' => $month, ':y' => $year]);
    $rates = $stmtRate->fetch(PDO::FETCH_ASSOC);

    $ELEC_RATE = $rates ? $rates['elec_rate'] : 7;
    $WATER_RATE = $rates ? $rates['water_rate'] : 15;

    // 2. ดึงบิล (ใช้ Query เดิมจากคำตอบก่อนหน้าได้เลย)
    try {
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
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $e_total = intval($result['e_units']??0) * $ELEC_RATE;
            $w_total = intval($result['w_units']??0) * $WATER_RATE;
            
            echo json_encode([
                'status' => 'success',
                'data' => $result,
                'calc' => [
                    'e_rate' => $ELEC_RATE, 'w_rate' => $WATER_RATE,
                    'e_total' => $e_total, 'w_total' => $w_total,
                    'grand_total' => $e_total + $w_total
                ]
            ]);
        } else { echo json_encode(['status' => 'not_found']); }
    } catch (PDOException $e) { echo json_encode(['status' => 'error', 'message' => $e->getMessage()]); }
}
?>