import { css, html, nothing } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";
import { fontAwesomeStyles } from "@admin-ui/styles/font-awesome.styles.js";

export class FlexCheckbox extends FlexElement {
    static formAssociated = true;

    static properties = {
        name: { type: String },
        label: { type: String },
        value: { type: String },
        helper: { type: String },
        error: { type: String },

        checked: {
            type: Boolean,
            reflect: true,
        },

        required: {
            type: Boolean,
            reflect: true,
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

            .field {
                display: flex;
                flex-direction: column;
                gap: 0.4rem;
            }

            .control {
                display: flex;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .checkbox {
                position: relative;

                display: inline-flex;
                width: 1.25rem;
                height: 1.25rem;

                flex: 0 0 1.25rem;

                align-items: center;
                justify-content: center;

                margin-top: 0.1rem;

                border: 1px solid var(--flex-color-border);

                border-radius: 0.35rem;

                background: var(--flex-color-surface);

                color: #ffffff;

                cursor: pointer;

                transition:
                    background var(--flex-duration-fast) var(--flex-easing),
                    border-color var(--flex-duration-fast) var(--flex-easing),
                    box-shadow var(--flex-duration-fast) var(--flex-easing);
            }

            .checkbox:hover {
                border-color: var(--flex-color-primary-400);
            }

            :host([checked]) .checkbox {
                border-color: var(--flex-color-primary-600);

                background: var(--flex-color-primary-600);
            }

            .checkbox:focus-visible {
                outline: none;

                box-shadow: 0 0 0 3px
                    color-mix(in srgb, var(--flex-color-primary-500) 15%, transparent);
            }

            .check-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;

                font-size: 0.7rem;

                opacity: 0;
                transform: scale(0.8);

                transition:
                    opacity var(--flex-duration-fast) var(--flex-easing),
                    transform var(--flex-duration-fast) var(--flex-easing);
            }

            :host([checked]) .check-icon {
                opacity: 1;
                transform: scale(1);
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

                cursor: pointer;
            }

            .required {
                margin-left: 0.125rem;
                color: #dc2626;
            }

            .helper,
            .error {
                font-size: 0.75rem;
                line-height: 1rem;
            }

            .helper {
                color: var(--flex-color-text-muted);
            }

            .error {
                color: #dc2626;
            }

            :host([invalid]) .checkbox {
                border-color: #dc2626;
            }

            :host([disabled]) {
                opacity: 0.55;
            }

            :host([disabled]) .checkbox,
            :host([disabled]) .label {
                cursor: not-allowed;
            }

            @media (prefers-reduced-motion: reduce) {
                .checkbox,
                .check-icon {
                    transition: none;
                }
            }
        `,
    ];

    constructor() {
        super();

        this.name = "";
        this.label = "";
        this.value = "1";

        this.helper = "";
        this.error = "";

        this.checked = false;
        this.required = false;
        this.disabled = false;

        this.internals = this.attachInternals();
    }

    connectedCallback() {
        super.connectedCallback();

        this.#syncFormValue();
        this.#validate();

        this.toggleAttribute("invalid", Boolean(this.error));
    }

    updated(changedProperties) {
        if (
            changedProperties.has("checked") ||
            changedProperties.has("value") ||
            changedProperties.has("disabled")
        ) {
            this.#syncFormValue();
        }

        if (changedProperties.has("checked") || changedProperties.has("required")) {
            this.#validate();
        }

        if (changedProperties.has("error")) {
            this.toggleAttribute("invalid", Boolean(this.error));
        }
    }

    render() {
        return html`
            <div class="field">
                <div class="control">
                    <button
                        class="checkbox"
                        type="button"
                        role="checkbox"
                        aria-checked=${this.checked ? "true" : "false"}
                        aria-invalid=${this.error ? "true" : "false"}
                        ?disabled=${this.disabled}
                        @click=${this.#toggle}
                    >
                        <span
                            class="check-icon"
                            aria-hidden="true"
                        >
                            <i class="fa-solid fa-check"></i>
                        </span>
                    </button>

                    <div class="content">
                        ${
                            this.label
                                ? html`
                                      <div
                                          class="label"
                                          @click=${this.#toggle}
                                      >
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
                        ${
                            this.error
                                ? html`
                                      <div
                                          class="error"
                                          role="alert"
                                      >
                                          ${this.error}
                                      </div>
                                  `
                                : this.helper
                                  ? html` <div class="helper">${this.helper}</div> `
                                  : nothing
                        }
                    </div>
                </div>
            </div>
        `;
    }

    #toggle = (event) => {
        event?.preventDefault();

        if (this.disabled) {
            return;
        }

        this.checked = !this.checked;

        this.error = "";

        this.#syncFormValue();
        this.#validate();

        this.emit("flex-change", {
            name: this.name,
            value: this.value,
            checked: this.checked,
            originalEvent: event,
        });
    };

    #syncFormValue() {
        this.internals.setFormValue(this.disabled || !this.checked ? null : this.value);
    }

    #validate(showError = false) {
        if (this.required && !this.checked) {
            const message = "Полето трябва да бъде отметнато.";

            this.internals.setValidity(
                {
                    valueMissing: true,
                },
                message,
            );

            if (showError) {
                this.error = message;
            }

            return false;
        }

        this.internals.setValidity({});

        if (showError) {
            this.error = "";
        }

        return true;
    }

    checkValidity() {
        return this.#validate();
    }

    reportValidity() {
        const valid = this.#validate(true);

        if (!valid) {
            this.renderRoot?.querySelector(".checkbox")?.focus();
        }

        return valid;
    }

    formResetCallback() {
        this.checked = false;
        this.error = "";

        this.#syncFormValue();
        this.#validate();
    }

    formDisabledCallback(disabled) {
        this.disabled = disabled;
    }

    focus() {
        this.renderRoot?.querySelector(".checkbox")?.focus();
    }
}

FlexCheckbox.register("flex-checkbox");
