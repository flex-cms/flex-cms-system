import { css, html, nothing } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";
import { TableColumn } from "./core/TableColumn.js";
import { TableQuery } from "./core/TableQuery.js";
import { TableState } from "./core/TableState.js";
import { TableSelection } from "./core/TableSelection.js";
import { LocalTableDataSource } from "./data/LocalTableDataSource.js";

import "./flex-table-empty.js";
import "./flex-table-loading.js";
import "./flex-table-page-size.js";
import "./flex-table-pagination.js";
import "./flex-table-sort-indicator.js";
import "./flex-table-toolbar.js";
import "./flex-table-checkbox.js";
import "./flex-table-bulk-actions.js";
import "./flex-table-row-actions.js";
import "./flex-table-search.js";
import "./flex-table-filters.js";

export class FlexDataTable extends FlexElement {
    static properties = {
        rows: { attribute: false },
        columns: { attribute: false },
        loading: { type: Boolean, reflect: true },
        rowKey: { type: String, attribute: "row-key" },
        emptyTitle: { type: String, attribute: "empty-title" },
        emptyDescription: { type: String, attribute: "empty-description" },
        striped: { type: Boolean, reflect: true },
        hoverable: { type: Boolean, reflect: true },
        sortBy: { type: String, attribute: "sort-by" },
        sortDirection: { type: String, attribute: "sort-direction" },
        page: { type: Number },
        pageSize: { type: Number, attribute: "page-size" },
        total: { type: Number },
        lastPage: { type: Number, attribute: "last-page" },
        paginated: { type: Boolean, reflect: true },
        searchable: { type: Boolean, reflect: true },
        searchPlaceholder: { type: String, attribute: "search-placeholder" },
        searchDebounce: { type: Number, attribute: "search-debounce" },
        filterDefinitions: { attribute: false },
        selectable: { type: Boolean, reflect: true },
        bulkActions: { attribute: false },
        rowActions: { attribute: false },
    };

    static styles = css`
        :host {
            display: block;
            width: 100%;
            color: var(--flex-table-text, rgb(71 85 105));
        }

        .table-shell {
            position: relative;
            overflow: visible;
            border: 1px solid var(--flex-table-border, rgb(226 232 240));
            border-radius: 0.875rem;
            background: var(--flex-table-bg, rgb(255 255 255));
            color: var(--flex-table-text, rgb(71 85 105));
        }

        .table-scroll {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 42rem;
            border-collapse: collapse;
            background: var(--flex-table-bg, rgb(255 255 255));
            color: var(--flex-table-text, rgb(71 85 105));
            font-size: 0.875rem;
        }

        thead {
            background: var(--flex-table-head-bg, rgb(248 250 252));
        }

        th {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--flex-table-border, rgb(226 232 240));
            background: var(--flex-table-head-bg, rgb(248 250 252));
            color: var(--flex-table-head-text, rgb(100 116 139));
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.035em;
            text-align: left;
            text-transform: uppercase;
            white-space: nowrap;
        }

        td {
            padding: 0.875rem 1rem;
            border-bottom: 1px solid var(--flex-table-row-border, rgb(241 245 249));
            background: var(--flex-table-bg, rgb(255 255 255));
            color: var(--flex-table-text, rgb(71 85 105));
            vertical-align: middle;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        :host([hoverable]) tbody tr:hover td {
            background: var(--flex-table-row-hover, rgb(248 250 252));
        }

        :host([striped]) tbody tr:nth-child(even) td {
            background: var(--flex-table-row-striped, rgb(248 250 252 / 0.6));
        }

        .cell-primary {
            color: var(--flex-table-text-strong, rgb(15 23 42));
            font-weight: 500;
        }

        .selection-cell {
            width: 2.75rem;
            text-align: center;
        }

        .row-actions-cell {
            width: 7rem;
            text-align: right;
        }

        .align-center {
            text-align: center;
        }

        .align-right {
            text-align: right;
        }

        .sort-button {
            display: inline-flex;
            width: 100%;
            align-items: center;
            gap: 0.45rem;
            border: 0;
            padding: 0;
            background: transparent;
            color: inherit;
            font: inherit;
            letter-spacing: inherit;
            text-transform: inherit;
            cursor: pointer;
        }

        .sort-button:hover {
            color: var(--flex-table-head-text-hover, rgb(30 41 59));
        }

        .sort-button:focus-visible {
            outline: 2px solid var(--flex-table-focus, rgb(99 102 241));
            outline-offset: 4px;
            border-radius: 0.25rem;
        }

        .sort-button.align-center {
            justify-content: center;
        }

        .sort-button.align-right {
            justify-content: flex-end;
        }

        .footer {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.85rem 1rem;
            border-top: 1px solid var(--flex-table-border, rgb(226 232 240));
            background: var(--flex-table-bg, rgb(255 255 255));
            color: var(--flex-table-text, rgb(71 85 105));
        }

        .footer-info {
            color: var(--flex-table-text-muted, rgb(100 116 139));
            font-size: 0.8125rem;
        }

        .footer-controls {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1rem;
        }
    `;

