import { html, nothing } from "lit";
import { FlexElement } from "../base/flex-element.js";

export class FlexTable extends FlexElement {
    static properties = {
        columns: { attribute: false },
        data: { attribute: false },
        page: { type: Number },
        pageSize: { type: Number, attribute: "page-size" },
        pageSizes: { attribute: false },
        sortKey: { type: String, attribute: "sort-key" },
        sortDirection: { type: String, attribute: "sort-direction" },
        serverSide: { type: Boolean, attribute: "server-side", reflect: true },
        totalItems: { type: Number, attribute: "total-items" },
        loading: { type: Boolean, reflect: true },
        emptyText: { type: String, attribute: "empty-text" },
        keyField: { type: String, attribute: "key-field" },
        caption: { type: String },
        url: { type: String },
        autoFetch: { type: Boolean, attribute: "auto-fetch" },
        filters: { attribute: false },
    };

    constructor() {
        super();
        this.columns = [];
        this.data = [];
        this.page = 1;
        this.pageSize = 10;
        this.pageSizes = [10, 25, 50, 100];
        this.sortKey = "";
        this.sortDirection = "asc";
        this.serverSide = false;
        this.totalItems = 0;
        this.loading = false;
        this.emptyText = "Няма намерени записи.";
        this.keyField = "id";
        this.caption = "";
        this.url = "";
        this.autoFetch = true;
        this.filters = {};
        this._fetchTimeout = null;
    }

    connectedCallback() {
        super.connectedCallback();
        if (this.url) {
            this.serverSide = true;
            if (this.autoFetch) {
                queueMicrotask(() => this.fetchData());
            }
        }
    }

    willUpdate(changedProperties) {
        if (changedProperties.has("pageSize")) {
            this.pageSize = Number(this.pageSize) || 10;
            this.page = Math.min(Math.max(1, this.page), this.totalPages);
        }

        if (changedProperties.has("url") && this.url) {
            this.serverSide = true;
        }

        if (
            !this.serverSide &&
            (changedProperties.has("data") || changedProperties.has("filters"))
        ) {
            this.page = Math.min(Math.max(1, this.page), this.totalPages);
        }
    }

    /**
     * Задаване на единичен филтър с незабавно или забавено презареждане
     */
    setFilter(key, value, debounceMs = 300) {
        this.filters = { ...this.filters, [key]: value };
        this.page = 1;
        this.emitStateChange("filter");

        if (this.serverSide) {
            this.debouncedFetchData(debounceMs);
        } else {
            this.requestUpdate();
        }
    }

    /**
     * Задаване на множество филтри наведнъж
     */
    setFilters(newFilters) {
        this.filters = { ...this.filters, ...newFilters };
        this.page = 1;
        this.emitStateChange("filter");

        if (this.serverSide) {
            this.fetchData();
        } else {
            this.requestUpdate();
        }
    }

    /**
     * Изчистване на всички филтри
     */
    clearFilters() {
        this.filters = {};
        this.page = 1;
        this.emitStateChange("filter");

        if (this.serverSide) {
            this.fetchData();
        } else {
            this.requestUpdate();
        }
    }

    debouncedFetchData(delay = 300) {
        clearTimeout(this._fetchTimeout);
        this._fetchTimeout = setTimeout(() => {
            this.fetchData();
        }, delay);
    }

