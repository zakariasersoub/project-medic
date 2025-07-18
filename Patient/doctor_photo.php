<?php
$conn = new mysqli("localhost", "root", "", "medic");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = intval($_GET['id']);
$sql = "SELECT avatar FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($image);
$stmt->fetch();
$stmt->close();
$conn->close();

if ($image) {
    header("Content-Type: image/jpeg");
    echo $image;
    exit;
} else {
    // عرض صورة افتراضية إن لم توجد صورة في قاعدة البيانات
    readfile("images/default.avif");
}
?>