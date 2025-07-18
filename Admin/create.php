<?php

$servername = "localhost";
$username = "root";
$password = "";
$database = "medic";

mysqli_report(MYSQLI_REPORT_OFF);

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST["fullname"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $password = $_POST["password"];
    $adress = $_POST["adress"];
    $user_type = $_POST["user_type"];
    $specialty = isset($_POST["specialty"]) ? $_POST["specialty"] : "";
    $clinic = isset($_POST["clinic"]) ? $_POST["clinic"] : "";

    //     // ... باقي الكود كما هو ...

    // // Handle image upload
    // $photo = null;
    // if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
    //     $photo = file_get_contents($_FILES['avatar']['tmp_name']);
    // }

    // // ... داخل do { ... } while (false)
    //     // Add to users with photo
    //     $sql = "INSERT INTO users (fullname, email, phone, password, adress, user_type, photo) VALUES (?, ?, ?, ?, ?, ?, ?)";
    //     $stmt = $conn->prepare($sql);
    //     if (!$stmt) {
    //         $errormessage = "Prepare failed: " . $conn->error;
    //         break;
    //     }
    //     $stmt->bind_param("sssssss", $fullname, $email, $phone, $password, $adress, $user_type, $photo);
    //     $result = $stmt->execute();
    // // ... باقي الكود كما هو ...


    // Handle image upload
    $avatar = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $avatar = file_get_contents($_FILES['avatar']['tmp_name']);
    }

    do {
        if (empty($fullname) || empty($email) || empty($phone) || empty($adress) || empty($user_type) || empty($password)) {
            $errormessage = "All fields are required";
            break;
        }

        if ($user_type != "patient" && $user_type != "doctor") {
            $errormessage = "User type must be either 'patient' or 'doctor'";
            break;
        }

        // Add to users with avatar
        $sql = "INSERT INTO users (fullname, email, phone, password, adress, user_type, avatar) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $errormessage = "Prepare failed: " . $conn->error;
            break;
        }
        $stmt->bind_param("sssssss", $fullname, $email, $phone, $password, $adress, $user_type, $avatar);
        $result = $stmt->execute();

        if (!$result) {
            $errormessage = "Error adding client: " . $stmt->error;
            break;
        }

        // get inserted id
        $user_id = $conn->insert_id;

        // insert into patient or doctor
        if ($user_type === "patient") {
            $conn->query("INSERT INTO patients (user_id) VALUES ('$user_id')");
        } elseif ($user_type === "doctor") {
            $stmt2 = $conn->prepare("INSERT INTO doctors (user_id, specialty, clinic) VALUES (?, ?, ?)");
            $stmt2->bind_param("iss", $user_id, $specialty, $clinic);
            $stmt2->execute();
        }

        // clear inputs
        $fullname = "";
        $email = "";
        $phone = "";
        $password = "";
        $adress = "";
        $user_type = "";
        $specialty = "";
        $clinic = "";

        $successmessage = "Client added successfully";
        header("location: /medic/Admin/index.php");
        exit;
    } while (false);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Client</title>
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
        <h2>New Client</h2>

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
                <label for="phone" class="col-sm-3 col-form-label">Password</label>
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
                        <input type="text" class="form-control" name="clinic" value="">
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="specialty" class="col-sm-3 col-form-label">Specialty</label>
                    <div class="col-sm-6">
                        <select id="specialty" name="specialty" class="form-control specialty-select">
                            <option value="">Select Specialty</option>
                            <option value="Cardiology">Cardiology</option>
                            <option value="Dermatology">Dermatology</option>
                            <option value="Neurology">Neurology</option>
                            <option value="Pediatrics">Pediatrics</option>
                            <option value="Orthopedics">Orthopedics</option>
                            <option value="Ophthalmology">Ophthalmology</option>
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
                    <button type="submit" class="btn btn-primary" >Submit</button>
                </div>
                <div class="col-sm-3 d-grid">
                    <a class="btn btn-outline-primary" href="/medic/Admin/index.php" role="button">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</body>
</html>