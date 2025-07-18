<?php
session_start();
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// الاتصال بقاعدة البيانات
$conn = new mysqli("localhost", "root", "", "medic");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// جلب جميع التخصصات لعرضها في الفلتر
$specialties = [];
$spec_result = $conn->query("SELECT DISTINCT specialty FROM doctors");
while ($spec_row = $spec_result->fetch_assoc()) {
    $specialties[] = $spec_row['specialty'];
}

// فلترة حسب التخصص إذا تم اختياره
$filter_specialty = isset($_GET['specialty']) ? $_GET['specialty'] : '';

$sql = "SELECT users.id as user_id, users.fullname, users.email, users.phone, doctors.specialty, doctors.clinic 
        FROM users 
        JOIN doctors ON users.id = doctors.user_id";
if ($filter_specialty && $filter_specialty != 'all') {
    $sql .= " WHERE doctors.specialty = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $filter_specialty);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="Doctors.css" />
    <title>Doctors Dashboard</title>

  </head>

  <body>
    <div class="dashboard-layout">
      <aside class="sidebar">
        <div class="logo-container">
          <img src="images/logo.svg" alt="App Logo" class="logo" />
          <p>Divo</p>
        </div>
        <nav class="main-nav">
          <ul class="nav-list">
                <li>
                    <a href="Home.php" class="nav-link">
                        <img src="images/home.svg" alt="Home" class="nav-icon" />
                        <span>Home</span>
                    </a>
                </li>
                <li>
                    <a href="Appointment.php" class="nav-link">
                        <img src="images/cal.svg" alt="Appointments" class="nav-icon" />
                        <span>Appointments</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link active">
                        <img src="images/doctor.svg" alt="Doctors" class="nav-icon" />
                        <span>Doctors</span>
                    </a>
                </li>
            </ul>
        </nav>
      </aside>

      <main class="main-content">
        <header class="page-header">
          <h1 class="page-title">Doctors List</h1>
          <div class="header-actions">
            <div class="notifications">
              <a href="../notifications/index.html">
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
            <input type="text" placeholder="Search doctors..." class="search-input" onkeyup="filterByName(this.value)">
          </div>
          <div class="action-buttons">
            <form method="get" style="display:inline;">
              <select name="specialty" class="form-select" onchange="this.form.submit()" style="padding: 11px 17px; border-radius: 8px; border: 1px solid #e5e7eb; background-color: transparent; font-size: 16px; font-weight: 600; color: #1e293b;">
                <option value="all">All Specialties</option>
                <option value="Cardiology" <?= $filter_specialty == 'Cardiology' ? 'selected' : '' ?>>Cardiology</option>
                <option value="Dermatology" <?= $filter_specialty == 'Dermatology' ? 'selected' : '' ?>>Dermatology</option>
                <option value="Neurology" <?= $filter_specialty == 'Neurology' ? 'selected' : '' ?>>Neurology</option>
                <option value="Pediatrics" <?= $filter_specialty == 'Pediatrics' ? 'selected' : '' ?>>Pediatrics</option>
                <option value="Orthopedics" <?= $filter_specialty == 'Orthopedics' ? 'selected' : '' ?>>Orthopedics</option>
                <option value="Ophthalmology" <?= $filter_specialty == 'Ophthalmology' ? 'selected' : '' ?>>Ophthalmology</option>
                <?php
                  $static = ['Cardiology','Dermatology','Neurology','Pediatrics','Orthopedics','Ophthalmology'];
                  foreach($specialties as $spec):
                    if (!in_array($spec, $static)):
                ?>
                  <option value="<?= htmlspecialchars($spec) ?>" <?= $filter_specialty == $spec ? 'selected' : '' ?>>
                    <?= htmlspecialchars($spec) ?>
                  </option>
                <?php
                    endif;
                  endforeach;
                ?>
              </select>
            </form>
          </div>
        </div>

        <!--                   cards                    -->
        <section class="doctors-grid" id="doctorsGrid">
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
              <article class="doctor-card">
                <a href="Doctor.php?id=<?php echo $row['user_id']; ?>" style="text-decoration: none; color: inherit;">
                  <div class="doctor-header">
                    <img src="doctor_photo.php?id=<?php echo $row['user_id']; ?>" alt="Dr. <?php echo htmlspecialchars($row['fullname']); ?>" class="doctor-avatar">
                    <div class="doctor-info">
                      <h2 class="doctor-name"><?= htmlspecialchars($row['fullname']) ?></h2>
                      <p class="doctor-specialty"><?= htmlspecialchars($row['specialty']) ?></p>
                    </div>
                  </div>
                  <div class="doctor-details">
                    <div class="contact-info">
                      <img src="images/message.svg" alt="Email">
                      <span><?= htmlspecialchars($row['email']) ?></span>
                    </div>
                    <div class="contact-info">
                      <img src="images/phone.svg" alt="Phone">
                      <span><?= htmlspecialchars($row['phone']) ?></span>
                    </div>
                    <div class="contact-info">
                      <img src="images/clinic.svg" alt="Clinic">
                      <span><?= htmlspecialchars($row['clinic']) ?></span>
                    </div>
                  </div>
                  <div class="doctor-footer">
                    <span class="status-badge available">Available</span>
                    <button class="options-btn">
                      <img src="images/points.svg" alt="Options">
                    </button>
                  </div>
                </a>
              </article>
            <?php endwhile; ?>
          <?php else: ?>
            <p>No doctors found.</p>
          <?php endif; ?>
        </section>
        <!--                   cards                    -->
        
      </main>
    </div>
    <!-- ضع هذا الكود مكان الشات بوت القديم في نهاية ملف Doctors.php -->

<!-- ضع هذا الكود مكان الشات بوت الحالي في نهاية ملف Doctors.php -->

<style>
  #chatbot-fab {
    position: fixed;
    bottom: 32px;
    right: 32px;
    width: 56px;
    height: 56px;
    background: #1e293b;
    border-radius: 50%;
    box-shadow: 0 4px 16px rgba(30,41,59,0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 9999;
    transition: background 0.2s;
    border: 2px solid #e5e7eb;
  }
  #chatbot-fab:hover {
    background:rgb(90, 123, 195);
  }
  #chatbot-fab img {
    width: 28px;
    height: 28px;
  }
  #chatbot-popup {
    display: none;
    position: fixed;
    bottom: 100px;
    right: 32px;
    width: 350px;
    z-index: 10000;
    background: #f8fafc;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(30,41,59,0.18);
    overflow: hidden;
    border: 1.5px solid #e5e7eb;
    font-family: 'Segoe UI', 'Tajawal', Arial, sans-serif;
    animation: chatbot-pop 0.2s;
  }
  @keyframes chatbot-pop {
    from { transform: translateY(40px); opacity: 0;}
    to { transform: translateY(0); opacity: 1;}
  }
  #chatbot-popup #chatbot-header {
    background:#1e293b;
    color:#fff;
    padding:14px 18px;
    border-radius:16px 16px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 18px;
    font-weight: 700;
    letter-spacing: 0.5px;
  }
  #chatbot-popup #chatbot-close {
    background: none;
    border: none;
    color: #fff;
    font-size: 26px;
    cursor: pointer;
    margin-left: 8px;
    line-height: 1;
    transition: color 0.2s;
  }
  #chatbot-popup #chatbot-close:hover {
    color: #f87171;
  }
  #chatbot-popup #chatbot-messages {
    background:#f8fafc;
    height:260px;
    overflow-y:auto;
    padding:16px 12px 8px 12px;
    border-top: none;
    border-bottom: 1.5px solid #e5e7eb;
    font-size: 15px;
  }
  #chatbot-popup .chatbot-bubble-user {
    text-align:right;
    margin:7px 0;
  }
  #chatbot-popup .chatbot-bubble-user span {
    background:#e0e7ef;
    color:#1e293b;
    padding:7px 16px;
    border-radius:18px 18px 2px 18px;
    display:inline-block;
    max-width: 80%;
    word-break: break-word;
    font-weight: 500;
  }
  #chatbot-popup .chatbot-bubble-bot {
    text-align:left;
    margin:7px 0;
  }
  #chatbot-popup .chatbot-bubble-bot span {
    background:#2563eb;
    color:#fff;
    padding:7px 16px;
    border-radius:18px 18px 18px 2px;
    display:inline-block;
    max-width: 80%;
    word-break: break-word;
    font-weight: 500;
  }
  #chatbot-popup #chatbot-quick-questions {
    display:flex;
    flex-direction:column;
    gap:8px;
    margin-bottom:10px;
    margin-top: 2px;
  }
  #chatbot-popup .quick-question {
    background: #fff;
    color:rgb(82, 114, 183);
    border: 1px solidrgb(64, 102, 184);
    border-radius: 8px;
    padding: 7px 0;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
  }
  #chatbot-popup .quick-question:hover {
    background:rgb(63, 81, 119);
    color: #fff;
  }
  #chatbot-popup #chatbot-form {
    display:flex;
    border-top:none;
    border-radius:0 0 16px 16px;
    background: #f1f5f9;
    padding: 10px 12px;
    gap: 8px;
  }
  #chatbot-popup #chatbot-input {
    flex:1;
    padding:9px 12px;
    border:1.5px solid #e5e7eb;
    border-radius: 8px;
    outline: none;
    font-size: 15px;
    background: #fff;
    color: #1e293b;
    transition: border 0.2s;
  }
  #chatbot-popup #chatbot-input:focus {
    border: 1.5px solidrgb(36, 47, 70);
  }
  #chatbot-popup button[type="submit"] {
    background:#2563eb;
    color:#fff;
    border:none;
    padding:9px 20px;
    border-radius:8px;
    font-size: 15px;
    font-weight: 600;
    cursor:pointer;
    transition: background 0.2s;
  }
  #chatbot-popup button[type="submit"]:hover {
    background: #1e293b;
  }
