import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";

export class FlexSidebar extends FlexElement {
    static properties = {
        brandName: {
            type: String,
            attribute: "brand-name",
        },

        brandUrl: {
            type: String,
            attribute: "brand-url",
        },

        version: {
            type: String,
        },

        collapsed: {
            type: Boolean,
            reflect: true,
        },
    };

    static styles = css`
        :host {
            display: block;
            width: 100%;
            height: 100%;
            color: var(--flex-color-text);
            background: var(--flex-color-surface);
        }

        .sidebar {
            display: flex;
            width: 100%;
            height: 100%;
            min-height: 0;
            flex-direction: column;
        }

        .brand {
            display: flex;
            min-height: var(--flex-header-height);
            flex: 0 0 auto;
            align-items: center;
            gap: var(--flex-space-3);
            padding: 0 var(--flex-space-4);
            border-bottom: 1px solid var(--flex-color-border);
            overflow: hidden;
        }

        .brand-link {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: var(--flex-space-3);
            color: var(--flex-color-text);
            text-decoration: none;
        }

        .brand-mark {
            display: flex;
            width: 2.5rem;
            height: 2.5rem;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            border-radius: var(--flex-radius-lg);
            background: linear-gradient(
                135deg,
                var(--flex-color-primary-500),
                var(--flex-color-primary-700)
            );
            color: #ffffff;
            font-size: var(--flex-font-size-lg);
            font-weight: 800;
            box-shadow: var(--flex-shadow-sm);
        }

        .brand-details {
            min-width: 0;
            opacity: 1;
            transition: opacity var(--flex-duration-fast) var(--flex-easing);
        }

        .brand-name,
        .brand-version {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .brand-name {
            font-size: var(--flex-font-size-base);
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .brand-version {
            margin-top: var(--flex-space-1);
            color: var(--flex-color-text-muted);
            font-size: var(--flex-font-size-xs);
        }

        .navigation {
            flex: 1;
            min-height: 0;
            padding: var(--flex-space-4) var(--flex-space-3);
            overflow-x: hidden;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--flex-color-border-strong) transparent;
        }

        .navigation::-webkit-scrollbar {
            width: 0.375rem;
        }

        .navigation::-webkit-scrollbar-thumb {
            border-radius: var(--flex-radius-full);
            background: var(--flex-color-border-strong);
        }

        .footer {
            flex: 0 0 auto;
            padding: var(--flex-space-3);
            border-top: 1px solid var(--flex-color-border);
        }

        .footer:empty {
            display: none;
        }

        :host([collapsed]) .brand {
            justify-content: center;
            padding-inline: var(--flex-space-2);
        }

        :host([collapsed]) .brand-details {
            width: 0;
            opacity: 0;
            pointer-events: none;
        }

        :host([collapsed]) .navigation,
        :host([collapsed]) .footer {
            padding-inline: var(--flex-space-2);
        }

        ::slotted([slot="brand"]) {
            min-width: 0;
        }

        ::slotted([slot="navigation"]) {
            display: block;
        }

        ::slotted([slot="footer"]) {
            display: block;
        }

        @media (max-width: 1023px) {
            .brand {
                justify-content: flex-start;
                padding: 0 var(--flex-space-4);
            }

            .brand-details {
                width: auto;
                opacity: 1;
                pointer-events: auto;
            }

            .navigation {
                padding: var(--flex-space-4) var(--flex-space-3);
            }
        }
    `;

    constructor() {
        super();

        this.brandName = "Flex CMS";
        this.brandUrl = "/admin";
        this.version = "";
        this.collapsed = false;
    }

    onConnect() {
        this.#syncShellState();

        this.listen(document, "flex-sidebar-state-change", this.#handleSidebarState);

        this.listen(this, "click", this.#handleNavigationClick);
    }

    render() {
        return html`
            <div class="sidebar">
                <div class="brand">
                    <slot name="brand">
                        <a
                            class="brand-link"
                            href=${this.brandUrl}
                            data-turbo="false"
                        >
                            <span
                                class="brand-mark"
                                aria-hidden="true"
                            >
                                F
                            </span>

                            <span class="brand-details">
                                <span class="brand-name"> ${this.brandName} </span>

                                ${
                                    this.version
                                        ? html`
                                              <span class="brand-version">
                                                  версия ${this.version}
                                              </span>
                                          `
                                        : null
                                }
                            </span>
                        </a>
                    </slot>
                </div>

                <nav
                    class="navigation"
                    aria-label="Административна навигация"
                >
                    <slot name="navigation"> </slot>
                </nav>

                <div class="footer">
                    <slot name="footer"></slot>
                </div>
            </div>
        `;
    }

    #handleSidebarState = (event) => {
        this.collapsed = event.detail?.collapsed ?? false;
    };

    #handleNavigationClick = (event) => {
        const anchor = event.composedPath().find((element) => element instanceof HTMLAnchorElement);

        if (!anchor) {
            return;
        }

        this.emit("flex-sidebar-close");
    };

    #syncShellState() {
        const shell = this.closest("flex-admin-shell");

        this.collapsed = shell?.hasAttribute("sidebar-collapsed") ?? false;
    }
}

FlexSidebar.register("flex-sidebar");
