import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";
import { fontAwesomeStyles } from "@admin-ui/styles/font-awesome.styles.js";

export class FlexNavItem extends FlexElement {
    static properties = {
        href: {
            type: String,
        },

        label: {
            type: String,
        },

        icon: {
            type: String,
        },

        badge: {
            type: String,
        },

        target: {
            type: String,
        },

        turbo: {
            type: Boolean,
            reflect: true,
        },

        exact: {
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

            a {
                position: relative;
                display: flex;
                padding: 0.375rem 0.75rem;
                min-height: 1.7rem;
                border: 1px solid transparent;
                align-items: center;
                gap: var(--flex-space-2);
                border-radius: var(--flex-radius-md);
                color: var(--flex-color-text-muted);
                font-size: 0.9375rem;
                font-weight: 550;
                line-height: 1.25rem;
                text-decoration: none;
                transition:
                    color var(--flex-duration-fast) var(--flex-easing),
                    border-color var(--flex-duration-fast) var(--flex-easing),
                    background var(--flex-duration-fast) var(--flex-easing),
                    transform var(--flex-duration-fast) var(--flex-easing);
            }

            a:hover {
                background: var(--flex-color-surface-muted);
                color: var(--flex-color-text);
            }

            a:active {
                transform: scale(0.985);
            }

            a:focus-visible {
                outline: 3px solid var(--flex-color-focus);
                outline-offset: 2px;
            }

            a[aria-current="page"] {
                background: var(--flex-color-surface-muted);
                color: var(--flex-color-text);
            }

            :host-context(html[data-theme="dark"]) a[aria-current="page"] {
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
                transition: opacity var(--flex-duration-fast) var(--flex-easing);
                font-size: inherit;
            }

            :host-context(flex-nav-group) a {
                font-size: 0.9rem;
                font-weight: 500;
            }

            :host-context(flex-nav-group) .badge {
                min-width: 1rem;
                height: 1rem;
                font-size: 0.55rem;
            }

            :host-context(flex-nav-group) .icon {
                font-size: inherit;
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

            a[aria-current="page"] .badge {
                background: var(--flex-color-primary-800);
                color: #ffffff;
            }

            :host([collapsed]) a {
                justify-content: center;
                gap: 0;
                padding-inline: var(--flex-space-2);
            }

            :host([collapsed]) .label,
            :host([collapsed]) .badge {
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
                :host([collapsed]) a {
                    justify-content: flex-start;
                    gap: var(--flex-space-2);
                    padding: 0.375rem 0.625rem;
                }

                :host([collapsed]) .label,
                :host([collapsed]) .badge {
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
            }

            @media (prefers-reduced-motion: reduce) {
                a,
                .label {
                    transition: none;
                }
            }
        `,
    ];

    constructor() {
        super();

        this.href = "#";
        this.label = "";
        this.icon = "fa-solid fa-circle";
        this.badge = "";
        this.target = "_self";
        this.turbo = false;
        this.exact = false;
        this.active = false;
        this.collapsed = false;
    }

    onConnect() {
        this.#syncShellState();
        this.#syncActiveState();

        this.listen(document, "flex-sidebar-state-change", this.#handleSidebarState);

        this.listen(document, "flex-navigation-end", this.#handleNavigationEnd);

        this.listen(window, "popstate", this.#handleNavigationEnd);
    }

    updated(changedProperties) {
        if (changedProperties.has("href") || changedProperties.has("exact")) {
            this.#syncActiveState();
        }
    }

    render() {
        const turboEnabled = this.#usesTurbo();

        const opensNewWindow = this.target === "_blank";

        return html`
            <a
                href=${this.href}
                target=${this.target || "_self"}
                rel=${opensNewWindow ? "noopener noreferrer" : null}
                data-turbo=${turboEnabled ? "true" : "false"}
                aria-current=${this.active ? "page" : null}
                title=${this.collapsed ? this.label : ""}
            >
                <span
                    class="icon"
                    aria-hidden="true"
                >
                    <i class=${this.icon}></i>
                </span>

                <span class="label">
                    <slot>${this.label}</slot>
                </span>

                ${this.badge ? html` <span class="badge"> ${this.badge} </span> ` : null}
            </a>
        `;
    }

    #usesTurbo() {
        if (!this.turbo || this.target !== "_self") {
            return false;
        }

        try {
            const url = new URL(this.href, window.location.href);

            return url.origin === window.location.origin;
        } catch {
            return false;
        }
    }

    #syncActiveState() {
        if (!this.href || this.href === "#") {
            this.active = false;

            return;
        }

        try {
            const target = new URL(this.href, window.location.href);

            if (target.origin !== window.location.origin) {
                this.active = false;

                return;
            }

            const currentPath = this.#normalizePath(window.location.pathname);

            const targetPath = this.#normalizePath(target.pathname);

            this.active = this.exact
                ? currentPath === targetPath
                : currentPath === targetPath || currentPath.startsWith(`${targetPath}/`);
        } catch {
            this.active = false;
        }
    }

    #normalizePath(path) {
        const normalized = `/${String(path).split("/").filter(Boolean).join("/")}`;

        return normalized === "/" ? "/" : normalized.replace(/\/+$/, "");
    }

    #syncShellState() {
        const shell = this.closest("flex-admin-shell");

        this.collapsed = shell?.hasAttribute("sidebar-collapsed") ?? false;
    }

    #handleSidebarState = (event) => {
        this.collapsed = event.detail?.collapsed ?? false;
    };

    #handleNavigationEnd = () => {
        this.#syncActiveState();
    };
}

FlexNavItem.register("flex-nav-item");
