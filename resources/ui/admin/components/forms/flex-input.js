import { css, html, nothing } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";
import { fontAwesomeStyles } from "@admin-ui/styles/font-awesome.styles.js";

export class FlexInput extends FlexElement {
    static formAssociated = true;

    static properties = {
        name: { type: String },
        label: { type: String },
        value: { type: String },
        type: { type: String },

        placeholder: { type: String },
        helper: { type: String },
        error: { type: String },
        icon: { type: String },

        min: { type: Number },
        max: { type: Number },
        step: { type: Number },

        minlength: {
            type: Number,
            attribute: "minlength",
        },

        maxlength: {
            type: Number,
            attribute: "maxlength",
        },

        pattern: { type: String },

        rows: { type: Number },

        autocomplete: {
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

        readonly: {
            type: Boolean,
            reflect: true,
        },

        fullWidth: {
            type: Boolean,
            attribute: "full-width",
            reflect: true,
        },
    };

    static styles = [
        fontAwesomeStyles,

        css`
            :host {
                display: inline-block;
                width: auto;
            }

            :host([full-width]) {
                display: block;
                width: 100%;
            }

            .field {
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
                width: 100%;
            }

            input,
            textarea {
                box-sizing: border-box;
                display: block;
                width: 100%;

                border: 1px solid var(--flex-color-border);

                border-radius: var(--flex-radius-md);

                background: var(--flex-color-surface);

                color: var(--flex-color-text);

                font: inherit;
                font-size: 0.875rem;

                transition:
                    border-color var(--flex-duration-fast) var(--flex-easing),
                    box-shadow var(--flex-duration-fast) var(--flex-easing),
                    background var(--flex-duration-fast) var(--flex-easing);
            }

            input {
                min-height: 2.625rem;
                padding: 0.5rem 0.75rem;
            }

            textarea {
                min-height: 6rem;
                padding: 0.625rem 0.75rem;
                line-height: 1.5;
                resize: vertical;
            }

            .has-icon input,
            .has-icon textarea {
                padding-left: 2.5rem;
            }

            input::placeholder,
            textarea::placeholder {
                color: var(--flex-color-text-muted);
            }

            input:hover:not(:disabled),
            textarea:hover:not(:disabled) {
                border-color: var(--flex-color-text-muted);
            }

            input:focus,
            textarea:focus {
                outline: none;

                border-color: var(--flex-color-primary-500);

                box-shadow: 0 0 0 3px
                    color-mix(in srgb, var(--flex-color-primary-500) 15%, transparent);
            }

            input:disabled,
            textarea:disabled {
                opacity: 0.55;
                cursor: not-allowed;
            }

            input:read-only,
            textarea:read-only {
                background: var(--flex-color-surface-muted);
            }

            .icon {
                position: absolute;
                top: 50%;
                left: 0.875rem;

                display: inline-flex;
                width: 1rem;
                align-items: center;
                justify-content: center;

                color: var(--flex-color-text-muted);

                font-size: 0.875rem;

                pointer-events: none;

                transform: translateY(-50%);
            }

            .textarea-icon {
                top: 0.875rem;
                transform: none;
            }

            .message {
                color: var(--flex-color-text-muted);

                font-size: 0.75rem;
                line-height: 1rem;
            }

            :host([invalid]) input,
            :host([invalid]) textarea {
                border-color: #dc2626;
            }

            :host([invalid]) .message {
                color: #dc2626;
            }

            :host([invalid]) .icon {
                color: #dc2626;
            }
        `,
    ];

    constructor() {
        super();

        this.name = "";
        this.label = "";
        this.value = "";
        this.type = "text";

        this.placeholder = "";
        this.helper = "";
        this.error = "";
        this.icon = "";

        this.min = undefined;
        this.max = undefined;
        this.step = undefined;

        this.minlength = undefined;
        this.maxlength = undefined;

        this.pattern = "";

        this.rows = 4;

        this.autocomplete = "";

        this.required = false;
        this.disabled = false;
        this.readonly = false;
        this.fullWidth = false;

        this.internals = this.attachInternals();
    }

    connectedCallback() {
        super.connectedCallback();

        this.#syncFormValue();
        this.#validate();

        this.toggleAttribute("invalid", Boolean(this.error));
    }

    updated(changedProperties) {
        if (changedProperties.has("value") || changedProperties.has("disabled")) {
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
        const textarea = this.type === "textarea";

        return html`
            <div class="field">
                ${
                    this.label
                        ? html`
                              <label
                                  class="label"
                                  @click=${this.focus}
                              >
                                  ${this.label}
                                  ${
                                  this.required
                                      ? html` <span class="required"> * </span> `
                                      : nothing
                              }
                              </label>
                          `
                        : nothing
                }

                <div
                    class="control
                        ${this.icon ? "has-icon" : ""}"
                >
                    ${
                        this.icon
                            ? html`
                                  <span
                                      class="icon
                                    ${textarea ? "textarea-icon" : ""}"
                                  >
                                      <i class=${this.icon}></i>
                                  </span>
                              `
                            : nothing
                    }
                    ${textarea ? this.#renderTextarea() : this.#renderInput()}
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

    #renderInput() {
        return html`
            <input
                type=${this.#normalizedType}
                .value=${this.value ?? ""}
                placeholder=${this.placeholder}
                autocomplete=${this.autocomplete || nothing}
                min=${this.min ?? nothing}
                max=${this.max ?? nothing}
                step=${this.step ?? nothing}
                minlength=${this.minlength ?? nothing}
                maxlength=${this.maxlength ?? nothing}
                pattern=${this.pattern || nothing}
                ?required=${this.required}
                ?disabled=${this.disabled}
                ?readonly=${this.readonly}
                aria-invalid=${this.error ? "true" : "false"}
                @input=${this.#handleInput}
                @change=${this.#handleChange}
                @blur=${this.#handleBlur}
            />
        `;
    }

    #renderTextarea() {
        return html`
            <textarea
                .value=${this.value ?? ""}
                placeholder=${this.placeholder}
                rows=${this.rows}
                minlength=${this.minlength ?? nothing}
                maxlength=${this.maxlength ?? nothing}
                ?required=${this.required}
                ?disabled=${this.disabled}
                ?readonly=${this.readonly}
                aria-invalid=${this.error ? "true" : "false"}
                @input=${this.#handleInput}
                @change=${this.#handleChange}
                @blur=${this.#handleBlur}
            ></textarea>
        `;
    }

    #handleInput = (event) => {
        this.value = event.target.value;

        this.error = "";

        this.#syncFormValue();
        this.#validate();

        this.emit("flex-input", {
            name: this.name,
            value: this.value,
            originalEvent: event,
        });
    };

    #handleChange = (event) => {
        this.value = event.target.value;

        this.#syncFormValue();
        this.#validate();

        this.emit("flex-change", {
            name: this.name,
            value: this.value,
            originalEvent: event,
        });
    };

    #handleBlur = () => {
        this.#validate(true);
    };

    #syncFormValue() {
        this.internals.setFormValue(this.disabled ? null : (this.value ?? ""));
    }

    #validate(showError = false) {
        const control = this.control;

        if (!control || this.disabled) {
            this.internals.setValidity({});

            return true;
        }

        if (control.validity.valid) {
            this.internals.setValidity({});

            if (showError) {
                this.error = "";
            }

            return true;
        }

        const message = this.#validationMessage(control);

        this.internals.setValidity(this.#validityFlags(control.validity), message, control);

        if (showError) {
            this.error = message;
        }

        return false;
    }

    #validationMessage(control) {
        const validity = control.validity;

        if (validity.valueMissing) {
            return "Полето е задължително.";
        }

        if (validity.typeMismatch) {
            return "Въведете валидна стойност.";
        }

        if (validity.tooShort) {
            return `Въведете поне ${this.minlength} символа.`;
        }

        if (validity.tooLong) {
            return `Въведете най-много ${this.maxlength} символа.`;
        }

        if (validity.rangeUnderflow) {
            return `Минималната стойност е ${this.min}.`;
        }

        if (validity.rangeOverflow) {
            return `Максималната стойност е ${this.max}.`;
        }

        if (validity.patternMismatch) {
            return "Стойността е в невалиден формат.";
        }

        return control.validationMessage || "Невалидна стойност.";
    }

    #validityFlags(validity) {
        return {
            valueMissing: validity.valueMissing,

            typeMismatch: validity.typeMismatch,

            patternMismatch: validity.patternMismatch,

            tooLong: validity.tooLong,

            tooShort: validity.tooShort,

            rangeUnderflow: validity.rangeUnderflow,

            rangeOverflow: validity.rangeOverflow,

            stepMismatch: validity.stepMismatch,

            badInput: validity.badInput,
        };
    }

    checkValidity() {
        return this.#validate();
    }

    reportValidity() {
        const valid = this.#validate(true);

        if (!valid) {
            this.control?.focus();
        }

        return valid;
    }

    formResetCallback() {
        this.value = "";

        this.#syncFormValue();
        this.#validate();

        this.error = "";
    }

    formDisabledCallback(disabled) {
        this.disabled = disabled;
    }

    focus() {
        this.control?.focus();
    }

    get control() {
        return this.renderRoot?.querySelector("input, textarea");
    }

    get #normalizedType() {
        const supported = ["text", "number", "email", "password", "url", "tel", "search"];

        return supported.includes(this.type) ? this.type : "text";
    }
}

FlexInput.register("flex-input");
