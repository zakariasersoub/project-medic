<?php

$conn = new mysqli("localhost", "root", "", "medic");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

session_start();
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
$patient_id = 0;
if ($user_id > 0) {
    $stmt = $conn->prepare("SELECT id FROM patients WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($patient_id);
    $stmt->fetch();
    $stmt->close();
}

// معالجة الحجز
$success = $error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_appointment'])) {
    $datetime = $_POST['datetime'];
    $reason = $_POST['reason'];

    // تحقق إذا كان الوقت المدخل في الماضي
    // تحويل الوقت المدخل إلى طابع زمني
    $appointment_timestamp = strtotime($datetime);
    $now = time();
    $appointment_date = date('Y-m-d', $appointment_timestamp);
    $current_date = date('Y-m-d', $now);

    // إذا كان في نفس اليوم، نسمح بالحجز حتى لو كان الوقت قد مضى
    if ($appointment_date !== $current_date && $appointment_timestamp <= $now) {
      $error = "لا يمكن الحجز في وقت سابق عن الآن.";
    } else {
        // جلب معرف الطبيب الداخلي من جدول doctors
        $doctor_internal_id = 0;
        $stmt_doctor = $conn->prepare("SELECT id, work_start, work_end FROM doctors WHERE user_id = ?");
        $stmt_doctor->bind_param("i", $id);
        $stmt_doctor->execute();
        $stmt_doctor->bind_result($doctor_internal_id, $work_start, $work_end);
        $stmt_doctor->fetch();
        $stmt_doctor->close();

        // تحقق من وقت الحجز بالنسبة لأوقات العمل
        $appointment_time = date('H:i:s', strtotime($datetime));
        if ($appointment_time < $work_start || $appointment_time > $work_end) {
            $error = "الحجز خارج أوقات عمل الطبيب.";
        } else if ($doctor_internal_id > 0) {
            // تحقق من وجود موعد آخر في نفس النصف ساعة
            $stmt_check = $conn->prepare(
                "SELECT COUNT(*) FROM appointments 
                 WHERE doctor_id = ? 
                 AND ABS(TIMESTAMPDIFF(MINUTE, scheduled_time, ?)) < 30"
            );
            $stmt_check->bind_param("is", $doctor_internal_id, $datetime);
            $stmt_check->execute();
            $stmt_check->bind_result($count);
            $stmt_check->fetch();
            $stmt_check->close();

            if ($count > 0) {
                $error = "لا يمكن الحجز في هذا الوقت، يوجد موعد آخر خلال نصف ساعة.";
            } else {
                // إدخال الموعد بحالة pending
                $stmt_appt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, scheduled_time, status, reason) VALUES (?, ?, ?, 'pending', ?)");
                $stmt_appt->bind_param("iiss", $patient_id, $doctor_internal_id, $datetime, $reason);
                if ($stmt_appt->execute()) {
                    $success = "تم إرسال طلب الحجز بنجاح!";
                } else {
                    $error = "حدث خطأ أثناء الحجز: " . $conn->error;
                }
                $stmt_appt->close();
            }
        } else {
            $error = "تعذر العثور على الطبيب.";
        }
    }
}
if (isset($_POST['add_rating']) && $patient_id > 0 && $id > 0) {
    $rating = intval($_POST['rating']);
    $review = trim($_POST['review']);
    // جلب معرف الطبيب الداخلي
    $stmt_doctor = $conn->prepare("SELECT id FROM doctors WHERE user_id = ?");
    $stmt_doctor->bind_param("i", $id);
    $stmt_doctor->execute();
    $stmt_doctor->bind_result($doctor_internal_id);
    $stmt_doctor->fetch();
    $stmt_doctor->close();

    if ($doctor_internal_id > 0 && $rating >= 1 && $rating <= 5) {
        // تحقق إذا كان المريض قد قيّم من قبل (اختياري)
        $stmt_check = $conn->prepare("SELECT id FROM doctor_ratings WHERE doctor_id = ? AND patient_id = ?");
        $stmt_check->bind_param("ii", $doctor_internal_id, $patient_id);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows == 0) {
            $stmt_insert = $conn->prepare("INSERT INTO doctor_ratings (doctor_id, patient_id, rating, review) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("iiis", $doctor_internal_id, $patient_id, $rating, $review);
            $stmt_insert->execute();
            $stmt_insert->close();
            $success = "تم إرسال تقييمك بنجاح!";
        } else {
            $error = "لقد قمت بتقييم هذا الطبيب من قبل.";
        }
        $stmt_check->close();
    }
}

