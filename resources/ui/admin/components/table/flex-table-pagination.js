import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";
import { fontAwesomeStyles } from "@admin-ui/styles/font-awesome.styles.js";

export class FlexTablePagination extends FlexElement {
    static properties = {
        page: {
            type: Number,
        },

        lastPage: {
            type: Number,
            attribute: "last-page",
        },

        disabled: {
            type: Boolean,
            reflect: true,
        },
    };

    static styles = [
        fontAwesomeStyles,
        css`
            :host {
                display: block;
            }

            .pagination {
                display: flex;
                align-items: center;
                gap: 0.35rem;
            }

            button {
                display: inline-flex;
                min-width: 2.25rem;
                height: 2.25rem;
                align-items: center;
                justify-content: center;
                border: 1px solid var(--flex-table-control-border, rgb(203 213 225));
                border-radius: 0.55rem;
                padding: 0 0.65rem;
                background: var(--flex-table-control-bg, rgb(255 255 255));
                color: var(--flex-table-control-text, rgb(51 65 85));
                cursor: pointer;
                font-size: 0.8125rem;
                font-weight: 500;
            }

            button:hover:not(:disabled):not([aria-current="page"]) {
                background: var(--flex-table-control-hover, rgb(248 250 252));
            }

            button[aria-current="page"] {
                border-color: var(--flex-table-pagination-active-bg, rgb(79 70 229));
                background: var(--flex-table-pagination-active-bg, rgb(79 70 229));
                color: var(--flex-table-pagination-active-text, rgb(255 255 255));
            }

            button:disabled {
                cursor: not-allowed;
                color: var(--flex-table-control-disabled, rgb(148 163 184));
                opacity: 0.55;
            }

            button:focus-visible {
                outline: 2px solid var(--flex-table-focus, rgb(99 102 241));
                outline-offset: 2px;
            }

            .ellipsis {
                display: inline-flex;
                width: 1.75rem;
                align-items: center;
                justify-content: center;
                color: var(--flex-table-text-muted, rgb(100 116 139));
            }
        `,
    ];

    constructor() {
        super();

        this.page = 1;
        this.lastPage = 1;
        this.disabled = false;
    }

    render() {
        const page = Math.max(1, Number(this.page) || 1);

        const lastPage = Math.max(1, Number(this.lastPage) || 1);

        return html`
            <nav
                class="pagination"
                aria-label="Страници на таблицата"
            >
                <button
                    type="button"
                    ?disabled=${this.disabled || page <= 1}
                    aria-label="Предишна страница"
                    @click=${() => this.#select(page - 1)}
                >
                    <i
                        class="fa-solid fa-chevron-left"
                        aria-hidden="true"
                    ></i>
                </button>

                ${this.#pages(page, lastPage).map((item) =>
                    item === "..."
                        ? html`<span class="ellipsis">…</span>`
                        : html`
                              <button
                                  type="button"
                                  aria-current=${item === page ? "page" : "false"}
                                  ?disabled=${this.disabled}
                                  @click=${() => this.#select(item)}
                              >
                                  ${item}
                              </button>
                          `,
                )}

                <button
                    type="button"
                    ?disabled=${this.disabled || page >= lastPage}
                    aria-label="Следваща страница"
                    @click=${() => this.#select(page + 1)}
                >
                    <i
                        class="fa-solid fa-chevron-right"
                        aria-hidden="true"
                    ></i>
                </button>
            </nav>
        `;
    }

    #select(page) {
        if (this.disabled || page < 1 || page > this.lastPage || page === this.page) {
            return;
        }

        this.emit("flex-table-page-change", {
            page,
        });
    }

    #pages(page, lastPage) {
        if (lastPage <= 7) {
            return Array.from({ length: lastPage }, (_, index) => index + 1);
        }

        const pages = [1];

        if (page > 4) {
            pages.push("...");
        }

        const start = Math.max(2, page - 1);

        const end = Math.min(lastPage - 1, page + 1);

        for (let current = start; current <= end; current++) {
            pages.push(current);
        }

        if (page < lastPage - 3) {
            pages.push("...");
        }

        pages.push(lastPage);

        return pages;
    }
}

FlexTablePagination.register("flex-table-pagination");
