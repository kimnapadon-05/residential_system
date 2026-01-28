<?php
require_once 'Database/config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// 1. ดึงรายชื่อโซน (Location) มาใส่ Dropdown
if ($action == 'get_locations') {
    // สมมติชื่อตารางคือ house_location และมี id กับ location_name
    $stmt = $conn->query("SELECT location_id, location_name FROM house_location ORDER BY location_id ASC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// 2. อ่านข้อมูลเรทราคา (JOIN เพื่อเอาชื่อโซนมาแสดง)
if ($action == 'read') {
    $sql = "SELECT u.*, l.location_name 
            FROM utility_rates u
            JOIN house_location l ON u.location_id = l.location_id
            ORDER BY u.bill_year DESC, u.bill_month DESC, u.location_id ASC";
    $stmt = $conn->query($sql);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// 3. บันทึกข้อมูล (เพิ่ม location_id)
if ($action == 'save') {
    $loc = $_POST['location_id'];
    $m = $_POST['month'];
    $y = $_POST['year'];
    $e_rate = $_POST['elec_rate'];
    $w_rate = $_POST['water_rate'];

    try {
        // เพิ่ม location_id เข้าไปใน SQL
        $sql = "INSERT INTO utility_rates (location_id, bill_month, bill_year, elec_rate, water_rate) 
                VALUES (:loc, :m, :y, :e, :w)
                ON DUPLICATE KEY UPDATE elec_rate = :e, water_rate = :w";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':loc' => $loc,
            ':m' => $m, 
            ':y' => $y, 
            ':e' => $e_rate, 
            ':w' => $w_rate
        ]);
        echo json_encode(['status' => 'success', 'message' => 'บันทึกอัตราค่าบริการเรียบร้อย']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
?>