    constructor() {
        super();

        this.rows = [];
        this.columns = [];
        this.loading = false;
        this.rowKey = "id";
        this.emptyTitle = "Няма записи";
        this.emptyDescription = "Все още няма налични данни за показване.";
        this.striped = false;
        this.hoverable = true;
        this.sortBy = "";
        this.sortDirection = "";
        this.page = 1;
        this.pageSize = 25;
        this.total = 0;
        this.lastPage = 1;
        this.paginated = false;
        this.searchable = false;
        this.searchPlaceholder = "Търсене...";
        this.searchDebounce = 400;
        this.filterDefinitions = [];
        this.selectable = false;
        this.bulkActions = [];
        this.rowActions = [];
        this.selection = new TableSelection({
            rowKey: this.rowKey,
        });

        this.pageSizeOptions = [25, 50, 100];
        this.search = "";
        this.filters = {};

        this.state = new TableState();
        this.dataSource = null;
        this.#requestId = 0;
    }

    #requestId;

    setData(rows) {
        this.rows = Array.isArray(rows) ? rows : [];

        this.state = this.state.clone().setRows(this.rows);

        this.requestUpdate();

        return this;
    }

    setColumns(columns) {
        this.columns = Array.isArray(columns)
            ? columns.map((column) =>
                  column instanceof TableColumn ? column : new TableColumn(column),
              )
            : [];

        this.requestUpdate();

        return this;
    }

    setDataSource(dataSource) {
        if (!dataSource || typeof dataSource.fetch !== "function") {
            throw new TypeError("Data source must implement fetch(query).");
        }

        this.dataSource = dataSource;

        return this;
    }

    useLocalData(rows = this.rows) {
        this.setDataSource(new LocalTableDataSource(rows));

        this.paginated = true;

        return this;
    }

    setSort(column, direction) {
        this.state = this.state.clone().setSort(column, direction);

        this.sortBy = this.state.sortBy ?? "";
        this.sortDirection = this.state.sortDirection ?? "";

        this.requestUpdate();

        return this;
    }

    configure({
        rows = this.rows,
        columns = this.columns,
        rowKey = this.rowKey,
        loading = this.loading,
        striped = this.striped,
        hoverable = this.hoverable,
        emptyTitle = this.emptyTitle,
        emptyDescription = this.emptyDescription,
        sortBy = this.sortBy,
        sortDirection = this.sortDirection,
        paginated = this.paginated,
        page = this.page,
        pageSize = this.pageSize,
        pageSizeOptions = this.pageSizeOptions,
        dataSource = this.dataSource,
        searchable = this.searchable,
        searchPlaceholder = this.searchPlaceholder,
        searchDebounce = this.searchDebounce,
        filterDefinitions = this.filterDefinitions,
        selectable = this.selectable,
        bulkActions = this.bulkActions,
        rowActions = this.rowActions,
    } = {}) {
        this.rowKey = rowKey;
        this.loading = Boolean(loading);
        this.striped = Boolean(striped);
        this.hoverable = Boolean(hoverable);
        this.emptyTitle = emptyTitle;
        this.emptyDescription = emptyDescription;
        this.paginated = Boolean(paginated);
        this.page = Number(page) || 1;
        this.pageSize = Number(pageSize) || 25;
        this.searchable = Boolean(searchable);
        this.searchPlaceholder = searchPlaceholder;
        this.searchDebounce = Number(searchDebounce) || 400;
        this.selectable = Boolean(selectable);
        this.rowActions = rowActions;

        this.bulkActions =
            typeof bulkActions === "function" || Array.isArray(bulkActions) ? bulkActions : [];

        this.selection = new TableSelection({
            rowKey: this.rowKey,
        });

        this.filterDefinitions = Array.isArray(filterDefinitions) ? filterDefinitions : [];

        this.pageSizeOptions = Array.isArray(pageSizeOptions) ? pageSizeOptions : [25, 50, 100];

        this.setColumns(columns);
        this.setData(rows);

        if (dataSource) {
            this.setDataSource(dataSource);
        }

        if (sortBy && sortDirection) {
            this.setSort(sortBy, sortDirection);
        }

        return this;
    }

