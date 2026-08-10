import axios from "axios";
import Sortable from "sortablejs";

export default (initialItems = [], titleField = "", loadUrl = null, saveUrl = null) => ({
    items: [],
    titleField,
    loadUrl,
    saveUrl,

    isLoading: false,
    sortables: [],
    rootElement: null,
    sortableRefreshId: 0,

    init() {
        this.rootElement = this.$el;
        this.items = this.prepareItems(initialItems);

        if (this.loadUrl) {
            this.loadItems();
            return;
        }

        this.refreshSortable();
    },

    async loadItems() {
        if (!this.loadUrl) {
            return;
        }

        this.isLoading = true;

        try {
            const response = await axios.get(this.loadUrl);

            if (!response.data?.success) {
                throw new Error(response.data?.message ?? "Неуспешно зареждане на елементите.");
            }

            this.items = this.prepareItems(
                Array.isArray(response.data.data) ? response.data.data : [],
            );
        } catch (error) {
            console.error("Load repeater error:", error);
        } finally {
            this.isLoading = false;
            this.refreshSortable();
        }
    },

    prepareItems(items = []) {
        if (!Array.isArray(items)) {
            return [];
        }

        return items.map((item) => ({
            ...item,
            _key: item._key ?? item._id ?? this.createKey(),
            _open: item._open ?? false,
            children: this.prepareItems(item.children ?? []),
        }));
    },

    createKey() {
        if (globalThis.crypto?.randomUUID) {
            return `item-${globalThis.crypto.randomUUID()}`;
        }

        return `item-${Date.now()}-${Math.random().toString(36).slice(2)}`;
    },

    createItem() {
        const key = this.createKey();

        return {
            _id: key,
            _key: key,
            label: "",
            _open: true,
            children: [],
        };
    },

    addItem() {
        this.items = [...this.items, this.createItem()];
        this.refreshSortable();
    },

    addChild(parent) {
        const child = this.createItem();

        this.items = this.updateItem(this.items, parent._key, (item) => ({
            ...item,
            _open: true,
            children: [...(item.children ?? []), child],
        }));

        this.refreshSortable();
    },

    removeItem(itemKey) {
        const removeByKey = (items) =>
            items.flatMap((item) => {
                if (item._key === itemKey) {
                    return [];
                }

                return [
                    {
                        ...item,
                        children: removeByKey(item.children ?? []),
                    },
                ];
            });

        this.items = removeByKey(this.items);
        this.refreshSortable();
    },

    updateItem(items, itemKey, callback) {
        return items.map((item) => {
            if (item._key === itemKey) {
                return callback(item);
            }

            return {
                ...item,
                children: this.updateItem(item.children ?? [], itemKey, callback),
            };
        });
    },

    moveItem(itemKey, parentKey, newIndex) {
        let movedItem = null;

        const detach = (items) =>
            items.flatMap((item) => {
                if (item._key === itemKey) {
                    movedItem = item;
                    return [];
                }

                return [
                    {
                        ...item,
                        children: detach(item.children ?? []),
                    },
                ];
            });

        const remainingItems = detach(this.items);
        if (!movedItem) {
            return;
        }

        const insert = (items) => {
            const nextItems = [...items];
            const index = Math.max(0, Math.min(newIndex, nextItems.length));
            nextItems.splice(index, 0, movedItem);
            return nextItems;
        };

        if (!parentKey) {
            this.items = insert(remainingItems);
            return;
        }

        let parentFound = false;
        const insertIntoParent = (items) =>
            items.map((item) => {
                if (item._key === parentKey) {
                    parentFound = true;
                    return {
                        ...item,
                        children: insert(item.children ?? []),
                    };
                }

                return {
                    ...item,
                    children: insertIntoParent(item.children ?? []),
                };
            });

        const nextItems = insertIntoParent(remainingItems);
        this.items = parentFound ? nextItems : insert(remainingItems);
    },

    restoreDraggedElement(event) {
        const { item, from, oldIndex } = event;

        if (!item || !from || oldIndex == null) {
            return;
        }

        item.remove();

        const siblings = Array.from(from.children).filter((element) =>
            element.classList.contains("menu-sortable-item"),
        );

        from.insertBefore(item, siblings[oldIndex] ?? null);
    },

    refreshSortable() {
        const refreshId = ++this.sortableRefreshId;

        this.$nextTick(() => {
            if (refreshId !== this.sortableRefreshId) {
                return;
            }

            this.initSortable();
        });
    },

    initSortable() {
        this.destroySortable();

        if (!this.rootElement?.isConnected) {
            return;
        }

        this.rootElement.querySelectorAll(".menu-tree").forEach((container) => {
            const sortable = new Sortable(container, {
                group: "menu-tree",
                animation: 150,
                handle: ".drag-handle",
                draggable: ".menu-sortable-item",
                fallbackOnBody: true,
                forceFallback: true,
                fallbackTolerance: 5,
                swapThreshold: 0.65,
                onMove: (event) => !event.dragged.contains(event.to),
                onEnd: (event) => this.handleSortEnd(event),
            });

            this.sortables.push(sortable);
        });
    },

    async handleSortEnd(event) {
        const element = event.item;
        const itemKey = element?.dataset.key;

        if (!element || !itemKey) {
            this.refreshSortable();
            return;
        }

        const parentElement = event.to.closest(".menu-sortable-item");
        const parentKey = parentElement?.dataset.key ?? null;
        const parentId = parentElement?.dataset.id ?? null;
        const siblings = Array.from(event.to.children).filter((child) =>
            child.classList.contains("menu-sortable-item"),
        );
        const newIndex = siblings.indexOf(element);

        const persistedId = Number(element.dataset.id);
        const persistedParentId = parentId ? Number(parentId) : null;
        const canPersistParent =
            !parentId || (Number.isInteger(persistedParentId) && persistedParentId > 0);

        // Sortable moves nodes owned by Alpine's x-for. Restore the DOM first,
        // then let Alpine render the new keyed tree from the data state.
        this.restoreDraggedElement(event);
        this.moveItem(itemKey, parentKey, newIndex);
        this.refreshSortable();

        if (
            !this.saveUrl ||
            !Number.isInteger(persistedId) ||
            persistedId <= 0 ||
            !canPersistParent
        ) {
            return;
        }

        try {
            const response = await axios.post(this.saveUrl, {
                id: persistedId,
                parent_id: persistedParentId,
                order: newIndex,
            });

            if (!response.data?.success) {
                throw new Error(response.data?.message ?? "Неуспешно преместване на елемента.");
            }
        } catch (error) {
            console.error("Tree update error:", error);
            await this.loadItems();
        }
    },

    destroySortable() {
        this.sortables.forEach((sortable) => sortable?.destroy());
        this.sortables = [];
    },

    destroy() {
        this.sortableRefreshId += 1;
        this.destroySortable();
        this.rootElement = null;
    },
});
