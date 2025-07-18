<?php

$conn = new mysqli("localhost", "root", "", "medic");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

$sql = "SELECT users.id as user_id, users.fullname, users.avatar, users.email, users.phone, doctors.specialty, doctors.clinic 
        FROM users 
        JOIN doctors ON users.id = doctors.user_id
        LIMIT 4";
$result = $conn->query($sql);

$doctors = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // إذا كان avatar موجود (BLOB)، حوله إلى base64
        if (!empty($row['avatar'])) {
            $row['image'] = 'data:image/jpeg;base64,' . base64_encode($row['avatar']);
        } else {
            $row['image'] = 'images/default.avif';
        }
        $row['name'] = $row['fullname'];
        $row['linkedin'] = '';
        $row['twitter'] = '';
        $doctors[] = $row;
    }
}
?>
<section class="doctors slide-up" id="doctors">
  <div class="container">
    <h2 class="section-title">Meet Our Expert Doctors</h2>
    <p class="section-description">Our team of experienced medical professionals is dedicated to providing you with the best possible care</p>
    <div class="doctors-grid">
      <?php foreach ($doctors as $doctor): ?>
        <div class="doctor-card scale-in ">
          <a href="../doctor-profile/index.php?id=<?= htmlspecialchars($doctor['user_id']) ?>">
            <div class="doctor-image">
              <img src="<?= htmlspecialchars($doctor['image']) ?>" alt="doctor image">
            </div>
            <div class="doctor-info">
              <h3 class="doctor-name"><?= htmlspecialchars($doctor['name']) ?></h3>
              <p class="doctor-specialty"><?= htmlspecialchars($doctor['specialty']) ?></p>
              <div class="doctor-social">
                <?php if (!empty($doctor['linkedin'])): ?>
                  <a href="<?= htmlspecialchars($doctor['linkedin']) ?>" target="_blank">
                    <img src="images/linkdin.svg" alt="LinkedIn">
                  </a>
                <?php endif; ?>
                <?php if (!empty($doctor['twitter'])): ?>
                  <a href="<?= htmlspecialchars($doctor['twitter']) ?>" target="_blank">
                    <img src="images/twiter.svg" alt="Twitter">
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>