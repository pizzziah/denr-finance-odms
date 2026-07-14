document.addEventListener('DOMContentLoaded', function () {

    loadNotifications();

    setInterval(loadNotifications, 3000);

});

function loadNotifications() {

    fetch('/notifications')
        .then(res => res.json())
        .then(data => {

            const badge = document.getElementById('notificationBadge');
            const list = document.getElementById('notificationList');

            if (!badge || !list) return;

            // Badge
            if (data.unreadCount > 0) {
                badge.classList.remove('d-none');
                badge.innerHTML = data.unreadCount;
            } else {
                badge.classList.add('d-none');
            }

            // Empty
            if (data.notifications.length === 0) {
                list.innerHTML = `
                    <div class="text-center p-3 text-muted">
                        No notifications
                    </div>
                `;
                return;
            }

            let html = '';

            data.notifications.forEach(notification => {

                let badgeColor = 'secondary';

                if (notification.priority === 'Critical')
                    badgeColor = 'danger';
                else if (notification.priority === 'High')
                    badgeColor = 'warning';
                else if (notification.priority === 'Medium')
                    badgeColor = 'primary';

                html += `
                    <a href="${notification.url}&view=${notification.related_id}"
                       class="dropdown-item notification-item border-bottom ${notification.is_read ? '' : 'bg-light'}"
                       data-id="${notification.id}">

                        <div class="d-flex justify-content-between">

                            <strong>${notification.title}</strong>

                            <span class="badge bg-${badgeColor}">
                                ${notification.priority}
                            </span>

                        </div>

                        <div class="small mt-1">
                            ${notification.message}
                        </div>

                        <small class="text-muted">
                            ${notification.created_at}
                        </small>

                    </a>
                `;
            });

            list.innerHTML = html;

        });

}
document.addEventListener('click', function (e) {

    const item = e.target.closest('.notification-item');

    if (!item) return;

    e.preventDefault();

    console.log('Notification clicked');
    console.log(item.href);
    console.log(item.dataset.id);

    fetch('/notifications/read/' + item.dataset.id, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(res => {
        console.log(res.status);
        return res.json();
    })
    .then(data => {
        console.log(data);
        window.location.href = item.href;
    })
    .catch(err => console.log(err));

});