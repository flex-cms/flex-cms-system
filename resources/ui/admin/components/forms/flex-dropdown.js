import { css, html, nothing } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";
import { fontAwesomeStyles } from "@admin-ui/styles/font-awesome.styles.js";

export class FlexDropdown extends FlexElement {
    static formAssociated = true;

    static properties = {
        name: { type: String },
        label: { type: String },
        value: { type: String },
        placeholder: { type: String },
        helper: { type: String },
        error: { type: String },

        open: {
            type: Boolean,
            reflect: true,
        },

        disabled: {
            type: Boolean,
            reflect: true,
        },

        required: {
            type: Boolean,
            reflect: true,
        },

        fullWidth: {
            type: Boolean,
            attribute: "full-width",
            reflect: true,
        },

        options: {
            state: true,
        },
    };

    static styles = [
        fontAwesomeStyles,
        css`
            :host {
                position: relative;
                display: inline-block;
                width: auto;
            }

            :host([full-width]) {
                display: block;
                width: 100%;
            }

            .field {
                position: relative;
                display: flex;
                width: 100%;
                flex-direction: column;
                gap: 0.4rem;
            }

            .label {
                color: var(--flex-color-text);
                font-size: 0.8125rem;
                font-weight: 600;
                line-height: 1.25rem;
            }

            .required {
                margin-left: 0.125rem;
                color: #dc2626;
            }

            .control {
                position: relative;
            }

            .trigger {
                display: flex;
                width: 100%;
                min-height: 2.625rem;
                align-items: center;
                gap: var(--flex-space-2);

                padding: 0.5rem 0.75rem;

                border: 1px solid var(--flex-color-border);
                border-radius: var(--flex-radius-md);

                background: var(--flex-color-surface);
                color: var(--flex-color-text);

                font: inherit;
                font-size: 0.875rem;
                font-weight: 500;

                text-align: left;
                cursor: pointer;

                transition:
                    border-color var(--flex-duration-fast) var(--flex-easing),
                    background var(--flex-duration-fast) var(--flex-easing),
                    box-shadow var(--flex-duration-fast) var(--flex-easing);
            }

            .trigger:hover:not(:disabled) {
                border-color: var(--flex-color-text-muted);
            }

            :host([open]) .trigger {
                border-color: var(--flex-color-primary-500);

                box-shadow: 0 0 0 3px
                    color-mix(in srgb, var(--flex-color-primary-500) 15%, transparent);
            }

            .trigger:focus-visible {
                outline: none;

                border-color: var(--flex-color-primary-500);

                box-shadow: 0 0 0 3px
                    color-mix(in srgb, var(--flex-color-primary-500) 15%, transparent);
            }

            .trigger:disabled {
                opacity: 0.55;
                cursor: not-allowed;
            }

            .selected {
                min-width: 0;
                flex: 1;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .placeholder {
                color: var(--flex-color-text-muted);
            }

            .chevron {
                display: inline-flex;
                width: 1rem;
                flex: 0 0 1rem;
                align-items: center;
                justify-content: center;

                color: var(--flex-color-text-muted);
                font-size: 0.7rem;

                transition: transform var(--flex-duration-fast) var(--flex-easing);
            }

            :host([open]) .chevron {
                transform: rotate(180deg);
            }

            /*
             * Dropdown
             */

            .menu {
                position: absolute;
                z-index: 1000;

                top: calc(100% + 0.375rem);
                right: 0;
                left: 0;

                display: flex;
                max-height: 16rem;
                flex-direction: column;
                gap: 0.125rem;

                padding: 0.375rem;

                overflow-y: auto;

                border: 1px solid var(--flex-color-border);
                border-radius: var(--flex-radius-md);

                background: var(--flex-color-surface);

                box-shadow:
                    0 10px 15px -3px rgb(0 0 0 / 10%),
                    0 4px 6px -4px rgb(0 0 0 / 10%);

                opacity: 0;
                visibility: hidden;

                transform: translateY(-0.25rem);

                transition:
                    opacity var(--flex-duration-fast) var(--flex-easing),
                    visibility var(--flex-duration-fast) var(--flex-easing),
                    transform var(--flex-duration-fast) var(--flex-easing);
            }

            :host([open]) .menu {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
            }

            /*
             * Option
             */

            .option {
                display: flex;
                width: 100%;
                min-height: 2.25rem;
                align-items: center;
                gap: var(--flex-space-2);

                padding: 0.425rem 0.625rem;

                border: 0;
                border-radius: var(--flex-radius-md);

                background: transparent;
                color: var(--flex-color-text);

                font: inherit;
                font-size: 0.875rem;
                font-weight: 500;

                text-align: left;
                cursor: pointer;
            }

            .option:hover:not(:disabled) {
                background: var(--flex-color-surface-muted);
            }

            .option.selected-option {
                background: var(--flex-color-primary-50);
                color: var(--flex-color-primary-700);
            }

            :host-context(html[data-theme="dark"]) .option.selected-option {
                background: rgb(49 46 129 / 28%);
                color: var(--flex-color-primary-300);
            }

            .option:disabled {
                opacity: 0.45;
                cursor: not-allowed;
            }

            .option-label {
                min-width: 0;
                flex: 1;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .check {
                display: inline-flex;
                width: 1rem;
                flex: 0 0 1rem;
                align-items: center;
                justify-content: center;

                color: var(--flex-color-primary-600);
                font-size: 0.75rem;
            }

            /*
             * Messages
             */

            .message {
                color: var(--flex-color-text-muted);
                font-size: 0.75rem;
                line-height: 1rem;
            }

            :host([invalid]) .trigger {
                border-color: #dc2626;
            }

            :host([invalid]) .message {
                color: #dc2626;
            }

            @media (prefers-reduced-motion: reduce) {
                .menu,
                .chevron,
                .trigger {
                    transition: none;
                }
            }
        `,
    ];