    async load() {
        if (!this.dataSource) {
            return this;
        }

        const requestId = ++this.#requestId;

        this.loading = true;
        this.requestUpdate();

        this.emit("flex-table-load-start", {
            query: this.#query(),
        });

        try {
            const result = await this.dataSource.fetch(this.#query());

            if (requestId !== this.#requestId) {
                return this;
            }

            this.setData(result.data ?? []);

            const pagination = result.pagination ?? {};

            this.page = Number(pagination.page ?? this.page);
            this.pageSize = Number(pagination.per_page ?? this.pageSize);
            this.total = Number(pagination.total ?? this.rows.length);
            this.lastPage = Math.max(1, Number(pagination.last_page ?? 1));

            this.clearSelection();

            this.emit("flex-table-load-success", {
                rows: this.rows,
                pagination: {
                    page: this.page,
                    pageSize: this.pageSize,
                    total: this.total,
                    lastPage: this.lastPage,
                },
            });
        } catch (error) {
            if (requestId !== this.#requestId) {
                return this;
            }

            this.state = this.state.clone().setError(error);

            this.emit("flex-table-load-error", {
                error,
            });
        } finally {
            if (requestId === this.#requestId) {
                this.loading = false;
                this.requestUpdate();
            }
        }

        return this;
    }

    render() {
        const visibleColumns = this.#visibleColumns();

        return html`
            <div class="table-shell">
                ${this.#hasToolbar() ? this.#renderToolbar() : nothing}
                ${
                    this.selectable && this.selection.count() > 0
                        ? this.#renderBulkActions()
                        : nothing
                }

                <div class="table-scroll">
                    <table>
                        ${this.#renderHead(visibleColumns)} ${this.#renderBody(visibleColumns)}
                    </table>
                </div>

                ${this.paginated ? this.#renderFooter() : nothing}
            </div>
        `;
    }

    #hasRowActions() {
        if (typeof this.rowActions === "function") {
            return true;
        }

        return Array.isArray(this.rowActions) && this.rowActions.length > 0;
    }

    #columnCount(columns) {
        return Math.max(
            1,
            columns.length + (this.selectable ? 1 : 0) + (this.#hasRowActions() ? 1 : 0),
        );
    }

    #handleRowAction(event) {
        event.stopPropagation();

