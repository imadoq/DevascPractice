<?php
include 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_POST['violation_id']) || !is_numeric($_POST['violation_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid Violation ID.']);
    exit;
}

$violation_id = intval($_POST['violation_id']);
$conn->begin_transaction();

try {
    $stmt_select = $conn->prepare("SELECT * FROM violations WHERE id = ?");
    $stmt_select->bind_param("i", $violation_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    if ($result->num_rows === 0) { throw new Exception("Violation not found."); }
    $violation = $result->fetch_assoc();
    $stmt_select->close();

    $stmt_insert = $conn->prepare("INSERT INTO archive (original_violation_id, violation_time, license_plate, violation_description, vehicle_status) VALUES (?, ?, ?, ?, ?)");
    $stmt_insert->bind_param("issss", $violation['id'], $violation['violation_time'], $violation['license_plate'], $violation['violation_description'], $violation['vehicle_status']);
    $stmt_insert->execute();
    $stmt_insert->close();

    $stmt_delete = $conn->prepare("DELETE FROM violations WHERE id = ?");
    $stmt_delete->bind_param("i", $violation_id);
    $stmt_delete->execute();
    $stmt_delete->close();

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to archive violation: ' . $e->getMessage()]);
}

$conn->close();
?>