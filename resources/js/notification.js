export function notificationSystem() {
    return {
        notifications: [],
        initNotifications() {
            const savedNotifications = JSON.parse(localStorage.getItem('notifications')) || [];
            this.notifications = savedNotifications.map(notification => ({
                ...notification,
                visible: true
            }));
            this.notifications.forEach(notification => {
                setTimeout(() => {
                    this.remove(notification.id);
                }, 5000);
            });
            window.Livewire.on('notify', ({type, message}) => {
                this.add(message, type);
            });
        },
        add(message, type = 'info') {
            const id = Date.now();
            this.notifications.push({
                id,
                type,
                message,
                visible: true
            });
            this.saveNotifications();
            setTimeout(() => this.remove(id), 5000);
        },
        remove(id) {
            const index = this.notifications.findIndex(n => n.id === id);
            if (index > -1) {
                this.notifications[index].visible = false;
                setTimeout(() => {
                    this.notifications.splice(index, 1);
                    this.saveNotifications();
                }, 500);
            }
        },
        saveNotifications() {
            const notificationsToSave = this.notifications.map(notification => ({
                id: notification.id,
                type: notification.type,
                message: notification.message
            }));
            localStorage.setItem('notifications', JSON.stringify(notificationsToSave));
        },
        removeNotification(id) {
            this.remove(id);
        }
    };
}