        this.emit("flex-table-row-action", {
            action: event.detail.action,
            definition: event.detail.definition,
            row: event.detail.row,
        });
    }

    #renderBulkActions() {
        const actions =
            typeof this.bulkActions === "function" ? this.bulkActions(this) : this.bulkActions;

        return html`
            <flex-table-bulk-actions
                .count=${this.selection.count()}
                .actions=${Array.isArray(actions) ? actions : []}
                ?disabled=${this.loading}
                @flex-table-bulk-action=${this.#handleBulkAction}
            ></flex-table-bulk-actions>
        `;
    }

    #handleSelectRow(event, key) {
        if (event.detail.checked) {
            this.selection.select(key);
        } else {
            this.selection.deselect(key);
        }

        this.emit("flex-table-selection-change", {
            selected: this.selection.values(),
            count: this.selection.count(),
        });

        this.requestUpdate();
    }

    #handleSelectPage(event) {
        if (event.detail.checked) {
            this.selection.selectRows(this.rows);
        } else {
            this.selection.deselectRows(this.rows);
        }

        this.emit("flex-table-selection-change", {
            selected: this.selection.values(),
            count: this.selection.count(),
        });

        this.requestUpdate();
    }

    #handleBulkAction(event) {
        this.emit("flex-table-bulk-action", {
            action: event.detail.action,
            definition: event.detail.definition,
            ids: this.selection.values(),
        });
    }

    clearSelection() {
        this.selection.clear();
        this.requestUpdate();

        this.emit("flex-table-selection-change", {
            selected: [],
            count: 0,
        });

        return this;
    }

    #hasToolbar() {
        return (
            this.searchable ||
            (Array.isArray(this.filterDefinitions) && this.filterDefinitions.length > 0)
        );
    }

    #renderToolbar() {
        return html`
            <flex-table-toolbar>
                ${
                    this.searchable
                        ? html`
                              <flex-table-search
                                  .value=${this.search}
                                  placeholder=${this.searchPlaceholder}
                                  .debounce=${this.searchDebounce}
                                  ?disabled=${this.loading}
                                  @flex-table-search-change=${this.#handleSearchChange}
                              ></flex-table-search>
                          `
                        : nothing
                }
                ${
                    Array.isArray(this.filterDefinitions) && this.filterDefinitions.length > 0
                        ? html`
                              <flex-table-filters
                                  .filters=${this.filterDefinitions}
                                  .values=${this.filters}
                                  ?disabled=${this.loading}
                                  @flex-table-filter-change=${this.#handleFilterChange}
                              ></flex-table-filters>
                          `
                        : nothing
                }
            </flex-table-toolbar>
        `;
    }

    #renderHead(columns) {
        return html`
            <thead>
                <tr>
                    ${
                        this.selectable
                            ? html`
                                  <th class="selection-cell">
                                      <flex-table-checkbox
                                          .checked=${this.selection.allSelected(this.rows)}
                                          .indeterminate=${this.selection.someSelected(this.rows)}
                                          ?disabled=${this.loading || this.rows.length === 0}
                                          label="Избери всички записи на страницата"
                                          @flex-table-checkbox-change=${this.#handleSelectPage}
                                      ></flex-table-checkbox>
                                  </th>
                              `
                            : nothing
                    }
                    ${columns.map(
                        (column) => html`
                            <th
                                class=${this.#alignmentClass(column.align)}
                                style=${column.width ? `width:${column.width}` : ""}
                            >
                                ${
                                    column.sortable
                                        ? html`
                                              <button
                                                  type="button"
                                                  class=${`sort-button ${this.#alignmentClass(column.align)}`}
                                                  aria-label=${this.#sortAriaLabel(column)}
                                                  @click=${() => this.#toggleSort(column)}
                                              >
                                                  <span>${column.label}</span>

                                                  <flex-table-sort-indicator
                                                      direction=${this.sortBy === column.key ? this.sortDirection : ""}
                                                  ></flex-table-sort-indicator>
                                              </button>
                                          `
                                        : column.label
                                }
                            </th>
                        `,
                    )}
                    ${
                        this.#hasRowActions()
                            ? html`<th class="row-actions-cell">Действия</th>`
                            : nothing
                    }
                </tr>
            </thead>
        `;
    }

    #renderBody(columns) {
        if (this.loading) {
            return html`
                <flex-table-loading
                    rows="6"
                    columns=${this.#columnCount(columns)}
                ></flex-table-loading>
            `;
        }

        if (!Array.isArray(this.rows) || this.rows.length === 0) {
            return html`
                <flex-table-empty
                    colspan=${this.#columnCount(columns)}
                    title=${this.emptyTitle}
                    description=${this.emptyDescription}
                ></flex-table-empty>
            `;
        }

        return html`
            <tbody>
                ${this.rows.map((row) => this.#renderRow(row, columns))}
            </tbody>
        `;
    }

    #renderFooter() {
        const from = this.total === 0 ? 0 : (this.page - 1) * this.pageSize + 1;

        const to = Math.min(this.page * this.pageSize, this.total);

        return html`
            <div class="footer">
                <div class="footer-info">Показани ${from}–${to} от ${this.total}</div>

                <div class="footer-controls">
                    <flex-table-page-size
                        .value=${this.pageSize}
                        .options=${this.pageSizeOptions}
                        ?disabled=${this.loading}
                        @flex-table-page-size-change=${this.#handlePageSizeChange}
                    ></flex-table-page-size>

                    <flex-table-pagination
                        .page=${this.page}
                        .lastPage=${this.lastPage}
                        ?disabled=${this.loading}
                        @flex-table-page-change=${this.#handlePageChange}
                    ></flex-table-pagination>
                </div>
            </div>
        `;
    }

    #renderRow(row, columns) {
        const key = row?.[this.rowKey];

        return html`
            <tr data-row-key=${key ?? ""}>
                ${
                    this.selectable
                        ? html`
                              <td class="selection-cell">
                                  <flex-table-checkbox
                                      .checked=${this.selection.has(key)}
                                      ?disabled=${this.loading}
                                      label="Избери ред"
                                      @flex-table-checkbox-change=${(event) => this.#handleSelectRow(event, key)}
                                  ></flex-table-checkbox>
                              </td>
                          `
                        : nothing
                }
                ${columns.map(
                    (column, index) => html`
                        <td
                            class=${`${this.#alignmentClass(column.align)} ${index === 0 ? "cell-primary" : ""}`}
                        >
                            ${this.#renderCell(row, column)}
                        </td>
                    `,
                )}
                ${
                    this.#hasRowActions()
                        ? html`
                              <td class="row-actions-cell">
                                  <flex-table-row-actions
                                      .row=${row}
                                      .actions=${this.rowActions}
                                      ?disabled=${this.loading}
                                      @flex-table-row-action=${this.#handleRowAction}
                                  ></flex-table-row-actions>
                              </td>
                          `
                        : nothing
                }
            </tr>
        `;
    }

    #renderCell(row, column) {
        const value = row?.[column.key];

        if (column.render) {
            return column.render(value, row, column, this);
        }

        if (value === null || value === undefined || value === "") {
            return "—";
        }

        return String(value);
    }

    #visibleColumns() {
        return Array.isArray(this.columns)
            ? this.columns
                  .map((column) =>
                      column instanceof TableColumn ? column : new TableColumn(column),
                  )
                  .filter((column) => !column.hidden)
            : [];
    }

    #query() {
        return new TableQuery({
            page: this.page,
            pageSize: this.pageSize,
            search: this.search,
            sortBy: this.sortBy || null,
            sortDirection: this.sortDirection || null,
            filters: this.filters,
        });
    }

    async #toggleSort(column) {
        const nextDirection = this.state.nextSortDirection(column.key);

        this.setSort(nextDirection === null ? null : column.key, nextDirection);

        this.page = 1;

        this.emit("flex-table-sort-change", {
            column: this.sortBy || null,
            direction: this.sortDirection || null,
        });

        if (this.dataSource) {
            await this.load();
        }
    }

    #handlePageChange = async (event) => {
        this.page = event.detail.page;

        this.emit("flex-table-pagination-change", {
            page: this.page,
            pageSize: this.pageSize,
        });

        await this.load();
    };

    #handlePageSizeChange = async (event) => {
        this.pageSize = event.detail.pageSize;
        this.page = 1;

        this.emit("flex-table-pagination-change", {
            page: this.page,
            pageSize: this.pageSize,
        });

        await this.load();
    };

    #handleSearchChange = async (event) => {
        this.search = event.detail.value ?? "";
        this.page = 1;

        this.emit("flex-table-search-change", {
            value: this.search,
        });

        if (this.dataSource) {
            await this.load();
        }
    };

    #handleFilterChange = async (event) => {
        this.filters = {
            ...this.filters,
            ...(event.detail.values ?? {}),
        };

        this.page = 1;

        this.emit("flex-table-filter-change", {
            key: event.detail.key,
            value: event.detail.value,
            values: {
                ...this.filters,
            },
        });

        if (this.dataSource) {
            await this.load();
        }
    };

    #sortAriaLabel(column) {
        if (this.sortBy !== column.key) {
            return `Сортирай по ${column.label} във възходящ ред`;
        }

        if (this.sortDirection === "asc") {
            return `Сортирай по ${column.label} в низходящ ред`;
        }

        if (this.sortDirection === "desc") {
            return `Премахни сортирането по ${column.label}`;
        }

        return `Сортирай по ${column.label}`;
    }

    #alignmentClass(align) {
        return align === "center" ? "align-center" : align === "right" ? "align-right" : "";
    }
}

FlexDataTable.register("flex-data-table");
