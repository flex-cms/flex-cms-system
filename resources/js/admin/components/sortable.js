import axios from "axios";
import Sortable from "sortablejs";

export default (url) => ({
    sortable: null,

    init() {
        this.sortable = new Sortable(this.$el, {
            animation: 150,
            handle: ".drag-handle",
            draggable: ".menu-sortable-item",
            group: "menu-tree",

            onEnd: async (event) => {
                const item = event.item;

                const id = item.dataset.id;

                const parent = item
                    .closest(".menu-tree")
                    ?.closest(".menu-item");
                const parentId = parent ? parent.dataset.id : null;

                const order = [...event.to.children].indexOf(item);

                await axios.post(url, {
                    id,
                    parent_id: parentId,
                    order,
                });
            },
        });
    },

    destroy() {
        if (this.sortable) {
            this.sortable.destroy();
        }
    },
});