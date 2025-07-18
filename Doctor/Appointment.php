<?php
session_start(); // أضف هذا في أعلى الملف إذا لم يكن موجوداً

$servername = "localhost";
$username = "root";
$password = "";
$database = "medic";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

if (!isset($_SESSION['user_id'])) {
  die("Unauthorized access.");
}
$doctor_user_id = intval($_SESSION['user_id']);

// جلب معرف الطبيب من جدول doctors بناءً على user_id
$doctor_id = 0;
$stmt = $conn->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->bind_param("i", $doctor_user_id);
$stmt->execute();
$stmt->bind_result($doctor_id);
$stmt->fetch();
$stmt->close();

if ($doctor_id == 0) {
  die("You are not registered as a doctor.");
}

$sql = "SELECT 
      a.id,
      p_user.fullname AS doctor_name,
      d.specialty AS appointment_type,
      a.scheduled_time AS request_time,
      a.status
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    JOIN patients p ON a.patient_id = p.id
    JOIN users p_user ON p.user_id = p_user.id
    WHERE a.doctor_id = $doctor_id AND a.status NOT IN ('canceled', 'cancelled')

    ORDER BY a.request_time ASC";

$result = $conn->query($sql);

?>



<!doctype html>
<html lang="en">
  <head>
    <link rel="preload" href="important-image.webp" as="image">

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css"> -->
    <link rel="stylesheet" href="Appointment.css" />
    <title>Appointments Dashboard</title>
  </head>
  <body>
    <div class="dashboard-layout">
      <aside class="sidebar">
        <div class="logo-container">
            <!-- REPLACE WITH YOUR LOGO IMAGE -->
            <img src="images/logo.svg" alt="App Logo" class="logo" />
            <p>Divo</p>
        </div>
        <nav class="main-nav">
          <ul class="nav-list">
            <li>
                <a href="Home.php" class="nav-link">
                    <!-- REPLACE WITH YOUR HOME ICON -->
                    <img src="images/home.svg" alt="Home" class="nav-icon" />
                    <span>Home</span>
                </a>
            </li>
            <li>
                <a href="#" class="nav-link active">
                    <!-- REPLACE WITH YOUR APPOINTMENTS ICON -->
                    <img src="images/cal.svg" alt="Appointments" class="nav-icon" />
                    <span>Appointments</span>
                </a>
            </li>
            
            
          </ul>
        </nav>
      </aside>

      <main class="main-content">
        <header class="header">
          <h1 class="page-title">My Appointments</h1>
          <div class="header-actions">
            <button class="notification-btn" aria-label="Notifications">
              <a href="../notifications/index.html">
                <!-- REPLACE WITH YOUR NOTIFICATION ICON -->
                <img src="images/bell.svg" alt="Notifications" class="notification-icon" />
              </a>
            </button>
            <?php
                // ...existing code...
                $profile_image_src = 'images/default.avif'; // الصورة الافتراضية
                if ($user_id > 0) {
                    $user_result = $conn->query("SELECT avatar FROM users WHERE id = $user_id LIMIT 1");
                    if ($user_row = $user_result->fetch_assoc()) {
                        if (!empty($user_row['avatar'])) {
                            $avatar_data = $user_row['avatar'];
                            $base64 = base64_encode($avatar_data);
                            $profile_image_src = 'data:image/jpeg;base64,' . $base64;
                        }
                    }
                }
            ?>
            <div style="position: relative; display: inline-block;">
                <img src="<?= $profile_image_src ?>" alt="Profile" class="profile-image" id="profileImage" style="cursor:pointer;" />
                <div id="profilePopup" style="display:none; position:absolute; top:110%; right:0; background:#fff; border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 8px 32px rgba(0,0,0,0.13); padding:24px 28px; z-index:1000; min-width:220px; min-height:120px;">
                    <form id="changeAvatarForm" action="change_avatar.php" method="post" enctype="multipart/form-data" style="display:flex; flex-direction:column; align-items:center;">
                        <div style="width:70px; height:70px; border-radius:50%; overflow:hidden; margin-bottom:14px; border:2px solid #2563eb; background:#f1f5f9;">
                            <img id="avatarPreview" src="<?= $profile_image_src ?>" alt="Preview" style="width:100%; height:100%; object-fit:cover;">
                        </div>
                        <label for="avatarInput" style="background:#2563eb; color:#fff; border:none; border-radius:7px; padding:7px 18px; font-weight:600; cursor:pointer; margin-bottom:10px; transition:background 0.2s;">اختر صورة جديدة</label>
                        <input type="file" id="avatarInput" name="avatar" accept="image/*" required style="display:none;">
                        <button type="submit" style="background:#22c55e; color:#fff; border:none; border-radius:7px; padding:7px 18px; font-weight:600; cursor:pointer; width:100%;">رفع الصورة</button>
                    </form>
                </div>
            </div>
            <script>
                // إظهار/إخفاء المربع المنبثق عند الضغط على الصورة
                const profileImg = document.getElementById('profileImage');
                const popup = document.getElementById('profilePopup');
                document.addEventListener('click', function(e){
                    if (profileImg.contains(e.target)) {
                        popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
                    } else if (!popup.contains(e.target)) {
                        popup.style.display = 'none';
                    }
                });

                // معاينة الصورة قبل الرفع
                const avatarInput = document.getElementById('avatarInput');
                const avatarPreview = document.getElementById('avatarPreview');
                document.querySelector('label[for="avatarInput"]').onclick = function() {
                    avatarInput.click();
                };
                avatarInput.onchange = function(e) {
                    if (e.target.files && e.target.files[0]) {
                        avatarPreview.src = URL.createObjectURL(e.target.files[0]);
                    }
                };
            </script>
          </div>
        </header>

        <section class="upcoming-appointments">
          <h2 class="section-title">Upcoming Appointments</h2>
          <div class="appointments-list">
            <?php
              if ($result && $result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                      $badgeClass = ($row['status'] == "scheduled") ? "pending" :
                                    (($row['status'] == "completed") ? "confirmed" : "cancelled");

                      $formattedDate = date("l, F j, Y", strtotime($row['request_time']));
                      $formattedTime = date("g:i A", strtotime($row['request_time']));

                      // Status badge color logic
                      $statusColors = [
                        'pending' => 'orange',
                        'accepted' => 'green',
                        'completed' => 'green',
                        'confirmed' => 'green',
                        'canceled' => 'red',
                        'cancelled' => 'red'
                      ];
                      $status = strtolower($row['status']);
                      $color = isset($statusColors[$status]) ? $statusColors[$status] : 'secondary';

                      echo '
                      <article class="appointment-card">
                        <div class="appointment-details">
                          <span class="status-badge ' . $badgeClass . '" style="background:' . $color . '; color:#fff;">' . ucfirst($row['status']) . '</span>
                          <h3 class="doctor-name mt-2">' . htmlspecialchars($row['doctor_name']) . '</h3>
                          <p class="appointment-type">' . htmlspecialchars($row['appointment_type']) . '</p>
                          <div class="appointment-meta d-flex align-items-center">
                            <div class="meta-item me-4 d-flex align-items-center">
                              <img src="images/callandry.svg" alt="Date" class="meta-icon" width="20" height="20" />
                              <span>' . $formattedDate . '</span>
                            </div>
                            <div class="meta-item d-flex align-items-center">
                              <img src="images/watch.svg" alt="Time" class="meta-icon" width="20" height="20" />
                              <span>' . $formattedTime . '</span>
                            </div>
                          </div>
                        </div>
                        <div class="appointment-actions mt-2">
                          <a style="  text-decoration: none;" class="btn-cancel btn btn-danger btn-md me-2" style="font-size: 1.1rem; padding: 0.6rem 1.2rem;" href="refuse.php?id=' . $row['id'] . '">
                          <img src="images/x.svg" alt="Refuse" class="action-icon" width="15" height="15" /> Refuse
                          </a>
                          <a style="  text-decoration: none;" class="btn-reschedule btn btn-primary btn-md" style="font-size: 1.1rem; padding: 0.6rem 1.2rem;" href="accept.php?id=' . $row['id'] . '">
                          <img src="images/return.svg" alt="Accept" class="action-icon" width="18" height="18" /> Accept
                          </a>
                        </div>
                      </article>';
                  }
              } else {
                  echo "<p>No appointments found.</p>";
              }

              $conn->close();
            ?>
          </div>

        </section>

      </main>
    </div>

  </body>
</html>