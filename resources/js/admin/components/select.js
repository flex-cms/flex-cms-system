import TomSelect from "tom-select";

export default () => ({
    init() {
        this.$nextTick(() => {
            new TomSelect(this.$el.querySelector("select"), {
                plugins: ["remove_button"],
                persist: false,
                create: false,
            });
        });
    },
});
