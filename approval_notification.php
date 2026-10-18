<?php
global $conn,$db, $pdo,$link;
$db_conn = $pdo ?? $conn ?? $db ?? $link ?? null;

if (isset($uid) &&$db_conn instanceof PDO) {
    try {
        $rental = null;
        
        // Get all column names from rentals table
        $colsStmt =$db_conn->query("SHOW COLUMNS FROM rentals");
        $columns =$colsStmt->fetchAll(PDO::FETCH_COLUMN);

        // Loop through columns to find any column containing 'id' (excluding general id & equipment_id)
        foreach ($columns as$col) {
            if (stripos($col, 'id') !== false && $col !== 'id' &&$col !== 'equipment_id') {
                try {
                    $sql = "SELECT * FROM rentals WHERE `{$col}` = :uid AND (status = 'Approved' OR status = 'approved') ORDER BY id DESC LIMIT 1";
                    $stmt = $db_conn->prepare($sql);
                    $stmt->execute(['uid' =>$uid]);
                    $rental =$stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($rental) {
                        break; // Found matching record!
                    }
                } catch (PDOException $e) {
                    continue;
                }
            }
        }

        if ($rental) {$equipment_title = $rental['equipment_name'] ?? $rental['equipment'] ?? 'Equipment';
?>
            <!-- Toast Notification Popup -->
            <div id="approvalPopup" class="approval-popup">
                <div class="popup-icon">🎉</div>
                <div class="popup-text">
                    <strong>Rental Request Approved!</strong>
                    <p>Your request for <strong><?php echo htmlspecialchars($equipment_title); ?></strong> has been approved by the admin.</p>
                </div>
                <button onclick="closePopup()" class="popup-close">&times;</button>
            </div>

            <style>
            .approval-popup {
                position: fixed;
                bottom: 30px;
                right: 30px;
                z-index: 999999;
                display: flex;
                align-items: center;
                gap: 15px;
                background: #0f172a;
                border: 1px solid #10b981;
                border-left: 6px solid #10b981;
                padding: 18px 22px;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
                color: #ffffff;
                animation: slideUp 0.4s ease-out forwards;
                max-width: 380px;
                font-family: 'Poppins', sans-serif;
            }
            .popup-icon { font-size: 26px; }
            .popup-text strong { display: block; font-size: 0.95rem; color: #34d399; margin-bottom: 2px; }
            .popup-text p { margin: 0; font-size: 0.85rem; color: #cbd5e1; line-height: 1.4; }
            .popup-text p strong { display: inline; color: #ffffff; }
            .popup-close { background: none; border: none; color: #94a3b8; font-size: 1.5rem; cursor: pointer; padding: 0; margin-left: auto; }
            .popup-close:hover { color: #ffffff; }
            @keyframes slideUp {
                from { transform: translateY(100px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
            </style>

            <script>
            function closePopup() {
                document.getElementById("approvalPopup").style.display = "none";
            }
            </script>
<?php
        }
    } catch (Exception $e) {
        // Silent error handling
    }
}
?>