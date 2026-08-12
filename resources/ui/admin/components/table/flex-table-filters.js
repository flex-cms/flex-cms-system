import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";

export class FlexTableFilters extends FlexElement {
    static properties = {
        filters: {
            attribute: false,
        },

        values: {
            attribute: false,
        },

        disabled: {
            type: Boolean,
            reflect: true,
        },
    };

    static styles = css`
        :host {
            display: block;
        }

        .filters {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.6rem;
        }

        label {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            color: var(--flex-table-text-muted, rgb(100 116 139));
            font-size: 0.8125rem;
        }

        select {
            height: 2.5rem;
            min-width: 9rem;
            border: 1px solid var(--flex-table-control-border, rgb(203 213 225));
            border-radius: 0.65rem;
            padding: 0 2rem 0 0.75rem;
            background: var(--flex-table-control-bg, rgb(255 255 255));
            color: var(--flex-table-control-text, rgb(51 65 85));
            font: inherit;
            font-size: 0.875rem;
        }

        select:focus {
            outline: 2px solid var(--flex-table-focus, rgb(99 102 241));
            outline-offset: 1px;
        }

        select:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }
    `;

    constructor() {
        super();

        this.filters = [];
        this.values = {};
        this.disabled = false;
    }

    render() {
        const filters = Array.isArray(this.filters) ? this.filters : [];

        return html`
            <div class="filters">${filters.map((filter) => this.#renderFilter(filter))}</div>
        `;
    }

    #renderFilter(filter) {
        if (filter?.type !== "select") {
            return "";
        }

        const options = Array.isArray(filter.options) ? filter.options : [];

        const value = this.values?.[filter.key] ?? "";

        return html`
            <flex-dropdown
                name=${filter.key}
                label=${filter.label ?? ""}
                label-position="left"
                min-width="250px"
                value=${String(value)}
                placeholder=${filter.placeholder ?? "Изберете"}
                ?disabled=${this.disabled}
                @flex-change=${this.#handleChange}
            >
                ${options.map(
                    (option) => html`
                        <option
                            value=${option.value}
                            ?disabled=${option.disabled === true}
                            ?selected=${String(option.value) === String(value)}
                        >
                            ${option.label}
                        </option>
                    `,
                )}
            </flex-dropdown>
        `;
    }

    #handleChange(event) {
        event.stopPropagation();

        const key = event.detail?.name;
        const value = event.detail?.value ?? "";

        if (!key) {
            return;
        }

        this.values = {
            ...this.values,
            [key]: value,
        };

        this.emit("flex-table-filter-change", {
            key,
            value,
            values: {
                ...this.values,
            },
        });
    }
}

FlexTableFilters.register("flex-table-filters");
