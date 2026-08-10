import { css, html, nothing } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";

import { fontAwesomeStyles } from "@admin-ui/styles/font-awesome.styles.js";

export class FlexTabs extends FlexElement {
    static properties = {
        value: {
            type: String,
            reflect: true,
        },

        variant: {
            type: String,
            reflect: true,
        },

        fullWidth: {
            type: Boolean,
            attribute: "full-width",
            reflect: true,
        },

        tabs: {
            state: true,
        },

        baseUrl: {
            type: String,
            attribute: "base-url",
        },

        urlMode: {
            type: String,
            attribute: "url-mode",
            reflect: true,
        },

        urlKey: {
            type: String,
            attribute: "url-key",
        },
    };

    static styles = [
        fontAwesomeStyles,

        css`
            :host {
                display: inline-block;
            }

            .tabs {
                background: var(--flex-color-surface-muted);
                border: 1px solid;
                border-color: var(--flex-color-border);
                border-radius: 5px;
                padding: 0.5rem;
                display: flex;
                width: 100%;

                gap: 0.25rem;

                overflow-x: auto;

                scrollbar-width: none;
            }

            .tabs::-webkit-scrollbar {
                display: none;
            }

            .tab {
                position: relative;

                display: inline-flex;
                min-height: 2.5rem;

                flex: 0 0 auto;

                align-items: center;
                justify-content: center;

                gap: 0.5rem;

                padding: 0.5rem 0.875rem;

                border: 0;

                background: transparent;

                color: var(--flex-color-text-muted);

                font: inherit;
                font-size: 0.875rem;
                font-weight: 600;

                white-space: nowrap;

                cursor: pointer;

                transition:
                    color var(--flex-duration-fast) var(--flex-easing),
                    background var(--flex-duration-fast) var(--flex-easing);
            }

            .tab:hover:not(:disabled) {
                color: var(--flex-color-text);
            }

            .tab:focus-visible {
                outline: 3px solid var(--flex-color-focus);

                outline-offset: -3px;
            }

            .tab:disabled {
                opacity: 0.45;
                cursor: not-allowed;
            }

            /*
             * Default line variant
             */

            :host([variant="line"]) .tabs {
                border-bottom: 1px solid var(--flex-color-border);
            }

            :host([variant="line"]) .tab {
                border-radius: var(--flex-radius-md) var(--flex-radius-md) 0 0;
            }

            :host([variant="line"]) .tab::after {
                position: absolute;

                right: 0.75rem;
                bottom: -1px;
                left: 0.75rem;

                height: 2px;

                border-radius: 2px;

                background: var(--flex-color-primary-600);

                content: "";

                opacity: 0;

                transform: scaleX(0.7);

                transition:
                    opacity var(--flex-duration-fast) var(--flex-easing),
                    transform var(--flex-duration-fast) var(--flex-easing);
            }

            :host([variant="line"]) .tab.active {
                color: var(--flex-color-primary-600);
            }

            :host([variant="line"]) .tab.active::after {
                opacity: 1;
                transform: scaleX(1);
            }

            /*
             * Pills
             */

            :host([variant="pills"]) .tabs {
                gap: 0.375rem;
            }

            :host([variant="pills"]) .tab {
                border-radius: var(--flex-radius-md);
            }

            :host([variant="pills"]) .tab:hover:not(:disabled) {
                background: var(--flex-color-surface-muted);
            }

            :host([variant="pills"]) .tab.active {
                background: var(--flex-color-primary-50);

                color: var(--flex-color-primary-700);
            }

            :host-context(html[data-theme="dark"]) :host([variant="pills"]) .tab.active {
                background: rgb(49 46 129 / 28%);

                color: var(--flex-color-primary-300);
            }

            /*
             * Boxed
             */

            :host([variant="boxed"]) .tabs {
                padding: 0.25rem;

                border: 1px solid var(--flex-color-border);

                border-radius: var(--flex-radius-lg);

                background: var(--flex-color-surface-muted);
            }

            :host([variant="boxed"]) .tab {
                border-radius: var(--flex-radius-md);
            }

            :host([variant="boxed"]) .tab.active {
                background: var(--flex-color-surface);

                color: var(--flex-color-text);

                box-shadow: 0 1px 3px rgb(0 0 0 / 8%);
            }

            /*
             * Full width
             */

            :host([full-width]) .tab {
                flex: 1 1 0;
            }

            /*
             * Content
             */

            .content {
                padding-top: 1.25rem;
            }

            .icon {
                display: inline-flex;

                width: 1rem;

                align-items: center;
                justify-content: center;

                font-size: 0.875rem;
            }
        `,
    ];

    constructor() {
        super();

        this.value = "";
        this.variant = "line";

        this.fullWidth = false;

        this.tabs = [];

        this.baseUrl = "";
    }

