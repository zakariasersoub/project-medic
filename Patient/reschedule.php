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
$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $time = $_POST['time'];
    $scheduled_time = $date . ' ' . $time . ':00';

    $stmt = $conn->prepare("UPDATE appointments SET scheduled_time=?, status='pending' WHERE id=?");
    $stmt->bind_param("si", $scheduled_time, $appointment_id);
    if ($stmt->execute()) {
        $success = "Appointment rescheduled successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
    $stmt->close();
}

// جلب بيانات الموعد الحالي
$stmt = $conn->prepare("SELECT scheduled_time FROM appointments WHERE id=?");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$stmt->bind_result($scheduled_time);
$stmt->fetch();
$stmt->close();

$current_date = $current_time = "";
if ($scheduled_time) {
    $current_date = date('Y-m-d', strtotime($scheduled_time));
    $current_time = date('H:i', strtotime($scheduled_time));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reschedule Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Reschedule Appointment</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form method="post">
                            <div class="mb-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" id="date" name="date" class="form-control" value="<?php echo $current_date; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="time" class="form-label">Time</label>
                                <input type="time" id="time" name="time" class="form-control" value="<?php echo $current_time; ?>" required>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">Save</button>
                                <a href="Appointment.php" class="btn btn-secondary">Back</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>