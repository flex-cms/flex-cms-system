import { html, nothing } from "lit";
import { FlexElement } from "../base/flex-element.js";

export class FlexTabs extends FlexElement {
    static properties = {
        active: { type: String, reflect: true },
        variant: { type: String }, // 'line' | 'pills' | 'boxed'
        panels: { state: true },
    };

    constructor() {
        super();
        this.active = "";
        this.variant = "line";
        this.panels = [];
    }

    connectedCallback() {
        super.connectedCallback();
        this._observer = new MutationObserver(() => this.syncPanels());
        this._observer.observe(this, { childList: true, subtree: true });
    }

    disconnectedCallback() {
        this._observer?.disconnect();
        super.disconnectedCallback();
    }

    firstUpdated() {
        this.syncPanels();
    }

    syncPanels() {
        const panelElements = Array.from(
            this.querySelectorAll("flex-tab-panel"),
        );

        this.panels = panelElements.map((panel) => ({
            key: panel.getAttribute("key") || "",
            label: panel.getAttribute("label") || "",
            icon: panel.getAttribute("icon") || null,
            badge: panel.getAttribute("badge") || null,
            disabled: panel.hasAttribute("disabled"),
            element: panel,
        }));

        if (
            (!this.active || !this.panels.some((p) => p.key === this.active)) &&
            this.panels.length > 0
        ) {
            this.active = this.panels[0].key;
        }

        this.updatePanelsState();
    }

    updated(changedProperties) {
        if (changedProperties.has("active")) {
            this.updatePanelsState();
        }
    }

    updatePanelsState() {
        this.panels.forEach((p) => {
            if (p.element) {
                p.element.active = p.key === this.active;
            }
        });
    }

    selectTab(panel) {
        if (panel.disabled || panel.key === this.active) return;

        this.active = panel.key;

        this.dispatchEvent(
            new CustomEvent("flex-change", {
                bubbles: true,
                composed: true,
                detail: { active: this.active, key: this.active },
            }),
        );
    }

    render() {
        return html`
            <style>
                flex-tabs {
                    display: flex !important;
                    flex-direction: column !important;
                }
                flex-tabs > .flex-tabs-wrapper {
                    order: -1 !important;
                    margin-bottom: 1rem;
                }
            </style>

            <!-- Навигация с табове (поставена най-отгоре чрез order: -1) -->
            <div class="flex-tabs-wrapper">
                <div class="${this.getNavContainerClasses()}" role="tablist">
                    ${this.panels.map((panel) => this.renderTabButton(panel))}
                </div>
            </div>
        `;
    }

    renderTabButton(panel) {
        const isActive = panel.key === this.active;

        return html`
            <button
                type="button"
                role="tab"
                aria-selected="${isActive ? "true" : "false"}"
                ?disabled="${panel.disabled}"
                class="${this.getButtonClasses(isActive, panel.disabled)}"
                @click="${() => this.selectTab(panel)}"
            >
                ${panel.icon
                    ? html`<i
                          class="${panel.icon} text-sm"
                          aria-hidden="true"
                      ></i>`
                    : nothing}
                <span>${panel.label}</span>

                ${panel.badge !== null && panel.badge !== undefined
                    ? html`
                          <span class="${this.getBadgeClasses(isActive)}">
                              ${panel.badge}
                          </span>
                      `
                    : nothing}
            </button>
        `;
    }

    getNavContainerClasses() {
        if (this.variant === "boxed") {
            return "inline-flex items-center gap-1 p-1 rounded-xl bg-gray-100 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700";
        }
        if (this.variant === "pills") {
            return "flex items-center gap-2 flex-wrap";
        }
        return "flex items-center gap-6 border-b border-gray-200 dark:border-gray-800 overflow-x-auto";
    }

    getButtonClasses(isActive, isDisabled) {
        const base =
            "inline-flex items-center gap-2 text-sm font-medium transition-all duration-200 disabled:cursor-not-allowed disabled:opacity-50 focus:outline-none cursor-pointer";

        if (this.variant === "boxed") {
            return `${base} px-4 py-2 rounded-lg ${
                isActive
                    ? "bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm"
                    : "text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
            }`;
        }

        if (this.variant === "pills") {
            return `${base} px-4 py-2 rounded-lg ${
                isActive
                    ? "bg-blue-600 text-white shadow-sm"
                    : "bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700"
            }`;
        }

        return `${base} py-3 -mb-px border-b-2 ${
            isActive
                ? "border-blue-600 text-blue-600 dark:text-blue-400 font-semibold"
                : "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200"
        }`;
    }

    getBadgeClasses(isActive) {
        if (this.variant === "pills" && isActive) {
            return "ml-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-white/20 text-white";
        }
        return "ml-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300";
    }
}

if (!customElements.get("flex-tabs")) {
    customElements.define("flex-tabs", FlexTabs);
}
