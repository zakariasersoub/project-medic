<?php 
require_once 'mail.php'; 

if (isset($_POST['send'])) {

    $mail->setFrom('szikodz2018@gmail.com', 'zakaria sersoub');
    $mail->addAddress('szakaria15dz@gmail.com');
    $mail->Subject = 'اختبار إشعار';
    $mail->Body = 'i am zakaria sersoub';

    $mail->send();
    if (!$mail->send()) {
        echo 'فشل الإرسال. الخطأ: ' . $mail->ErrorInfo;
    } else {
        echo 'تم إرسال الرسالة بنجاح';
    }
    header('Location: index.php' , true);
}

?>

<form method = "post">
    <button type = "submit" name = "send"> send </button>
</form>