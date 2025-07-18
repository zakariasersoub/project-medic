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
    $stmt = $conn->prepare("UPDATE appointments SET status='cancelled' WHERE id=?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $stmt->close();
    header("Location: Appointment.php");
    exit();
} else {
    echo "Invalid appointment ID.";
}
?>