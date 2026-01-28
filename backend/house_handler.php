<?php
header('Content-Type: application/json');
require_once 'Database/config.php';

$action = $_POST['action'] ?? '';

// 1. Read (ดึงข้อมูลแบบแบ่งหน้า + ค้นหา)
if ($action == 'read') {
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
    $search = isset($_POST['search']) ? $_POST['search'] : '';
    $offset = ($page - 1) * $limit;

    try {
        // สร้างเงื่อนไข WHERE สำหรับค้นหา
        $whereSQL = " WHERE 1=1 ";
        $params = [];
        if (!empty($search)) {
            $whereSQL .= " AND (h.house_name LIKE :s OR l.location_name LIKE :s) ";
            $params[':s'] = "%$search%";
        }

        // 1.1 นับจำนวนข้อมูลทั้งหมด (Count)
        $countSql = "SELECT COUNT(*) FROM house_info h LEFT JOIN house_location l ON h.location_id = l.location_id $whereSQL";
        $stmt = $conn->prepare($countSql);
        $stmt->execute($params);
        $totalRows = $stmt->fetchColumn();
        $totalPages = ceil($totalRows / $limit);

        // 1.2 ดึงข้อมูล (Data)
        $sql = "SELECT h.house_id, h.house_name, l.location_name, l.location_id 
                FROM house_info h 
                LEFT JOIN house_location l ON h.location_id = l.location_id 
                $whereSQL
                ORDER BY h.house_id ASC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        echo json_encode([
            'status' => 'success',
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
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

// 2. ดึงข้อมูล Location
if ($action == 'get_locations') {
    try {
        $stmt = $conn->prepare("SELECT * FROM house_location ORDER BY location_id ASC");
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// 3. Create
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

// 4. Update
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

// 5. Delete
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