    constructor() {
        super();

        this.name = "";
        this.label = "";
        this.value = "";
        this.placeholder = "Изберете";
        this.helper = "";
        this.error = "";

        this.open = false;
        this.disabled = false;
        this.required = false;
        this.fullWidth = false;

        this.options = [];

        this.internals = this.attachInternals();
    }

    connectedCallback() {
        super.connectedCallback();

        this.#readOptions();

        this.internals.setFormValue(this.disabled ? null : this.value || null);

        this.#validate();

        this.toggleAttribute("invalid", Boolean(this.error));

        document.addEventListener("click", this.#handleOutsideClick);

        document.addEventListener("keydown", this.#handleKeydown);
    }

    disconnectedCallback() {
        document.removeEventListener("click", this.#handleOutsideClick);

        document.removeEventListener("keydown", this.#handleKeydown);

        super.disconnectedCallback();
    }

    updated(changedProperties) {
        if (changedProperties.has("value") || changedProperties.has("disabled")) {
            this.internals.setFormValue(this.disabled ? null : this.value || null);
        }

        if (changedProperties.has("value") || changedProperties.has("required")) {
            this.#validate();
        }

        if (changedProperties.has("error")) {
            this.toggleAttribute("invalid", Boolean(this.error));
        }
    }

    checkValidity() {
        this.#validate();

        return this.internals.checkValidity();
    }

    reportValidity() {
        this.#validate();

        return this.internals.reportValidity();
    }

    formResetCallback() {
        const selectedOption = this.options.find((option) => option.selected);

        this.value = selectedOption?.value ?? "";

        this.internals.setFormValue(this.value || null);

        this.#validate();
    }

    formDisabledCallback(disabled) {
        this.disabled = disabled;
    }

    render() {
        const selectedOption = this.options.find(
            (option) => String(option.value) === String(this.value),
        );

        return html`
            <div class="field">
                ${
                    this.label
                        ? html`
                              <div class="label">
                                  ${this.label}
                                  ${
                                      this.required
                                          ? html` <span class="required"> * </span> `
                                          : nothing
                                  }
                              </div>
                          `
                        : nothing
                }

                <div class="control">
                    <button
                        class="trigger"
                        type="button"
                        ?disabled=${this.disabled}
                        aria-haspopup="listbox"
                        aria-expanded=${this.open ? "true" : "false"}
                        @click=${this.#toggle}
                    >
                        <span
                            class="selected
                            ${selectedOption ? "" : "placeholder"}"
                        >
                            ${selectedOption ? selectedOption.label : this.placeholder}
                        </span>

                        <span
                            class="chevron"
                            aria-hidden="true"
                        >
                            <i
                                class="fa-solid
                                    fa-chevron-down"
                            ></i>
                        </span>
                    </button>

                    <div
                        class="menu"
                        role="listbox"
                    >
                        ${this.options.map((option) => this.#renderOption(option))}
                    </div>
                </div>

                ${
                    this.error || this.helper
                        ? html` <div class="message">${this.error || this.helper}</div> `
                        : nothing
                }
            </div>
        `;
    }

    #renderOption(option) {
        const selected = String(option.value) === String(this.value);

        return html`
            <button
                class="option
                    ${selected ? "selected-option" : ""}"
                type="button"
                role="option"
                aria-selected=${selected ? "true" : "false"}
                ?disabled=${option.disabled}
                @click=${() => this.#selectOption(option)}
            >
                <span class="option-label"> ${option.label} </span>

                ${
                    selected
                        ? html`
                              <span
                                  class="check"
                                  aria-hidden="true"
                              >
                                  <i class="fa-solid fa-check"></i>
                              </span>
                          `
                        : nothing
                }
            </button>
        `;
    }

    #readOptions() {
        this.options = Array.from(this.querySelectorAll(":scope > option")).map((option) => ({
            value: option.value,
            label: option.textContent?.trim() ?? "",
            disabled: option.disabled,
            selected: option.selected,
        }));

        const selectedOption = this.options.find((option) => option.selected);

        if (!this.value && selectedOption) {
            this.value = selectedOption.value;
        }
    }

    #validate() {
        if (this.required && !this.value) {
            this.internals.setValidity(
                {
                    valueMissing: true,
                },
                "Моля, изберете стойност.",
            );

            return false;
        }

        this.internals.setValidity({});

        return true;
    }

    #toggle = (event) => {
        event.stopPropagation();

        if (this.disabled) {
            return;
        }

        this.open = !this.open;
    };

    #selectOption(option) {
        if (option.disabled) {
            return;
        }

        this.value = option.value;

        this.internals.setFormValue(this.disabled ? null : this.value);

        this.#validate();

        this.open = false;

        this.emit("flex-change", {
            name: this.name,
            value: this.value,
            option,
        });
    }

    #handleOutsideClick = (event) => {
        if (!this.contains(event.target)) {
            this.open = false;
        }
    };

    #handleKeydown = (event) => {
        if (event.key === "Escape" && this.open) {
            this.open = false;
        }
    };
}

FlexDropdown.register("flex-dropdown");
