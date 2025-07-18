<?php
session_start();
$conn = new mysqli("localhost", "root", "", "medic");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// جلب معرف المستخدم من الجلسة
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// جلب معرف الطبيب المرتبط بهذا المستخدم
$doctor_id = 0;
if ($user_id > 0) {
    $result = $conn->query("SELECT id FROM doctors WHERE user_id = $user_id LIMIT 1");
    if ($row = $result->fetch_assoc()) {
        $doctor_id = intval($row['id']);
    }
}

// معالجة زر تحديد الموقع
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_location']) && isset($_POST['latitude']) && isset($_POST['longitude'])) {
    $lat = floatval($_POST['latitude']);
    $lng = floatval($_POST['longitude']);
    $stmt = $conn->prepare("UPDATE doctors SET latitude = ?, longitude = ? WHERE id = ?");
    $stmt->bind_param("ddi", $lat, $lng, $doctor_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// عدد المرضى
$patients = $conn->query("SELECT COUNT(*) as total FROM patients")->fetch_assoc()['total'];

// عدد الأطباء
$doctors = $conn->query("SELECT COUNT(*) as total FROM doctors")->fetch_assoc()['total'];

// عدد المواعيد القادمة لهذا الطبيب فقط
$appointments = 0;
if ($doctor_id > 0) {
    $appointments = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE scheduled_time >= NOW() AND doctor_id = $doctor_id")->fetch_assoc()['total'];
}

// جلب آخر 3 مواعيد قادمة لهذا الطبيب فقط مع صور المرضى والأطباء
$appointments_result = $conn->query("
    SELECT 
        p_user.fullname AS patient_name,
        p_user.avatar AS patient_avatar,
        d_user.fullname AS doctor_name,
        d_user.avatar AS doctor_avatar,
        a.scheduled_time,
        a.status
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    JOIN users p_user ON p.user_id = p_user.id
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users d_user ON d.user_id = d_user.id
    WHERE a.scheduled_time >= NOW() AND a.doctor_id = $doctor_id
    ORDER BY a.scheduled_time ASC
    LIMIT 3
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preload" href="important-image.webp" as="image">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Home.css">
    <title>Medical Dashboard</title>
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="logo-container">
                <img src="images/logo.svg" alt="App Logo" class="logo" />
                <p>Divo</p>
            </div>
            <nav class="main-nav">
                <ul class="nav-list">
                    <li>
                        <a href="#" class="nav-link active">
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
                    
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Header Section -->
            <header class="header">
                <div class="text">
                    <h1 class="header-title">Dashboard Overview</h1>
                    <p class="header-subtitle">Welcome back, Doctor</p>
                </div>
                <div class="header-actions">
                    <button class="notification-btn" aria-label="Notifications">
                        <a href="../notifications/index.html">
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
            
            <!-- Statistics Cards -->
            <section class="stats-grid">
                <!-- Total Patients Card -->
                <article class="stat-card">
                    <div class="stat-content">
                        <div class="stat-info">
                            <h2 class="stat-title">Total Patients</h2>
                            <p class="stat-value"><?= $patients ?></p>
                            <div class="stat-trend">
                                <div class="trend-icon-wrapper">
                                    <img src="images/slach.svg" alt="Trend" class="trend-icon">
                                </div>
                                <span class="trend-text">12% from last month</span>
                            </div>
                        </div>
                        <div class="stat-image-wrapper blue">
                            <img src="images/people.svg" alt="Patients Illustration" class="stat-image">
                        </div>
                    </div>
                </article>
                
                <!-- Total Doctors Card -->
                <article class="stat-card">
                    <div class="stat-content">
                        <div class="stat-info">
                            <h2 class="stat-title">Total Doctors</h2>
                            <p class="stat-value"><?= $doctors ?></p>
                            <div class="stat-trend">
                                <div class="trend-icon-wrapper">
                                    <img src="images/slach.svg" alt="Trend" class="trend-icon">
                                </div>
                                <span class="trend-text">8% from last month</span>
                            </div>
                        </div>
                        <div class="stat-image-wrapper green">
                            <img src="images/green_doctor.svg" alt="Doctors Illustration" class="stat-image">
                        </div>
                    </div>
                </article>
                
                <!-- Appointments Card -->
                <article class="stat-card">
                    <div class="stat-content">
                        <div class="stat-info">
                            <h2 class="stat-title">Upcoming Appointments</h2>
                            <p class="stat-value"><?= $appointments ?></p>
                            <div class="stat-trend">
                                <div class="trend-icon-wrapper">
                                    <img src="images/slach.svg" alt="Trend" class="trend-icon">
                                </div>
                                <span class="trend-text">24% from last week</span>
                            </div>
                        </div>
                        <div class="stat-image-wrapper purple">
                            <img src="images/purple_cal.svg" alt="Appointments" class="stat-image">
                        </div>
                    </div>
                </article>
            </section>
            
            <!-- Appointments Table -->
            <section class="appointments-section">
                <h2 class="section-title">Upcoming Appointments</h2>
                <div class="table-container">
                    <table class="appointments-table">
                        <thead>
                            <tr>
                                <th>Patient Name</th>
                                <th>Doctor Name</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $appointments_result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="patient-info">
                                            <?php
                                                // صورة المريض من BLOB أو صورة افتراضية
                                                $patient_avatar_src = 'images/default.avif';
                                                if (!empty($row['patient_avatar'])) {
                                                    $avatar_data = $row['patient_avatar'];
                                                    if (!empty($avatar_data)) {
                                                        $base64 = base64_encode($avatar_data);
                                                        $patient_avatar_src = 'data:image/jpeg;base64,' . $base64;
                                                    }
                                                }
                                            ?>
                                            <img src="<?= $patient_avatar_src ?>" alt="<?= htmlspecialchars($row['patient_name']) ?>" class="patient-avatar" style="width:28px;height:28px;border-radius:50%;object-fit:cover;margin-right:6px;display:block;">
                                            <span class="patient-name"><?= htmlspecialchars($row['patient_name']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="doctor-info" style="display: flex; align-items: center;">
                                            <?php
                                                // صورة الطبيب من BLOB أو صورة افتراضية
                                                $doctor_avatar_src = 'images/default.avif';
                                                if (!empty($row['doctor_avatar'])) {
                                                    $avatar_data = $row['doctor_avatar'];
                                                    if (!empty($avatar_data)) {
                                                        $base64 = base64_encode($avatar_data);
                                                        $doctor_avatar_src = 'data:image/jpeg;base64,' . $base64;
                                                    }
                                                }
                                            ?>
                                            <img src="<?= $doctor_avatar_src ?>" alt="<?= htmlspecialchars($row['doctor_name']) ?>" class="doctor-avatar" style="width:28px;height:28px;border-radius:50%;object-fit:cover;margin-right:6px;display:block;">
                                            <span style="display: flex; align-items: center; height:28px;"><?= htmlspecialchars($row['doctor_name']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= date('M d, Y - h:i A', strtotime($row['scheduled_time'])) ?></td>
                                    <td>
                                        <?php
                                            $status = strtolower($row['status']);
                                            $status_class = 'status-badge ';
                                            if($status == 'accepted') $status_class .= 'status-confirmed';
                                            elseif($status == 'pending') $status_class .= 'status-pending';
                                            elseif($status == 'cancelled') $status_class .= 'status-cancelled';
                                            else $status_class .= 'status-other';
                                        ?>
                                        <span class="<?= $status_class ?>"><?= ucfirst($status) ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <section class="map-section" style="margin: 32px 0;">
                <h2 class="section-title">موقع العيادة على الخريطة</h2>
                <?php
                    // جلب الإحداثيات من قاعدة البيانات
                    $latitude = $longitude = null;
                    if ($doctor_id > 0) {
                        $result = $conn->query("SELECT latitude, longitude FROM doctors WHERE id = $doctor_id LIMIT 1");
                        if ($row = $result->fetch_assoc()) {
                            $latitude = $row['latitude'];
                            $longitude = $row['longitude'];
                        }
                    }

                    // معالجة زر إعادة تحديد الموقع
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_location'])) {
                        $stmt = $conn->prepare("UPDATE doctors SET latitude = NULL, longitude = NULL WHERE id = ?");
                        $stmt->bind_param("i", $doctor_id);
                        $stmt->execute();
                        $stmt->close();
                        header("Location: " . $_SERVER['REQUEST_URI']);
                        exit;
                    }
                    // معالجة زر تحديد الموقع تلقائياً
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_location']) && isset($_POST['latitude']) && isset($_POST['longitude'])) {
                        $lat = floatval($_POST['latitude']);
                        $lng = floatval($_POST['longitude']);
                        $stmt = $conn->prepare("UPDATE doctors SET latitude = ?, longitude = ? WHERE id = ?");
                        $stmt->bind_param("ddi", $lat, $lng, $doctor_id);
                        $stmt->execute();
                        $stmt->close();
                        header("Location: " . $_SERVER['REQUEST_URI']);
                        exit;
                    }
                ?>
                <?php if ($latitude && $longitude): ?>
                    <div style="width:100%;height:320px;border-radius:14px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);margin-top:12px;position:relative;">
                        <iframe
                            width="100%"
                            height="100%"
                            frameborder="0"
                            style="border:0;"
                            src="https://www.google.com/maps?q=<?= $latitude ?>,<?= $longitude ?>&hl=ar&z=16&output=embed"
                            allowfullscreen>
                        </iframe>
                        <div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);pointer-events:none;">
                            <div style="width:22px;height:22px;background:#2563eb;border:3px solid #fff;border-radius:50%;box-shadow:0 0 8px #2563eb88;"></div>
                        </div>
                    </div>
                    <button id="detectLocationBtn" type="button" style="background:#2563eb;color:#fff;padding:8px 18px;border:none;border-radius:8px;font-weight:600;cursor:pointer;margin-top:10px;">تحديد الموقع تلقائياً</button>
                    <form id="locationForm" method="post" style="display:none;">
                        <input type="hidden" name="set_location" value="1">
                        <input type="hidden" name="latitude" id="latitudeInput">
                        <input type="hidden" name="longitude" id="longitudeInput">
                    </form>
                    <script>
                        document.getElementById('detectLocationBtn').onclick = function() {
                            if (navigator.geolocation) {
                                navigator.geolocation.getCurrentPosition(function(position) {
                                    document.getElementById('latitudeInput').value = position.coords.latitude;
                                    document.getElementById('longitudeInput').value = position.coords.longitude;
                                    document.getElementById('locationForm').submit();
                                }, function(error) {
                                    alert('تعذر الحصول على الموقع. الرجاء السماح بالوصول إلى الموقع أو المحاولة لاحقاً.');
                                });
                            } else {
                                alert('المتصفح لا يدعم تحديد الموقع الجغرافي.');
                            }
                        };
                    </script>
                    <?php else: ?>
                        <button id="detectLocationBtn" type="button" style="background:#2563eb;color:#fff;padding:8px 18px;border:none;border-radius:8px;font-weight:600;cursor:pointer;margin-bottom:14px;">تحديد الموقع تلقائياً</button>
                        <form id="locationForm" method="post" style="display:none;">
                            <input type="hidden" name="set_location" value="1">
                            <input type="hidden" name="latitude" id="latitudeInput">
                            <input type="hidden" name="longitude" id="longitudeInput">
                        </form>
                        <p style="color:#888;">لم يتم تحديد موقع العيادة بعد.</p>
                        <script>
                            document.getElementById('detectLocationBtn').onclick = function() {
                                if (navigator.geolocation) {
                                    navigator.geolocation.getCurrentPosition(function(position) {
                                        document.getElementById('latitudeInput').value = position.coords.latitude;
                                        document.getElementById('longitudeInput').value = position.coords.longitude;
                                        document.getElementById('locationForm').submit();
                                    }, function(error) {
                                        alert('تعذر الحصول على الموقع. الرجاء السماح بالوصول إلى الموقع أو المحاولة لاحقاً.');
                                    });
                                } else {
                                    alert('المتصفح لا يدعم تحديد الموقع الجغرافي.');
                                }
                            };
                        </script>
                    <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>