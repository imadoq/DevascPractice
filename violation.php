<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkSense - Violation History</title>
    <!-- Your Font and CSS links -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
        .confirm-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }

        .confirm-btn:hover {
            background-color: #218838;
        }
        
        .delete-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        .confirmed-row {
            background-color: #d3d3d3 !important;
            opacity: 0.8;
        }

        .violation-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            word-wrap: break-word;
        }

        .violation-table th, .violation-table td {
            padding: 10px;
            text-align: left;
        }

        .violation-table th:nth-child(1) { width: 15%; }
        .violation-table th:nth-child(2) { width: 25%; }
        .violation-table th:nth-child(3) { width: 35%; }
        .violation-table th:nth-child(4) { width: 25%; }


        .violation-table th {
            background-color: #333;
            color: white;
        }
        
        .confirm-btn.checked {
            background-color: gray !important;
            cursor: not-allowed;
        }

        .confirm-btn.checked::before {
            content: "✔ ";
        }

        /* --- NEW: CSS for the Confirmation Modal --- */
        .modal-overlay {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5); /* Black with opacity */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; /* 15% from the top and centered */
            padding: 25px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            border-radius: 10px;
            text-align: center;
        }
        
        .modal-content h3 {
            margin-top: 0;
        }

        .modal-buttons button {
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin: 0 10px;
        }

        #confirm-delete-btn {
            background-color: #dc3545;
            color: white;
        }

        #cancel-delete-btn {
            background-color: #ccc;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- (The main container content remains the same) -->
        <aside class="sidebar">
            <div>
                <div class="sidebar-header">
                <div class="logo-title-container">
                    <img src="assets/ustlogo.png" alt="UST Logo" class="header-logo">
                    <h1>ParkSense</h1>
                </div>
                </div>

                <div id="current-date-time">
                    <p id="date"></p>
                    <p id="time"></p>
                </div>

                <div class="system-status">
                    <h2>System Activated</h2>
                    <label class="toggle-switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>

                <nav class="sidebar-nav">
                    <h2>Parking Areas:</h2>
                    <a href="admin.php">Admin</a>
                    <a href="student.php">Student</a>
                </nav>

                <nav class="sidebar-nav">
                    <h2>Violations:</h2>
                    <a href="unregistered.php">Unregistered Vehicles</a>
                    <a href="#" class="active">Violation history</a>
                </nav>
            </div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <h2>Violation History</h2>
                <div class="notification-bell" id="notification-container">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">1</span>
                    <div class="notification-popup" id="notification-popup">
                        <div class="popup-content">
                            <p>Violation detected by</p>
                            <p><em>*license plate number*</em></p>
                        </div>
                    </div>
                </div>
            </header>
            
            <div class="violation-content">
                <div class="table-section">
                    <table class="violation-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>License Plate</th>
                                <th>Violation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                // Fetch registered violations from the database
                                $sql = "SELECT * FROM violations WHERE vehicle_status = 'registered' ORDER BY violation_time ASC";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . date("g:i A", strtotime($row["violation_time"])) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["license_plate"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["violation_description"]) . "</td>";
                                        echo '<td><button class="confirm-btn">Confirm</button> <button class="delete-btn">Delete</button></td>';
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>No registered violations found.</td></tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- NEW: HTML for the Confirmation Modal -->
    <div id="delete-modal" class="modal-overlay">
        <div class="modal-content">
            <h3>Confirm Deletion</h3>
            <p>Are you sure you want to delete this violation record?</p>
            <div class="modal-buttons">
                <button id="cancel-delete-btn">Cancel</button>
                <button id="confirm-delete-btn">Yes, Delete</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // (Existing notification and clock code...)
            const notificationContainer = document.getElementById('notification-container');
            const notificationPopup = document.getElementById('notification-popup');

            notificationContainer.addEventListener('click', function(event) {
                event.stopPropagation(); 
                notificationPopup.classList.toggle('show');
            });

            window.addEventListener('click', function(event) {
                if (notificationPopup.classList.contains('show')) {
                    notificationPopup.classList.remove('show');
                }
            });
            
            function updateDateTime() {
                const now = new Date();
                const options = { month: 'long', day: 'numeric', year: 'numeric' };
                document.getElementById('date').textContent = now.toLocaleDateString('en-US', options);
                let hours = now.getHours();
                let minutes = now.getMinutes();
                let seconds = now.getSeconds();
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12; 
                minutes = minutes < 10 ? '0' + minutes : minutes;
                seconds = seconds < 10 ? '0' + seconds : seconds;
                document.getElementById('time').textContent = `${hours}:${minutes}:${seconds}${ampm}`;
            }
            updateDateTime();
            setInterval(updateDateTime, 1000);

            // Confirm Button Functionality
            const confirmButtons = document.querySelectorAll('.confirm-btn');
            confirmButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const row = this.closest('tr');
                    row.classList.add('confirmed-row');
                    this.classList.add('checked');
                    this.textContent = 'Confirmed';
                    this.disabled = true;

                    const deleteButton = row.querySelector('.delete-btn');
                    if (deleteButton) {
                        deleteButton.style.display = 'none';
                    }
                });
            });
            
            // --- UPDATED: Delete Button and Modal Functionality ---
            const modal = document.getElementById('delete-modal');
            const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
            const cancelDeleteBtn = document.getElementById('cancel-delete-btn');
            let rowToDelete = null;

            const deleteButtons = document.querySelectorAll('.delete-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Store the row we want to delete and show the modal
                    rowToDelete = this.closest('tr');
                    modal.style.display = 'block';
                });
            });

            // When the user clicks "Yes, Delete"
            confirmDeleteBtn.addEventListener('click', function() {
                if (rowToDelete) {
                    rowToDelete.remove();
                }
                modal.style.display = 'none';
                rowToDelete = null; // Reset the variable
            });

            // When the user clicks "Cancel"
            cancelDeleteBtn.addEventListener('click', function() {
                modal.style.display = 'none';
                rowToDelete = null; // Reset the variable
            });

            // When the user clicks anywhere outside of the modal, close it
            window.addEventListener('click', function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                    rowToDelete = null; // Reset the variable
                }
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>