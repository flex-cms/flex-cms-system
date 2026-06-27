import Sortable from "sortablejs";

export default (url) => ({
    init() {
        new Sortable(this.$el, {
            animation: 150,

            handle: ".drag-handle",

            onEnd: () => {
                this.save(url);
            },
        });
    },

    save(url) {
        const items = [...this.$el.querySelectorAll("tr[data-id]")].map(
            (row, index) => ({
                id: row.dataset.id,
                position: index,
            }),
        );

        fetch(url, {
            method: "POST",

            headers: {
                "Content-Type": "application/json",
            },

            body: JSON.stringify({
                items,
            }),
        });
    },
});
