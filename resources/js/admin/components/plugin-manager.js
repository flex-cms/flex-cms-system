import axios from "axios";

export default (config = {}) => ({
    installUrl: config.installUrl,
    toggleUrl: config.toggleUrl,
    deleteUrl: config.deleteUrl,
    updateUrl: config.updateUrl,

    statuses: { ...(config.initialStatuses || {}) },
    installed: { ...(config.initialInstalled || {}) },
    versions: { ...(config.initialVersions || {}) },
    loading: {},

    detailsOpen: false,
    selectedPlugin: null,

    buttonText(slug) {
        if (this.loading[slug]) return "Обработка...";
        if (!this.installed[slug]) return "Инсталиране";
        return this.statuses[slug] ? "Деактивиране" : "Активиране";
    },

    buttonIcon(slug) {
        if (this.loading[slug]) return "fa-spinner fa-spin";
        if (!this.installed[slug]) return "fa-download";
        return this.statuses[slug] ? "fa-pause" : "fa-play";
    },

    buttonClass(slug) {
        if (!this.installed[slug]) {
            return "bg-blue-600 text-white hover:bg-blue-700";
        }

        return this.statuses[slug]
            ? "bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-400"
            : "bg-emerald-600 text-white hover:bg-emerald-700";
    },

    openDetails(plugin) {
        const slug = plugin.slug;

        this.selectedPlugin = {
            ...plugin,
            is_installed: this.installed[slug] ?? plugin.is_installed,
            is_active: this.statuses[slug] ?? plugin.is_active,
            installed_version: this.versions[slug] ?? plugin.installed_version,
        };

        this.detailsOpen = true;
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

    async toggleStatus(slug) {
        if (!this.toggleUrl || this.loading[slug]) return;

        this.loading[slug] = true;

        try {
            const response = await axios.post(this.toggleUrl, { slug });

            if (response.data?.success) {
                this.statuses[slug] = !this.statuses[slug];
                notify(response.data.message, 'success');
            } else {
                notify(response.data?.message || "Възникна грешка при промяна на статуса.");
            }
        } catch (error) {
            console.error(error);
            notify("Грешка при комуникация със сървъра.", "error");
        } finally {
            this.loading[slug] = false;
        }
    },

    async deleteItem(id) {
        if (!this.deleteUrl || this.loading[id]) return;
        if (!confirm(this.confirmDeleteMessage)) return;

        const dropTables = confirm("Желаете ли да премахнете и таблиците, свързани с този плъгин?");

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

                notify(response.data.message || "Плъгинът беше премахнат успешно.");
            } else {
                notify(response.data?.message || "Възникна грешка при премахването.", "error");
            }
        } catch (error) {
            console.error(error);
            notify("Грешка при комуникация със сървъра.", "error");
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
                notify(response.data.message || "Плъгинът беше обновен успешно.");
                window.location.reload();
            } else {
                notify(response.data?.message || "Възникна грешка при обновяването.", "error");
            }
        } catch (error) {
            console.error(error);
            notify("Грешка при комуникация със сървъра.", "error");
        } finally {
            this.loading[id] = false;
        }
    },

    async handlePluginStatus(slug) {
        if (this.installed[slug]) {
            await this.toggleStatus(slug);
        } else {
            await this.installPlugin(slug);
        }
    },

    async installPlugin(slug) {
        this.loading[slug] = true;

        try {
            const response = await axios.post(this.installUrl, { slug });
            const data = response.data;

            if (!data.success) {
                throw new Error(data.message);
            }

            const version = data.version ?? data.data?.version ?? null;

            this.installed[slug] = true;
            this.statuses[slug] = false;
            this.versions[slug] = version;

            notify(data.message, "success");
        } catch (error) {
            notify(error.response?.data?.message || error.message, "error");
        } finally {
            this.loading[slug] = false;
        }
    },
});
