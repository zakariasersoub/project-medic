<?php
// إعداد اتصال قاعدة البيانات
$conn = mysqli_connect('localhost', 'root', '', 'medic');

// التحقق من الاتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// جمع بيانات النموذج
$userType = $_POST['userType'] ?? ''; // نوع المستخدم (patient/doctor)
$fullName = $_POST['fullName'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// حقول الطبيب الإضافية
$specialty = $_POST['specialty'] ?? '';
$clinic = $_POST['clinic'] ?? '';

// التحقق من صحة البيانات الأساسية
if (empty($fullName) || empty($email) || empty($phone) || empty($password)) {
    die("All required fields must be filled");
}

// التحقق من تطابق كلمات المرور
if ($password !== $confirmPassword) {
    die("Passwords do not match");
}

// التحقق من عدم وجود البريد الإلكتروني مسبقاً
$checkEmail = $conn->prepare("SELECT email FROM users WHERE email = ?");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$checkEmail->store_result();

if ($checkEmail->num_rows > 0) {
    die("Email already registered");
}
$checkEmail->close();

// معالجة ملف الرخصة للطبيب
$licensePath = '';
if ($userType === 'doctor' && isset($_FILES['license'])) {
    $licenseFile = $_FILES['license'];
    
    // التحقق من وجود أخطاء في التحميل
    if ($licenseFile['error'] !== UPLOAD_ERR_OK) {
        die("File upload error");
    }
    
    // التحقق من نوع الملف
    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    if (!in_array($licenseFile['type'], $allowedTypes)) {
        die("Only JPG, PNG, and PDF files are allowed");
    }
    
    // التحقق من حجم الملف (5MB كحد أقصى)
    if ($licenseFile['size'] > 5 * 1024 * 1024) {
        die("File size must be less than 5MB");
    }
    
    // إنشاء مجلد التحميل إذا لم يكن موجوداً
    if (!file_exists('uploads/licenses')) {
        mkdir('uploads/licenses', 0777, true);
    }
    
    // إنشاء اسم فريد للملف
    $licenseExt = pathinfo($licenseFile['name'], PATHINFO_EXTENSION);
    $licenseFileName = 'license_' . time() . '.' . $licenseExt;
    $licensePath = 'uploads/licenses/' . $licenseFileName;
    
    // نقل الملف إلى المجلد المطلوب
    if (!move_uploaded_file($licenseFile['tmp_name'], $licensePath)) {
        die("Failed to upload license file");
    }
}

// إدراج البيانات في جدول المستخدمين (بدون تشفير كلمة المرور)
$stmt = $conn->prepare("INSERT INTO users (fullName, email, phone, password, user_type) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $fullName, $email, $phone, $password, $userType);

if (!$stmt->execute()) {
    die("Registration failed: " . $stmt->error);
}

$userId = $stmt->insert_id;
$stmt->close();

// // تشفير كلمة المرور
// $hashedPassword = password_hash($password, PASSWORD_DEFAULT);


// إدراج البيانات في الجدول المناسب حسب نوع المستخدم
if ($userType === 'patient') {
    $stmt = $conn->prepare("INSERT INTO patients (user_id) VALUES (?)");
    $stmt->bind_param("i", $userId);
} elseif ($userType === 'doctor') {
    $stmt = $conn->prepare("INSERT INTO doctors (user_id, specialty, license_file, clinic) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userId, $specialty, $licensePath, $clinic);
}

if (!$stmt->execute()) {
    // حذف المستخدم إذا فشل إدراج بيانات المريض/الطبيب
    $conn->query("DELETE FROM users WHERE id = $userId");
    die("Registration failed: " . $stmt->error);
}

$stmt->close();

// إرسال بريد إلكتروني ترحيبي حسب نوع المستخدم
if ($userType === 'patient') {
    // إرسال بريد ترحيبي للمريض
    $subject = "Welcome to Our Medical Service";
    $message = "Dear $fullName,\n\nThank you for registering as a patient.";
} else {
    // إرسال بريد ترحيبي للطبيب (في انتظار الموافقة)
    $subject = "Doctor Registration Under Review";
    $message = "Dear Dr. $fullName,\n\nYour registration is under review. We will notify you once approved.";
}

// إرسال البريد الإلكتروني
mail($email, $subject, $message);

// إعادة التوجيه إلى صفحة تسجيل الدخول مع رسالة نجاح
header("Location: ../Login Page/index.html?registration=success");
exit();

// دالة لتنظيف بيانات الإدخال
function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}