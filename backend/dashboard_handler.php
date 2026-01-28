<?php
header('Content-Type: application/json');
require_once '../Database/config.php';

$action = $_POST['action'] ?? '';

if ($action == 'get_stats') {
    try {
        // 1. นับจำนวนบ้านทั้งหมด
        $stmt = $conn->query("SELECT COUNT(*) as total FROM house_info");
        $total_houses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // 2. นับจำนวนบ้านที่มีคนอยู่ (Active)
        $stmt = $conn->query("SELECT COUNT(DISTINCT house_id) as occupied FROM residency_history WHERE move_out_date IS NULL");
        $occupied_houses = $stmt->fetch(PDO::FETCH_ASSOC)['occupied'];

        // 3. คำนวณบ้านว่าง
        $vacant_houses = $total_houses - $occupied_houses;

        // 4. นับจำนวนประชากรปัจจุบัน
        $stmt = $conn->query("SELECT COUNT(*) as total_people FROM person_info WHERE status = 1");
        $total_people = $stmt->fetch(PDO::FETCH_ASSOC)['total_people'];

        // 5. ข้อมูลกราฟย้อนหลัง 6 เดือน (ไฟฟ้า)
        // Query นี้จะดึงหน่วยการใช้รวม (sum usage) แยกตามเดือน
        $stmt = $conn->query("
            SELECT CONCAT(bill_month, '/', bill_year) as month_label, SUM(usage_units) as total_usage
            FROM electric_readings 
            GROUP BY bill_year, bill_month 
            ORDER BY bill_year DESC, bill_month DESC 
            LIMIT 6
        ");
        $elec_chart = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC)); // กลับด้านให้เดือนเก่าอยู่ซ้าย

        // 6. ข้อมูลกราฟย้อนหลัง 6 เดือน (น้ำ)
        $stmt = $conn->query("
            SELECT CONCAT(bill_month, '/', bill_year) as month_label, SUM(usage_units) as total_usage
            FROM water_readings 
            GROUP BY bill_year, bill_month 
            ORDER BY bill_year DESC, bill_month DESC 
            LIMIT 6
        ");
        $water_chart = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));

        // 7. รายการย้ายเข้าล่าสุด 5 รายการ
        $stmt = $conn->query("
            SELECT p.person_fname, p.person_lname, h.house_name, r.move_in_date
            FROM residency_history r
            JOIN person_info p ON r.person_id = p.person_id
            JOIN house_info h ON r.house_id = h.house_id
            ORDER BY r.move_in_date DESC LIMIT 5
        ");
        $recent_movein = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'stats' => [
                'total_houses' => $total_houses,
                'occupied' => $occupied_houses,
                'vacant' => $vacant_houses,
                'people' => $total_people
            ],
            'chart' => [
                'electric' => $elec_chart,
                'water' => $water_chart
            ],
            'recent_movein' => $recent_movein
        ]);

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
?>