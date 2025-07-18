<?php

$servername = "localhost";
$username = "root";
$password = "";
$database = "medic";

$conn = new mysqli($servername, $username, $password, $database);

$doctor_id = "";
$patient_id = "";
$scheduled_time = "";
$status = "";
$errormessage = "";
$successmessage = "";

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $doctor_id = intval($_POST["doctor_id"]);
    $patient_id = intval($_POST["patient_id"]);
    $scheduled_time = $_POST["scheduled_time"];
    $status = $_POST["status"];

    do {
        if (empty($doctor_id) || empty($patient_id) || empty($scheduled_time) || empty($status)) {
            $errormessage = "All fields are required";
            break;
        }

        $sql = "INSERT INTO appointments (doctor_id, patient_id, scheduled_time, status) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $errormessage = "Prepare failed: " . $conn->error;
            break;
        }
        $stmt->bind_param("iiss", $doctor_id, $patient_id, $scheduled_time, $status);
        $result = $stmt->execute();

        if (!$result) {
            $errormessage = "Error adding appointment: " . $stmt->error;
            break;
        }

        $successmessage = "Appointment added successfully";
        header("location: /medic/Admin/appointments.php");
        exit;
    } while (false);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Appointment</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container my-5">
    <h2>New Appointment</h2>

    <?php
        if(!empty($errormessage)) {
            echo "
                <div class='alert alert-warning alert-dismissible fade show' role='alert'>
                    <strong>$errormessage</strong>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>
            ";
        }
    ?>

    <form method="post">
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
                       value="<?= htmlspecialchars($scheduled_time) ?>" required>
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-sm-3 col-form-label">Status</label>
            <div class="col-sm-6">
                <select class="form-control" name="status" required>
                    <option value="">Select Status</option>
                    <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="accepted" <?= $status == 'accepted' ? 'selected' : '' ?>>Accepted</option>
                    <option value="rejected" <?= $status == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    <option value="cancelled" <?= $status == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
        </div>

        <?php
            if(!empty($successmessage)) {
                echo "
                    <div class='row mb-3'>
                        <div class='offset-sm-3 col-sm-6'>
                            <div class='alert alert-success alert-dismissible fade show' role='alert'>
                                <strong>$successmessage</strong>
                                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                            </div>
                        </div>
                    </div>
                ";
            }
        ?>

        <div class="row mb-3">
            <div class="offset-sm-3 col-sm-3 d-grid">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
            <div class="col-sm-3 d-grid">
                <a class="btn btn-outline-primary" href="/medic/Admin/appointments.php" role="button">Cancel</a>
            </div>
        </div>
    </form>
</div>
</body>
</html>