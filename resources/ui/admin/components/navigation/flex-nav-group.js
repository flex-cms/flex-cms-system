import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";
import { fontAwesomeStyles } from "@admin-ui/styles/font-awesome.styles.js";

export class FlexNavGroup extends FlexElement {
    static properties = {
        label: {
            type: String,
        },

        icon: {
            type: String,
        },

        badge: {
            type: String,
        },

        open: {
            type: Boolean,
            reflect: true,
        },

        active: {
            type: Boolean,
            reflect: true,
        },

        collapsed: {
            type: Boolean,
            reflect: true,
        },
    };

    static styles = [
        fontAwesomeStyles,
        css`
            :host {
                display: block;
                width: 100%;
            }

            .trigger {
                position: relative;
                display: flex;
                width: 100%;
                min-height: 2.5rem;
                align-items: center;
                gap: var(--flex-space-2);
                padding: 0.7rem 0.75rem;
                border: none;
                border-radius: var(--flex-radius-md);
                background: transparent;
                color: var(--flex-color-text-muted);
                font: inherit;
                font-size: 0.9375rem;
                font-weight: 550;
                line-height: 1.25rem;
                text-align: left;
                cursor: pointer;

                transition:
                    color var(--flex-duration-fast) var(--flex-easing),
                    border-color var(--flex-duration-fast) var(--flex-easing),
                    background var(--flex-duration-fast) var(--flex-easing),
                    transform var(--flex-duration-fast) var(--flex-easing);
            }

            .trigger:hover {
                background: var(--flex-color-surface-muted);
                color: var(--flex-color-text);
            }

            .trigger:active {
                transform: scale(0.985);
            }

            .trigger:focus-visible {
                outline: 3px solid var(--flex-color-focus);
                outline-offset: 2px;
            }

            :host([active]) .trigger {
                background: var(--flex-color-primary-50);
                color: var(--flex-color-primary-700);
            }

            :host-context(html[data-theme="dark"]):host([active]) .trigger {
                background: var(--flex-color-surface-muted);
                color: var(--flex-color-primary-300);
            }

            .icon {
                display: inline-flex;
                width: 1.25rem;
                flex: 0 0 1.25rem;
                align-items: center;
                justify-content: center;
                font-size: 1rem;
            }

            .label {
                min-width: 0;
                flex: 1;
                overflow: hidden;
                opacity: 1;
                text-overflow: ellipsis;
                white-space: nowrap;
                font-size: inherit;

                transition: opacity var(--flex-duration-fast) var(--flex-easing);
            }

            .badge {
                display: inline-flex;
                min-width: 1.125rem;
                height: 1.125rem;
                flex: 0 0 auto;
                align-items: center;
                justify-content: center;
                padding-inline: 0.25rem;
                border-radius: var(--flex-radius-full);
                background: var(--flex-color-surface-muted);
                color: var(--flex-color-text-muted);
                font-size: 0.6rem;
                font-weight: 700;
                line-height: 1;
            }

            .chevron {
                display: inline-flex;
                width: 1rem;
                flex: 0 0 1rem;
                align-items: center;
                justify-content: center;
                font-size: 0.7rem;
                transition: transform var(--flex-duration-fast) var(--flex-easing);
            }

            :host([open]) .chevron {
                transform: rotate(90deg);
            }

            .children {
                display: grid;
                grid-template-rows: 0fr;
                opacity: 0;
                transition:
                    grid-template-rows var(--flex-duration-normal) var(--flex-easing),
                    opacity var(--flex-duration-fast) var(--flex-easing);
            }

            :host([open]:not([collapsed])) .children {
                grid-template-rows: 1fr;
                opacity: 1;
            }

            .children-inner {
                min-height: 0;
                overflow: hidden;
            }

            .children-content {
                display: flex;
                flex-direction: column;
                gap: var(--flex-space-1);
                padding-top: var(--flex-space-1);
                padding-left: var(--flex-space-3);
                border-left: 1px solid var(--flex-color-border);
            }

            ::slotted(flex-nav-item),
            ::slotted(flex-nav-group) {
                display: block;
                width: 100%;
            }

            :host([collapsed]) .trigger {
                justify-content: center;
                gap: 0;
                padding-inline: var(--flex-space-2);
            }

            :host([collapsed]) .label,
            :host([collapsed]) .badge,
            :host([collapsed]) .chevron {
                position: absolute;
                width: 1px;
                height: 1px;
                padding: 0;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                opacity: 0;
                white-space: nowrap;
            }

            @media (max-width: 1023px) {
                :host([collapsed]) .trigger {
                    justify-content: flex-start;
                    gap: var(--flex-space-3);
                    padding: var(--flex-space-2) var(--flex-space-3);
                }

                :host([collapsed]) .label,
                :host([collapsed]) .badge,
                :host([collapsed]) .chevron {
                    position: static;
                    width: auto;
                    height: auto;
                    overflow: visible;
                    clip: auto;
                    opacity: 1;
                    white-space: nowrap;
                }

                :host([collapsed]) .label {
                    flex: 1;
                }

                :host([collapsed][open]) .children {
                    grid-template-rows: 1fr;
                    opacity: 1;
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .trigger,
                .chevron,
                .children {
                    transition: none;
                }
            }
        `,
    ];

