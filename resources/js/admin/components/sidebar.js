import axios from "axios";

export default (id, initialState, initialStateDarkMode) => ({
    id: id,
    isOpen: initialState,
    darkMode: initialStateDarkMode,
    mounted: false,

    init() {
        this.$nextTick(() => {
            this.mounted = true;
        });

        window.addEventListener("toggle-sidebar", (e) => {
            if (!e.detail || !e.detail.id || e.detail.id === this.id) {
                this.toggle();
            }
        });

        // Автоматично отваряне на голям екран при преоразмеряване
        window.addEventListener("resize", () => {
            if (window.innerWidth >= 1024 && !this.isOpen) {
                this.isOpen = true;
            }
        });
    },

    toggle() {
        this.isOpen = !this.isOpen;
        this.syncState();
    },

    toggleTheme() {
        this.darkMode = !this.darkMode;

        if (this.darkMode) {
            document.documentElement.classList.add("dark");
        } else {
            document.documentElement.classList.remove("dark");
        }

        axios
            .post("/admin/theme-toggle", {
                darkMode: this.darkMode,
            })
            .catch((err) => {
                console.error("Theme sync error:", err.response?.data || err.message);
            });
    },

    syncState() {
        axios
            .post("/admin/sidebar-toggle", {
                sidebarId: this.id,
                sidebarOpen: this.isOpen,
            })
            .catch((err) => {
                console.error("Error toggling sidebar:", err.response?.data || err.message);
            });
    },

    navigateTo(url) {
        if (window.innerWidth < 1024) {
            this.isOpen = false;
            setTimeout(() => {
                window.location.href = url;
            }, 300);
        } else {
            window.location.href = url;
        }
    },
});