    connectedCallback() {
        super.connectedCallback();

        this.addEventListener("keydown", this.#handleKeydown);

        window.addEventListener("popstate", this.#handlePopState);
    }

    disconnectedCallback() {
        this.removeEventListener("keydown", this.#handleKeydown);

        window.removeEventListener("popstate", this.#handlePopState);

        super.disconnectedCallback();
    }

    firstUpdated() {
        const urlValue = this.#readValueFromUrl();

        if (urlValue) {
            this.value = urlValue;
        }

        this.#readTabs();
    }

    updated(changedProperties) {
        if (changedProperties.has("value")) {
            this.#syncTabs();
        }
    }

    render() {
        return html`
            <div
                class="tabs"
                role="tablist"
            >
                ${this.tabs.map((tab) => this.#renderTabButton(tab))}
            </div>

            <div class="content">
                <slot @slotchange=${this.#readTabs}></slot>
            </div>
        `;
    }

    #renderTabButton(tab) {
        const active = String(tab.value) === String(this.value);

        return html`
            <flex-button
                class="tab-button"
                type="button"
                variant=${active ? "primary" : "ghost"}
                label=${tab.label}
                icon=${tab.icon || ""}
                ?disabled=${tab.disabled}
                data-value=${tab.value}
                role="tab"
                aria-selected=${active ? "true" : "false"}
                tabindex=${active ? "0" : "-1"}
                @click=${() => this.#selectTab(tab.value)}
            ></flex-button>
        `;
    }

    #readTabs = () => {
        const elements = Array.from(this.querySelectorAll(":scope > flex-tab"));

        this.tabs = elements.map((tab) => ({
            element: tab,
            value: tab.value,
            label: tab.label || tab.value,
            icon: tab.icon,
            disabled: tab.disabled,
        }));

        /*
         * Ако няма зададена стойност,
         * активираме първия разрешен tab.
         */
        if (!this.value) {
            const first = this.tabs.find((tab) => !tab.disabled);

            if (first) {
                this.value = first.value;
            }
        }

        /*
         * Ако текущият value вече
         * не съществува.
         */
        const exists = this.tabs.some((tab) => tab.value === this.value && !tab.disabled);

        if (!exists) {
            const first = this.tabs.find((tab) => !tab.disabled);

            this.value = first?.value ?? "";
        }

        this.#syncTabs();
    };

    #syncTabs() {
        for (const tab of this.tabs) {
            tab.element.active = String(tab.value) === String(this.value);
        }
    }

    #selectTab(value) {
        const tab = this.tabs.find((item) => String(item.value) === String(value));

        if (!tab || tab.disabled) {
            return;
        }

        const previous = this.value;

        this.value = String(value);

        this.#syncTabs();

        this.#syncUrl();

        this.emit("flex-tab-change", {
            value: this.value,
            previousValue: previous,
            tab: tab.element,
        });
    }

    #syncUrl() {
        if (this.urlMode === "none") {
            return;
        }

        if (this.urlMode === "query") {
            const url = new URL(window.location.href);

            url.searchParams.set(this.urlKey || "tab", this.value);

            history.pushState({}, "", url);

            return;
        }

        if (this.urlMode === "path" && this.baseUrl) {
            const url = `${this.baseUrl.replace(/\/$/, "")}/${encodeURIComponent(this.value)}`;

            history.pushState({}, "", url);
        }
    }

    #readValueFromUrl() {
        if (this.urlMode === "query") {
            const url = new URL(window.location.href);

            return url.searchParams.get(this.urlKey || "tab") ?? "";
        }

        if (this.urlMode === "path" && this.baseUrl) {
            const current = window.location.pathname;

            const base = this.baseUrl.replace(/\/$/, "");

            if (current.startsWith(`${base}/`)) {
                return decodeURIComponent(current.slice(base.length + 1));
            }
        }

        return "";
    }

    #handleKeydown = (event) => {
        if (
            event.key !== "ArrowRight" &&
            event.key !== "ArrowLeft" &&
            event.key !== "Home" &&
            event.key !== "End"
        ) {
            return;
        }

        const enabled = this.tabs.filter((tab) => !tab.disabled);

        if (!enabled.length) {
            return;
        }

        let index = enabled.findIndex((tab) => String(tab.value) === String(this.value));

        if (event.key === "ArrowRight") {
            index = (index + 1) % enabled.length;
        }

        if (event.key === "ArrowLeft") {
            index = (index - 1 + enabled.length) % enabled.length;
        }

        if (event.key === "Home") {
            index = 0;
        }

        if (event.key === "End") {
            index = enabled.length - 1;
        }

        event.preventDefault();

        const next = enabled[index];

        this.#selectTab(next.value);

        this.updateComplete.then(() => {
            this.renderRoot
                ?.querySelector(`.tab[data-value="${CSS.escape(String(next.value))}"]`)
                ?.focus();
        });
    };

    #handlePopState = () => {
        const value = this.#readValueFromUrl();

        if (!value) {
            return;
        }

        const exists = this.tabs.some((tab) => tab.value === value && !tab.disabled);

        if (!exists) {
            return;
        }

        this.value = value;

        this.#syncTabs();
    };
}

FlexTabs.register("flex-tabs");
