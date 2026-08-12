export class TableQuery {
    constructor({
        page = 1,
        pageSize = 25,
        search = "",
        sortBy = null,
        sortDirection = null,
        filters = {},
    } = {}) {
        this.page = this.#positiveInteger(page, 1);
        this.pageSize = this.#positiveInteger(pageSize, 25);
        this.search = typeof search === "string" ? search.trim() : "";
        this.sortBy = typeof sortBy === "string" && sortBy.trim() !== ""
            ? sortBy.trim()
            : null;
        this.sortDirection = sortDirection === "asc" || sortDirection === "desc"
            ? sortDirection
            : null;
        this.filters = filters && typeof filters === "object"
            ? { ...filters }
            : {};
    }

    toSearchParams() {
        const params = new URLSearchParams();

        params.set("page", String(this.page));
        params.set("per_page", String(this.pageSize));

        if (this.search !== "") {
            params.set("search", this.search);
        }

        if (this.sortBy && this.sortDirection) {
            params.set("sort", this.sortBy);
            params.set("direction", this.sortDirection);
        }

        for (const [key, value] of Object.entries(this.filters)) {
            if (
                value === null
                || value === undefined
                || value === ""
            ) {
                continue;
            }

            if (Array.isArray(value)) {
                for (const item of value) {
                    params.append(
                        `filter[${key}][]`,
                        String(item),
                    );
                }

                continue;
            }

            params.set(
                `filter[${key}]`,
                String(value),
            );
        }

        return params;
    }

    toString() {
        return this.toSearchParams().toString();
    }

    #positiveInteger(value, fallback) {
        const parsed = Number.parseInt(
            String(value),
            10,
        );

        return Number.isInteger(parsed) && parsed > 0
            ? parsed
            : fallback;
    }
}
