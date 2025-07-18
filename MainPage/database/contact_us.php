<?php 

$name = $_POST['name'];
$email = $_POST['email'];
$message = $_POST['message'];

//connect to the database

$conn = mysqli_connect('localhost', 'root', '', 'medic');

//check if the connection is successful
if ($conn -> connect_error) {
    die("Connection failed: " . $conn -> connect_error);
}else {
    
        $stmt = $conn -> prepare("INSERT INTO contact_us (name , email , message) VALUES (?, ?, ?)");
        $stmt -> bind_param("sss", $name, $email, $message);
        $stmt -> execute();
        echo "<script>alert('Registration successful');</script>";
        $stmt -> close();
        $conn -> close();
    
}




