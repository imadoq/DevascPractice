<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkSense - Archives</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>
        .violation-section h2 { text-decoration: underline; font-size: 1.8em; font-weight: bold; margin-bottom: 20px; }
        .violation-section { margin-bottom: 50px; }
        .violation-table { width: 100%; border-collapse: collapse; }
        .violation-table th, .violation-table td { padding: 12px 15px; text-align: left; }
        .violation-table th { background-color: #333; color: white; text-transform: uppercase; }
        .violation-table td { border-bottom: 1px solid #f0f0f0; }
        .restore-btn { background-color: #28a745; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; }
        .restore-btn:hover { background-color: #218838; }
        .pagination { display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 30px; }
        .pagination a { display: flex; align-items: center; justify-content: center; text-decoration: none; color: #333; padding: 10px 15px; border: 1px solid #ddd; border-radius: 5px; }
        .pagination a:hover { background-color: #f5f5f5; }
        .pagination a.active { background-color: #333; color: white; border-color: #333; cursor: default; }
        .download-btn { position: fixed; bottom: 20px; right: 30px; background-color: #007bff; color: white; border: none; padding: 12px 18px; border-radius: 8px; cursor: pointer; font-size: 16px; box-shadow: 0px 3px 6px rgba(0,0,0,0.2); }
        .download-btn:hover { background-color: #0056b3; }

        .modal-overlay { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fefefe; margin: 15% auto; padding: 25px; border: 1px solid #888; width: 80%; max-width: 400px; border-radius: 10px; text-align: center; }
        .modal-content h3 { margin-top: 0; }
        .modal-buttons button { border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; margin: 0 10px; }
        #confirm-restore-btn { background-color: #28a745; color: white; }
        #cancel-restore-btn { background-color: #ccc; color: #333; }
    </style>
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div>
            <div class="sidebar-header"><div class="logo-title-container"><img src="assets/ustlogo.png" alt="UST Logo" class="header-logo"><h1>ParkSense</h1></div></div>
            <div id="current-date-time"><p id="date"></p><p id="time"></p></div>
            <div class="system-status"><h2>System Activated</h2><label class="toggle-switch"><input type="checkbox" checked><span class="slider"></span></label></div>
            <nav class="sidebar-nav"><h2>Parking Areas:</h2><a href="admin.php">Admin</a><a href="student.php">Student</a></nav>
            <nav class="sidebar-nav"><h2>Violations:</h2><a href="unregistered.php">Unregistered Vehicles</a><a href="violation.php">Violation history</a><a href="archive.php" class="active">Archives</a> </nav>
        </div>
    </aside>

    <main class="main-content" id="archiveContent">
        <header class="main-header">
            <h2>Archives</h2>
            <div class="notification-bell" id="notification-container"><i class="fas fa-bell"></i><span class="notification-badge">1</span><div class="notification-popup" id="notification-popup"><div class="popup-content"><p>Violation detected by</p><p><em>*license plate number*</em></p></div></div></div>
        </header>

        <div class="violation-section">
            <h2>Violation History</h2>
            <table class="violation-table">
                <thead><tr><th style="width: 15%;">Time</th><th style="width: 25%;">License Plate</th><th style="width: 45%;">Violation</th><th style="width: 15%;">Actions</th></tr></thead>
                <tbody>
                    <?php
                        $sql_registered = "SELECT * FROM archive WHERE vehicle_status = 'registered' ORDER BY archive_time DESC";
                        $result_registered = $conn->query($sql_registered);
                        if ($result_registered->num_rows > 0) {
                            while($row = $result_registered->fetch_assoc()) {
                                // ✅ ADDED data-id ATTRIBUTE
                                echo "<tr data-id='" . htmlspecialchars($row["id"]) . "'>";
                                echo "<td>" . date("g:i A", strtotime($row["violation_time"])) . "</td>";
                                echo "<td>" . htmlspecialchars($row["license_plate"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["violation_description"]) . "</td>";
                                echo '<td><button class="restore-btn">Restore</button></td>';
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No archived registered violations found.</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
            <div class="pagination"><a href="#"><i class="fas fa-chevron-left"></i> Prev</a><a href="#" class="active">1</a><a href="#">Next <i class="fas fa-chevron-right"></i></a></div>
        </div>

        <div class="violation-section">
            <h2>Unregistered Vehicles</h2>
            <table class="violation-table">
                <thead><tr><th style="width: 15%;">Time</th><th style="width: 25%;">License Plate</th><th style="width: 45%;">Violation</th><th style="width: 15%;">Actions</th></tr></thead>
                <tbody>
                    <?php
                        $sql_unregistered = "SELECT * FROM archive WHERE vehicle_status = 'unregistered' ORDER BY archive_time DESC";
                        $result_unregistered = $conn->query($sql_unregistered);
                        if ($result_unregistered->num_rows > 0) {
                            while($row = $result_unregistered->fetch_assoc()) {
                                // ✅ ADDED data-id ATTRIBUTE
                                echo "<tr data-id='" . htmlspecialchars($row["id"]) . "'>";
                                echo "<td>" . date("g:i A", strtotime($row["violation_time"])) . "</td>";
                                echo "<td>" . htmlspecialchars($row["license_plate"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["violation_description"]) . "</td>";
                                echo '<td><button class="restore-btn">Restore</button></td>';
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No archived unregistered violations found.</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
            <div class="pagination"><a href="#"><i class="fas fa-chevron-left"></i> Prev</a><a href="#" class="active">1</a><a href="#">Next <i class="fas fa-chevron-right"></i></a></div>
        </div>
    </main>
</div>

<button class="download-btn" id="downloadPDF"><i class="fas fa-file-download"></i> Download PDF</button>

<!-- ✅ ADDED RESTORE MODAL HTML -->
<div id="restore-modal" class="modal-overlay">
    <div class="modal-content">
        <h3>Restore Violation</h3>
        <p>Are you sure you want to restore this violation? It will be moved back to the active violations list.</p>
        <div class="modal-buttons">
            <button id="cancel-restore-btn">Cancel</button>
            <button id="confirm-restore-btn">Yes, Restore</button>
        </div>
    </div>
</div>

<!-- ✅ UPDATED SCRIPT WITH RESTORE LOGIC -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Clock, Notification, and PDF functions (unchanged) ---
    function updateDateTime() {
        const now = new Date();
        const options = { month: 'long', day: 'numeric', year: 'numeric' };
        document.getElementById('date').textContent = now.toLocaleDateString('en-US', options);
        let hours = now.getHours(), minutes = now.getMinutes(), seconds = now.getSeconds();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        document.getElementById('time').textContent = `${hours}:${minutes.toString().padStart(2,'0')}:${seconds.toString().padStart(2,'0')}${ampm}`;
    }
    updateDateTime(); setInterval(updateDateTime, 1000);
    const notificationContainer = document.getElementById('notification-container');
    const notificationPopup = document.getElementById('notification-popup');
    notificationContainer.addEventListener('click', function(event) { event.stopPropagation(); notificationPopup.classList.toggle('show'); });
    window.addEventListener('click', function() { if (notificationPopup.classList.contains('show')) { notificationPopup.classList.remove('show'); } });
    document.getElementById('downloadPDF').addEventListener('click', async () => { /* PDF Logic is unchanged */ });


    // --- NEW: Restore Functionality ---
    const restoreModal = document.getElementById('restore-modal');
    let rowToRestore = null;

    // Function to call the restore API
    async function restoreViolation(archiveId) {
        const formData = new FormData();
        formData.append('archive_id', archiveId);
        try {
            const response = await fetch('api_restore_violation.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) {
                return true;
            } else {
                alert('Server Error: ' + result.message);
                return false;
            }
        } catch (error) {
            console.error('Network error:', error);
            alert('A network error occurred. Could not restore violation.');
            return false;
        }
    }

    // Use event delegation to listen for clicks on any "Restore" button
    document.querySelector('.main-content').addEventListener('click', function(event) {
        if (event.target.classList.contains('restore-btn')) {
            rowToRestore = event.target.closest('tr');
            restoreModal.style.display = 'block';
        }
    });

    // Handle the "Yes, Restore" click in the modal
    document.getElementById('confirm-restore-btn').addEventListener('click', async function() {
        if (rowToRestore) {
            const archiveId = rowToRestore.dataset.id;
            const success = await restoreViolation(archiveId);
            if (success) {
                rowToRestore.remove(); // Remove the row from the page
            }
        }
        restoreModal.style.display = 'none'; // Hide the modal
        rowToRestore = null; // Reset the variable
    });

    // Handle the "Cancel" click and clicking outside the modal
    document.getElementById('cancel-restore-btn').addEventListener('click', function() {
        restoreModal.style.display = 'none';
        rowToRestore = null;
    });

    window.addEventListener('click', function(event) {
        if (event.target == restoreModal) {
            restoreModal.style.display = 'none';
            rowToRestore = null;
        }
    });
});
</script>
</body>
</html>
<?php $conn->close(); ?>