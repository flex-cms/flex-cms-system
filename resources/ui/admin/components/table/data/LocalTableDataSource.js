import { TableDataSource } from "./TableDataSource.js";

export class LocalTableDataSource extends TableDataSource {
    constructor(rows = []) {
        super();

        this.rows = Array.isArray(rows)
            ? [...rows]
            : [];
    }

    setRows(rows) {
        this.rows = Array.isArray(rows)
            ? [...rows]
            : [];

        return this;
    }

    async fetch(query) {
        let rows = [...this.rows];

        if (query.search) {
            const needle = query.search.toLocaleLowerCase("bg");

            rows = rows.filter((row) =>
                Object.values(row).some((value) =>
                    String(value ?? "")
                        .toLocaleLowerCase("bg")
                        .includes(needle),
                ),
            );
        }

        for (const [key, value] of Object.entries(query.filters ?? {})) {
            if (
                value === null
                || value === undefined
                || value === ""
            ) {
                continue;
            }

            rows = rows.filter((row) => {
                const rowValue = row?.[key];

                if (Array.isArray(value)) {
                    return value.map(String).includes(
                        String(rowValue),
                    );
                }

                return String(rowValue) === String(value);
            });
        }

        if (query.sortBy && query.sortDirection) {
            const direction = query.sortDirection === "desc"
                ? -1
                : 1;

            rows.sort((left, right) => {
                const a = left?.[query.sortBy];
                const b = right?.[query.sortBy];

                if (a === b) {
                    return 0;
                }

                if (a === null || a === undefined) {
                    return 1;
                }

                if (b === null || b === undefined) {
                    return -1;
                }

                if (
                    typeof a === "number"
                    && typeof b === "number"
                ) {
                    return (a - b) * direction;
                }

                return String(a).localeCompare(
                    String(b),
                    "bg",
                    {
                        numeric: true,
                        sensitivity: "base",
                    },
                ) * direction;
            });
        }

        const total = rows.length;
        const lastPage = Math.max(
            1,
            Math.ceil(total / query.pageSize),
        );

        const page = Math.min(
            query.page,
            lastPage,
        );

        const offset = (page - 1) * query.pageSize;
        const data = rows.slice(
            offset,
            offset + query.pageSize,
        );

        return {
            data,
            pagination: {
                page,
                per_page: query.pageSize,
                total,
                last_page: lastPage,
            },
        };
    }
}
