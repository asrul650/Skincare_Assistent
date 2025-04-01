<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    exit('Unauthorized');
}

$query = "SELECT *, CASE 
            WHEN status = 'active' THEN 'Aktif'
            WHEN status = 'inactive' THEN 'Tidak Aktif'
            END as status_text 
          FROM users 
          ORDER BY created_at DESC";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $statusClass = $row['status'] == 'active' ? 'status-active' : 'status-inactive';
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . date('d M Y', strtotime($row['created_at'])) . "</td>";
        echo "<td><span class='status-badge {$statusClass}'>{$row['status_text']}</span></td>";
        echo "<td class='action-buttons'>
                <button class='btn-delete' onclick='deleteUser({$row['id']})'>
                    <i class='fas fa-trash'></i> Hapus
                </button>
              </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5' style='text-align: center;'>Tidak ada pengguna terdaftar</td></tr>";
}
?> 