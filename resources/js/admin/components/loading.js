export default () => ({
    loading: false,

    init() {
        window.addEventListener("axios-loading-start", () => {
            this.loading = true;
        });

        window.addEventListener("axios-loading-end", () => {
            this.loading = false;
        });
    },
});
