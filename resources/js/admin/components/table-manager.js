import axios from "axios";
import Sortable from "sortablejs";

export default (config = {}) => ({
    toggleUrl: config.toggleUrl || null,
    deleteUrl: config.deleteUrl || null,
    restoreUrl: config.restoreUrl || null,
    forceDeleteUrl: config.forceDeleteUrl || null,
    reorderUrl: config.reorderUrl || null,

    statuses: config.initialStatuses || {},

    confirmDeleteMessage: config.confirmDeleteMessage || "Сигурни ли сте, че искате да изтриете този елемент?",
    confirmRestoreMessage: config.confirmRestoreMessage || "Сигурни ли сте, че искате да възстановите този елемент?",
    confirmForceDeleteMessage: config.confirmForceDeleteMessage || "ВНИМАНИЕ: Това действие е перманентно! Сигурни ли сте?",

    errorToggleMessage: config.errorToggleMessage || "Възникна грешка при промяна на статуса.",
    errorDeleteMessage: config.errorDeleteMessage || "Грешка при изтриването.",
    errorRestoreMessage: config.errorRestoreMessage || "Грешка при възстановяването.",
    errorForceDeleteMessage: config.errorForceDeleteMessage || "Грешка при перманентното изтриване.",
    errorNetworkMessage: config.errorNetworkMessage || "Грешка при комуникация със сървъра.",
    errorReorderMessage: "Грешка при запазване на подредбата.",

    successToggleMessage: config.successToggleMessage || "Статусът е променен успешно.",
    successDeleteMessage: config.successDeleteMessage || "Записът беше изтрит успешно!",
    successRestoreMessage: config.successRestoreMessage || "Записът беше възстановен успешно!",
    successForceDeleteMessage: config.successForceDeleteMessage || "Записът беше изтрит перманентно!",
    successReorderMessage: "Успешно преподреждане!",

    loading: {},

    init() {
        const tbody = this.$el.querySelector('tbody[x-data*="sortable"]') || this.$el.querySelector('tbody');
        
        if (tbody && this.reorderUrl) {
            Sortable.create(tbody, {
                handle: '.drag-handle',
                animation: 150,
                onEnd: async (evt) => {
                    const row = evt.item;
                    const id = row.dataset.id;
                    const newIndex = evt.newIndex;

                    try {
                        const res = await axios.post(this.reorderUrl, { 
                            id: id, 
                            order: newIndex 
                        });
                        
                        if (res.data && res.data.success) {
                            notify(res.data.message || this.successReorderMessage, "success");
                        } else {
                            notify(res.data?.message || this.errorReorderMessage);
                        }
                    } catch (error) {
                        notify(error.response?.data?.message || this.errorNetworkMessage);
                    }
                }
            });
        }
    },

    async toggleStatus(id) {
        if (!this.toggleUrl || this.loading[id]) return;
        this.loading[id] = true;

        try {
            const res = await axios.post(this.toggleUrl, { id: id });

            if (res.data && res.data.success) {
                const oldStatus = this.statuses[id];
                this.statuses[id] = !oldStatus;
                const newStatus = this.statuses[id];

                const urlParams = new URLSearchParams(window.location.search);
                const statusValue = urlParams.get('status');

                const isFiltered = (statusValue !== null && statusValue !== '' && statusValue !== 'all');
                
                const isFilteringByActive = (statusValue === 'active' || statusValue === '1');
                const shouldRemove = isFiltered && (newStatus !== isFilteringByActive);

                const row = this.$el.closest('tr');

                if (shouldRemove) {
                    notify(res.data.message || "Статусът е променен!", "success", () => {
                        window.removeRowWithAnimation(row);
                    });
                } else {
                    notify(res.data.message || "Статусът е променен!", "success");
                }
            }
        } catch (error) {
            notify(error.response?.data?.message || this.errorNetworkMessage);
        } finally {
            this.loading[id] = false;
        }
    },

    async deleteItem(id) {
        if (!this.deleteUrl || this.loading[id]) return;
        if (!confirm(this.confirmDeleteMessage)) return;

        this.loading[id] = true;

        try {
            const res = await axios.post(this.deleteUrl, { id: id });

            if (res.data && res.data.success) {
                notify(res.data.message || this.successDeleteMessage, "success", () => window.removeRowWithAnimation(this.$el));
            } else {
                notify(res.data.message || this.errorDeleteMessage);
            }
        } catch (error) {
            notify(error.response?.data?.message || this.errorNetworkMessage);
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
                notify(res.data.message || this.successRestoreMessage, "success", () => window.removeRowWithAnimation(this.$el));
            } else {
                notify(res.data.message || this.errorRestoreMessage);
            }
        } catch (error) {
            notify(error.response?.data?.message || this.errorNetworkMessage);
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
                notify(res.data.message || this.successForceDeleteMessage, "success", () => window.removeRowWithAnimation(this.$el));
            } else {
                notify(res.data.message || this.errorForceDeleteMessage);
            }
        } catch (error) {
            notify(error.response?.data?.message || this.errorNetworkMessage);
        } finally {
            this.loading[id] = false;
        }
    },
});