if ($success || $error) {
    echo '<script>
      setTimeout(function() {
        var alerts = document.querySelectorAll(".alert");
        alerts.forEach(function(alert) {
          alert.style.display = "none";
        });
      }, 2000);
    </script>';
}

// جلب بيانات الطبيب
$sql = "SELECT users.fullname, users.email, users.phone, users.adress, doctors.specialty, doctors.clinic, users.avatar, doctors.work_start, doctors.work_end, doctors.latitude, doctors.longitude
        FROM users
        JOIN doctors ON users.id = doctors.user_id
        WHERE users.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($fullname, $email, $phone, $adress, $specialty, $clinic, $photo, $work_start, $work_end, $latitude, $longitude);
$stmt->fetch();
$stmt->close();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Profile</title>
    <link rel="stylesheet" href="Doctor.css">
</head>
<body>
<nav class="navbar">
    <div class="navbar-container">
      <div class="logo-container">
        <img src="images/logo.svg" alt="App Logo" class="logo" />
        <p>Divo</p>
      </div>
      <div class="navbar-actions">
        <button class="notification-btn" aria-label="Notifications">
          <img src="images/bell.svg" alt="Notifications">
        </button>
        <img src="images/img1.png" alt="">
      </div>
    </div>
</nav>

<?php if ($success): ?>
  <div class="alert alert-success" style="color:green; text-align:center;"><?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-danger" style="color:red; text-align:center;"><?php echo $error; ?></div>
<?php endif; ?>

