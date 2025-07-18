    <!DOCTYPE html>
    <html lang="en">
    <head>
        <link rel="preload" href="important-image.webp" as="image">
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="stylesheet" href="./css/style.css" />
        <title>Register-page</title>
    </head>
    <body>
        <main class="signup-container">
            <div class="signup-wrapper">
                <div class="signup-content">
                    <?php include 'includes/header.php'; ?>
                    
                    <form class="signup-form" action="register.php" method="post">
                        <input type="hidden" id="userType" name="userType" value="patient">
                        <div class="form-section">
                            <?php include 'includes/patient-doctor-btn.php'; ?>
                            <?php include 'includes/form-fields.php'; ?>
                            <?php include 'includes/doctor-fields.php'; ?>
                            <?php include 'includes/terms-section.php'; ?>
                            <?php include 'includes/signup-btn.php'; ?>
                            <?php include 'includes/login-text.php'; ?>
                        </div>
                    </form>
                </div>
            </div>
        </main>
        <script>
            // تبديل بين نوعي المستخدم
            document.querySelectorAll('.user-type-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const userType = this.getAttribute('data-type');
                    
                    // تحديث حالة الأزرار
                    document.querySelectorAll('.user-type-btn').forEach(b => {
                        b.classList.remove('active');
                        b.setAttribute('aria-pressed', 'false');
                    });
                    this.classList.add('active');
                    this.setAttribute('aria-pressed', 'true');
                    
                    // تحديث الحقل المخفي
                    document.getElementById('userType').value = userType;
                    
                    // إظهار/إخفاء حقول الطبيب
                    const doctorFields = document.querySelector('.doctor-fields');
                    if (userType === 'doctor') {
                        doctorFields.style.display = 'block';
                        
                        // جعل حقول الطبيب مطلوبة
                        document.querySelectorAll('.doctor-fields input, .doctor-fields select').forEach(field => {
                            field.required = true;
                        });
                    } else {
                        doctorFields.style.display = 'none';
                        
                        // إزالة السمة المطلوبة من حقول الطبيب
                        document.querySelectorAll('.doctor-fields input, .doctor-fields select').forEach(field => {
                            field.required = false;
                        });
                    }
                });
            });

            // معالجة تحميل ملف الرخصة
            const licenseInput = document.getElementById('license');
            const licensePreview = document.getElementById('licensePreview');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const removeLicense = document.getElementById('removeLicense');

            licenseInput.addEventListener('change', function(e) {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    fileName.textContent = file.name;
                    fileSize.textContent = formatFileSize(file.size);
                    
                    licensePreview.style.display = 'flex';
                    document.getElementById('licenseUploadBox').style.display = 'none';
                }
            });

            removeLicense.addEventListener('click', function() {
                licenseInput.value = '';
                licensePreview.style.display = 'none';
                document.getElementById('licenseUploadBox').style.display = 'block';
            });

            // دالة لتنسيق حجم الملف
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
        </script>
        <script src="js/script.js"></script>
    </body>
    </html>