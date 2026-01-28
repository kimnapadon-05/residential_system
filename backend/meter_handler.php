<?php
header('Content-Type: application/json');
require_once '../Database/config.php';

$action = $_POST['action'] ?? '';

// 1. Read (ดึงข้อมูลแบบแบ่งหน้า + ค้นหา)
if ($action == 'read') {
    // รับค่าหน้าปัจจุบัน (Default = 1) และจำนวนต่อหน้า (Default = 10)
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
    $search = isset($_POST['search']) ? $_POST['search'] : '';
    $offset = ($page - 1) * $limit;

    try {
        // สร้างเงื่อนไข WHERE สำหรับการค้นหา
        $whereSQL = " WHERE 1=1 ";
        $params = [];
        if (!empty($search)) {
            // ค้นหาจาก Serial Number หรือ ชื่อยี่ห้อ
            $whereSQL .= " AND (m.meter_serial LIKE :s OR b.brand_name LIKE :s) ";
            $params[':s'] = "%$search%";
        }

        // 1.1 นับจำนวนข้อมูลทั้งหมด (Count) เพื่อทำ Pagination
        $countSql = "SELECT COUNT(*) 
                     FROM meter_info m 
                     LEFT JOIN meter_brand b ON m.brand_id = b.brand_id 
                     $whereSQL";
        $stmt = $conn->prepare($countSql);
        $stmt->execute($params);
        $totalRows = $stmt->fetchColumn();
        $totalPages = ceil($totalRows / $limit);

        // 1.2 ดึงข้อมูลจริง (Data) พร้อม LIMIT และ OFFSET
        $sql = "SELECT m.*, b.brand_name
                FROM meter_info m
                LEFT JOIN meter_brand b ON m.brand_id = b.brand_id
                $whereSQL
                ORDER BY m.meter_id DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $conn->prepare($sql);
        // Bind Parameters สำหรับ Search
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        // Bind Parameters สำหรับ Pagination
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $meters = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ส่งข้อมูลกลับไปให้ Frontend
        echo json_encode([
            'status' => 'success',
            'data' => $meters,
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

// 2. ดึงตัวเลือกยี่ห้อ (Dropdown)
if ($action == 'get_options') {
    try {
        $brands = $conn->query("SELECT * FROM meter_brand ORDER BY brand_name ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['brands' => $brands]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database Error: ' . $e->getMessage()]);
    }
    exit;
}

// 3. บันทึกข้อมูล (เพิ่ม/แก้ไข)
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
        echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลเรียบร้อย']);
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
        echo json_encode(['status' => 'success', 'message' => 'ลบข้อมูลสำเร็จ']);
    } catch (PDOException $e) { 
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]); 
    }
    exit;
}
?>