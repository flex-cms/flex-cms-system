export default (id, limit) => ({
    visible: false,
    id: id,
    limit: parseInt(limit || 0),

    init() {
        if (this.limit === 0) {
            this.visible = true;
            return;
        }

        const key = `alert_count_${this.id}`;
        let count = parseInt(localStorage.getItem(key) || 0);

        if (count < this.limit) {
            this.visible = true;
            localStorage.setItem(key, count + 1);
        }
    },

    close() {
        this.visible = false;
    },
});
