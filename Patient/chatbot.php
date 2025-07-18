<?php
$conn = new mysqli("localhost", "root", "", "medic");
if ($conn->connect_error) {
    die("Connection error");
}
$msg = isset($_POST['message']) ? strtolower(trim($_POST['message'])) : '';
$response = "<div style='direction: rtl; text-align: right;'>مرحباً بك في نظام الاستفسار! . يمكنك أن تسأل عن:<br>
- اسم طبيب (مثال: دكتور Ahmed)<br>
- تخصص (مثال: تخصص Cardiology)<br>
- عيادة (مثال: عيادة el monsef)<br>
يرجى كتابة سؤالك بشكل واضح.</div>";

if ($msg) {
    // جميع الأطباء
    if (strpos($msg, 'جميع الأطباء') !== false || strpos($msg, 'all doctors') !== false) {
        $sql = "SELECT users.fullname, doctors.specialty FROM users JOIN doctors ON users.id=doctors.user_id";
        $res = $conn->query($sql);
        if ($res->num_rows > 0) {
            $response = "قائمة الأطباء:<br>";
            while($row = $res->fetch_assoc()) {
                $response .= "{$row['fullname']} (تخصص: {$row['specialty']})<br>";
            }
        } else {
            $response = "لا يوجد أطباء حالياً.";
        }
    }
    // عدد الأطباء
    elseif (strpos($msg, 'كم عدد الأطباء') !== false || strpos($msg, 'how many doctors') !== false) {
        $sql = "SELECT COUNT(*) as total FROM doctors";
        $res = $conn->query($sql);
        $row = $res->fetch_assoc();
        $response = "عدد الأطباء في النظام: {$row['total']}";
    }
    // التخصصات المتوفرة
    elseif (strpos($msg, 'ما هي التخصصات') !== false || strpos($msg, 'specialties') !== false) {
        $sql = "SELECT DISTINCT specialty FROM doctors";
        $res = $conn->query($sql);
        $response = "التخصصات المتوفرة:<br>";
        while($row = $res->fetch_assoc()) {
            $response .= "{$row['specialty']}<br>";
        }
    }
    // سؤال عن عيادات المستشفى
    elseif (strpos($msg, 'عيادات المستشفى') !== false || strpos($msg, 'hospital clinics') !== false) {
        $sql = "SELECT DISTINCT clinic FROM doctors";
        $res = $conn->query($sql);
        $response = "العيادات المتوفرة:<br>";
        while($row = $res->fetch_assoc()) {
            $response .= "{$row['clinic']}<br>";
        }
    }
    // سؤال عن طبيب بالاسم أو جزء من الاسم
    elseif (preg_match('/(doctor|دكتور)\s*([a-zأ-ي ]+)/i', $msg, $m)) {
        $name = $conn->real_escape_string(trim($m[2]));
        $sql = "SELECT users.fullname, doctors.specialty, doctors.clinic, users.phone, users.email 
                FROM users JOIN doctors ON users.id=doctors.user_id 
                WHERE users.fullname LIKE '%$name%'";
        $res = $conn->query($sql);
        if ($row = $res->fetch_assoc()) {
            $response = "الاسم: {$row['fullname']}<br>التخصص: {$row['specialty']}<br>العيادة: {$row['clinic']}<br>الهاتف: {$row['phone']}<br>البريد: {$row['email']}";
        } else {
            $response = "لم يتم العثور على طبيب بهذا الاسم.";
        }
    }
    // سؤال عن تخصص
    elseif (preg_match('/(تخصص|specialty)\s*([a-zأ-ي ]+)/i', $msg, $m)) {
        $spec = $conn->real_escape_string(trim($m[2]));
        $sql = "SELECT users.fullname, doctors.clinic FROM users JOIN doctors ON users.id=doctors.user_id WHERE doctors.specialty LIKE '%$spec%'";
        $res = $conn->query($sql);
        if ($res->num_rows > 0) {
            $response = "أطباء تخصص {$spec}:<br>";
            while($row = $res->fetch_assoc()) {
                $response .= "{$row['fullname']} (العيادة: {$row['clinic']})<br>";
            }
        } else {
            $response = "لا يوجد أطباء بهذا التخصص.";
        }
    }
    // سؤال عن عيادة
    elseif (preg_match('/(عيادة|clinic)\s*([a-zأ-ي ]+)/i', $msg, $m)) {
        $clinic = $conn->real_escape_string(trim($m[2]));
        $sql = "SELECT users.fullname, doctors.specialty FROM users JOIN doctors ON users.id=doctors.user_id WHERE doctors.clinic LIKE '%$clinic%'";
        $res = $conn->query($sql);
        if ($res->num_rows > 0) {
            $response = "أطباء في عيادة {$clinic}:<br>";
            while($row = $res->fetch_assoc()) {
                $response .= "{$row['fullname']} (تخصص: {$row['specialty']})<br>";
            }
        } else {
            $response = "لا يوجد أطباء في هذه العيادة.";
        }
    }
    // سؤال عن رقم هاتف أو بريد طبيب
    elseif (strpos($msg, 'رقم') !== false || strpos($msg, 'phone') !== false || strpos($msg, 'بريد') !== false || strpos($msg, 'email') !== false) {
        $sql = "SELECT users.fullname, users.phone, users.email FROM users JOIN doctors ON users.id=doctors.user_id";
        $res = $conn->query($sql);
        $response = "بيانات التواصل مع الأطباء:<br>";
        while($row = $res->fetch_assoc()) {
            $response .= "{$row['fullname']} - هاتف: {$row['phone']} - بريد: {$row['email']}<br>";
        }
    }
    // سؤال عام عن النظام أو المساعدة
    elseif (strpos($msg, 'مساعدة') !== false || strpos($msg, 'help') !== false) {
        $response = "يمكنك أن تسألني عن: جميع الأطباء، عدد الأطباء، التخصصات المتوفرة، عيادات المستشفى، معلومات عن طبيب، أو تخصص.";
    }
}
echo $response;
?>