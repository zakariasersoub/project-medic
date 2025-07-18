<?php
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // Database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "medic";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        $sql = "DELETE FROM users WHERE id=$id";
        $conn->query($sql);
       
    } 
    header("Location: bootstrap-section.php");
    exit();

?>