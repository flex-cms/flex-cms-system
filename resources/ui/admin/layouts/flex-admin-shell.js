import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";

const SIDEBAR_STORAGE_KEY = "flex.admin.sidebar.collapsed";

export class FlexAdminShell extends FlexElement {
    static properties = {
        sidebarCollapsed: {
            type: Boolean,
            attribute: "sidebar-collapsed",
            reflect: true,
        },

        mobileSidebarOpen: {
            type: Boolean,
            attribute: "mobile-sidebar-open",
            reflect: true,
        },
    };

    static styles = css`
        :host {
            display: block;
            min-width: 0;
            min-height: 100vh;
            color: var(--flex-color-text);
            background: var(--flex-color-background);
        }

        .shell {
            display: grid;
            grid-template-columns:
                var(--flex-sidebar-width)
                minmax(0, 1fr);
            min-height: 100vh;
            transition: grid-template-columns var(--flex-duration-normal) var(--flex-easing);
        }

        :host([sidebar-collapsed]) .shell {
            grid-template-columns:
                var(--flex-sidebar-collapsed-width)
                minmax(0, 1fr);
        }

        .sidebar {
            position: sticky;
            top: 0;
            z-index: var(--flex-z-sidebar);
            min-width: 0;
            height: 100vh;
            overflow: hidden;
            border-right: 1px solid var(--flex-color-border);
            background: var(--flex-color-surface);
        }

        .main {
            display: flex;
            min-width: 0;
            min-height: 100vh;
            flex-direction: column;
        }

        .header {
            position: sticky;
            top: 0;
            z-index: var(--flex-z-header);
            min-height: var(--flex-header-height);
            border-bottom: 1px solid var(--flex-color-border);
            background: color-mix(in srgb, var(--flex-color-surface) 94%, transparent);
            backdrop-filter: blur(12px);
        }

        .content {
            flex: 1;
            min-width: 0;
        }

        .footer {
            border-top: 1px solid var(--flex-color-border);
            background: var(--flex-color-surface);
        }

        .footer:empty {
            display: none;
        }

        .overlay {
            display: none;
        }

        ::slotted([slot="sidebar"]),
        ::slotted([slot="header"]),
        ::slotted([slot="content"]),
        ::slotted([slot="footer"]) {
            box-sizing: border-box;
        }

        ::slotted([slot="sidebar"]) {
            display: block;
            width: 100%;
            height: 100%;
        }

        ::slotted([slot="header"]) {
            display: block;
            min-height: var(--flex-header-height);
        }

        ::slotted([slot="content"]) {
            display: block;
            min-width: 0;
        }

        @media (max-width: 1023px) {
            .shell,
            :host([sidebar-collapsed]) .shell {
                grid-template-columns: minmax(0, 1fr);
            }

            .sidebar {
                position: fixed;
                inset: 0 auto 0 0;
                z-index: var(--flex-z-sidebar);
                width: min(var(--flex-sidebar-width), calc(100vw - 3rem));
                height: 100dvh;
                box-shadow: var(--flex-shadow-lg);
                transform: translateX(-105%);
                transition: transform var(--flex-duration-normal) var(--flex-easing);
            }

            :host([mobile-sidebar-open]) .sidebar {
                transform: translateX(0);
            }

            .overlay {
                position: fixed;
                inset: 0;
                z-index: calc(var(--flex-z-sidebar) - 1);
                display: block;
                width: 100%;
                height: 100%;
                padding: 0;
                border: 0;
                background: var(--flex-color-overlay);
                opacity: 0;
                pointer-events: none;
                transition: opacity var(--flex-duration-normal) var(--flex-easing);
            }

            :host([mobile-sidebar-open]) .overlay {
                opacity: 1;
                pointer-events: auto;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .shell,
            .sidebar,
            .overlay {
                transition: none;
            }
        }
    `;

    #desktopMedia = window.matchMedia("(min-width: 1024px)");

    constructor() {
        super();

        this.sidebarCollapsed = this.#readCollapsedState();

        this.mobileSidebarOpen = false;
    }

    onConnect() {
        this.listen(this, "flex-sidebar-toggle", this.#handleSidebarToggle);

        this.listen(this, "flex-sidebar-open", this.#handleSidebarOpen);

        this.listen(this, "flex-sidebar-close", this.#handleSidebarClose);

        this.listen(this.#desktopMedia, "change", this.#handleViewportChange);

        this.listen(document, "keydown", this.#handleKeydown);
    }

    beforeTurboCache() {
        this.mobileSidebarOpen = false;
    }

    render() {
        return html`
            <div class="shell">
                <aside
                    class="sidebar"
                    aria-label="Основна навигация"
                >
                    <slot name="sidebar"></slot>
                </aside>

                <button
                    class="overlay"
                    type="button"
                    aria-label="Затвори навигацията"
                    tabindex=${this.mobileSidebarOpen ? "0" : "-1"}
                    @click=${this.#closeMobileSidebar}
                ></button>

                <div class="main">
                    <header class="header">
                        <slot name="header"></slot>
                    </header>

                    <main
                        id="flex-main-content"
                        class="content"
                    >
                        <slot name="content"></slot>
                    </main>

                    <footer class="footer">
                        <slot name="footer"></slot>
                    </footer>
                </div>
            </div>
        `;
    }

    #handleSidebarToggle = () => {
        if (this.#desktopMedia.matches) {
            this.sidebarCollapsed = !this.sidebarCollapsed;

            this.#writeCollapsedState(this.sidebarCollapsed);

            this.#emitSidebarState();
            return;
        }

        this.mobileSidebarOpen = !this.mobileSidebarOpen;

        this.#emitSidebarState();
    };

    #handleSidebarOpen = () => {
        if (!this.#desktopMedia.matches) {
            this.mobileSidebarOpen = true;
            this.#emitSidebarState();
        }
    };

    #handleSidebarClose = () => {
        this.mobileSidebarOpen = false;
        this.#emitSidebarState();
    };

    #handleViewportChange = (event) => {
        if (event.matches) {
            this.mobileSidebarOpen = false;
        }

        this.#emitSidebarState();
    };

    #handleKeydown = (event) => {
        if (event.key === "Escape" && this.mobileSidebarOpen) {
            this.#closeMobileSidebar();
        }
    };

    #closeMobileSidebar = () => {
        this.mobileSidebarOpen = false;
        this.#emitSidebarState();
    };

    #emitSidebarState() {
        this.emit("flex-sidebar-state-change", {
            collapsed: this.sidebarCollapsed,

            mobileOpen: this.mobileSidebarOpen,

            desktop: this.#desktopMedia.matches,
        });
    }

    #readCollapsedState() {
        try {
            return window.localStorage.getItem(SIDEBAR_STORAGE_KEY) === "true";
        } catch {
            return false;
        }
    }

    #writeCollapsedState(collapsed) {
        try {
            window.localStorage.setItem(SIDEBAR_STORAGE_KEY, String(collapsed));
        } catch {
            /*
             * Sidebar-ът продължава да работи,
             * дори storage да е недостъпен.
             */
        }
    }
}

FlexAdminShell.register("flex-admin-shell");
