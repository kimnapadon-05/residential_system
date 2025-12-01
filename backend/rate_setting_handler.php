<?php
require_once '../Database/config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action == 'read') {
    $stmt = $conn->query("SELECT * FROM utility_rates ORDER BY bill_year DESC, bill_month DESC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($action == 'save') {
    $m = $_POST['month'];
    $y = $_POST['year'];
    $e_rate = $_POST['elec_rate'];
    $w_rate = $_POST['water_rate'];

    try {
        $sql = "INSERT INTO utility_rates (bill_month, bill_year, elec_rate, water_rate) 
                VALUES (:m, :y, :e, :w)
                ON DUPLICATE KEY UPDATE elec_rate = :e, water_rate = :w";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':m'=>$m, ':y'=>$y, ':e'=>$e_rate, ':w'=>$w_rate]);
        echo json_encode(['status' => 'success', 'message' => 'บันทึกอัตราค่าบริการเรียบร้อย']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
?>