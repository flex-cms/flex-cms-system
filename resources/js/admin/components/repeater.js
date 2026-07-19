import axios from "axios";
import Sortable from "sortablejs";

export default (
    initialItems = [],
    titleField = "",
    loadUrl = null,
    saveUrl = null,
) => ({
    items: [],

    titleField,
    loadUrl,
    saveUrl,

    isLoading: false,
    sortables: [],

    init() {
        this.items = this.prepareItems(initialItems);

        if (this.loadUrl) {
            this.loadItems();
        } else {
            this.$nextTick(() => {
                this.initSortable();
            });
        }
    },

    async loadItems() {
        this.isLoading = true;

        try {
            const response = await axios.get(this.loadUrl);

            if (response.data?.success) {
                this.items = this.prepareItems(response.data.data);
            }
        } catch (error) {
            console.error("Load repeater error:", error);
        } finally {
            this.isLoading = false;

            this.$nextTick(() => {
                this.initSortable();
            });
        }
    },

    prepareItems(items = []) {
        return items.map((item) => ({
            ...item,
            _open: false,
            children: item.children ? this.prepareItems(item.children) : [],
        }));
    },

    addItem() {
        this.items.push({
            _id: crypto.randomUUID(),
            label: "",
            _open: true,
            children: [],
        });

        this.$nextTick(() => {
            this.initSortable();
        });
    },

    removeItem(item) {
        const remove = (items) => {
            const index = items.indexOf(item);

            if (index !== -1) {
                items.splice(index, 1);

                return true;
            }

            for (const child of items) {
                if (child.children && remove(child.children)) {
                    return true;
                }
            }

            return false;
        };

        remove(this.items);
    },

    initSortable() {
        this.destroySortable();

        this.$nextTick(() => {
            this.$root.querySelectorAll(".menu-tree").forEach((container) => {
                const sortable = new Sortable(container, {
                    group: {
                        name: "menu-tree",
                        pull: true,
                        put: true,
                    },

                    animation: 150,

                    handle: ".drag-handle",

                    draggable: ".menu-sortable-item",

                    fallbackOnBody: true,

                    forceFallback: true,

                    fallbackTolerance: 5,

                    swapThreshold: 0.65,

                    onEnd: async (event) => {
                        if (!this.saveUrl) {
                            return;
                        }

                        const element = event.item;

                        const id = element.dataset.id;

                        if (!id) {
                            return;
                        }

                        /*
                         * Намираме директния родител.
                         *
                         * Ако е в root .menu-tree
                         * няма parent_id.
                         *
                         * Ако е вътре в друг menu-item
                         * взимаме неговото id.
                         */
                        const parentItem = event.to.closest(
                            ".menu-sortable-item",
                        );

                        const parentId = parentItem
                            ? parentItem.dataset.id
                            : null;

                        const siblings = Array.from(event.to.children).filter(
                            (el) => el.classList.contains("menu-sortable-item"),
                        );

                        const order = siblings.indexOf(element);

                        try {
                            await axios.post(this.saveUrl, {
                                id: Number(id),
                                parent_id: parentId ? Number(parentId) : null,
                                order,
                            });
                        } catch (error) {
                            console.error("Tree update error:", error);
                        }
                    },
                });

                this.sortables.push(sortable);
            });
        });
    },

    destroySortable() {
        this.sortables.forEach((sortable) => {
            if (sortable) {
                sortable.destroy();
            }
        });

        this.sortables = [];
    },
});