    constructor() {
        super();

        this.label = "";
        this.icon = "fa-solid fa-folder";
        this.badge = "";
        this.open = false;
        this.active = false;
        this.collapsed = false;
    }

    onConnect() {
        this.#syncShellState();
        this.#scheduleActiveStateSync();

        this.listen(document, "flex-sidebar-state-change", this.#handleSidebarState);

        this.listen(document, "flex-navigation-end", this.#handleNavigationEnd);

        this.listen(window, "popstate", this.#handleNavigationEnd);
    }

    render() {
        const expanded = this.open && !this.collapsed;

        return html`
            <button
                class="trigger"
                type="button"
                aria-expanded=${expanded ? "true" : "false"}
                title=${this.collapsed ? this.label : ""}
                @click=${this.#toggle}
            >
                <span
                    class="icon"
                    aria-hidden="true"
                >
                    <i class=${this.icon}></i>
                </span>

                <span class="label"> ${this.label} </span>

                ${this.badge ? html` <span class="badge"> ${this.badge} </span> ` : null}

                <span
                    class="chevron"
                    aria-hidden="true"
                >
                    <i class="fa-solid fa-chevron-right"></i>
                </span>
            </button>

            <div
                class="children"
                aria-hidden=${expanded ? "false" : "true"}
            >
                <div class="children-inner">
                    <div class="children-content">
                        <slot @slotchange=${this.#handleSlotChange}></slot>
                    </div>
                </div>
            </div>
        `;
    }

    #toggle = () => {
        if (this.collapsed && this.#isDesktop()) {
            this.open = true;
            this.emit("flex-sidebar-toggle");

            return;
        }

        this.open = !this.open;
    };

    #handleSlotChange = () => {
        this.#scheduleActiveStateSync();
    };

    #handleSidebarState = (event) => {
        this.collapsed = event.detail?.collapsed ?? false;

        if (!this.collapsed) {
            this.#syncActiveState();
        }
    };

    #handleNavigationEnd = () => {
        this.#scheduleActiveStateSync();
    };

    #scheduleActiveStateSync() {
        queueMicrotask(() => {
            this.#syncActiveState();
        });
    }

    #syncActiveState() {
        this.active = Array.from(this.querySelectorAll("flex-nav-item, flex-nav-group")).some(
            (item) => item.hasAttribute("active"),
        );

        if (this.active && !this.collapsed) {
            this.open = true;
        }
    }

    #syncShellState() {
        const shell = this.closest("flex-admin-shell");

        this.collapsed = shell?.hasAttribute("sidebar-collapsed") ?? false;
    }

    #isDesktop() {
        return window.matchMedia("(min-width: 1024px)").matches;
    }
}

FlexNavGroup.register("flex-nav-group");