    async fetchData() {
        if (!this.url) return;

        const axiosClient = globalThis.axios || window.axios;

        if (!axiosClient) {
            console.error("<flex-table> Грешка: Axios не е наличен глобално.");
            return;
        }

        this.loading = true;

        this.dispatchEvent(
            new CustomEvent("flex-table-fetch-start", {
                bubbles: true,
                composed: true,
            }),
        );

        // Почистваме празни филтри (null, undefined, "") преди изпращане към PHP
        const cleanFilters = {};
        if (this.filters && typeof this.filters === "object") {
            Object.entries(this.filters).forEach(([k, v]) => {
                if (v !== "" && v !== null && v !== undefined) {
                    cleanFilters[k] = v;
                }
            });
        }

        try {
            const response = await axiosClient.get(this.url, {
                params: {
                    page: this.page,
                    page_size: Number(this.pageSize),
                    sort_key: this.sortKey || undefined,
                    sort_direction: this.sortKey
                        ? this.normalizedSortDirection
                        : undefined,
                    ...cleanFilters,
                },
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            let payload = response.data;

            if (payload && payload.success !== undefined && payload.data) {
                payload = payload.data;
            }

            if (
                payload &&
                typeof payload === "object" &&
                !Array.isArray(payload)
            ) {
                this.data = payload.data || payload.items || [];
                this.totalItems = Number(
                    payload.total ??
                        payload.totalItems ??
                        payload.total_items ??
                        this.data.length,
                );
            } else if (Array.isArray(payload)) {
                this.data = payload;
                this.totalItems = payload.length;
            }

            this.dispatchEvent(
                new CustomEvent("flex-table-fetch-success", {
                    bubbles: true,
                    composed: true,
                    detail: { data: response.data },
                }),
            );
        } catch (error) {
            console.error(
                "<flex-table> Грешка при зареждане на данните:",
                error,
            );
            this.dispatchEvent(
                new CustomEvent("flex-table-fetch-error", {
                    bubbles: true,
                    composed: true,
                    detail: { error },
                }),
            );
        } finally {
            this.loading = false;
        }
    }

    render() {
        return html`
            <div
                class="relative rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"
            >
                <!-- Слот за Филтри над таблицата -->
                <slot name="filters"></slot>

                ${this.loading && this.visibleRows.length > 0
                    ? html`
                          <div
                              class="absolute inset-0 z-10 flex items-center justify-center bg-white/60 backdrop-blur-[1px] dark:bg-gray-900/60"
                          >
                              <div
                                  class="flex items-center gap-3 rounded-lg bg-white px-4 py-2.5 shadow-md dark:bg-gray-800 border border-gray-100 dark:border-gray-700"
                              >
                                  <svg
                                      class="h-5 w-5 animate-spin text-blue-600 dark:text-blue-400"
                                      xmlns="http://www.w3.org/2000/svg"
                                      fill="none"
                                      viewBox="0 0 24 24"
                                  >
                                      <circle
                                          class="opacity-25"
                                          cx="12"
                                          cy="12"
                                          r="10"
                                          stroke="currentColor"
                                          stroke-width="4"
                                      ></circle>
                                      <path
                                          class="opacity-75"
                                          fill="currentColor"
                                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                      ></path>
                                  </svg>
                                  <span
                                      class="font-medium text-gray-700 dark:text-gray-200"
                                  >
                                      Зареждане…
                                  </span>
                              </div>
                          </div>
                      `
                    : nothing}

                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-left">
                        ${this.caption
                            ? html`<caption class="sr-only">
                                  ${this.caption}
                              </caption>`
                            : nothing}
                        <thead
                            class="bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                        >
                            <tr>
                                ${this.columns.map((column) =>
                                    this.renderHeader(column),
                                )}
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-gray-200 dark:divide-gray-800"
                        >
                            ${this.loading && this.visibleRows.length === 0
                                ? this.renderSkeletonRows()
                                : this.visibleRows.length === 0
                                  ? this.renderMessageRow(this.emptyText)
                                  : this.visibleRows.map((row, index) =>
                                        this.renderRow(row, index),
                                    )}
                        </tbody>
                    </table>
                </div>

                ${this.renderPagination()}
            </div>
        `;
    }

    renderHeader(column) {
        const sortable = column.sortable === true;
        const active = sortable && this.sortKey === column.key;
        const ariaSort = active
            ? this.normalizedSortDirection === "asc"
                ? "ascending"
                : "descending"
            : "none";

        return html`
            <th
                scope="col"
                aria-sort=${sortable ? ariaSort : nothing}
                class=${column.headerClass ?? "px-5 py-3"}
                style=${column.width ? `width: ${column.width}` : nothing}
            >
                ${sortable
                    ? html`
                          <button
                              type="button"
                              class="flex w-full items-center gap-2 text-left font-semibold hover:text-blue-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                              @click=${() => this.toggleSort(column.key)}
                          >
                              <span>${column.label ?? column.key}</span>
                              <span
                                  aria-hidden="true"
                                  class=${active
                                      ? "text-blue-600"
                                      : "text-gray-400"}
                              >
                                  ${active
                                      ? this.normalizedSortDirection === "asc"
                                          ? "▲"
                                          : "▼"
                                      : "⇅"}
                              </span>
                          </button>
                      `
                    : html`<span class="font-semibold"
                          >${column.label ?? column.key}</span
                      >`}
            </th>
        `;
    }

    renderRow(row, index) {
        const absoluteIndex = (this.page - 1) * this.pageSize + index;
        const key = row?.[this.keyField] ?? absoluteIndex;

        return html`
            <tr
                data-row-key=${String(key)}
                class="text-gray-700 transition hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800/60"
            >
                ${this.columns.map(
                    (column) => html`
                        <td class=${column.cellClass ?? "px-5 py-3.5"}>
                            ${this.renderCell(column, row, absoluteIndex)}
                        </td>
                    `,
                )}
            </tr>
        `;
    }

    renderCell(column, row, index) {
        const value = this.getValue(row, column.key);
        return typeof column.render === "function"
            ? column.render(value, row, index)
            : (value ?? "—");
    }

    renderMessageRow(message, busy = false) {
        return html`
            <tr>
                <td
                    colspan=${Math.max(1, this.columns.length)}
                    class="px-5 py-10 text-center text-gray-500 dark:text-gray-400"
                    aria-busy=${busy ? "true" : nothing}
                >
                    ${message}
                </td>
            </tr>
        `;
    }

    renderSkeletonRows() {
        const count = Math.min(this.pageSize, 5);
        const colCount = Math.max(1, this.columns.length);

        return Array.from({ length: count }).map(
            () => html`
                <tr class="animate-pulse">
                    ${Array.from({ length: colCount }).map(
                        () => html`
                            <td class="px-5 py-4">
                                <div
                                    class="h-4 rounded bg-gray-200 dark:bg-gray-700"
                                ></div>
                            </td>
                        `,
                    )}
                </tr>
            `,
        );
    }

    renderPagination() {
        return html`
            <div
                class="flex flex-col gap-3 border-t border-gray-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800"
            >
                <div
                    class="flex items-center gap-2 text-gray-600 dark:text-gray-400"
                >
                    <span>Показване</span>
                    <select
                        class="rounded-md border border-gray-300 bg-white px-2 py-1.5 dark:border-gray-700 dark:bg-gray-800"
                        aria-label="Брой записи на страница"
                        .value=${String(this.pageSize)}
                        @change=${this.handlePageSizeChange}
                    >
                        ${this.normalizedPageSizes.map(
                            (size) =>
                                html`<option value=${size}>${size}</option>`,
                        )}
                    </select>
                    <span>от ${this.totalCount}</span>
                </div>

                <nav class="flex items-center gap-1" aria-label="Страници">
                    <button
                        type="button"
                        class=${this.paginationButtonClass(this.page === 1)}
                        ?disabled=${this.page === 1}
                        @click=${() => this.setPage(this.page - 1)}
                    >
                        Назад
                    </button>

                    ${this.paginationItems.map((item) =>
                        item === "ellipsis"
                            ? html`<span
                                  class="px-2 text-gray-400"
                                  aria-hidden="true"
                                  >…</span
                              >`
                            : html`
                                  <button
                                      type="button"
                                      class=${this.paginationButtonClass(
                                          false,
                                          item === this.page,
                                      )}
                                      aria-current=${item === this.page
                                          ? "page"
                                          : nothing}
                                      @click=${() => this.setPage(item)}
                                  >
                                      ${item}
                                  </button>
                              `,
                    )}

                    <button
                        type="button"
                        class=${this.paginationButtonClass(
                            this.page === this.totalPages,
                        )}
                        ?disabled=${this.page === this.totalPages}
                        @click=${() => this.setPage(this.page + 1)}
                    >
                        Напред
                    </button>
                </nav>
            </div>
        `;
    }

    paginationButtonClass(disabled, active = false) {
        const base =
            "rounded-md border px-3 py-1.5 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500";
        if (active) return `${base} border-blue-600 bg-blue-600 text-white`;
        if (disabled)
            return `${base} cursor-not-allowed border-gray-200 text-gray-300 dark:border-gray-800 dark:text-gray-600`;
        return `${base} border-gray-300 text-gray-700 hover:border-blue-500 hover:text-blue-600 dark:border-gray-700 dark:text-gray-300`;
    }

    toggleSort(key) {
        if (this.sortKey === key) {
            this.sortDirection =
                this.normalizedSortDirection === "asc" ? "desc" : "asc";
        } else {
            this.sortKey = key;
            this.sortDirection = "asc";
        }

        this.page = 1;
        this.emitStateChange("sort");

        if (this.url) {
            this.fetchData();
        }
    }

    setPage(page) {
        const nextPage = Math.min(
            Math.max(1, Number(page) || 1),
            this.totalPages,
        );
        if (nextPage === this.page) return;
        this.page = nextPage;
        this.emitStateChange("page");

        if (this.url) {
            this.fetchData();
        }
    }

    handlePageSizeChange(event) {
        this.pageSize = Math.max(1, Number(event.target.value) || 10);
        this.page = 1;
        this.emitStateChange("page-size");

        if (this.url) {
            this.fetchData();
        }
    }

    emitStateChange(reason) {
        this.dispatchEvent(
            new CustomEvent("flex-table-change", {
                bubbles: true,
                composed: true,
                detail: {
                    reason,
                    page: this.page,
                    pageSize: this.pageSize,
                    sortKey: this.sortKey,
                    sortDirection: this.normalizedSortDirection,
                    filters: this.filters,
                },
            }),
        );
    }

    get filteredRows() {
        let rows = Array.isArray(this.data) ? [...this.data] : [];
        if (!this.filters || Object.keys(this.filters).length === 0) {
            return rows;
        }

        return rows.filter((row) => {
            return Object.entries(this.filters).every(([key, value]) => {
                if (value === "" || value === null || value === undefined)
                    return true;

                if (key === "search" || key === "q") {
                    const searchStr = String(value).toLowerCase();
                    return Object.values(row).some(
                        (val) =>
                            val != null &&
                            String(val).toLowerCase().includes(searchStr),
                    );
                }

                const cellVal = this.getValue(row, key);
                return String(cellVal ?? "")
                    .toLowerCase()
                    .includes(String(value).toLowerCase());
            });
        });
    }

    get visibleRows() {
        if (this.serverSide) return Array.isArray(this.data) ? this.data : [];
        const start = (this.page - 1) * this.pageSize;
        return this.sortedRows.slice(start, start + this.pageSize);
    }

    get sortedRows() {
        const rows = [...this.filteredRows];
        if (!this.sortKey) return rows;

        const column = this.columns.find((item) => item.key === this.sortKey);
        if (!column?.sortable) return rows;

        const direction = this.normalizedSortDirection === "asc" ? 1 : -1;
        return rows.sort((left, right) => {
            const a = this.getValue(left, this.sortKey);
            const b = this.getValue(right, this.sortKey);

            if (typeof column.compare === "function") {
                return column.compare(a, b, left, right) * direction;
            }

            return this.compareValues(a, b, column.type) * direction;
        });
    }

    compareValues(a, b, type = "string") {
        if (a == null && b == null) return 0;
        if (a == null) return 1;
        if (b == null) return -1;

        if (type === "number") return Number(a) - Number(b);
        if (type === "date")
            return new Date(a).getTime() - new Date(b).getTime();

        return String(a).localeCompare(String(b), "bg", {
            numeric: true,
            sensitivity: "base",
        });
    }

    getValue(row, key) {
        return String(key)
            .split(".")
            .reduce((value, part) => value?.[part], row);
    }

    get totalCount() {
        return this.serverSide
            ? Math.max(0, Number(this.totalItems) || 0)
            : this.filteredRows.length;
    }

    get totalPages() {
        return Math.max(
            1,
            Math.ceil(this.totalCount / Math.max(1, this.pageSize)),
        );
    }

    get normalizedSortDirection() {
        return this.sortDirection === "desc" ? "desc" : "asc";
    }

    get normalizedPageSizes() {
        const sizes = Array.isArray(this.pageSizes) ? this.pageSizes : [];
        return [...new Set([...sizes, this.pageSize])]
            .map(Number)
            .filter((size) => Number.isInteger(size) && size > 0)
            .sort((a, b) => a - b);
    }

    get paginationItems() {
        const total = this.totalPages;
        if (total <= 7)
            return Array.from({ length: total }, (_, index) => index + 1);

        const pages = new Set([
            1,
            total,
            this.page - 1,
            this.page,
            this.page + 1,
        ]);
        const validPages = [...pages]
            .filter((page) => page >= 1 && page <= total)
            .sort((a, b) => a - b);
        const items = [];

        validPages.forEach((page, index) => {
            if (index > 0 && page - validPages[index - 1] > 1)
                items.push("ellipsis");
            items.push(page);
        });

        return items;
    }
}

if (!customElements.get("flex-table")) {
    customElements.define("flex-table", FlexTable);
}

export class FlexTableActions extends FlexElement {
    static properties = {
        actions: { attribute: false },
        row: { attribute: false },
        open: { type: Boolean, reflect: true },
    };

    constructor() {
        super();
        this.actions = [];
        this.row = {};
        this.open = false;

        this.handleOutsideClick = this.handleOutsideClick.bind(this);
    }

    connectedCallback() {
        super.connectedCallback();
        window.addEventListener("click", this.handleOutsideClick);
    }

    disconnectedCallback() {
        super.disconnectedCallback();
        window.removeEventListener("click", this.handleOutsideClick);
    }

    handleOutsideClick(event) {
        if (this.open && !event.composedPath().includes(this)) {
            this.open = false;
        }
    }

    toggleDropdown(event) {
        event.stopPropagation();
        this.open = !this.open;
    }

    handleActionClick(action, event) {
        event.stopPropagation();
        this.open = false;

        if (typeof action.handler === "function") {
            action.handler(this.row);
        }

        this.dispatchEvent(
            new CustomEvent("action-click", {
                bubbles: true,
                composed: true,
                detail: {
                    action: action.key || action.label,
                    row: this.row,
                },
            }),
        );
    }

    render() {
        if (!Array.isArray(this.actions) || this.actions.length === 0) {
            return nothing;
        }

        return html`
            <div class="inline-block text-left">
                <button
                    type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                    aria-expanded=${this.open}
                    aria-haspopup="true"
                    @click=${this.toggleDropdown}
                >
                    <span class="sr-only">Действия</span>
                    <svg
                        class="h-5 w-5"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"
                        />
                    </svg>
                </button>

                ${this.open
                    ? html`
                          <div
                              class="absolute right-0 z-1000 mt-1 w-48 origin-top-right rounded-md border border-gray-200 bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:border-gray-700 dark:bg-gray-800"
                              role="menu"
                          >
                              ${this.actions.map((action) => {
                                  if (action.divider) {
                                      return html`<div
                                          class="my-1 border-t border-gray-100 dark:border-gray-700"
                                      ></div>`;
                                  }

                                  const isDanger = action.danger === true;
                                  const textClass = isDanger
                                      ? "text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/30"
                                      : "text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700/50";

                                  return html`
                                      <button
                                          type="button"
                                          class="flex w-full items-center gap-2 px-4 py-2 text-left transition ${textClass}"
                                          role="menuitem"
                                          @click=${(e) =>
                                              this.handleActionClick(action, e)}
                                      >
                                          ${action.icon
                                              ? html`<i
                                                    class="${action.icon} w-4 text-center"
                                                ></i>`
                                              : nothing}
                                          <span>${action.label}</span>
                                      </button>
                                  `;
                              })}
                          </div>
                      `
                    : nothing}
            </div>
        `;
    }
}

if (!customElements.get("flex-table-actions")) {
    customElements.define("flex-table-actions", FlexTableActions);
}
