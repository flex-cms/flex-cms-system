import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";

export class FlexRadio extends FlexElement {
    static properties = {
        value: {
            type: String,
        },

        label: {
            type: String,
        },

        helper: {
            type: String,
        },

        checked: {
            type: Boolean,
            reflect: true,
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

        .option {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;

            padding: 0.5rem;

            border-radius: var(--flex-radius-md);

            cursor: pointer;

            transition: background var(--flex-duration-fast) var(--flex-easing);
        }

        .option:hover {
            background: var(--flex-color-surface-muted);
        }

        .radio {
            display: inline-flex;

            width: 1.25rem;
            height: 1.25rem;

            flex: 0 0 1.25rem;

            align-items: center;
            justify-content: center;

            margin-top: 0.1rem;

            border: 1px solid var(--flex-color-border);

            border-radius: 50%;

            background: var(--flex-color-surface);

            transition:
                border-color var(--flex-duration-fast) var(--flex-easing),
                box-shadow var(--flex-duration-fast) var(--flex-easing);
        }

        :host([checked]) .radio {
            border-color: var(--flex-color-primary-600);
        }

        .dot {
            width: 0.625rem;
            height: 0.625rem;

            border-radius: 50%;

            background: var(--flex-color-primary-600);

            opacity: 0;

            transform: scale(0.6);

            transition:
                opacity var(--flex-duration-fast) var(--flex-easing),
                transform var(--flex-duration-fast) var(--flex-easing);
        }

        :host([checked]) .dot {
            opacity: 1;
            transform: scale(1);
        }

        .option:focus-visible {
            outline: none;

            box-shadow: 0 0 0 3px color-mix(in srgb, var(--flex-color-primary-500) 15%, transparent);
        }

        .content {
            display: flex;
            min-width: 0;
            flex-direction: column;
            gap: 0.15rem;
        }

        .label {
            color: var(--flex-color-text);

            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1.25rem;
        }

        .helper {
            color: var(--flex-color-text-muted);

            font-size: 0.75rem;
            line-height: 1rem;
        }

        :host([disabled]) {
            opacity: 0.5;
        }

        :host([disabled]) .option {
            cursor: not-allowed;
            pointer-events: none;
        }
    `;

    constructor() {
        super();

        this.value = "";
        this.label = "";
        this.helper = "";

        this.checked = false;
        this.disabled = false;
    }

    render() {
        return html`
            <div
                class="option"
                role="radio"
                tabindex=${this.disabled ? "-1" : "0"}
                aria-checked=${this.checked ? "true" : "false"}
                aria-disabled=${this.disabled ? "true" : "false"}
                @click=${this.#select}
                @keydown=${this.#handleKeydown}
            >
                <span
                    class="radio"
                    aria-hidden="true"
                >
                    <span class="dot"></span>
                </span>

                <div class="content">
                    <span class="label"> ${this.label} </span>

                    ${this.helper ? html` <span class="helper"> ${this.helper} </span> ` : ""}
                </div>
            </div>
        `;
    }

    #select = () => {
        if (this.disabled) {
            return;
        }

        this.dispatchEvent(
            new CustomEvent("flex-radio-select", {
                bubbles: true,
                composed: true,

                detail: {
                    value: this.value,
                    radio: this,
                },
            }),
        );
    };

    #handleKeydown = (event) => {
        if (event.key !== " " && event.key !== "Enter") {
            return;
        }

        event.preventDefault();

        this.#select();
    };

    focus() {
        this.renderRoot?.querySelector(".option")?.focus();
    }
}

FlexRadio.register("flex-radio");
