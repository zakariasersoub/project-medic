<?php


$servername = "localhost";
$username = "root";
$password = "";
$database = "medic";

mysqli_report(MYSQLI_REPORT_OFF);

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

$id = "";
$fullname = "";
$email = "";
$phone = "";
$password = "";
$adress = "";
$user_type = "";
$specialty = "";
$clinic = "";
$errormessage = "";
$successmessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Show the data of the user
    if (!isset($_GET["id"])) {
        header("location: /medic/Admin/bootstrap-section.php");
        exit;
    }

    $id = $_GET["id"];

    // Get user data
    $sql = "SELECT * FROM users WHERE id = $id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if (!$row) {
        header("location: /medic/Admin/indebootstrap-sectionx.php");
        exit;
    }

    $fullname = $row['fullName'];
    $email = $row['email'];
    $password = $row['password'];
    $phone = $row['phone'];
    $adress = $row['adress'];
    $user_type = $row['user_type'];

    // If doctor, get specialty and clinic
    if ($user_type == "doctor") {
        $sql2 = "SELECT specialty, clinic FROM doctors WHERE user_id = $id";
        $result2 = $conn->query($sql2);
        $row2 = $result2->fetch_assoc();
        if ($row2) {
            $specialty = $row2['specialty'];
            $clinic = $row2['clinic'];
        }
    }
} else {
    // POST method: Update the data
    $id = $_POST["id"];
    $fullname = $_POST["fullname"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $phone = $_POST["phone"];
    $adress = $_POST["adress"];
    $user_type = $_POST["user_type"];
    $specialty = isset($_POST["specialty"]) ? $_POST["specialty"] : "";
    $clinic = isset($_POST["clinic"]) ? $_POST["clinic"] : "";

    $avatar = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $avatar = file_get_contents($_FILES['avatar']['tmp_name']);
    }

    do {
        if (empty($fullname) || empty($email) || empty($phone) || empty($adress) || empty($user_type) || empty($password)) {
            $errormessage = "All fields are required";
            break;
        }

        

        // تحديث المستخدم مع أو بدون صورة جديدة
        if ($avatar !== null) {
            $sql = "UPDATE users SET fullname=?, email=?, password=?, phone=?, adress=?, user_type=?, avatar=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssi", $fullname, $email, $password, $phone, $adress, $user_type, $avatar, $id);
        } else {
            $sql = "UPDATE users SET fullname=?, email=?, password=?, phone=?, adress=?, user_type=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $fullname, $email, $password, $phone, $adress, $user_type, $id);
        }
        $result = $stmt->execute();

        if (!$result) {
            $errormessage = "Error updating user: " . $conn->error;
            break;
        }

        

        // Update doctor or patient table
        if ($user_type == "doctor") {
            // Check if doctor row exists
            $check = $conn->query("SELECT * FROM doctors WHERE user_id = $id");
            if ($check->num_rows > 0) {
                $stmt2 = $conn->prepare("UPDATE doctors SET specialty=?, clinic=? WHERE user_id=?");
                $stmt2->bind_param("ssi", $specialty, $clinic, $id);
                $stmt2->execute();
            } else {
                $stmt2 = $conn->prepare("INSERT INTO doctors (user_id, specialty, clinic) VALUES (?, ?, ?)");
                $stmt2->bind_param("iss", $id, $specialty, $clinic);
                $stmt2->execute();
            }
            // Remove from patients if exists
            $conn->query("DELETE FROM patients WHERE user_id = $id");
        } elseif ($user_type == "patient") {
            // Check if patient row exists
            $check = $conn->query("SELECT * FROM patients WHERE user_id = $id");
            if ($check->num_rows == 0) {
                $conn->query("INSERT INTO patients (user_id) VALUES ($id)");
            }
            // Remove from doctors if exists
            $conn->query("DELETE FROM doctors WHERE user_id = $id");
        }

        $successmessage = "User updated successfully";
        header("location: /medic/Admin/bootstrap-section.php");
        exit;
    } while (false);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Client</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userTypeSelect = document.getElementsByName('user_type')[0];
            const doctorFields = document.getElementById('doctorFields');
            function toggleDoctorFields() {
                if (userTypeSelect.value === 'doctor') {
                    doctorFields.style.display = 'block';
                } else {
                    doctorFields.style.display = 'none';
                }
            }
            userTypeSelect.addEventListener('change', toggleDoctorFields);
            toggleDoctorFields();
        });
    </script>
</head>
<body>
    <div class="container my-5">
        <h2>Edit Client</h2>

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

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">

            <div class="row mb-3">
                <label for="avatar" class="col-sm-3 col-form-label">Upload Profile Image:</label>
                <div class="col-sm-6">
                <input type="file" class="form-control" name="avatar" id="avatar" accept="image/*" >
                </div>
            </div> 
            
            <div class="row mb-3">
                <label for="fullname" class="col-sm-3 col-form-label">Name</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="fullname" value ="<?php echo $fullname; ?>">
                </div>
            </div>
            
            <div class="row mb-3">
                <label for="email" class="col-sm-3 col-form-label">Email</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="email" value ="<?php echo $email; ?>">
                </div>
            </div>

            <div class="row mb-3">
                <label for="email" class="col-sm-3 col-form-label">Password</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="password" value ="<?php echo $password; ?>">
                </div>
            </div>

            <div class="row mb-3">
                <label for="phone" class="col-sm-3 col-form-label">Phone</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="phone" value ="<?php echo $phone; ?>">
                </div>
            </div>

            <div class="row mb-3">
                <label for="adress" class="col-sm-3 col-form-label">Adress</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="adress" value ="<?php echo $adress; ?>">
                </div>
            </div>
            <div class="row mb-3">
                <label for="user_type" class="col-sm-3 col-form-label">User Type</label>
                <div class="col-sm-6">
                    <select class="form-control" name="user_type">
                        <option value="">Select type</option>
                        <option value="patient" <?php if($user_type=="patient") echo "selected"; ?>>Patient</option>
                        <option value="doctor" <?php if($user_type=="doctor") echo "selected"; ?>>Doctor</option>
                    </select>
                </div>
            </div>

            <!-- Doctor extra fields -->
            <div id="doctorFields" style="display:none;">
                <div class="row mb-3">
                    <label for="clinic" class="col-sm-3 col-form-label">Clinic</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="clinic" value="<?php echo $clinic; ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="specialty" class="col-sm-3 col-form-label">Specialty</label>
                    <div class="col-sm-6">
                        <select id="specialty" name="specialty" class="form-control specialty-select">
                            <option value="">Select Specialty</option>
                            <option value="Cardiology" <?php if($specialty=="Cardiology") echo "selected"; ?>>Cardiology</option>
                            <option value="Dermatology" <?php if($specialty=="Dermatology") echo "selected"; ?>>Dermatology</option>
                            <option value="Neurology" <?php if($specialty=="Neurology") echo "selected"; ?>>Neurology</option>
                            <option value="Pediatrics" <?php if($specialty=="Pediatrics") echo "selected"; ?>>Pediatrics</option>
                            <option value="Orthopedics" <?php if($specialty=="Orthopedics") echo "selected"; ?>>Orthopedics</option>
                            <option value="Ophthalmology" <?php if($specialty=="Ophthalmology") echo "selected"; ?>>Ophthalmology</option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- End Doctor extra fields -->

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
                    <button type="submit" class="btn btn-primary" >Save</button>
                </div>
                <div class="col-sm-3 d-grid">
                    <a class="btn btn-outline-primary" href="/medic/Admin/bootstrap-section.php" role="button">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</body>
</html>