<main class="main-content">
    <header class="doctor-profile">
      <div class="profile-container">
        <div class="profile-image-wrapper">
          <?php if ($photo): ?>
            <img src="data:image/jpeg;base64,<?php echo base64_encode($photo); ?>" alt="Doctor" class="profile-image">
          <?php else: ?>
            <img src="images/default.avif" alt="Doctor" class="profile-image">
          <?php endif; ?>
          <span class="status-indicator"></span>
        </div>
        <div class="profile-info">
          <div class="profile-header">
            <h1 class="doctor-name">Dr. <?php echo htmlspecialchars($fullname); ?></h1>
            <span class="availability-badge">Available</span>
          </div>
          <h2 class="specialty"><?php echo htmlspecialchars($specialty); ?></h2>
            <div class="rating-info">
            <div class="rating">
              <img src="images/star.svg" alt="">
              <span class="rating-score">
              <?php
                // حساب متوسط التقييم وعدد التقييمات للطبيب
                $doctor_internal_id_rating = 0;
                $stmt_doctor_rating = $conn->prepare("SELECT id FROM doctors WHERE user_id = ?");
                $stmt_doctor_rating->bind_param("i", $id);
                $stmt_doctor_rating->execute();
                $stmt_doctor_rating->bind_result($doctor_internal_id_rating);
                $stmt_doctor_rating->fetch();
                $stmt_doctor_rating->close();

                $avg_rating = 0;
                $review_count = 0;
                if ($doctor_internal_id_rating > 0) {
                  $stmt_rating = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM doctor_ratings WHERE doctor_id = ?");
                  $stmt_rating->bind_param("i", $doctor_internal_id_rating);
                  $stmt_rating->execute();
                  $stmt_rating->bind_result($avg_rating, $review_count);
                  $stmt_rating->fetch();
                  $stmt_rating->close();
                }
                echo $avg_rating ? round($avg_rating, 1) : '0.0';
              ?>
              </span>
            </div>
            <span class="rating-separator">|</span>
            <span class="review-count">
              <?php
              if ($review_count > 500) {
                echo "500+ Reviews";
              } else {
                echo $review_count . " Review" . ($review_count == 1 ? "" : "s");
              }
              ?>
            </span>
            </div>
        </div>
      </div>
    </header>

    <div class="content-grid">
      <div class="main-column">
        <section class="info-section">
          <h2 class="section-title">Doctor Information</h2>
          <div class="info-grid">
            <div class="info-item">
              <img src="images/bag.svg" alt="">
              <div class="info-content">
                <h3 class="info-title">Phone</h3>
                <p class="info-text"><?php echo htmlspecialchars($phone); ?></p>
              </div>
            </div>
            <div class="info-item">
              <img src="images/hospital.svg" alt="">
              <div class="info-content">
                <h3 class="info-title">Clinic</h3>
                <p class="info-text"><?php echo htmlspecialchars($clinic); ?></p>
              </div>
            </div>
            <div class="info-item">
              <img src="images/cap.svg" alt="">
              <div class="info-content">
                <h3 class="info-title">Address</h3>
                <p class="info-text"><?php echo htmlspecialchars($adress); ?></p>
              </div>
            </div>
            <div class="info-item">
              <img src="images/message.svg" alt="">
              <div class="info-content">
                <h3 class="info-title">Email</h3>
                <p class="info-text"><?php echo htmlspecialchars($email); ?></p>
              </div>
            </div>
            <div class="info-item">
              <img src="images/clock.svg" alt="">
              <div class="info-content">
                <h3 class="info-title">Working Hours</h3>
                <p class="info-text">
                  <?php
                    echo htmlspecialchars(substr($work_start, 0, 5)) . " - " . htmlspecialchars(substr($work_end, 0, 5));
                  ?>
                </p>
              </div>
            </div>
          </div>
        </section>
        <!-- بعد info-section أو في أي مكان مناسب -->
        <section class="map-section" style="margin: 32px 0;">
          <h2 class="section-title">موقع العيادة على الخريطة</h2>
          <?php if ($latitude && $longitude): ?>
            <div style="width:100%;height:320px;border-radius:14px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);margin-top:12px;">
              <iframe
                width="100%"
                height="100%"
                frameborder="0"
                style="border:0;"
                src="https://www.google.com/maps?q=<?= $latitude ?>,<?= $longitude ?>&hl=ar&z=16&output=embed"
                allowfullscreen>
              </iframe>
            </div>
          <?php else: ?>
            <p style="color:#888;">لم يتم تحديد موقع العيادة بعد.</p>
          <?php endif; ?>
        </section>
      </div>
      <!-- <section class="map-section" style="margin: 32px 0;">
        <h2 class="section-title">موقع العيادة على الخريطة</h2>
        <?php if ($latitude && $longitude): ?>
          <div style="width:100%;height:320px;border-radius:14px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);margin-top:12px;">
            <iframe
              width="100%"
              height="100%"
              frameborder="0"
              style="border:0;"
              src="https://www.google.com/maps?q=<?= $latitude ?>,<?= $longitude ?>&hl=ar&z=16&output=embed"
              allowfullscreen>
            </iframe>
          </div>
        <?php else: ?>
          <p style="color:#888;">لم يتم تحديد موقع العيادة بعد.</p>
        <?php endif; ?>
      </section> -->
      
      <aside class="sidebar">
        <section class="booking-section">
          <h2 class="section-title">Book Appointment</h2>
          <form class="booking-form" method="post">
            <div class="form-group">
              <label for="datetime" class="form-label">Select Date & Time</label>
              <input type="datetime-local" id="datetime" name="datetime" class="form-input" required />
            </div>
            <div class="form-group">
              <label for="reason" class="form-label">Reason for Visit</label>
              <textarea id="reason" name="reason" class="form-textarea" required></textarea>
            </div>
            <button type="submit" name="book_appointment" class="book-button">Book Appointment</button>
          </form>
        </section>
  
        <section class="reviews-section">
          <h2 class="section-title">Patient Reviews</h2>
          
          <?php

          $doctor_internal_id = 0;
          $stmt_doctor = $conn->prepare("SELECT id FROM doctors WHERE user_id = ?");
          $stmt_doctor->bind_param("i", $id);
          $stmt_doctor->execute();
          $stmt_doctor->bind_result($doctor_internal_id);
          $stmt_doctor->fetch();
          $stmt_doctor->close();

          $ratings = [];
          $avg_rating = 0;
          $review_count = 0;
          if ($doctor_internal_id > 0) {
              $result = $conn->query("SELECT r.rating, r.review, p_user.fullname, p_user.avatar
                  FROM doctor_ratings r
                  JOIN patients p ON r.patient_id = p.id
                  JOIN users p_user ON p.user_id = p_user.id
                  WHERE r.doctor_id = $doctor_internal_id
                  ORDER BY r.created_at DESC
                  LIMIT 10");
              while($row = $result->fetch_assoc()) {
                  $ratings[] = $row;
                  $avg_rating += $row['rating'];
                  $review_count++;
              }
              if ($review_count > 0) $avg_rating = round($avg_rating / $review_count, 1);
          }
          ?>

          <div class="rating-summary">
            <span class="rating-average"><?= $avg_rating ?: '0' ?></span>
            <div class="rating-details">
              <div class="star-rating">
                <?php for($i=1;$i<=5;$i++): ?>
                  <img src="images/small_star.svg" style="opacity:<?= ($i <= round($avg_rating)) ? 1 : 0.3 ?>">
                <?php endfor; ?>
              </div>
              <p class="rating-text">Based on <?= $review_count ?> reviews</p>
            </div>
          </div>

          <div class="reviews-list">
            <?php foreach($ratings as $r): ?>
              <article class="review-card">
                <div class="reviewer-info">
                  <?php
                    $avatar = 'images/default.avif';
                    if (!empty($r['avatar'])) {
                        $avatar = 'data:image/jpeg;base64,' . base64_encode($r['avatar']);
                    }
                  ?>
                  <img src="<?= $avatar ?>" alt="<?= htmlspecialchars($r['fullname']) ?>" class="reviewer-avatar">
                  <div class="reviewer-details">
                    <h3 class="reviewer-name"><?= htmlspecialchars($r['fullname']) ?></h3>
                    <div class="reviewer-rating">
                      <div class="star-rating">
                        <?php for($i=1;$i<=5;$i++): ?>
                          <img src="images/small_star.svg" style="opacity:<?= ($i <= $r['rating']) ? 1 : 0.3 ?>">
                        <?php endfor; ?>
                      </div>
                    </div>
                  </div>
                </div>
                <?php if($r['review']): ?>
                  <p class="review-text"><?= htmlspecialchars($r['review']) ?></p>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
          <div class="add-review-form" style="margin-top:32px; background:#f8fafc; border-radius:14px; box-shadow:0 2px 8px rgba(0,0,0,0.06); padding:24px 20px; display:flex; flex-direction:column; align-items:center;">
            <form method="post" style="width:100%; max-width:340px;">
              <label style="font-weight:700; color:#3674b5; font-size:1.1rem; margin-bottom:12px; display:block; text-align:center;">قيّم الطبيب</label>
              <div class="star-input" style="direction:ltr; display:flex; justify-content:center; gap:4px; margin-bottom:14px;">
                <?php for($i=5;$i>=1;$i--): ?>
                  <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" style="display:none;">
                  <label for="star<?= $i ?>" style="font-size:2.1rem; color:#3674b5; cursor:pointer; transition:transform 0.15s;" onmouseover="highlightStars(<?= $i ?>)" onmouseout="resetStars()" onclick="selectStar(<?= $i ?>)">&#9733;</label>
                <?php endfor; ?>
              </div>
              <textarea name="review" placeholder="اكتب تعليقك (اختياري)" style="width:100%;min-height:60px;resize:vertical;border:1px solid #3674b5;border-radius:8px;padding:10px 12px;font-size:1rem;margin-bottom:14px;outline:none;transition:border 0.2s;"></textarea>
              <button type="submit" name="add_rating" style="background:#3674b5;color:#fff;padding:9px 0;border:none;border-radius:8px;font-weight:700;cursor:pointer;width:100%;font-size:1rem;transition:background 0.2s;">إرسال التقييم</button>
            </form>
          </div>
          <script>
            // تفاعل النجوم (تلوين عند المرور والاختيار)
            const stars = document.querySelectorAll('.star-input label');
            const radios = document.querySelectorAll('.star-input input[type="radio"]');
            let selected = 0;
            function highlightStars(n) {
              stars.forEach((star, i) => {
                star.style.color = (5-i <= n) ? '#3674b5' : '#e5e7eb';
                star.style.transform = (5-i <= n) ? 'scale(1.15)' : 'scale(1)';
              });
            }
            function resetStars() {
              stars.forEach((star, i) => {
                star.style.color = (5-i <= selected) ? '#3674b5' : '#e5e7eb';
                star.style.transform = 'scale(1)';
              });
            }
            function selectStar(n) {
              selected = n;
              radios.forEach((radio, i) => {
                radio.checked = (5-i === n);
              });
              resetStars();
            }
            // عند تحميل الصفحة، إذا كان هناك اختيار سابق
            document.addEventListener('DOMContentLoaded', resetStars);
          </script>
        </section>
      </aside>
    </div>
  </main>
</body>
</html>