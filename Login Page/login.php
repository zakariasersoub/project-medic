<?php
session_start();
$email = $_POST["email"];
$password = $_POST["password"];

// الاتصال بقاعدة البيانات
$conn = mysqli_connect("localhost", "root", "", "medic");

if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// البحث عن المستخدم بناءً على البريد مع نوع المستخدم
$stmt = $conn->prepare("SELECT id, password, user_type FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($user_id, $hashed_password, $user_type);
    $stmt->fetch();

    // التحقق من كلمة المرور
    if ($password === $hashed_password) {
        $_SESSION['user_id'] = $user_id;
        
        echo "تم تسجيل الدخول بنجاح!"; 
        
        // توجيه المستخدم حسب نوعه
        if ($user_type === 'doctor') {
            // // التحقق من حالة الموافقة على الطبيب
            // $stmt2 = $conn->prepare("SELECT is_verified FROM doctors WHERE user_id = (SELECT id FROM users WHERE email = ?)");
            // $stmt2->bind_param("s", $email);
            // $stmt2->execute();
            // $stmt2->bind_result($approved);
            // $stmt2->fetch();
            // $stmt2->close();
            
            // if ($approved) {
                header("Location: ../doctor");
            } elseif ($user_type === 'admin') {
                header("Location: ../Admin");
            }elseif ($user_type === 'patient') {
                header("Location: ../patient");
            } else {
                // نوع مستخدم غير معروف
                header("Location: .");
        }
        exit();
    } else {
        echo "كلمة المرور غير صحيحة.";
        exit();
    }
} else {
    echo "الحساب غير موجود.";
    exit();
}

$stmt->close();
$conn->close();
?>