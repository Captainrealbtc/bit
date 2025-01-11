let currentIndex = 0;

function showSlide(index) {
    const slides = document.querySelectorAll('.slide');
    if (index >= slides.length) {
        currentIndex = 0;
    } else if (index < 0) {
        currentIndex = slides.length - 1;
    } else {
        currentIndex = index;
    }
    const offset = -currentIndex * 100;
    document.querySelector('.slider').style.transform = `translateX(${offset}%)`;
}

function nextSlide() {
    showSlide(currentIndex + 1);
}

function previousSlide() {
    showSlide(currentIndex - 1);
}

setInterval(nextSlide, 3000); // Change slide every 3 seconds


document.addEventListener('DOMContentLoaded', function() {
    // Handle notification clicks
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const notificationId = this.dataset.id;
            
            fetch('mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'notification_id=' + notificationId
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    this.classList.remove('unread');
                    const indicator = this.querySelector('.new-indicator');
                    if(indicator) indicator.remove();
                    
                    // Update badge count
                    const badge = document.querySelector('.badge');
                    if(badge) {
                        const currentCount = parseInt(badge.textContent);
                        if(currentCount > 1) {
                            badge.textContent = currentCount - 1;
                        } else {
                            badge.remove();
                        }
                    }
                }
            });
        });
    });
});

// Fetch notifications on page load
fetch('get_notifications.php')