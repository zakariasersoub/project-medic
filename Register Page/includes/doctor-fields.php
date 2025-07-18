<div class="doctor-fields" style="display: none;">
<div class="form-group">
    <label for="specialty" class="form-label">Medical Specialty</label>
    <select id="specialty" name="specialty" class="specialty-select">
        <option value="">Select Specialty</option>
        <option value="Cardiology">Cardiology</option>
        <option value="Dermatology">Dermatology</option>
        <option value="Neurology">Neurology</option>
        <option value="Pediatrics">Pediatrics</option>
        <option value="Orthopedics">Orthopedics</option>
        <option value="Ophthalmology">Ophthalmology</option>
    </select>
</div>
<div class="form-group">
    <label for="license" class="form-label">Medical License</label>
    <div class="license-upload-container">
        <div id="licenseUploadBox" class="license-upload-box">
            <input type="file" id="license" name="license" accept=".pdf,.jpg,.png,.jpeg" class="license-input" style="display: none;">
            <label for="license" class="upload-label">
                <div class="upload-icon-container">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upload-svg-icon">
                        <path d="M11 15V8.85L8.4 11.45L7 10L12 5L17 10L15.6 11.45L13 8.85V15H11ZM6 20C5.45 20 4.979 19.804 4.587 19.412C4.195 19.02 3.99934 18.5493 4 18V16H6V18H18V16H20V18C20 18.55 19.804 19.021 19.412 19.413C19.02 19.805 18.5493 20.0007 18 20H6Z" fill="#3674B5"/>
                    </svg>
                </div>
                <div class="upload-text-container">
                    <span class="upload-main-text">Drag & drop your file here</span>
                    <span class="upload-sub-text">or click to browse</span>
                    <span class="file-requirements">Supports: PDF, JPG, PNG (Max 5MB)</span>
                </div>
            </label>
        </div>
        <div id="licensePreview" class="license-preview" style="display: none;">
            <div class="file-preview-container">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="#3674B5" class="file-icon">
                    <path d="M14 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V8L14 2ZM6 20V4H13V9H18V20H6Z"/>
                </svg>
                <div class="file-info">
                    <span id="fileName" class="file-name"></span>
                    <span id="fileSize" class="file-size"></span>
                </div>
            </div>
            <button id="removeLicense" type="button" class="remove-btn" aria-label="Remove file">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#EF4444">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/>
                </svg>
            </button>
        </div>
    </div>
</div>
<div class="form-group">
    <label for="clinic" class="form-label">Clinic/Hospital Name</label>
    <input type="text" id="clinic" name="clinic" class="form-input" placeholder="Enter clinic or hospital name">
</div>
</div>