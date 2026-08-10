import { css, html, nothing } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";

export class FlexRadioGroup extends FlexElement {
    static formAssociated = true;

    static properties = {
        name: {
            type: String,
        },

        label: {
            type: String,
        },

        value: {
            type: String,
        },

        helper: {
            type: String,
        },

        error: {
            type: String,
        },

        required: {
            type: Boolean,
            reflect: true,
        },

        disabled: {
            type: Boolean,
            reflect: true,
        },

        direction: {
            type: String,
            reflect: true,
        },
    };

    static styles = css`
        :host {
            display: block;
            width: 100%;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
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

        .options {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        :host([direction="horizontal"]) .options {
            flex-direction: row;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .message {
            color: var(--flex-color-text-muted);

            font-size: 0.75rem;
            line-height: 1rem;
        }

        :host([invalid]) .message {
            color: #dc2626;
        }

        :host([disabled]) {
            opacity: 0.6;
        }
    `;

    constructor() {
        super();

        this.name = "";
        this.label = "";
        this.value = "";

        this.helper = "";
        this.error = "";

        this.required = false;
        this.disabled = false;

        this.direction = "vertical";

        this.internals = this.attachInternals();
    }

    connectedCallback() {
        super.connectedCallback();

        this.addEventListener("flex-radio-select", this.#handleSelect);

        this.#syncRadios();
        this.#syncFormValue();
        this.#validate();
    }

    disconnectedCallback() {
        this.removeEventListener("flex-radio-select", this.#handleSelect);

        super.disconnectedCallback();
    }

    updated(changedProperties) {
        if (changedProperties.has("value") || changedProperties.has("disabled")) {
            this.#syncRadios();
            this.#syncFormValue();
        }

        if (changedProperties.has("value") || changedProperties.has("required")) {
            this.#validate();
        }

        if (changedProperties.has("error")) {
            this.toggleAttribute("invalid", Boolean(this.error));
        }
    }

    render() {
        return html`
            <div class="field">
                ${
                    this.label
                        ? html`
                              <div
                                  class="label"
                                  id="label"
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

                <div
                    class="options"
                    role="radiogroup"
                    aria-labelledby=${this.label ? "label" : nothing}
                    aria-invalid=${this.error ? "true" : "false"}
                >
                    <slot @slotchange=${this.#syncRadios}></slot>
                </div>

                ${
                    this.error || this.helper
                        ? html`
                              <div
                                  class="message"
                                  role=${this.error ? "alert" : nothing}
                              >
                                  ${this.error || this.helper}
                              </div>
                          `
                        : nothing
                }
            </div>
        `;
    }

    #handleSelect = (event) => {
        event.stopPropagation();

        if (this.disabled) {
            return;
        }

        const value = event.detail?.value;

        if (value === undefined) {
            return;
        }

        this.value = String(value);

        this.error = "";

        this.#syncRadios();
        this.#syncFormValue();
        this.#validate();

        this.emit("flex-change", {
            name: this.name,
            value: this.value,
        });
    };

    #radios() {
        return Array.from(this.querySelectorAll(":scope > flex-radio"));
    }

    #syncRadios = () => {
        for (const radio of this.#radios()) {
            radio.checked = String(radio.value) === String(this.value);

            radio.disabled = this.disabled || radio.hasAttribute("data-disabled");
        }
    };

    #syncFormValue() {
        this.internals.setFormValue(this.disabled || !this.value ? null : this.value);
    }

    #validate(showError = false) {
        if (this.required && !this.value) {
            const message = "Изберете една от опциите.";

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
            const first = this.#radios().find((radio) => !radio.disabled);

            first?.focus();
        }

        return valid;
    }

    formResetCallback() {
        this.value = "";

        this.error = "";

        this.#syncRadios();
        this.#syncFormValue();
        this.#validate();
    }

    formDisabledCallback(disabled) {
        this.disabled = disabled;
    }
}

FlexRadioGroup.register("flex-radio-group");
