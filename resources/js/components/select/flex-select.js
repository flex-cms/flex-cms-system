import { html, nothing } from "lit";
import { FlexElement } from "../base/flex-element.js";

export class FlexSelect extends FlexElement {
    static properties = {
        name: { type: String },
        label: { type: String },
        value: { attribute: false },
        options: { attribute: false },
        placeholder: { type: String },
        helpText: { type: String, attribute: "help-text" },
        error: { type: String },
        required: { type: Boolean, reflect: true },
        disabled: { type: Boolean, reflect: true },
        multiple: { type: Boolean, reflect: true },
        inputId: { type: String, attribute: "input-id" },
        open: { type: Boolean, reflect: true },
    };

    constructor() {
        super();
        this.name = "";
        this.label = "";
        this.value = "";
        this.options = [];
        this.placeholder = "Изберете...";
        this.helpText = "";
        this.error = "";
        this.required = false;
        this.disabled = false;
        this.multiple = false;
        this.inputId = "";
        this.open = false;
        this.generatedId = `flex-select-${crypto.randomUUID?.() ?? Math.random().toString(36).slice(2)}`;

        this.handleOutsideClick = this.handleOutsideClick.bind(this);
    }

    connectedCallback() {
        super.connectedCallback();
        window.addEventListener("click", this.handleOutsideClick);
    }

    disconnectedCallback() {
        window.removeEventListener("click", this.handleOutsideClick);
        super.disconnectedCallback();
    }

    handleOutsideClick(event) {
        if (this.open && !event.composedPath().includes(this)) {
            this.open = false;
        }
    }

    toggleOpen(event) {
        event.stopPropagation();
        if (this.disabled) return;
        this.open = !this.open;
    }

    selectOption(option, event) {
        event?.stopPropagation();

        if (this.multiple) {
            let currentValues = Array.isArray(this.value) ? [...this.value] : [];
            const index = currentValues.indexOf(option.value);
            if (index > -1) {
                currentValues.splice(index, 1);
            } else {
                currentValues.push(option.value);
            }
            this.value = currentValues;
        } else {
            this.value = option.value;
            this.open = false;
        }

        this.dispatchEvent(
            new CustomEvent("flex-change", {
                bubbles: true,
                composed: true,
                detail: { value: this.value, name: this.name },
            }),
        );
    }

    get selectedLabel() {
        if (this.multiple) {
            const selectedVals = Array.isArray(this.value) ? this.value : [];
            if (selectedVals.length === 0) return "";
            const labels = (this.options || [])
                .filter((opt) => selectedVals.includes(opt.value))
                .map((opt) => opt.label);
            return labels.join(", ");
        }

        const selectedOpt = (this.options || []).find(
            (opt) => String(opt.value) === String(this.value),
        );
        return selectedOpt ? selectedOpt.label : "";
    }

    isSelected(val) {
        if (this.multiple) {
            return Array.isArray(this.value) && this.value.includes(val);
        }
        return String(this.value) === String(val);
    }

    render() {
        const id = this.inputId || this.generatedId;
        const displayLabel = this.selectedLabel;
        const controlClasses = `relative flex w-full items-center justify-between rounded-md border text-left py-3 px-5 transition focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:bg-gray-800 ${
            this.error
                ? "border-red-500 focus:border-red-500 focus:ring-red-200"
                : "border-gray-300 dark:border-gray-900 focus:border-blue-500 focus:ring-blue-200"
        }`;

        return html`
            <div class="flex-select-field relative">
                ${
                    this.label
                        ? html`
                              <label
                                  for=${id}
                                  class="mb-1.5 block font-medium"
                              >
                                  ${this.label}
                                  ${
                                  this.required
                                      ? html`<span
                                            class="text-red-600"
                                            aria-hidden="true"
                                            >*</span
                                        >`
                                      : nothing
                              }
                              </label>
                          `
                        : nothing
                }

                <button
                    type="button"
                    id=${id}
                    class=${controlClasses}
                    ?disabled=${this.disabled}
                    aria-expanded=${this.open}
                    @click=${this.toggleOpen}
                >
                    <span
                        class=${
                            displayLabel
                                ? "text-gray-900 dark:text-gray-100"
                                : "text-gray-400 dark:text-gray-500"
                        }
                    >
                        ${displayLabel || this.placeholder}
                    </span>
                    <svg
                        class="h-4 w-4 text-gray-400 transition-transform duration-200 ${
                            this.open ? "rotate-180" : ""
                        }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 9l-7 7-7-7"
                        />
                    </svg>
                </button>

                ${
                    this.open
                        ? html`
                              <div
                                  class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md border border-gray-200 bg-white py-1 shadow-lg ring-1 ring-black/5 focus:outline-none dark:border-gray-700 dark:bg-gray-800"
                                  role="listbox"
                              >
                                  ${
                                  (this.options || []).length === 0
                                      ? html`<div
                                            class="px-4 py-2.5 text-gray-500 dark:text-gray-400"
                                        >
                                            Няма налични опции
                                        </div>`
                                      : (this.options || []).map((opt) => {
                                            const selected = this.isSelected(opt.value);
                                            return html`
                                                <div
                                                    class="flex cursor-pointer items-center justify-between px-4 py-2.5 transition hover:bg-gray-100 dark:hover:bg-gray-700/50 ${
                                                    selected
                                                        ? "font-semibold text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-950/30"
                                                        : "text-gray-700 dark:text-gray-200"
                                                }"
                                                    role="option"
                                                    aria-selected=${selected}
                                                    @click=${(e) => this.selectOption(opt, e)}
                                                >
                                                    <span>${opt.label}</span>
                                                    ${
                                                    selected
                                                        ? html`
                                                              <svg
                                                                  class="h-4 w-4 text-blue-600 dark:text-blue-400"
                                                                  fill="none"
                                                                  stroke="currentColor"
                                                                  viewBox="0 0 24 24"
                                                              >
                                                                  <path
                                                                      stroke-linecap="round"
                                                                      stroke-linejoin="round"
                                                                      stroke-width="2"
                                                                      d="M5 13l4 4L19 7"
                                                                  />
                                                              </svg>
                                                          `
                                                        : nothing
                                                }
                                                </div>
                                            `;
                                        })
                              }
                              </div>
                          `
                        : nothing
                }
                ${
                    this.helpText
                        ? html`<p
                              id="${id}-help"
                              class="mt-1.5 text-xs text-gray-500"
                          >
                              ${this.helpText}
                          </p>`
                        : nothing
                }
                ${
                    this.error
                        ? html`<p
                              id="${id}-error"
                              class="mt-1.5 text-red-600"
                              role="alert"
                          >
                              ${this.error}
                          </p>`
                        : nothing
                }
            </div>
        `;
    }
}

if (!customElements.get("flex-select")) {
    customElements.define("flex-select", FlexSelect);
}
