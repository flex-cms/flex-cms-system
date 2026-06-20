import axios from "axios";

export default (config = {}) => ({
    toggleUrl: config.toggleUrl || null,
    deleteUrl: config.deleteUrl || null,
    restoreUrl: config.restoreUrl || null,
    forceDeleteUrl: config.forceDeleteUrl || null,

    statuses: config.initialStatuses || {},

    confirmDeleteMessage:
        config.confirmDeleteMessage ||
        "Сигурни ли сте, че искате да изтриете този елемент?",
    confirmRestoreMessage:
        config.confirmRestoreMessage ||
        "Сигурни ли сте, че искате да възстановите този елемент?",
    confirmForceDeleteMessage:
        config.confirmForceDeleteMessage ||
        "ВНИМАНИЕ: Това действие е перманентно! Сигурни ли сте?",

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

        if (!confirm(this.confirmDeleteMessage)) return;

        this.loading[id] = true;

        try {
            const res = await axios.post(this.deleteUrl, {
                id: id,
            });

            if (res.data && res.data.success) {
                const row = this.$el.closest("tr");
                const tbody = row.closest("tbody");

                if (row) {
                    row.style.transition = "all 0.3s ease";
                    row.style.opacity = "0";

                    setTimeout(() => {
                        row.remove();

                        const remainingRows =
                            tbody.querySelectorAll("tr").length;

                        if (remainingRows === 0) {
                            window.location.reload();
                        }
                    }, 300);
                }
            } else {
                alert(res.data.message || "Грешка при изтриването.");
            }
        } catch (error) {
            console.error(error);
            alert("Грешка при комуникация със сървъра.");
        } finally {
            this.loading[id] = false;
        }
    },

    async restoreItem(id) {
        if (!this.restoreUrl || this.loading[id]) return;
        if (!confirm(this.confirmRestoreMessage)) return;

        this.loading[id] = true;
        try {
            const res = await axios.post(this.restoreUrl, { id: id });
            if (res.data && res.data.success) {
                window.location.reload(); // Най-лесно за обновяване на таблицата
            } else {
                alert(res.data.message || "Грешка при възстановяването.");
            }
        } catch (error) {
            alert("Грешка при комуникация със сървъра.");
        } finally {
            this.loading[id] = false;
        }
    },

    async forceDeleteItem(id) {
        if (!this.forceDeleteUrl || this.loading[id]) return;
        if (!confirm(this.confirmForceDeleteMessage)) return;

        this.loading[id] = true;
        try {
            const res = await axios.post(this.forceDeleteUrl, {
                id: id,
                force: true,
            });
            if (res.data && res.data.success) {
                const row = this.$el.closest("tr");
                row.remove();
            } else {
                alert(
                    res.data.message || "Грешка при перманентното изтриване.",
                );
            }
        } catch (error) {
            alert("Грешка при комуникация със сървъра.");
        } finally {
            this.loading[id] = false;
        }
    },
});
