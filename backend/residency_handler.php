<?php
header('Content-Type: application/json');
require_once '../Database/config.php';

$action = $_POST['action'] ?? '';

// Read History
if ($action == 'read') {
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
            ORDER BY r.history_id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// Get Data for Dropdowns (People, Houses, Available Meters)
if ($action == 'get_form_data') {
    $people = $conn->query("SELECT person_id, CONCAT(person_fname, ' ', person_lname) as name FROM person_info WHERE status = 1")->fetchAll(PDO::FETCH_ASSOC);
    $houses = $conn->query("SELECT house_id, house_name FROM house_info")->fetchAll(PDO::FETCH_ASSOC);
    // เลือกเฉพาะมิเตอร์ไฟฟ้า
    $elecMeters = $conn->query("SELECT meter_id, meter_serial FROM meter_info WHERE meter_type = 'electric'")->fetchAll(PDO::FETCH_ASSOC);
    // เลือกเฉพาะมิเตอร์น้ำ
    $waterMeters = $conn->query("SELECT meter_id, meter_serial FROM meter_info WHERE meter_type = 'water'")->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'people' => $people,
        'houses' => $houses,
        'elecMeters' => $elecMeters,
        'waterMeters' => $waterMeters
    ]);
    exit;
}

// Create (Move In)
if ($action == 'move_in') {
    try {
        $stmt = $conn->prepare("INSERT INTO residency_history 
        (person_id, house_id, electric_meter_id, water_meter_id, move_in_date, starting_electric_reading, starting_water_reading) 
        VALUES (:pid, :hid, :eid, :wid, :date, :s_elec, :s_water)");
        
        $stmt->execute([
            ':pid' => $_POST['person_id'],
            ':hid' => $_POST['house_id'],
            ':eid' => $_POST['electric_meter_id'] ?: NULL,
            ':wid' => $_POST['water_meter_id'] ?: NULL,
            ':date' => $_POST['move_in_date'],
            ':s_elec' => $_POST['starting_electric_reading'] ?? 0,
            ':s_water' => $_POST['starting_water_reading'] ?? 0
        ]);
        
        echo json_encode(['status' => 'success', 'message' => 'บันทึกการเข้าพักสำเร็จ']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Move Out (Update move_out_date)
if ($action == 'move_out') {
    try {
        $stmt = $conn->prepare("UPDATE residency_history SET move_out_date = :date WHERE history_id = :id");
        $stmt->execute([
            ':date' => date('Y-m-d'), // หรือรับจาก post
            ':id' => $_POST['history_id']
        ]);
        echo json_encode(['status' => 'success', 'message' => 'แจ้งย้ายออกเรียบร้อย']);
    } catch (PDOException $e) { echo json_encode(['status' => 'error']); }
    exit;
}
?>