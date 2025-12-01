<?php
header('Content-Type: application/json');
require_once '../Database/config.php';

$action = $_POST['action'] ?? '';

// 1. อ่านข้อมูลลงตาราง (ตัด meter_type / type_name ออก)
if ($action == 'read') {
    try {
        $stmt = $conn->prepare("
            SELECT m.*, b.brand_name
            FROM meter_info m
            LEFT JOIN meter_brand b ON m.brand_id = b.brand_id
            ORDER BY m.meter_id DESC
        ");
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode([]); 
    }
    exit;
}

// 2. ดึงตัวเลือก Dropdown (เหลือแค่ Brand)
if ($action == 'get_options') {
    try {
        $brands = $conn->query("SELECT * FROM meter_brand")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['brands' => $brands]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database Error: ' . $e->getMessage()]);
    }
    exit;
}

// 3. บันทึกข้อมูล (ตัด type_id ออก)
if ($action == 'create' || $action == 'update') {
    $sql = ($action == 'create') 
        ? "INSERT INTO meter_info (meter_serial, meter_type, brand_id) VALUES (:serial, :m_type, :b_id)"
        : "UPDATE meter_info SET meter_serial=:serial, meter_type=:m_type, brand_id=:b_id WHERE meter_id=:id";
    
    try {
        $stmt = $conn->prepare($sql);
        $params = [
            ':serial' => $_POST['meter_serial'],
            ':m_type' => $_POST['meter_type'],
            ':b_id'   => $_POST['brand_id']
        ];
        if($action == 'update') $params[':id'] = $_POST['meter_id'];
        
        $stmt->execute($params);
        echo json_encode(['status' => 'success', 'message' => 'บันทึกสำเร็จ']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// 4. ลบข้อมูล
if ($action == 'delete') {
    try {
        $stmt = $conn->prepare("DELETE FROM meter_info WHERE meter_id = :id");
        $stmt->execute([':id' => $_POST['meter_id']]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) { echo json_encode(['status' => 'error']); }
}
?>