<?php
include 'db_connect.php';

// Set header to return JSON
header('Content-Type: application/json');

// Check if archive_id is provided
if (!isset($_POST['archive_id']) || !is_numeric($_POST['archive_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid Archive ID.']);
    exit;
}

$archive_id = intval($_POST['archive_id']);

// Start a transaction to ensure both operations succeed or fail together
$conn->begin_transaction();

try {
    // 1. Select the archived record
    $stmt_select = $conn->prepare("SELECT * FROM archive WHERE id = ?");
    $stmt_select->bind_param("i", $archive_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Archived record not found.");
    }
    $archive_record = $result->fetch_assoc();
    $stmt_select->close();

    // 2. Insert the data back into the violations table (letting it get a new ID)
    $stmt_insert = $conn->prepare("INSERT INTO violations (violation_time, license_plate, violation_description, vehicle_status) VALUES (?, ?, ?, ?)");
    $stmt_insert->bind_param("ssss", 
        $archive_record['violation_time'], 
        $archive_record['license_plate'], 
        $archive_record['violation_description'], 
        $archive_record['vehicle_status']
    );
    $stmt_insert->execute();
    $stmt_insert->close();

    // 3. Delete the record from the archive table
    $stmt_delete = $conn->prepare("DELETE FROM archive WHERE id = ?");
    $stmt_delete->bind_param("i", $archive_id);
    $stmt_delete->execute();
    $stmt_delete->close();

    // If all steps were successful, commit the transaction
    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // If any step failed, roll back the transaction
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to restore violation: ' . $e->getMessage()]);
}

$conn->close();
?>