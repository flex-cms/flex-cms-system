import axios from "axios";

export default (config = {}) => ({
    // Първоначални настройки с дефолтни стойности
    toggleUrl: config.toggleUrl || null,
    deleteUrl: config.deleteUrl || null,
    statuses: config.initialStatuses || {},
    confirmDeleteMessage:
        config.confirmDeleteMessage ||
        "Сигурни ли сте, че искате да изтриете този елемент?",

    loading: {},

    async toggleStatus(id) {
        if (!this.toggleUrl || this.loading[id]) return;

        this.loading[id] = true;

        try {
            const res = await axios.post(this.toggleUrl, { id: id });

            if (res.data && res.data.success) {
                this.statuses[id] = !this.statuses[id];
            } else {
                alert(
                    res.data.message ||
                        "Възникна грешка при промяна на статуса.",
                );
            }
        } catch (error) {
            alert("Грешка при комуникация със сървъра.");
        } finally {
            this.loading[id] = false;
        }
    },

    async deleteItem(id) {
        if (!this.deleteUrl || this.loading[id]) return;

        if (!confirm(this.confirmDeleteMessage)) {
            return;
        }

        this.loading[id] = true;

        try {
            const res = await axios.post(this.deleteUrl, { id: id });

            if (res.data && res.data.success) {
                if (res.data.message) alert(res.data.message);

                const row =
                    document.querySelector(`tr[data-row-id="${id}"]`) ||
                    document
                        .querySelector(`button[*|click*="deleteItem(${id})"]`)
                        ?.closest("tr") ||
                    document
                        .querySelector(`button[@click*="deleteItem(${id})"]`)
                        ?.closest("tr");

                if (row) {
                    row.style.transition = "all 0.3s ease";
                    row.style.opacity = "0";
                    row.style.transform = "translateX(20px)";
                    setTimeout(() => row.remove(), 300);
                } else {
                    window.location.reload();
                }
            } else {
                alert(res.data.message || "Възникна грешка при изтриването.");
            }
        } catch (error) {
            alert("Грешка при премахване на елемента.");
        } finally {
            this.loading[id] = false;
        }
    },
});
