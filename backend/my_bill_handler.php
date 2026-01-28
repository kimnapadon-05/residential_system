<?php
// ไฟล์: backend/my_bill_handler.php

// ปิดการแสดงผล warnings ในหน้าผลลัพธ์ (ส่ง JSON เท่านั้น)
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();

require_once '../Database/config.php';

function send_json($data, $http_code = 200) {
    if (ob_get_length()) {
        @ob_clean();
    }
    http_response_code($http_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

$action = $_POST['action'] ?? '';

// --- ACTION 1: ดึงรายชื่อบ้าน ---
if ($action == 'get_houses') {
    try {
        $stmt = $conn->query("SELECT house_id, house_name FROM house_info ORDER BY house_name ASC");
        // ส่งเป็น Array ของ object (JavaScript จะเรียก data.forEach)
        send_json($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        send_json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

// --- ACTION 2: ดึงข้อมูลบิล ---
if ($action == 'get_bill') {
    $house_id = $_POST['house_id'] ?? null;
    $month = $_POST['month'] ?? null;
    $year = $_POST['year'] ?? null;

    // ตรวจสอบว่าส่งค่ามาครบไหม
    if (!$house_id || !$month || !$year) {
        send_json(['status' => 'error', 'message' => 'Missing parameters'], 400);
    }

    try {
        // 1. ดึงเรทราคา (Rate)
        $stmtRate = $conn->prepare("SELECT elec_rate, water_rate FROM utility_rates WHERE bill_month = :m AND bill_year = :y");
        $stmtRate->execute([':m' => $month, ':y' => $year]);
        $rates = $stmtRate->fetch(PDO::FETCH_ASSOC);

        // กำหนดค่า Default หากไม่เจอเรทในเดือนนั้น
        $ELEC_RATE = $rates ? $rates['elec_rate'] : 7;
        $WATER_RATE = $rates ? $rates['water_rate'] : 15;

        // 2. Query ดึงข้อมูลการใช้น้ำ/ไฟ และชื่อผู้พัก
        $sql = "SELECT h.house_name, CONCAT(p.person_fname, ' ', p.person_lname) as fullname,
                    er.previous_reading as e_prev, er.current_reading as e_curr, er.usage_units as e_units,
                    wr.previous_reading as w_prev, wr.current_reading as w_curr, wr.usage_units as w_units
                FROM residency_history rh
                JOIN house_info h ON rh.house_id = h.house_id
                JOIN person_info p ON rh.person_id = p.person_id
                LEFT JOIN electric_readings er ON rh.history_id = er.history_id AND er.bill_month = :m AND er.bill_year = :y
                LEFT JOIN water_readings wr ON rh.history_id = wr.history_id AND wr.bill_month = :m1 AND wr.bill_year = :y1
                WHERE rh.house_id = :hid
                AND (rh.move_out_date IS NULL OR (MONTH(rh.move_out_date) >= :m2 AND YEAR(rh.move_out_date) = :y2))
                ORDER BY rh.history_id DESC LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':hid' => $house_id,
            ':m' => $month, ':y' => $year,
            ':m1' => $month, ':y1' => $year,
            ':m2' => $month, ':y2' => $year
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // คำนวณยอดเงิน
            $e_total = intval($result['e_units'] ?? 0) * $ELEC_RATE;
            $w_total = intval($result['w_units'] ?? 0) * $WATER_RATE;

            send_json([
                'status' => 'success',
                'data' => $result,
                'calc' => [
                    'e_rate' => $ELEC_RATE, 'w_rate' => $WATER_RATE,
                    'e_total' => $e_total, 'w_total' => $w_total,
                    'grand_total' => $e_total + $w_total
                ]
            ]);
        } else {
            send_json(['status' => 'not_found']);
        }
    } catch (PDOException $e) {
        send_json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

// หากไม่มี action ที่รองรับ ให้ส่งค่าเริ่มต้น (empty array)
send_json([]);
