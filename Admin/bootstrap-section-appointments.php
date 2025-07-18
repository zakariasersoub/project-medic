<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Bootstrap Section</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style = "background-color: #f8f9fa;">
  <div class="container my-5">
    <table class="table table-striped table-bordered">
      <thead>
        <tr>
          <th>ID</th>
          <th>PatientName</th>
          <th>DoctorName</th>
          <th>scheduledDate</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
            $servername = "localhost";
            $username = "root";
            $password = "";
            $database = "medic";
            $conn = new mysqli($servername, $username, $password, $database);

            if($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // فلترة حسب الحالة
            $where = [];
            if (isset($_GET['status']) && $_GET['status'] != 'all' && $_GET['status'] != '') {
                $status = $conn->real_escape_string($_GET['status']);
                $where[] = "appointments.status = '$status'";
            }

            // فلترة حسب البحث باسم المريض أو الطبيب
            if (isset($_GET['search']) && $_GET['search'] != '') {
                $search = $conn->real_escape_string($_GET['search']);
                $where[] = "(patient_user.fullName LIKE '%$search%' OR doctor_user.fullName LIKE '%$search%')";
            }

            $sql = "SELECT 
                        appointments.id,
                        patient_user.fullName AS patient_name,
                        doctor_user.fullName AS doctor_name,
                        appointments.status,
                        appointments.scheduled_time
                    FROM appointments
                    JOIN doctors ON doctors.id = appointments.doctor_id
                    JOIN users AS doctor_user ON doctors.user_id = doctor_user.id
                    JOIN patients ON patients.id = appointments.patient_id
                    JOIN users AS patient_user ON patients.user_id = patient_user.id";
            if (count($where) > 0) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }

            $result = $conn->query($sql);

            if(!$result) {
                die("Query failed: " . $conn->error);
            }

            while ($row = $result->fetch_assoc()) {
                echo "
                    <tr>
                        <td>{$row['id']}</td>
                        <td>{$row['patient_name']}</td>
                        <td>{$row['doctor_name']}</td>
                        <td>{$row['scheduled_time']}</td>
                        <td>{$row['status']}</td>
                        <td>
                            <a class='btn btn-primary btn-sm' href='/medic/Admin/edit-appointment.php?id={$row['id']}'>Edit</a>
                            <a class='btn btn-danger btn-sm' href='/medic/Admin/delete-appointment.php?id={$row['id']}'>Delete</a>
                        </td>
                    </tr>
                ";
            }
            ?>
      </tbody>
    </table>
  </div>
</body>
</html>