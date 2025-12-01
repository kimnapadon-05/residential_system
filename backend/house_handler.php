<?php
header('Content-Type: application/json');
require_once '../Database/config.php';

$action = $_POST['action'] ?? '';

// 1. Read (ดึงข้อมูลทั้งหมด)
if ($action == 'read') {
    $stmt = $conn->prepare("
        SELECT h.house_id, h.house_name, l.location_name, l.location_id 
        FROM house_info h 
        LEFT JOIN house_location l ON h.location_id = l.location_id 
        ORDER BY h.house_id ASC
    ");
    $stmt->execute();
    $houses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($houses);
    exit;
}

// 1.1 Get Locations (ดึงข้อมูลโซนที่พักมาใส่ Dropdown)
if ($action == 'get_locations') {
    $stmt = $conn->prepare("SELECT * FROM house_location");
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// 2. Create (เพิ่มข้อมูล)
if ($action == 'create') {
    try {
        $stmt = $conn->prepare("INSERT INTO house_info (house_name, location_id) VALUES (:name, :loc)");
        $stmt->execute([
            ':name' => $_POST['house_name'],
            ':loc' => $_POST['location_id']
        ]);
        echo json_encode(['status' => 'success', 'message' => 'เพิ่มข้อมูลสำเร็จ']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// 3. Update (แก้ไขข้อมูล)
if ($action == 'update') {
    try {
        $stmt = $conn->prepare("UPDATE house_info SET house_name = :name, location_id = :loc WHERE house_id = :id");
        $stmt->execute([
            ':name' => $_POST['house_name'],
            ':loc' => $_POST['location_id'],
            ':id' => $_POST['house_id']
        ]);
        echo json_encode(['status' => 'success', 'message' => 'แก้ไขข้อมูลสำเร็จ']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// 4. Delete (ลบข้อมูล)
if ($action == 'delete') {
    try {
        $stmt = $conn->prepare("DELETE FROM house_info WHERE house_id = :id");
        $stmt->execute([':id' => $_POST['house_id']]);
        echo json_encode(['status' => 'success', 'message' => 'ลบข้อมูลสำเร็จ']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
?>