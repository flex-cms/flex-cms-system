import { html } from "lit";
import { FlexElement } from "../base/flex-element.js";

export class FlexToggle extends FlexElement {
    static properties = {
        name: { type: String },
        label: { type: String },
        width: { type: String },
        description: { type: String },
        value: { type: String },
        checked: { type: Boolean, reflect: true },
        disabled: { type: Boolean, reflect: true },
    };

    constructor() {
        super();
        this.name = "";
        this.label = "";
        this.width = "";
        this.description = "";
        this.value = "1";
        this.checked = false;
        this.disabled = false;
    }

    createRenderRoot() {
        return this; // Light DOM за директна интеграция във форми
    }

    toggle() {
        if (this.disabled) return;

        this.checked = !this.checked;

        this.dispatchEvent(
            new CustomEvent("flex-change", {
                bubbles: true,
                composed: true,
                detail: {
                    checked: this.checked,
                    value: this.checked ? this.value || "1" : "0",
                    name: this.name,
                },
            }),
        );
    }

    handleKeyDown(e) {
        if (e.key === " " || e.key === "Enter") {
            e.preventDefault();
            this.toggle();
        }
    }

    render() {
        const toggleId = `flex-toggle-${this.name || Math.random().toString(36).substring(2, 9)}`;
        const fieldValue = this.checked ? this.value || "1" : "0";

        return html`
            <div
                class="flex items-center justify-between gap-4 select-none ${this.width ? this.width : "w-full"} ${
                    this.disabled ? "opacity-50 cursor-not-allowed" : "cursor-pointer"
                }"
                @click=${this.toggle}
            >
                <!-- Етикет и описание -->
                ${
                    this.label || this.description
                        ? html`
                              <div class="flex-1">
                                  ${
                                  this.label
                                      ? html`<label
                                            for=${toggleId}
                                            class="font-medium text-gray-900 dark:text-gray-100 cursor-pointer block"
                                        >
                                            ${this.label}
                                        </label>`
                                      : ""
                              }
                                  ${
                                  this.description
                                      ? html`<p
                                            class="text-sm text-gray-500 dark:text-gray-400 mt-0.5"
                                        >
                                            ${this.description}
                                        </p>`
                                      : ""
                              }
                              </div>
                          `
                        : ""
                }

                <!-- Скрит инпут, който ВИНАГИ присъства във формата и изпраща "0" или "1" -->
                ${
                    this.name
                        ? html`<input
                              type="hidden"
                              name=${this.name}
                              .value=${fieldValue}
                              ?disabled=${this.disabled}
                          />`
                        : ""
                }

                <!-- Визуален суич (Switch) -->
                <div
                    id=${toggleId}
                    role="switch"
                    tabindex=${this.disabled ? "-1" : "0"}
                    aria-checked=${this.checked ? "true" : "false"}
                    @keydown=${this.handleKeyDown}
                    class="relative inline-flex h-6 w-11 shrink-0 rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 ${
                        this.checked ? "bg-blue-600" : "bg-gray-300 dark:bg-gray-700"
                    }"
                >
                    <span
                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out ${
                            this.checked ? "translate-x-5" : "translate-x-0"
                        }"
                    ></span>
                </div>
            </div>
        `;
    }
}

if (!customElements.get("flex-toggle")) {
    customElements.define("flex-toggle", FlexToggle);
}
