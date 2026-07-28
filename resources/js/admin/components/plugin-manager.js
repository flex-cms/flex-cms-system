import axios from "axios";

export default (config = {}) => ({
    toggleUrl: config.toggleUrl || null,
    deleteUrl: config.deleteUrl || null,
    updateUrl: config.updateUrl || null,
    statuses: config.initialStatuses || {},
    confirmDeleteMessage:
        config.confirmDeleteMessage ||
        "Сигурни ли сте, че искате да премахнете този плъгин?",

    loading: {},
    detailsOpen: false,
    selectedPlugin: null,

    openDetails(plugin) {
        this.selectedPlugin = plugin;
        this.detailsOpen = true;
        document.documentElement.classList.add("overflow-hidden");
    },

    closeDetails() {
        this.detailsOpen = false;
        document.documentElement.classList.remove("overflow-hidden");

        setTimeout(() => {
            this.selectedPlugin = null;
        }, 200);
    },

    hasItems(value) {
        return Array.isArray(value) && value.length > 0;
    },

    booleanLabel(value) {
        return value ? "Да" : "Не";
    },

    async toggleStatus(id) {
        if (!this.toggleUrl || this.loading[id]) return;

        this.loading[id] = true;

        try {
            const response = await axios.post(this.toggleUrl, { id });

            if (response.data?.success) {
                this.statuses[id] = !this.statuses[id];
            } else {
                alert(
                    response.data?.message ||
                        "Възникна грешка при промяна на статуса.",
                );
            }
        } catch (error) {
            console.error(error);
            alert("Грешка при комуникация със сървъра.");
        } finally {
            this.loading[id] = false;
        }
    },

    async deleteItem(id) {
        if (!this.deleteUrl || this.loading[id]) return;
        if (!confirm(this.confirmDeleteMessage)) return;

        const dropTables = confirm(
            "Желаете ли да премахнете и таблиците, свързани с този плъгин?",
        );

        this.loading[id] = true;

        try {
            const response = await axios.post(this.deleteUrl, {
                id,
                dropTables,
            });

            if (response.data?.success) {
                const card = document.querySelector(
                    `[data-plugin-card="${id}"]`,
                );

                if (card) {
                    card.style.transition = "all 0.3s ease";
                    card.style.opacity = "0";
                    card.style.transform = "scale(0.97)";

                    setTimeout(() => {
                        card.remove();

                        if (
                            document.querySelectorAll("[data-plugin-card]")
                                .length === 0
                        ) {
                            window.location.reload();
                        }
                    }, 300);
                }
            } else {
                alert(
                    response.data?.message ||
                        "Възникна грешка при премахването.",
                );
            }
        } catch (error) {
            console.error(error);
            alert("Грешка при комуникация със сървъра.");
        } finally {
            this.loading[id] = false;
        }
    },

    async updatePlugin(id) {
        if (!this.updateUrl || this.loading[id]) return;

        this.loading[id] = true;

        try {
            const response = await axios.post(this.updateUrl, { id });

            if (response.data?.success) {
                alert(
                    response.data.message || "Плъгинът беше обновен успешно.",
                );

                window.location.reload();
            } else {
                alert(
                    response.data?.message ||
                        "Възникна грешка при обновяването.",
                );
            }
        } catch (error) {
            console.error(error);
            alert("Грешка при комуникация със сървъра.");
        } finally {
            this.loading[id] = false;
        }
    },
});
