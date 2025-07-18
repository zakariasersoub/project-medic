<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Bootstrap Section</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #f8f9fa;">
  <div class="container my-5">
    <table class="table table-striped table-bordered">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>User Type</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
            $servername = "localhost";
            $username = "root";
            $password = "";
            $database = "medic";
            $conn = new mysqli($servername, $username, $password, $database);

            if($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // فلترة حسب نوع المستخدم
            $where = [];
            if (isset($_GET['user_type']) && $_GET['user_type'] != 'all' && $_GET['user_type'] != '') {
                $user_type = $conn->real_escape_string($_GET['user_type']);
                $where[] = "user_type = '$user_type'";
            }

            // فلترة حسب البحث بالاسم
            if (isset($_GET['search']) && $_GET['search'] != '') {
                $search = $conn->real_escape_string($_GET['search']);
                $where[] = "fullName LIKE '%$search%'";
            }

            $sql = "SELECT * FROM users";
            if (count($where) > 0) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }

            $result = $conn->query($sql);

            if(!$result) {
                die("Query failed: " . $conn->error);
            }

            while ($row = $result->fetch_assoc()) {
                echo "
                    <tr>
                        <td>{$row['id']}</td>
                        <td>{$row['fullName']}</td>
                        <td>{$row['email']}</td>
                        <td>{$row['phone']}</td>
                        <td>{$row['user_type']}</td>
                        <td>
                            <a class='btn btn-primary btn-sm' href='/medic/Admin/edit.php?id={$row['id']}'>Edit</a>
                            <a class='btn btn-danger btn-sm' href='/medic/Admin/delete.php?id={$row['id']}'>Delete</a>
                        </td>
                    </tr>
                ";
            }
            ?>
      </tbody>
    </table>
  </div>
</body>
</html>