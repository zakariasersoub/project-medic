<?php
require_once '../Doctor/mail.php'; // عدّل المسار حسب مكان ملف mail.php
$conn = new mysqli("localhost", "root", "", "medic");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// جلب المواعيد التي لم يتم إرسال تنبيه لها وباقي عليها بين 29 و31 دقيقة
$sql = "SELECT 
            appointments.id,
            appointments.scheduled_time,
            patient_user.email AS patient_email,
            doctor_user.email AS doctor_email,
            doctor_user.fullname AS doctor_name
        FROM appointments
        INNER JOIN patients ON appointments.patient_id = patients.id
        INNER JOIN users AS patient_user ON patients.user_id = patient_user.id
        INNER JOIN doctors ON appointments.doctor_id = doctors.id
        INNER JOIN users AS doctor_user ON doctors.user_id = doctor_user.id
        WHERE appointments.status = 'accepted'
        AND appointments.scheduled_time > NOW()
        AND TIMESTAMPDIFF(MINUTE, NOW(), appointments.scheduled_time) BETWEEN 29 AND 31
        AND (appointments.notified IS NULL OR appointments.notified = 0)";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $mail->setFrom($row['doctor_email'], 'Dr.' . $row['doctor_name']);
    $mail->addAddress($row['patient_email']);
    $mail->Subject = 'تنبيه بموعدك الطبي بعد نصف ساعة';
    $mail->Body = 'مرحباً، هذا تذكير بأن موعدك الطبي سيكون في تمام الساعة: ' . date('Y-m-d H:i', strtotime($row['scheduled_time'])) . '. الرجاء الحضور في الوقت المحدد.';

    if ($mail->send()) {
        // ضع علامة أنه تم إرسال التنبيه
        $stmt = $conn->prepare("UPDATE appointments SET notified = 1 WHERE id = ?");
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
        $stmt->close();
    }
    $mail->clearAddresses();
}

$conn->close();
?>