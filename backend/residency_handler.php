<?php
header('Content-Type: application/json');
require_once realpath(__DIR__ . '/../Database/config.php');

$action = $_POST['action'] ?? '';

// 1. ACTION: READ (ดึงข้อมูล + ค้นหา + แบ่งหน้า)
if ($action == 'read') {
    try {
        // รับค่า Pagination
        $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
        $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
        $search = isset($_POST['search']) ? $_POST['search'] : '';
        $offset = ($page - 1) * $limit;

        // เงื่อนไขการค้นหา
        $whereSQL = " WHERE 1=1 ";
        $params = [];

        if (!empty($search)) {
            // ค้นหาจาก ชื่อจริง, นามสกุล หรือ ชื่อบ้าน
            $whereSQL .= " AND (p.person_fname LIKE :s OR p.person_lname LIKE :s OR h.house_name LIKE :s) ";
            $params[':s'] = "%$search%";
        }

        // 1.1 นับจำนวนทั้งหมด (Count)
        $countSql = "SELECT COUNT(*) 
                     FROM residency_history r
                     JOIN person_info p ON r.person_id = p.person_id
                     JOIN house_info h ON r.house_id = h.house_id
                     $whereSQL";
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute($params);
        $totalRows = $countStmt->fetchColumn();
        $totalPages = ceil($totalRows / $limit);

        // 1.2 ดึงข้อมูลจริง (Data)
        $sql = "SELECT r.*, 
                CONCAT(p.person_fname, ' ', p.person_lname) as fullname,
                h.house_name,
                me.meter_serial as elec_serial,
                mw.meter_serial as water_serial
                FROM residency_history r
                JOIN person_info p ON r.person_id = p.person_id
                JOIN house_info h ON r.house_id = h.house_id
                LEFT JOIN meter_info me ON r.electric_meter_id = me.meter_id
                LEFT JOIN meter_info mw ON r.water_meter_id = mw.meter_id
                $whereSQL
                ORDER BY r.history_id DESC
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
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => $data,
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

// 2. ACTION: GET LAST READING (ดึงมิเตอร์ล่าสุดของบ้าน)
if ($action == 'get_last_reading') {
    try {
        $house_id = $_POST['house_id'];
        $sql = "SELECT final_electric_reading, final_water_reading 
                FROM residency_history 
                WHERE house_id = :house_id 
                ORDER BY history_id DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':house_id' => $house_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode($result ?: ['final_electric_reading' => 0, 'final_water_reading' => 0]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// 3. ACTION: MOVE IN (ย้ายเข้า)
if ($action == 'move_in') {
    try {
        $sql = "INSERT INTO residency_history 
                (person_id, house_id, electric_meter_id, water_meter_id, move_in_date, starting_electric_reading, starting_water_reading) 
                VALUES (:pid, :hid, :eid, :wid, :date, :s_elec, :s_water)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':pid' => $_POST['person_id'],
            ':hid' => $_POST['house_id'],
            ':eid' => !empty($_POST['electric_meter_id']) ? $_POST['electric_meter_id'] : NULL,
            ':wid' => !empty($_POST['water_meter_id']) ? $_POST['water_meter_id'] : NULL,
            ':date' => $_POST['move_in_date'],
            ':s_elec' => $_POST['starting_electric_reading'] ?? 0,
            ':s_water' => $_POST['starting_water_reading'] ?? 0
        ]);
        
        echo json_encode(['status' => 'success', 'message' => 'บันทึกการเข้าพักเรียบร้อยแล้ว']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
    exit;
}

// 4. ACTION: MOVE OUT (ย้ายออก)
if ($action == 'move_out') {
    try {
        $sql = "UPDATE residency_history SET 
                move_out_date = :date,
                final_electric_reading = :f_elec,
                final_water_reading = :f_water
                WHERE history_id = :id";
                
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':date' => $_POST['move_out_date'],
            ':f_elec' => $_POST['final_electric_reading'],
            ':f_water' => $_POST['final_water_reading'],
            ':id' => $_POST['history_id']
        ]);
        
        echo json_encode(['status' => 'success', 'message' => 'แจ้งย้ายออกและบันทึกค่ามิเตอร์เรียบร้อย']);
    } catch (PDOException $e) { 
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]); 
    }
    exit;
}

// 5. ACTION: GET FORM DATA (ดึงตัวเลือก Dropdown)
if ($action == 'get_form_data') {
    try {
        $people = $conn->query("SELECT person_id, CONCAT(person_fname, ' ', person_lname) as name FROM person_info WHERE status = 1")->fetchAll(PDO::FETCH_ASSOC);
        $houses = $conn->query("SELECT house_id, house_name FROM house_info")->fetchAll(PDO::FETCH_ASSOC);
        $elecMeters = $conn->query("SELECT meter_id, meter_serial FROM meter_info WHERE meter_type = 'electric'")->fetchAll(PDO::FETCH_ASSOC);
        $waterMeters = $conn->query("SELECT meter_id, meter_serial FROM meter_info WHERE meter_type = 'water'")->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'people' => $people, 'houses' => $houses,
            'elecMeters' => $elecMeters, 'waterMeters' => $waterMeters
        ]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}
?>