</style>

<!-- Floating Button -->
<!-- Floating Button -->
<div id="chatbot-fab" title="اسأل الشات بوت">
  <img src="images/chatbot.svg" alt="My Photo" style="width: 53px; height: 53px; border-radius: 50%; object-fit: cover; border:2px solid #fff;">
</div>

<!-- Popup Chatbot -->
<div id="chatbot-popup" style="display:none;">
  <div id="chatbot-header">
    <span>اسأل الشات بوت</span>
    <button id="chatbot-close" title="إغلاق">&times;</button>
  </div>
  <div id="chatbot-messages">
    <div style="margin-bottom:10px;color:#334155;font-weight:600;">اختر سؤالاً شائعاً أو اكتب سؤالك:</div>
    <div id="chatbot-quick-questions">
      <button class="quick-question" type="button">ما هي التخصصات المتوفرة؟</button>
      <button class="quick-question" type="button">كم عدد الأطباء في النظام؟</button>
      <button class="quick-question" type="button">جميع الأطباء</button>
      <button class="quick-question" type="button" style="direction: rtl;">من هم أطباء تخصص Cardiology</button>
      <button class="quick-question" type="button">ما هي عيادات المستشفى؟</button>
    </div>
  </div>
  <form id="chatbot-form" autocomplete="off">
    <input type="text" id="chatbot-input" placeholder="اكتب سؤالك هنا...">
    <button type="submit" style ="background: #1e293b;color:#fff; ">إرسال</button>
  </form>
