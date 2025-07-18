<?php
    $email = $_POST['email'];

    // الاتصال بقاعدة البيانات
    $conn = mysqli_connect("localhost", "root", "", "medic");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } else {
        // التحقق من وجود الإيميل في قاعدة البيانات أولاً
        $check_stmt = $conn->prepare("SELECT email FROM newsletter WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            echo "<script>alert('هذا البريد الإلكتروني مسجل من قبل');</script>";
        } else {
            // حفظ الإيميل في جدول newsletter
            $insert_stmt = $conn->prepare("INSERT INTO newsletter (email) VALUES (?)");
            $insert_stmt->bind_param("s", $email);
            $insert_stmt->execute();
            
            if ($insert_stmt->affected_rows > 0) {
                echo "<script>alert('تم الاشتراك بنجاح');</script>";
            } else {
                echo "<script>alert('فشل الاشتراك');</script>";
            }
            
            $insert_stmt->close();
        }
        
        $check_stmt->close();
        $conn->close();
    }
?>
