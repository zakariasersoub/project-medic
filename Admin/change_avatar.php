<?php
session_start();
$conn = new mysqli("localhost", "root", "", "medic");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// تحديد الصفحة السابقة للعودة إليها بعد رفع الصورة
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : './';

if ($user_id > 0 && isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
    $imgData = file_get_contents($_FILES['avatar']['tmp_name']);
    $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
    $stmt->bind_param("bi", $null, $user_id);
    $null = NULL;
    $stmt->send_long_data(0, $imgData);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header("Location: $redirect");
exit;
?>