</div>

<script>
  // إظهار/إخفاء الشات بوت
  document.getElementById('chatbot-fab').onclick = function() {
    document.getElementById('chatbot-popup').style.display = 'block';
  };
  document.getElementById('chatbot-close').onclick = function() {
    document.getElementById('chatbot-popup').style.display = 'none';
  };

  // دالة لإضافة رسالة في الشات
  function addMessage(msg, type) {
    var messages = document.getElementById('chatbot-messages');
    var div = document.createElement('div');
    div.className = type === 'user' ? 'chatbot-bubble-user' : 'chatbot-bubble-bot';
    div.innerHTML = '<span>' + msg + '</span>';
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
  }

  // إرسال الرسالة
  document.getElementById('chatbot-form').onsubmit = function(e) {
    e.preventDefault();
    var input = document.getElementById('chatbot-input');
    var msg = input.value.trim();
    if(!msg) return;
    addMessage(msg, 'user');
    input.value = '';

    // AJAX to chatbot.php
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'chatbot.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function(){
      if(xhr.status === 200){
        addMessage(xhr.responseText, 'bot');
      }
    };
    xhr.send('message='+encodeURIComponent(msg));
  };

  // عند الضغط على سؤال شائع
  document.querySelectorAll('.quick-question').forEach(function(btn){
    btn.onclick = function() {
      var question = this.textContent;
      addMessage(question, 'user');
      // AJAX to chatbot.php
      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'chatbot.php', true);
      xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
      xhr.onload = function(){
        if(xhr.status === 200){
          addMessage(xhr.responseText, 'bot');
        }
      };
      xhr.send('message='+encodeURIComponent(question));
    };
  });
</script>
</script>
    <script>
      // فلترة بالاسم بدون تحديث الصفحة
      function filterByName(value) {
        var cards = document.querySelectorAll('.doctor-card');
        value = value.toLowerCase();
        cards.forEach(function(card) {
          var name = card.querySelector('.doctor-name').textContent.toLowerCase();
          if(name.includes(value)) {
            card.style.display = '';
          } else {
            card.style.display = 'none';
          }
        });
      }
    </script>
  </body>
</html>
<?php $conn->close(); ?>