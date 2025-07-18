<?php
session_start();
$conn = new mysqli("localhost", "root", "", "medic");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css"> -->
    <link rel="stylesheet" href="./style.css" />
    
    
    <title>Doctors Dashboard</title>
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
              <a href="Appointments.php" class="nav-link ">
                <!-- REPLACE WITH YOUR APPOINTMENTS ICON -->
                <img src="images/calendry_white.svg" alt="Appointments" class="nav-icon" />
                <span>Appointments</span>
              </a>
            </li>
            <li>
              <a href="#" class="nav-link active">
                <!-- REPLACE WITH YOUR DOCTORS ICON -->
                <img src="images/patient.svg" alt="Users" class="nav-icon" />
                <span>Users</span>
              </a>
            </li>
            


          </ul>
        </nav>
      </aside>

      <main class="main-content">
        <header class="page-header">
          <h1 class="page-title">Users List</h1>
          <div class="header-actions">
            <div class="notifications">
              <a href="../notifications/index.html">
                <!-- REPLACE WITH YOUR NOTIFICATION ICON -->
                <img src="images/bell.svg" alt="Notifications" class="notification-icon" />
              </a>
            </div>
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
      
        <div class="content-controls">
          <div class="search-container">
            <img src="images/search.svg" alt="Search" class="search-icon">
            <input type="text" placeholder="Search users..." class="search-input" id="userSearchInput" onkeyup="filterUsers()" autocomplete="off">
          </div>
          <div class="action-buttons">
            <form id="userTypeFilterForm" style="display:inline;">
              <select name="user_type" class="form-select" id="userTypeSelect" onchange="filterUsers()" style="padding: 11px 17px; border-radius: 8px; border: 1px solid #e5e7eb; background-color: transparent; font-size: 16px; font-weight: 600; color: #1e293b;">
                <option value="all">All</option>
                <option value="doctor">Doctor</option>
                <option value="patient">Patient</option>
              </select>
            </form>
            <button class="add-btn">
              <a href="/medic/Admin/create.php" style ="color: white; text-decoration: none;">
                <img src="images/add.svg" alt="Add">
                <span >Add User</span>
              </a>
            </button>
          </div>
        </div>

        <iframe id="usersFrame" src="bootstrap-section.php" style="width:100%; height:600px; border:none;"></iframe>

        <script>
        function filterUsers() {
          var input = document.getElementById('userSearchInput').value;
          var userType = document.getElementById('userTypeSelect').value;
          var frame = document.getElementById('usersFrame');
          var url = 'bootstrap-section.php?search=' + encodeURIComponent(input);
          if (userType && userType !== 'all') {
            url += '&user_type=' + encodeURIComponent(userType);
          }
          frame.src = url;
        }
        </script>       
        <!--                   cards                    -->

        
      </main>
    </div>


<!-- Popup Form -->
<!-- <div class="popup-overlay" id="addDoctorPopup">
  <div class="popup-content compact">
    <div class="popup-header">
      <h2>Add New Doctor</h2>
      <button class="close-btn">&times;</button>
    </div>
    <form id="doctorForm" class="popup-form" enctype="multipart/form-data">
      <div class="form-row">
        <div class="form-group avatar-upload">
          <label for="doctorAvatar">Photo</label>
          <div class="avatar-preview" id="avatarPreview">
            <img src="images/doctor.svg" alt="Preview" id="avatarImage">
          </div>
          <input type="file" id="doctorAvatar" name="avatar" accept="image/*" style="display: none;">
          <button type="button" class="upload-btn" id="uploadBtn">Choose Image</button>
        </div>
        <div class="form-group">
          <label for="doctorName">Full Name</label>
          <input type="text" id="doctorName" name="name" required>
        </div>
      </div>
      
      <div class="form-group">
        <label for="doctorSpecialty">Specialty</label>
        <select id="doctorSpecialty" name="specialty" required>
          <option value="">Select Specialty</option>
          <option value="Cardiologist">Cardiologist</option>
          <option value="Neurologist">Neurologist</option>
          <option value="Pediatrician">Pediatrician</option>
          <option value="Dermatologist">Dermatologist</option>
        </select>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label for="doctorEmail">Email</label>
          <input type="email" id="doctorEmail" name="email" required>
        </div>
        <div class="form-group">
          <label for="doctorPhone">Phone</label>
          <input type="tel" id="doctorPhone" name="phone" required>
        </div>
      </div>
      
      <div class="form-group">
        <label for="doctorClinic">Clinic</label>
        <input type="text" id="doctorClinic" name="clinic" required>
      </div>
      
      <div class="form-actions">
        <button type="button" class="cancel-btn">Cancel</button>
        <button type="submit" class="save-btn">Save Doctor</button>
      </div>
    </form>
  </div>
</div> -->

<script src="script.js"></script>
  </body>
</html>
