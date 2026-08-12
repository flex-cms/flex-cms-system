import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";

export class FlexTablePageSize extends FlexElement {
    static properties = {
        value: {
            type: Number,
        },

        options: {
            attribute: false,
        },

        disabled: {
            type: Boolean,
            reflect: true,
        },
    };

    static styles = css`
        :host {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--flex-table-text-muted, rgb(100 116 139));
            font-size: 0.8125rem;
        }

        select {
            height: 2.25rem;
            border: 1px solid var(--flex-table-control-border, rgb(203 213 225));
            border-radius: 0.55rem;
            padding: 0 2rem 0 0.7rem;
            background: var(--flex-table-control-bg, rgb(255 255 255));
            color: var(--flex-table-control-text, rgb(51 65 85));
            font: inherit;
        }

        select:focus-visible {
            outline: 2px solid var(--flex-table-focus, rgb(99 102 241));
            outline-offset: 2px;
        }

        select:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }
    `;

    constructor() {
        super();

        this.value = 25;
        this.options = [25, 50, 100];
        this.disabled = false;
    }

    render() {
        return html`
            <flex-dropdown
                name="page-size"
                label="Покажи"
                label-position="left"
                min-width="160px"
                value=${String(this.value)}
                ?disabled=${this.disabled}
                @flex-change=${this.#handleChange}
            >
                ${this.options.map((option) => html` <option value=${option}>${option}</option> `)}
            </flex-dropdown>
        `;
    }

    #handleChange(event) {
        const pageSize = Number(event.currentTarget.value);

        if (!Number.isInteger(pageSize) || pageSize <= 0) {
            return;
        }

        this.emit("flex-table-page-size-change", {
            pageSize,
        });
    }
}

FlexTablePageSize.register("flex-table-page-size");
