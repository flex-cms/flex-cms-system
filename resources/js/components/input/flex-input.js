import { html, nothing } from "lit";
import { FlexElement } from "../base/flex-element.js";

export class FlexInput extends FlexElement {
    static properties = {
        type: { type: String, reflect: true },
        name: { type: String },
        label: { type: String },
        value: { type: String },
        placeholder: { type: String },
        helpText: { type: String, attribute: "help-text" },
        error: { type: String },
        required: { type: Boolean, reflect: true },
        disabled: { type: Boolean, reflect: true },
        readonly: { type: Boolean, reflect: true },
        autocomplete: { type: String },
        min: { type: String },
        max: { type: String },
        step: { type: String },
        minlength: { type: Number },
        maxlength: { type: Number },
        rows: { type: Number },
        inputId: { type: String, attribute: "input-id" },
    };

    constructor() {
        super();
        this.type = "text";
        this.name = "";
        this.label = "";
        this.value = "";
        this.placeholder = "";
        this.helpText = "";
        this.error = "";
        this.required = false;
        this.disabled = false;
        this.readonly = false;
        this.autocomplete = "";
        this.min = "";
        this.max = "";
        this.step = "";
        this.minlength = -1;
        this.maxlength = -1;
        this.rows = 4;
        this.inputId = "";
        this.generatedId = `flex-input-${crypto.randomUUID?.() ?? Math.random().toString(36).slice(2)}`;
    }

    render() {
        const id = this.inputId || this.generatedId;
        const describedBy =
            [this.helpText ? `${id}-help` : "", this.error ? `${id}-error` : ""]
                .filter(Boolean)
                .join(" ") || nothing;
        const controlClasses = `block w-full rounded-md border border-gray-300 dark:border-gray-900 bg-white dark:bg-gray-800 dark:text-gray-300 dark:placeholder:text-gray-500 py-3 px-5
            outline-none transition focus:ring-2 disabled:cursor-not-allowed disabled:bg-gray-100
            disabled:text-gray-500 ${
                this.error
                    ? "border-red-500 focus:border-red-500 focus:ring-red-200"
                    : "border-gray-300 focus:border-blue-500 focus:ring-blue-200"
            }`;

        return html`
            <div class="flex-input-field">
                ${this.label
                    ? html`
                          <label
                              for=${id}
                              class="mb-1.5 block font-medium"
                          >
                              ${this.label}
                              ${this.required
                                  ? html`<span
                                        class="text-red-600"
                                        aria-hidden="true"
                                        >*</span
                                    >`
                                  : nothing}
                          </label>
                      `
                    : nothing}
                ${this.normalizedType === "textarea"
                    ? html`
                          <textarea
                              id=${id}
                              class=${controlClasses}
                              name=${this.name || nothing}
                              .value=${this.value ?? ""}
                              placeholder=${this.placeholder || nothing}
                              rows=${Math.max(1, this.rows || 4)}
                              minlength=${this.minlength >= 0
                                  ? this.minlength
                                  : nothing}
                              maxlength=${this.maxlength >= 0
                                  ? this.maxlength
                                  : nothing}
                              autocomplete=${this.autocomplete || nothing}
                              ?required=${this.required}
                              ?disabled=${this.disabled}
                              ?readonly=${this.readonly}
                              aria-invalid=${this.error ? "true" : "false"}
                              aria-describedby=${describedBy}
                              @input=${this.handleInput}
                              @change=${this.handleChange}
                          ></textarea>
                      `
                    : html`
                          <input
                              id=${id}
                              class=${controlClasses}
                              type=${this.normalizedType}
                              name=${this.name || nothing}
                              .value=${this.value ?? ""}
                              placeholder=${this.placeholder || nothing}
                              min=${this.normalizedType === "number" &&
                              this.min !== ""
                                  ? this.min
                                  : nothing}
                              max=${this.normalizedType === "number" &&
                              this.max !== ""
                                  ? this.max
                                  : nothing}
                              step=${this.normalizedType === "number" &&
                              this.step !== ""
                                  ? this.step
                                  : nothing}
                              minlength=${this.normalizedType === "text" &&
                              this.minlength >= 0
                                  ? this.minlength
                                  : nothing}
                              maxlength=${this.normalizedType === "text" &&
                              this.maxlength >= 0
                                  ? this.maxlength
                                  : nothing}
                              autocomplete=${this.autocomplete || nothing}
                              ?required=${this.required}
                              ?disabled=${this.disabled}
                              ?readonly=${this.readonly}
                              aria-invalid=${this.error ? "true" : "false"}
                              aria-describedby=${describedBy}
                              @input=${this.handleInput}
                              @change=${this.handleChange}
                          />
                      `}
                ${this.helpText
                    ? html`
                          <p
                              id="${id}-help"
                              class="mt-1.5 text-xs text-gray-500"
                          >
                              ${this.helpText}
                          </p>
                      `
                    : nothing}
                ${this.error
                    ? html`
                          <p
                              id="${id}-error"
                              class="mt-1.5 text-sm text-red-600"
                              role="alert"
                          >
                              ${this.error}
                          </p>
                      `
                    : nothing}
            </div>
        `;
    }

    get normalizedType() {
        return ["text", "number", "textarea"].includes(this.type)
            ? this.type
            : "text";
    }

    get inputElement() {
        return this.querySelector("input, textarea");
    }

    handleInput(event) {
        this.value = event.target.value;
        this.emit("flex-input", event);
    }

    handleChange(event) {
        this.emit("flex-change", event);
    }

    emit(name, originalEvent) {
        this.dispatchEvent(
            new CustomEvent(name, {
                bubbles: true,
                composed: true,
                detail: { value: this.value, name: this.name, originalEvent },
            }),
        );
    }

    focus() {
        this.inputElement?.focus();
    }

    checkValidity() {
        return this.inputElement?.checkValidity() ?? true;
    }

    reportValidity() {
        return this.inputElement?.reportValidity() ?? true;
    }
}

if (!customElements.get("flex-input")) {
    customElements.define("flex-input", FlexInput);
}