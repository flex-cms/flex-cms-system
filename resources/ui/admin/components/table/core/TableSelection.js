export class TableSelection {
    constructor({
        rowKey = "id",
    } = {}) {
        this.rowKey = rowKey;
        this.selected = new Set();
    }

    select(key) {
        if (key === null || key === undefined) {
            return this;
        }

        this.selected.add(String(key));

        return this;
    }

    deselect(key) {
        this.selected.delete(String(key));

        return this;
    }

    toggle(key) {
        const normalized = String(key);

        if (this.selected.has(normalized)) {
            this.selected.delete(normalized);
        } else {
            this.selected.add(normalized);
        }

        return this;
    }

    clear() {
        this.selected.clear();

        return this;
    }

    has(key) {
        return this.selected.has(String(key));
    }

    selectRows(rows = []) {
        for (const row of rows) {
            const key = row?.[this.rowKey];

            if (key !== null && key !== undefined) {
                this.select(key);
            }
        }

        return this;
    }

    deselectRows(rows = []) {
        for (const row of rows) {
            const key = row?.[this.rowKey];

            if (key !== null && key !== undefined) {
                this.deselect(key);
            }
        }

        return this;
    }

    allSelected(rows = []) {
        if (!Array.isArray(rows) || rows.length === 0) {
            return false;
        }

        return rows.every((row) =>
            this.has(row?.[this.rowKey]),
        );
    }

    someSelected(rows = []) {
        if (!Array.isArray(rows) || rows.length === 0) {
            return false;
        }

        const selectedCount = rows.filter((row) =>
            this.has(row?.[this.rowKey]),
        ).length;

        return selectedCount > 0
            && selectedCount < rows.length;
    }

    values() {
        return [...this.selected];
    }

    count() {
        return this.selected.size;
    }
}
