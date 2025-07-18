<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$database = "medic";
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$appointment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($appointment_id > 0) {
    require_once 'mail.php';

        $sql = "SELECT 
                    patient_user.email AS patient_email, 
                    doctor_user.email AS doctor_email,
                    doctor_user.fullname AS doctor_name,
                    appointments.status as sta_tus,
                    appointments.scheduled_time
                FROM appointments 
                INNER JOIN patients ON appointments.patient_id = patients.id 
                INNER JOIN users AS patient_user ON patients.user_id = patient_user.id 
                INNER JOIN doctors ON appointments.doctor_id = doctors.id
                INNER JOIN users AS doctor_user ON doctors.user_id = doctor_user.id
                WHERE appointments.id = ?";
        $stmt1 = $conn->prepare($sql);
        $stmt1->bind_param("i", $appointment_id);
        $stmt1->execute();
        $stmt1->bind_result($patient_email, $doctor_email, $doctor_name, $status, $scheduled_time);
        $stmt1->fetch();
        $stmt1->close();

        if (!empty($patient_email) && !empty($doctor_email) && $status != 'accepted') {
            $mail->setFrom($doctor_email, 'Dr.' . $doctor_name);
            $mail->addAddress($patient_email); // المريض هو المستلم
            $mail->Subject = 'تم قبول موعدك';
            $mail->Body = 'مرحباً، تم قبول موعدك. الرجاء الحضور في الوقت المحدد: ' . date('Y-m-d H:i', strtotime($scheduled_time));

            if (!$mail->send()) {
                echo 'فشل الإرسال. الخطأ: ' . $mail->ErrorInfo;
            } else {
                echo 'تم إرسال الرسالة بنجاح';
            }

            // بعد إرسال الرسالة، حدث حالة الموعد
            $stmt = $conn->prepare("UPDATE appointments SET status='accepted' WHERE id=?");
            $stmt->bind_param("i", $appointment_id);
            $stmt->execute();
            $stmt->close();
        } else {
            echo 'لم يتم العثور على البريد الإلكتروني للطبيب أو المريض أو الموعد مقبول بالفعل.';
        }

    header("Location: Appointment.php");
    exit();
} else {
    echo "Invalid appointment ID.";
}
?>