export default function updater() {
    return {
        isUpdating: false,

        startUpdate(event) {
            if (!confirm("Сигурен ли си, че искаш да започнеш ъпдейта?")) {
                event.preventDefault();
                this.isUpdating = false;
                return;
            }

            this.isUpdating = true;
        },
    };
}
