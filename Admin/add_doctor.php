<?php
header('Content-Type: application/json');

// Database connection
$conn = mysqli_connect("localhost", "root", "", "medic");
if (!$conn) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Handle GET request to fetch all doctors
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT users.fullName AS name, users.email, users.phone, doctors.specialty, doctors.clinic, doctors.id AS doctorId 
              FROM users 
              JOIN doctors ON users.id = doctors.user_id";
    $result = mysqli_query($conn, $query);

    $doctors = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $doctors[] = $row;
    }

    echo json_encode(['success' => true, 'doctors' => $doctors]);
    exit;
}

// Process avatar upload
$avatarPath = null;
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $targetDir = "uploads/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = uniqid() . '_' . basename($_FILES['avatar']['name']);
    $targetPath = $targetDir . $fileName;
    
    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
        $avatarPath = $targetPath;
    }
}

// Get form data
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$specialty = $_POST['specialty'];
$clinic = $_POST['clinic'];

// 1. Add user to users table
$userSql = "INSERT INTO users (fullName, email, phone, user_type) 
            VALUES (?, ?, ?, 'doctor')";
$stmt = mysqli_prepare($conn, $userSql);
mysqli_stmt_bind_param($stmt, "sss", $name, $email, $phone);
mysqli_stmt_execute($stmt);
$userId = mysqli_insert_id($conn);

// 2. Add doctor to doctors table
if ($userId) {
    $doctorSql = "INSERT INTO doctors (user_id, specialty, clinic) 
                  VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $doctorSql);
    mysqli_stmt_bind_param($stmt, "iss", $userId, $specialty, $clinic);
    mysqli_stmt_execute($stmt);
    $doctorId = mysqli_insert_id($conn);
    
    echo json_encode([
        'success' => true,
        'doctorId' => $doctorId,
        'avatarPath' => $avatarPath
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to add user'
    ]);
}

mysqli_close($conn);
?>