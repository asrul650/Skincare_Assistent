<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/email_functions.php';

// Cek jadwal yang akan datang dalam 30 menit
$query = "SELECT s.*, u.email, u.username 
          FROM schedules s 
          JOIN users u ON s.user_id = u.id 
          WHERE DATE_FORMAT(s.schedule_time, '%Y-%m-%d %H:%i:00') 
          BETWEEN DATE_ADD(NOW(), INTERVAL 25 MINUTE) 
          AND DATE_ADD(NOW(), INTERVAL 35 MINUTE) 
          AND s.email_sent = 0";

$result = $conn->query($query);

if ($result) {
    while ($schedule = $result->fetch_assoc()) {
        // Format waktu untuk email
        $schedule_time = date('H:i', strtotime($schedule['schedule_time']));
        
        if (sendScheduleReminder(
            $schedule['email'],
            $schedule['username'],
            $schedule['routine_name'],
            $schedule_time
        )) {
            // Update status email terkirim
            $update = "UPDATE schedules 
                      SET email_sent = 1, 
                          last_notification = NOW() 
                      WHERE id = " . $schedule['id'];
            $conn->query($update);
            
            error_log("Email terkirim untuk jadwal ID: " . $schedule['id']);
        }
    }
} 