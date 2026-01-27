<?php
header('Content-Type: application/json');
require_once realpath(__DIR__ . '/../Database/config.php');

$action = $_POST['action'] ?? '';

// 1. Read (ดึงข้อมูลแบบแบ่งหน้า + ค้นหา)
if ($action == 'read') {
    // รับค่าหน้าปัจจุบัน (Default = 1) และจำนวนต่อหน้า (Default = 10)
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
    $search = isset($_POST['search']) ? $_POST['search'] : '';
    $offset = ($page - 1) * $limit;

    try {
        // เงื่อนไขหลัก: ดึงเฉพาะคนที่สถานะปกติ (status = 1)
        $whereSQL = " WHERE p.status = 1 ";
        $params = [];

        // ถ้ามีการค้นหา ให้เพิ่มเงื่อนไข AND
        if (!empty($search)) {
            $whereSQL .= " AND (p.person_fname LIKE :s OR p.person_lname LIKE :s OR pos.position_name LIKE :s) ";
            $params[':s'] = "%$search%";
        }

        // 1.1 นับจำนวนข้อมูลทั้งหมด (Count) เพื่อคำนวณหน้า
        $countSql = "SELECT COUNT(*) 
                     FROM person_info p 
                     LEFT JOIN position_info pos ON p.position_id = pos.position_id 
                     $whereSQL";
        $stmt = $conn->prepare($countSql);
        $stmt->execute($params);
        $totalRows = $stmt->fetchColumn();
        $totalPages = ceil($totalRows / $limit);

        // 1.2 ดึงข้อมูลจริง (Data) พร้อม LIMIT และ OFFSET
        $sql = "SELECT p.*, pos.position_name 
                FROM person_info p 
                LEFT JOIN position_info pos ON p.position_id = pos.position_id 
                $whereSQL
                ORDER BY p.person_id DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $conn->prepare($sql);
        
        // Bind ค่า Search
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        
        // Bind ค่า Pagination
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $people = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ส่งข้อมูลกลับไปให้ Frontend
        echo json_encode([
            'status' => 'success',
            'data' => $people,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_rows' => $totalRows
            ]
        ]);

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// 2. ดึงข้อมูลตำแหน่ง (สำหรับ Dropdown)
if ($action == 'get_positions') {
    try {
        $stmt = $conn->query("SELECT * FROM position_info ORDER BY position_id ASC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// 3. Create (เพิ่มข้อมูล)
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

// 4. Update (แก้ไขข้อมูล)
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

// 5. Delete (Soft Delete - เปลี่ยนสถานะเป็น 0)
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