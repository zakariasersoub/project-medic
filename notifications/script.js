// Function to mark all notifications as read
function markAllAsRead() {
    const notifications = document.querySelectorAll('.notification-item');
    notifications.forEach(notification => {
        const unreadIndicator = notification.querySelector('.unread-indicator');
        if (unreadIndicator) {
            unreadIndicator.style.display = 'none'; // Hide the unread indicator
        }
    });
}

// Function to filter notifications
function filterNotifications(filter) {
    const notifications = document.querySelectorAll('.notification-item');
    notifications.forEach(notification => {
        const isUnread = notification.querySelector('.unread-indicator').style.display !== 'none';
        const isImportant = notification.classList.contains('important'); // Assuming important notifications have a class
        if (filter === 'all') {
            notification.style.display = 'block';
        } else if (filter === 'unread' && isUnread) {
            notification.style.display = 'block';
        } else if (filter === 'important' && isImportant) {
            notification.style.display = 'block';
        } else {
            notification.style.display = 'none';
        }
    });
}

// Event listener for the "Mark all as read" button
document.querySelector('.mark-read-btn').addEventListener('click', markAllAsRead);

// Event listeners for filter tabs
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        const filter = tab.textContent.toLowerCase();
        filterNotifications(filter);
        document.querySelectorAll('.filter-tab').forEach(t => t.setAttribute('aria-selected', 'false'));
        tab.setAttribute('aria-selected', 'true');
    });
});
