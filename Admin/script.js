document.addEventListener('DOMContentLoaded', function () {
    // Get elements
    const addBtn = document.querySelector('.add-btn');
    const popup = document.getElementById('addDoctorPopup');
    const closeBtn = document.querySelector('.close-btn');
    const cancelBtn = document.querySelector('.cancel-btn');
    const doctorForm = document.getElementById('doctorForm');
    const doctorsGrid = document.querySelector('.doctors-grid');
    const uploadBtn = document.getElementById('uploadBtn');
    const doctorAvatar = document.getElementById('doctorAvatar');
    const avatarImage = document.getElementById('avatarImage');
    let avatarFile = null;

    // Clear existing doctor cards
    doctorsGrid.innerHTML = '';

    // Handle avatar upload
    uploadBtn.addEventListener('click', function () {
        doctorAvatar.click();
    });

    doctorAvatar.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            avatarFile = file;
            const reader = new FileReader();
            reader.onload = function (event) {
                avatarImage.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Open popup
    addBtn.addEventListener('click', function () {
        popup.style.display = 'flex';
    });

    // Close popup
    function closePopup() {
        popup.style.display = 'none';
        doctorForm.reset();
        avatarImage.src = 'images/doctor.svg';
        avatarFile = null;
    }

    closeBtn.addEventListener('click', closePopup);
    cancelBtn.addEventListener('click', closePopup);

    // Render doctor card
    function renderDoctorCard(doctor) {
        const doctorCard = document.createElement('article');
        doctorCard.className = 'doctor-card';

        // Use the avatar path from database or default image
        const avatarSrc = doctor.avatar_path || 'images/doctor.svg';

        doctorCard.innerHTML = `
            <div class="doctor-header">
                <img src="${avatarSrc}" alt="${doctor.name}" class="doctor-avatar">
                <div class="doctor-info">
                    <h2 class="doctor-name">Dr. ${doctor.name}</h2>
                    <p class="doctor-specialty">${doctor.specialty}</p>
                </div>
            </div>
            <div class="doctor-details">
                <div class="contact-info">
                    <img src="images/message.svg" alt="Email">
                    <span>${doctor.email}</span>
                </div>
                <div class="contact-info">
                    <img src="images/phone.svg" alt="Phone">
                    <span>${doctor.phone}</span>
                </div>
                <div class="contact-info">
                    <img src="images/clinic.svg" alt="Clinic">
                    <span>${doctor.clinic}</span>
                </div>
            </div>
        `;

        doctorsGrid.appendChild(doctorCard);
    }

    // Fetch doctors from the server
    function fetchDoctors() {
        fetch('add_doctor.php?fetch=1')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.doctors.forEach(doctor => renderDoctorCard(doctor));
                } else {
                    console.error('Failed to fetch doctors:', data.message);
                }
            })
            .catch(error => console.error('Error fetching doctors:', error));
    }

    // Load doctors on page load
    fetchDoctors();

    // Handle form submission
    doctorForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // Get form values
        const name = document.getElementById('doctorName').value;
        const specialty = document.getElementById('doctorSpecialty').value;
        const email = document.getElementById('doctorEmail').value;
        const phone = document.getElementById('doctorPhone').value;
        const clinic = document.getElementById('doctorClinic').value;

        // Create FormData for file upload
        const formData = new FormData();
        formData.append('name', name);
        formData.append('specialty', specialty);
        formData.append('email', email);
        formData.append('phone', phone);
        formData.append('clinic', clinic);
        if (avatarFile) {
            formData.append('avatar', avatarFile);
        }

        // Send data to server
        fetch('add_doctor.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Refresh the doctors list
                    doctorsGrid.innerHTML = '';
                    fetchDoctors();
                    
                    // Close popup and reset form
                    closePopup();
                } else {
                    alert('Error adding doctor: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the doctor');
            });
    });
});