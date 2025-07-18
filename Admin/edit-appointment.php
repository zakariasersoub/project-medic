<?php

$servername = "localhost";
$username = "root";
$password = "";
$database = "medic";

$conn = new mysqli($servername, $username, $password, $database);

$errormessage = "";
$successmessage = "";

$id = "";
$doctor_id = "";
$patient_id = "";
$scheduled_time = "";
$status = "";

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!isset($_GET["id"])) {
        header("location: /medic/Admin/bootstrap-section-appointments.php");
        exit;
    }

    $id = intval($_GET["id"]);

    // جلب بيانات الموعد
    $sql = "SELECT * FROM appointments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        header("location: /medic/Admin/bootstrap-section-appointments.php");
        exit;
    }

    $doctor_id = $row['doctor_id'];
    $patient_id = $row['patient_id'];
    $scheduled_time = $row['scheduled_time'];
    $status = $row['status'];
    $stmt->close();
} else {
    // POST: تحديث بيانات الموعد
    $id = intval($_POST["id"]);
    $doctor_id = intval($_POST["doctor_id"]);
    $patient_id = intval($_POST["patient_id"]);
    $scheduled_time = $_POST["scheduled_time"];
    $status = $_POST["status"];

    do {
        if (empty($doctor_id) || empty($patient_id) || empty($scheduled_time) || empty($status)) {
            $errormessage = "All fields are required";
            break;
        }

        $sql = "UPDATE appointments SET doctor_id=?, patient_id=?, scheduled_time=?, status=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissi", $doctor_id, $patient_id, $scheduled_time, $status, $id);
        $result = $stmt->execute();

        if (!$result) {
            $errormessage = "Error updating appointment: " . $conn->error;
            break;
        }

        $successmessage = "Appointment updated successfully";
        header("location: /medic/Admin/bootstrap-section-appointments.php");
        exit;
    } while (false);
}

// جلب قائمة الأطباء والمرضى للاختيار
$doctors = [];
$patients = [];
$dq = $conn->query("SELECT d.id, u.fullName FROM doctors d JOIN users u ON d.user_id = u.id");
while ($dr = $dq->fetch_assoc()) {
    $doctors[] = $dr;
}
$pq = $conn->query("SELECT p.id, u.fullName FROM patients p JOIN users u ON p.user_id = u.id");
while ($pr = $pq->fetch_assoc()) {
    $patients[] = $pr;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Appointment</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container my-5">
    <h2>Edit Appointment</h2>

    <?php if(!empty($errormessage)): ?>
        <div class='alert alert-warning alert-dismissible fade show' role='alert'>
            <strong><?= $errormessage ?></strong>
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

        <div class="row mb-3">
            <label class="col-sm-3 col-form-label">Doctor</label>
            <div class="col-sm-6">
                <select class="form-control" name="doctor_id" required>
                    <option value="">Select Doctor</option>
                    <?php foreach($doctors as $doc): ?>
                        <option value="<?= $doc['id'] ?>" <?= $doctor_id == $doc['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($doc['fullName']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-sm-3 col-form-label">Patient</label>
            <div class="col-sm-6">
                <select class="form-control" name="patient_id" required>
                    <option value="">Select Patient</option>
                    <?php foreach($patients as $pat): ?>
                        <option value="<?= $pat['id'] ?>" <?= $patient_id == $pat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pat['fullName']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-sm-3 col-form-label">Scheduled Time</label>
            <div class="col-sm-6">
                <input type="datetime-local" class="form-control" name="scheduled_time"
                       value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($scheduled_time))) ?>" required>
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-sm-3 col-form-label">Status</label>
            <div class="col-sm-6">
                <select class="form-control" name="status" required>
                    <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="accepted" <?= $status == 'accepted' ? 'selected' : '' ?>>Accepted</option>
                    <option value="rejected" <?= $status == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= $status == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
        </div>

        <?php if(!empty($successmessage)): ?>
            <div class='row mb-3'>
                <div class='offset-sm-3 col-sm-6'>
                    <div class='alert alert-success alert-dismissible fade show' role='alert'>
                        <strong><?= $successmessage ?></strong>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="row mb-3">
            <div class="offset-sm-3 col-sm-3 d-grid">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
            <div class="col-sm-3 d-grid">
                <a class="btn btn-outline-primary" href="/medic/Admin/bootstrap-section-appointments.php" role="button">Cancel</a>
            </div>
        </div>
    </form>
</div>
</body>
</html>