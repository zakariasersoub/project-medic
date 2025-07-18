document.addEventListener('DOMContentLoaded', function() {
    // System State
    const state = {
        currentView: 'today',
        appointments: [],
        patients: [],
        doctors: [],
        notifications: [
            {
                id: 1,
                type: 'appointment',
                title: 'New appointment request',
                message: 'Sarah Johnson requested an appointment for tomorrow',
                time: '10 min ago',
                read: false
            },
            {
                id: 2,
                type: 'payment',
                title: 'Payment received',
                message: 'James Wilson completed payment for his consultation',
                time: '1 hour ago',
                read: false
            },
            {
                id: 3,
                type: 'system',
                title: 'System update',
                message: 'New system update available (v2.3.1)',
                time: 'Yesterday',
                read: true
            }
        ]
    };

    // DOM Elements
    const elements = {
        // Navigation
        navLinks: document.querySelectorAll('.nav-link'),
        
        // Header
        currentSection: document.getElementById('current-section'),
        currentDate: document.getElementById('current-date'),
        notificationBtn: document.getElementById('notification-btn'),
        notificationDropdown: document.getElementById('notification-dropdown'),
        
        // Stats Cards
        todayAppointmentsCount: document.getElementById('today-appointments-count'),
        totalPatients: document.getElementById('total-patients'),
        monthlyRevenue: document.getElementById('monthly-revenue'),
        
        // Appointments Table
        appointmentsBody: document.getElementById('appointments-body'),
        viewOptions: document.querySelectorAll('.view-option'),
        addAppointmentBtn: document.querySelector('.add-appointment-btn'),
        
        // Modal
        appointmentModal: document.getElementById('appointment-modal'),
        closeModalBtn: document.getElementById('close-modal'),
        appointmentForm: document.getElementById('appointment-form'),
        patientSelect: document.getElementById('patient-select'),
        doctorSelect: document.getElementById('doctor-select'),
        
        // Recent Patients
        recentPatients: document.getElementById('recent-patients')
    };

    // Initialize the dashboard
    function initDashboard() {
        // Set current date
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        elements.currentDate.textContent = now.toLocaleDateString('en-US', options);
        
        // Load sample data
        loadSampleData();
        
        // Render all components
        renderStats();
        renderAppointments();
        renderRecentPatients();
        renderNotifications();
        
        // Set up event listeners
        setupEventListeners();
    }

    // Load sample data
    function loadSampleData() {
        // Sample appointments data
        state.appointments = [
            {
                id: 1,
                patient: { id: 1, name: 'Sarah Johnson', avatar: 'images/img2.png' },
                doctor: { id: 1, name: 'Dr. Michael Brown' },
                date: '2025-01-15',
                time: '10:00',
                type: 'consultation',
                status: 'confirmed',
                notes: 'Follow-up for previous treatment'
            },
            {
                id: 2,
                patient: { id: 2, name: 'James Wilson', avatar: 'images/img3.png' },
                doctor: { id: 2, name: 'Dr. Emily Davis' },
                date: '2025-01-15',
                time: '11:30',
                type: 'checkup',
                status: 'pending',
                notes: 'Annual physical examination'
            },
            {
                id: 3,
                patient: { id: 3, name: 'Emma Thompson', avatar: 'images/img4.png' },
                doctor: { id: 3, name: 'Dr. Robert Wilson' },
                date: '2025-01-15',
                time: '14:00',
                type: 'follow-up',
                status: 'cancelled',
                notes: 'Patient rescheduled'
            },
            {
                id: 4,
                patient: { id: 4, name: 'David Miller', avatar: 'images/img5.png' },
                doctor: { id: 1, name: 'Dr. Michael Brown' },
                date: '2025-01-16',
                time: '09:30',
                type: 'emergency',
                status: 'confirmed',
                notes: 'Severe headache and dizziness'
            },
            {
                id: 5,
                patient: { id: 5, name: 'Lisa Anderson', avatar: 'images/img6.png' },
                doctor: { id: 2, name: 'Dr. Emily Davis' },
                date: '2025-01-16',
                time: '13:15',
                type: 'consultation',
                status: 'pending',
                notes: 'New patient consultation'
            }
        ];

        // Sample patients data
        state.patients = [
            { id: 1, name: 'Sarah Johnson', avatar: 'images/img2.png', lastVisit: '2025-01-10', age: 32, gender: 'Female' },
            { id: 2, name: 'James Wilson', avatar: 'images/img3.png', lastVisit: '2025-01-12', age: 45, gender: 'Male' },
            { id: 3, name: 'Emma Thompson', avatar: 'images/img4.png', lastVisit: '2025-01-08', age: 28, gender: 'Female' },
            { id: 4, name: 'David Miller', avatar: 'images/img5.png', lastVisit: '2025-01-14', age: 52, gender: 'Male' },
            { id: 5, name: 'Lisa Anderson', avatar: 'images/img6.png', lastVisit: '2025-01-13', age: 38, gender: 'Female' },
            { id: 6, name: 'Robert Garcia', avatar: 'images/img7.png', lastVisit: '2025-01-09', age: 41, gender: 'Male' }
        ];

        // Sample doctors data
        state.doctors = [
            { id: 1, name: 'Dr. Michael Brown', specialty: 'Cardiology', availability: 'Mon-Fri' },
            { id: 2, name: 'Dr. Emily Davis', specialty: 'Dermatology', availability: 'Mon-Wed-Fri' },
            { id: 3, name: 'Dr. Robert Wilson', specialty: 'Neurology', availability: 'Tue-Thu' },
            { id: 4, name: 'Dr. Sarah Johnson', specialty: 'Pediatrics', availability: 'Mon-Fri' }
        ];
    }

    // Render statistics cards
    function renderStats() {
        // Today's appointments count
        const today = new Date().toISOString().split('T')[0];
        const todaysAppointments = state.appointments.filter(a => a.date === today && a.status !== 'cancelled');
        elements.todayAppointmentsCount.textContent = todaysAppointments.length;
        
        // Total patients
        elements.totalPatients.textContent = state.patients.length.toLocaleString();
        
        // Monthly revenue (sample calculation)
        const revenue = state.appointments
            .filter(a => a.status === 'confirmed')
            .reduce((sum, appointment) => sum + (appointment.type === 'emergency' ? 300 : 150), 0);
        elements.monthlyRevenue.textContent = `$${revenue.toLocaleString()}`;
    }

    // Render appointments table
    function renderAppointments() {
        // Clear existing rows
        elements.appointmentsBody.innerHTML = '';
        
        // Filter appointments based on current view
        let filteredAppointments = [];
        const today = new Date().toISOString().split('T')[0];
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const tomorrowStr = tomorrow.toISOString().split('T')[0];
        
        switch(state.currentView) {
            case 'today':
                filteredAppointments = state.appointments.filter(a => a.date === today);
                break;
            case 'tomorrow':
                filteredAppointments = state.appointments.filter(a => a.date === tomorrowStr);
                break;
            case 'week':
                const nextWeek = new Date();
                nextWeek.setDate(nextWeek.getDate() + 7);
                filteredAppointments = state.appointments.filter(a => 
                    new Date(a.date) >= new Date(today) && 
                    new Date(a.date) <= nextWeek
                );
                break;
            default:
                filteredAppointments = state.appointments;
        }
        
        // Sort by date and time
        filteredAppointments.sort((a, b) => {
            const dateCompare = new Date(a.date) - new Date(b.date);
            if (dateCompare !== 0) return dateCompare;
            return a.time.localeCompare(b.time);
        });
        
        // Render each appointment
        filteredAppointments.forEach(appointment => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <div class="patient-info">
                        <img src="${appointment.patient.avatar}" alt="${appointment.patient.name}" class="patient-avatar">
                        <span class="patient-name">${appointment.patient.name}</span>
                    </div>
                </td>
                <td>${appointment.doctor.name}</td>
                <td>${formatDate(appointment.date)} - ${appointment.time}</td>
                <td>${formatAppointmentType(appointment.type)}</td>
                <td>
                    <span class="status-badge status-${appointment.status}">
                        ${formatStatus(appointment.status)}
                    </span>
                </td>
                <td>
                    <img src="images/points.svg" alt="Actions" class="action-icon">
                </td>
            `;
            elements.appointmentsBody.appendChild(row);
        });
        
        // Update pagination info
        document.getElementById('showing-start').textContent = 1;
        document.getElementById('showing-end').textContent = filteredAppointments.length;
        document.getElementById('total-entries').textContent = state.appointments.length;
    }

    // Render recent patients
    function renderRecentPatients() {
        elements.recentPatients.innerHTML = '';
        
        // Get 6 most recent patients
        const recentPatients = [...state.patients]
            .sort((a, b) => new Date(b.lastVisit) - new Date(a.lastVisit))
            .slice(0, 6);
        
        recentPatients.forEach(patient => {
            const patientCard = document.createElement('div');
            patientCard.className = 'patient-card';
            patientCard.innerHTML = `
                <img src="${patient.avatar}" alt="${patient.name}" class="patient-card-avatar">
                <div class="patient-card-info">
                    <p class="patient-card-name">${patient.name}</p>
                    <p class="patient-card-meta">${patient.age} yrs • ${patient.gender}</p>
                </div>
            `;
            elements.recentPatients.appendChild(patientCard);
        });
    }

    // Render notifications
    function renderNotifications() {
        const unreadCount = state.notifications.filter(n => !n.read).length;
        if (unreadCount > 0) {
            document.querySelector('.notification-badge').textContent = unreadCount;
            document.querySelector('.notification-badge').style.display = 'flex';
        } else {
            document.querySelector('.notification-badge').style.display = 'none';
        }
    }

    // Format date for display
    function formatDate(dateStr) {
        const options = { month: 'short', day: 'numeric', year: 'numeric' };
        return new Date(dateStr).toLocaleDateString('en-US', options);
    }

    // Format appointment type
    function formatAppointmentType(type) {
        const types = {
            'consultation': 'Consultation',
            'follow-up': 'Follow-up',
            'checkup': 'Checkup',
            'emergency': 'Emergency'
        };
        return types[type] || type;
    }

    // Format status
    function formatStatus(status) {
        return status.charAt(0).toUpperCase() + status.slice(1);
    }

    // Set up event listeners
    function setupEventListeners() {
        // Navigation links
        elements.navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Update active state
                elements.navLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                
                // Update current section title
                const sectionName = this.getAttribute('data-section');
                elements.currentSection.textContent = sectionName.charAt(0).toUpperCase() + sectionName.slice(1);
                
                // Here you would typically load the appropriate content for the section
                // For now, we'll just update the title
            });
        });
        
        // Notification button
        elements.notificationBtn.addEventListener('click', function() {
            elements.notificationDropdown.classList.toggle('show');
            
            // Mark notifications as read when dropdown is opened
            if (elements.notificationDropdown.classList.contains('show')) {
                state.notifications.forEach(n => n.read = true);
                renderNotifications();
            }
        });
        
        // Close notification dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!elements.notificationBtn.contains(e.target)) {
                elements.notificationDropdown.classList.remove('show');
            }
        });
        
        // View options for appointments
        elements.viewOptions.forEach(option => {
            option.addEventListener('click', function() {
                elements.viewOptions.forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
                state.currentView = this.textContent.toLowerCase().replace(' ', '-');
                renderAppointments();
            });
        });
        
        // Add appointment button
        elements.addAppointmentBtn.addEventListener('click', function() {
            // Populate patient and doctor select options
            elements.patientSelect.innerHTML = '<option value="">Select Patient</option>';
            state.patients.forEach(patient => {
                const option = document.createElement('option');
                option.value = patient.id;
                option.textContent = patient.name;
                elements.patientSelect.appendChild(option);
            });
            
            elements.doctorSelect.innerHTML = '<option value="">Select Doctor</option>';
            state.doctors.forEach(doctor => {
                const option = document.createElement('option');
                option.value = doctor.id;
                option.textContent = doctor.name;
                elements.doctorSelect.appendChild(option);
            });
            
            // Set default date to today
            document.getElementById('appointment-date').valueAsDate = new Date();
            
            // Show modal
            elements.appointmentModal.style.display = 'flex';
        });
        
        // Close modal button
        elements.closeModalBtn.addEventListener('click', function() {
            elements.appointmentModal.style.display = 'none';
        });
        
        // Close modal when clicking outside
        elements.appointmentModal.addEventListener('click', function(e) {
            if (e.target === elements.appointmentModal) {
                elements.appointmentModal.style.display = 'none';
            }
        });
        
        // Appointment form submission
        elements.appointmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const patientId = parseInt(elements.patientSelect.value);
            const doctorId = parseInt(elements.doctorSelect.value);
            const date = document.getElementById('appointment-date').value;
            const time = document.getElementById('appointment-time').value;
            const type = document.getElementById('appointment-type').value;
            const notes = document.getElementById('appointment-notes').value;
            
            // Find patient and doctor objects
            const patient = state.patients.find(p => p.id === patientId);
            const doctor = state.doctors.find(d => d.id === doctorId);
            
            if (!patient || !doctor) {
                alert('Please select both patient and doctor');
                return;
            }
            
            // Create new appointment
            const newAppointment = {
                id: state.appointments.length + 1,
                patient: {
                    id: patient.id,
                    name: patient.name,
                    avatar: patient.avatar
                },
                doctor: {
                    id: doctor.id,
                    name: doctor.name
                },
                date,
                time,
                type,
                status: 'pending',
                notes
            };
            
            // Add to appointments array
            state.appointments.push(newAppointment);
            
            // Update UI
            renderAppointments();
            renderStats();
            
            // Close modal and reset form
            elements.appointmentModal.style.display = 'none';
            this.reset();
            
            // Show success message
            alert('Appointment scheduled successfully!');
        });
    }

    // Initialize the dashboard
    initDashboard();
});