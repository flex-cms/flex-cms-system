export default (initialData = {}) => ({
    open: false,
    selected: initialData.selected || "",
    label: initialData.label || "Изберете...",
    isCustom: initialData.isCustom || false,
    customValue: initialData.customValue || "",

    clearCustom() {
        this.customValue = "";
        this.selected = "";
        this.label = "Изберете...";
        this.isCustom = false;
        this.open = true;
        this.$nextTick(() => (this.open = true));
    },

    selectOption(val, text) {
        this.selected = val;
        this.label = text;
        this.isCustom = false;
        this.open = false;
    },

    toggleCustom() {
        this.isCustom = true;
        this.open = false;
        this.customValue = "";
        this.$nextTick(() => this.$refs.customInput.focus());
    },

    toggleMenu() {
        this.open = !this.open;
    },
});