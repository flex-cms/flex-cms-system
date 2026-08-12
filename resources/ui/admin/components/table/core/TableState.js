export class TableState {
    constructor({
        rows = [],
        loading = false,
        error = null,
        sortBy = null,
        sortDirection = null,
    } = {}) {
        this.rows = Array.isArray(rows) ? rows : [];
        this.loading = Boolean(loading);
        this.error = error;
        this.sortBy = sortBy;
        this.sortDirection = this.#normalizeSortDirection(sortDirection);
    }

    setRows(rows) {
        this.rows = Array.isArray(rows) ? rows : [];

        return this;
    }

    setLoading(loading) {
        this.loading = Boolean(loading);

        return this;
    }

    setError(error) {
        this.error = error;

        return this;
    }

    clearError() {
        this.error = null;

        return this;
    }

    setSort(column, direction) {
        this.sortBy = column;
        this.sortDirection = this.#normalizeSortDirection(direction);

        if (this.sortDirection === null) {
            this.sortBy = null;
        }

        return this;
    }

    clearSort() {
        this.sortBy = null;
        this.sortDirection = null;

        return this;
    }

    nextSortDirection(column) {
        if (this.sortBy !== column) {
            return "asc";
        }

        if (this.sortDirection === "asc") {
            return "desc";
        }

        if (this.sortDirection === "desc") {
            return null;
        }

        return "asc";
    }

    clone() {
        return new TableState({
            rows: [...this.rows],
            loading: this.loading,
            error: this.error,
            sortBy: this.sortBy,
            sortDirection: this.sortDirection,
        });
    }

    #normalizeSortDirection(direction) {
        return direction === "asc" || direction === "desc"
            ? direction
            : null;
    }
}
