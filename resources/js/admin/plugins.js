import axios from "axios";

export default (initialStatuses = {}) => ({
    statuses: initialStatuses,
    loading: {},

    togglePlugin(id) {
        this.loading[id] = true;

        axios
            .post("/admin/plugins/toggle", { id: id })
            .then((res) => {
                this.loading[id] = false;
                if (res.data && res.data.success) {
                    this.statuses[id] = !this.statuses[id];
                } else {
                    alert(
                        res.data.message ||
                            "Възникна грешка при промяна на статуса.",
                    );
                }
            })
            .catch(() => {
                this.loading[id] = false;
                alert("Грешка при комуникация със сървъра.");
            });
    },

    deletePlugin(id) {
        if (!confirm("Сигурни ли сте, че искате да премахнете този плъгин?")) {
            return;
        }

        this.loading[id] = true;

        axios
            .post("/admin/plugins/delete", { id: id })
            .then((res) => {
                this.loading[id] = false;
                if (res.data && res.data.success) {
                    alert(res.data.message);
                    window.location.reload();
                } else {
                    alert(
                        res.data.message || "Възникна грешка при изтриването.",
                    );
                }
            })
            .catch(() => {
                this.loading[id] = false;
                alert("Грешка при премахване на плъгина.");
            });
    },
});