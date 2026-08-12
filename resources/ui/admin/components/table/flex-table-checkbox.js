import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";

export class FlexTableCheckbox extends FlexElement {
    static properties = {
        checked: {
            type: Boolean,
            reflect: true,
        },

        indeterminate: {
            type: Boolean,
            reflect: true,
        },

        disabled: {
            type: Boolean,
            reflect: true,
        },

        label: {
            type: String,
        },
    };

    static styles = css`
        :host {
            display: inline-flex;
        }

        input {
            width: 1rem;
            height: 1rem;
            margin: 0;
            accent-color: var(--flex-table-focus, rgb(99 102 241));
            cursor: pointer;
        }

        input:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }
    `;

    constructor() {
        super();

        this.checked = false;
        this.indeterminate = false;
        this.disabled = false;
        this.label = "Избери";
    }

    updated() {
        const input = this.renderRoot.querySelector("input");

        if (input) {
            input.indeterminate = Boolean(this.indeterminate);
        }
    }

    render() {
        return html`
            <input
                type="checkbox"
                .checked=${this.checked}
                ?disabled=${this.disabled}
                aria-label=${this.label}
                @change=${this.#handleChange}
            >
        `;
    }

    #handleChange(event) {
        this.checked = event.currentTarget.checked;
        this.indeterminate = false;

        this.emit("flex-table-checkbox-change", {
            checked: this.checked,
        });
    }
}

FlexTableCheckbox.register("flex-table-checkbox");
