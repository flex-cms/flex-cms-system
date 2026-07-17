export default (initialItems = [], titleField = "") => ({
    items: initialItems.length
        ? initialItems.map((item) => ({ ...item, _open: false }))
        : [{ _open: true }],

    titleField: titleField,

    addItem() {
        this.items.push({
            _id: Math.random().toString(36).substr(2, 9),
            label: "",
            _open: true,
            children: [],
        });
    },

    removeItem(index) {
        if (this.items.length > 1) {
            this.items.splice(index, 1);
        } else {
            this.items[0] = { _open: true };
        }
    },

    toggleItem(index) {
        this.items[index]._open = !this.items[index]._open;
    },
});
