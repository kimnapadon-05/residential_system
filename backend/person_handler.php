<?php
header('Content-Type: application/json');
require_once '../Database/config.php';

$action = $_POST['action'] ?? '';

// ดึงข้อมูลคน + ชื่อตำแหน่ง
if ($action == 'read') {
    $stmt = $conn->prepare("
        SELECT p.*, pos.position_name 
        FROM person_info p 
        LEFT JOIN position_info pos ON p.position_id = pos.position_id 
        WHERE p.status = 1 
        ORDER BY p.person_id DESC
    ");
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ดึงข้อมูลตำแหน่ง (ใส่ Dropdown)
if ($action == 'get_positions') {
    $stmt = $conn->prepare("SELECT * FROM position_info");
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// เพิ่มข้อมูล
if ($action == 'create') {
    try {
        $stmt = $conn->prepare("INSERT INTO person_info (person_fname, person_lname, position_id) VALUES (:fname, :lname, :pos)");
        $stmt->execute([
            ':fname' => $_POST['person_fname'],
            ':lname' => $_POST['person_lname'],
            ':pos' => $_POST['position_id']
        ]);
        echo json_encode(['status' => 'success', 'message' => 'เพิ่มข้อมูลสำเร็จ']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// แก้ไขข้อมูล
if ($action == 'update') {
    try {
        $stmt = $conn->prepare("UPDATE person_info SET person_fname = :fname, person_lname = :lname, position_id = :pos WHERE person_id = :id");
        $stmt->execute([
            ':fname' => $_POST['person_fname'],
            ':lname' => $_POST['person_lname'],
            ':pos' => $_POST['position_id'],
            ':id' => $_POST['person_id']
        ]);
        echo json_encode(['status' => 'success', 'message' => 'แก้ไขข้อมูลสำเร็จ']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// ลบข้อมูล (Soft Delete เปลี่ยน status เป็น 0)
if ($action == 'delete') {
    try {
        $stmt = $conn->prepare("UPDATE person_info SET status = 0 WHERE person_id = :id");
        $stmt->execute([':id' => $_POST['person_id']]);
        echo json_encode(['status' => 'success', 'message' => 'ลบข้อมูลสำเร็จ']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
?>