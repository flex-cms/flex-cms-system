export class TableColumn {
    /**
     * @param {{
     *   key: string,
     *   label?: string,
     *   sortable?: boolean,
     *   visible?: boolean,
     *   width?: string|null,
     *   align?: "left"|"center"|"right",
     *   headerClass?: string,
     *   cellClass?: string,
     *   formatter?: ((value: unknown, row: Record<string, unknown>, column: TableColumn) => unknown)|null
     * }} config
     */
    constructor(config) {
        if (!config || typeof config !== "object") {
            throw new TypeError("TableColumn config must be an object.");
        }

        if (typeof config.key !== "string" || config.key.trim() === "") {
            throw new TypeError("TableColumn requires a non-empty key.");
        }

        this.key = config.key.trim();
        this.label = typeof config.label === "string" && config.label.trim() !== ""
            ? config.label
            : this.key;

        this.sortable = config.sortable === true;
        this.visible = config.visible !== false;

        this.width = typeof config.width === "string" && config.width.trim() !== ""
            ? config.width.trim()
            : null;

        this.align = ["left", "center", "right"].includes(config.align)
            ? config.align
            : "left";

        this.headerClass = typeof config.headerClass === "string"
            ? config.headerClass
            : "";

        this.cellClass = typeof config.cellClass === "string"
            ? config.cellClass
            : "";

        this.formatter = typeof config.formatter === "function"
            ? config.formatter
            : null;
    }

    /**
     * @param {unknown} value
     * @param {Record<string, unknown>} row
     * @returns {unknown}
     */
    format(value, row) {
        if (this.formatter === null) {
            return value;
        }

        return this.formatter(
            value,
            row,
            this
        );
    }

    /**
     * @param {object|TableColumn} column
     * @returns {TableColumn}
     */
    static from(column) {
        return column instanceof TableColumn
            ? column
            : new TableColumn(column);
    }
}

export default TableColumn;
