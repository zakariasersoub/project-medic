document.addEventListener('DOMContentLoaded', function() {
    const elements = {
        patientBtn: document.querySelector('.patient-btn'),
        doctorBtn: document.querySelector('.doctor-btn'),
        licenseUploadBox: document.getElementById('licenseUploadBox'),
        licensePreview: document.getElementById('licensePreview'),
        fileName: document.getElementById('fileName'),
        fileSize: document.getElementById('fileSize'),
        removeLicense: document.getElementById('removeLicense'),
        licenseInput: document.getElementById('license'),
        uploadLabel: document.querySelector('.upload-label'),
        passwordToggles: document.querySelectorAll('.password-toggle')
    };

    let isDoctor = false;

    const helpers = {
        formatFileSize: (bytes) => {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        },
        
        truncateFileName: (name, maxLength) => {
            if (name.length <= maxLength) return name;
            const extension = name.split('.').pop();
            const basename = name.substring(0, name.length - extension.length - 1);
            return basename.substring(0, maxLength - extension.length - 3) + '...' + extension;
        },
        
        preventDefaults: (e) => {
            e.preventDefault();
            e.stopPropagation();
        },
        
        highlight: () => {
            elements.licenseUploadBox.classList.add('highlight');
            elements.uploadLabel.classList.add('highlight');
        },
        
        unhighlight: () => {
            elements.licenseUploadBox.classList.remove('highlight');
            elements.uploadLabel.classList.remove('highlight');
        }
    };

    const userType = {
        toggle: () => {
            isDoctor = !isDoctor;
            document.body.classList.toggle('patient-active', !isDoctor);
            document.body.classList.toggle('doctor-active', isDoctor);
            elements.patientBtn.classList.toggle('active', !isDoctor);
            elements.doctorBtn.classList.toggle('active', isDoctor);
        },
        
        init: () => {
            elements.patientBtn.addEventListener('click', userType.toggle);
            elements.doctorBtn.addEventListener('click', userType.toggle);
        }
    };

    const licenseUpload = {
        handleUpload: (file) => {
            if (!file) return false;
            
            const validTypes = ['application/pdf', 'image/jpeg', 'image/png'];
            const maxSize = 5 * 1024 * 1024;
            
            if (!validTypes.includes(file.type)) {
                alert('Please upload a PDF, JPG, or PNG file.');
                return false;
            }
            
            if (file.size > maxSize) {
                alert('File size exceeds 5MB limit.');
                return false;
            }
            
            elements.fileName.textContent = helpers.truncateFileName(file.name, 25);
            elements.fileSize.textContent = helpers.formatFileSize(file.size);
            elements.licenseUploadBox.style.display = 'none';
            elements.licensePreview.style.display = 'flex';
            elements.licensePreview.style.animation = 'fadeIn 0.3s ease';
            
            return true;
        },
        
        handleRemove: (e) => {
            if (e) e.stopPropagation();
            
            elements.licenseInput.value = '';
            elements.licensePreview.style.display = 'none';
            elements.licenseUploadBox.style.display = 'block';
            
            if (elements.removeLicense) {
                elements.removeLicense.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    elements.removeLicense.style.transform = 'scale(1)';
                }, 100);
            }
        },
        
        handleDrop: (e) => {
            const dt = e.dataTransfer;
            const file = dt.files[0];
            elements.licenseInput.files = dt.files;
            licenseUpload.handleUpload(file);
        },
        
        initDragDrop: () => {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                elements.licenseUploadBox.addEventListener(eventName, helpers.preventDefaults, false);
                elements.uploadLabel.addEventListener(eventName, helpers.preventDefaults, false);
            });
            
            ['dragenter', 'dragover'].forEach(eventName => {
                elements.licenseUploadBox.addEventListener(eventName, helpers.highlight, false);
                elements.uploadLabel.addEventListener(eventName, helpers.highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                elements.licenseUploadBox.addEventListener(eventName, helpers.unhighlight, false);
                elements.uploadLabel.addEventListener(eventName, helpers.unhighlight, false);
            });
            
            elements.licenseUploadBox.addEventListener('drop', licenseUpload.handleDrop);
            elements.uploadLabel.addEventListener('drop', licenseUpload.handleDrop);
        },
        
        init: () => {
            elements.licenseInput.addEventListener('change', (e) => licenseUpload.handleUpload(e.target.files[0]));
            if (elements.removeLicense) {
                elements.removeLicense.addEventListener('click', licenseUpload.handleRemove);
            }
            licenseUpload.initDragDrop();
        }
    };

    const passwordToggle = {
        setup: () => {
            elements.passwordToggles.forEach(toggle => {
                const input = toggle.previousElementSibling;
                const icon = toggle.querySelector('img');
                
                if (icon) {
                    icon.style.width = '24px';
                    icon.style.height = '24px';
                }
                
                toggle.addEventListener('click', function() {
                    if (input.type === 'password') {
                        input.type = 'text';
                        if (icon) {
                            icon.src = 'images/eye-off.svg';
                            icon.alt = 'Hide password';
                        }
                    } else {
                        input.type = 'password';
                        if (icon) {
                            icon.src = 'images/eye.svg';
                            icon.alt = 'Show password';
                        }
                    }
                });
            });
        }
    };

    const init = () => {
        document.body.classList.add('patient-active');
        if (elements.patientBtn) elements.patientBtn.classList.add('active');
        
        userType.init();
        licenseUpload.init();
        passwordToggle.setup();
        
        elements.licenseUploadBox.style.display = 'block';
        elements.licensePreview.style.display = 'none';
    };

    init();
});

