import { css, html, nothing } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";
import { fontAwesomeStyles } from "@admin-ui/styles/font-awesome.styles.js";

export class FlexButton extends FlexElement {
    static properties = {
        label: {
            type: String,
        },

        icon: {
            type: String,
        },

        variant: {
            type: String,
            reflect: true,
        },

        size: {
            type: String,
            reflect: true,
        },

        type: {
            type: String,
        },

        href: {
            type: String,
        },

        target: {
            type: String,
        },

        tooltip: {
            type: String,
        },

        tooltipPosition: {
            type: String,
            attribute: "tooltip-position",
            reflect: true,
        },

        disabled: {
            type: Boolean,
            reflect: true,
        },

        loading: {
            type: Boolean,
            reflect: true,
        },

        turbo: {
            type: Boolean,
            reflect: true,
        },

        iconOnly: {
            type: Boolean,
            attribute: "icon-only",
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
                position: relative;
                display: inline-flex;
                vertical-align: middle;
            }

            :host([full-width]) {
                display: flex;
                width: 100%;
            }

            .button {
                position: relative;
                display: inline-flex;
                min-height: 2.5rem;
                align-items: center;
                justify-content: center;
                gap: var(--flex-space-2);
                padding: 0.5rem 0.875rem;

                border: 1px solid transparent;
                border-radius: var(--flex-radius-md);

                background: transparent;
                color: var(--flex-color-text);

                font: inherit;
                font-size: 0.875rem;
                font-weight: 600;
                line-height: 1.25rem;

                text-decoration: none;
                white-space: nowrap;
                cursor: pointer;

                transition:
                    background var(--flex-duration-fast) var(--flex-easing),
                    border-color var(--flex-duration-fast) var(--flex-easing),
                    color var(--flex-duration-fast) var(--flex-easing),
                    opacity var(--flex-duration-fast) var(--flex-easing),
                    transform var(--flex-duration-fast) var(--flex-easing);
            }

            .button:hover {
                text-decoration: none;
            }

            .button:active:not(.disabled) {
                transform: scale(0.98);
            }

            .button:focus-visible {
                outline: 3px solid var(--flex-color-focus);
                outline-offset: 2px;
            }

            /*
             * Variants
             */

            :host([variant="primary"]) .button {
                background: var(--flex-color-primary-600);
                color: #ffffff;
            }

            :host([variant="primary"]) .button:hover {
                background: var(--flex-color-primary-700);
            }

            :host([variant="secondary"]) .button {
                border-color: var(--flex-color-border);
                background: var(--flex-color-surface);
                color: var(--flex-color-text);
            }

            :host([variant="secondary"]) .button:hover {
                background: var(--flex-color-surface-muted);
            }

            :host([variant="ghost"]) .button {
                background: transparent;
                color: var(--flex-color-text-muted);
            }

            :host([variant="ghost"]) .button:hover {
                background: var(--flex-color-surface-muted);
                color: var(--flex-color-text);
            }

            :host([variant="danger"]) .button {
                background: #dc2626;
                color: #ffffff;
            }

            :host([variant="danger"]) .button:hover {
                background: #b91c1c;
            }

            /*
             * Sizes
             */

            :host([size="sm"]) .button {
                min-height: 2rem;
                gap: 0.375rem;
                padding: 0.25rem 0.625rem;
                font-size: 0.8125rem;
            }

            :host([size="lg"]) .button {
                min-height: 3rem;
                padding: 0.625rem 1.125rem;
                font-size: 0.9375rem;
            }

            /*
             * Icon
             */

            .icon {
                display: inline-flex;
                width: 1rem;
                flex: 0 0 1rem;
                align-items: center;
                justify-content: center;
                font-size: 0.875rem;
            }

            :host([size="lg"]) .icon {
                font-size: 1rem;
            }

            /*
             * Icon-only
             */

            :host([icon-only]) .button {
                width: 2.5rem;
                min-width: 2.5rem;
                padding: 0;
            }

            :host([icon-only][size="sm"]) .button {
                width: 2rem;
                min-width: 2rem;
            }

            :host([icon-only][size="lg"]) .button {
                width: 3rem;
                min-width: 3rem;
            }

            /*
             * Full width
             */

            :host([full-width]) .button {
                width: 100%;
            }

            /*
             * Disabled
             */

            .button.disabled,
            :host([disabled]) .button {
                opacity: 0.5;
                cursor: not-allowed;
                pointer-events: none;
            }

            /*
             * Loading
             */

            .spinner {
                display: inline-flex;
                width: 1rem;
                height: 1rem;
                align-items: center;
                justify-content: center;
            }

            .spinner i {
                animation: flex-button-spin 0.75s linear infinite;
            }

            @keyframes flex-button-spin {
                to {
                    transform: rotate(360deg);
                }
            }

            /*
             * Tooltip
             */

            .tooltip {
                position: absolute;
                z-index: 1000;

                max-width: 14rem;
                padding: 0.375rem 0.5rem;

                border-radius: var(--flex-radius-md);

                background: var(--flex-color-text);
                color: var(--flex-color-surface);

                font-size: 0.75rem;
                font-weight: 500;
                line-height: 1rem;

                white-space: nowrap;
                pointer-events: none;

                opacity: 0;
                visibility: hidden;

                transition:
                    opacity var(--flex-duration-fast) var(--flex-easing),
                    transform var(--flex-duration-fast) var(--flex-easing),
                    visibility var(--flex-duration-fast) var(--flex-easing);
            }

            :host(:hover) .tooltip,
            :host(:focus-within) .tooltip {
                opacity: 1;
                visibility: visible;
            }

            /*
             * Tooltip top
             */

            :host([tooltip-position="top"]) .tooltip {
                bottom: calc(100% + 0.5rem);
                left: 50%;
                transform: translate(-50%, 0.25rem);
            }

            :host([tooltip-position="top"]:hover) .tooltip,
            :host([tooltip-position="top"]:focus-within) .tooltip {
                transform: translate(-50%, 0);
            }

            /*
             * Tooltip bottom
             */

            :host([tooltip-position="bottom"]) .tooltip {
                top: calc(100% + 0.5rem);
                left: 50%;
                transform: translate(-50%, -0.25rem);
            }

            :host([tooltip-position="bottom"]:hover) .tooltip,
            :host([tooltip-position="bottom"]:focus-within) .tooltip {
                transform: translate(-50%, 0);
            }

            /*
             * Tooltip left
             */

            :host([tooltip-position="left"]) .tooltip {
                top: 50%;
                right: calc(100% + 0.5rem);
                transform: translate(0.25rem, -50%);
            }

            :host([tooltip-position="left"]:hover) .tooltip,
            :host([tooltip-position="left"]:focus-within) .tooltip {
                transform: translate(0, -50%);
            }

            /*
             * Tooltip right
             */

            :host([tooltip-position="right"]) .tooltip {
                top: 50%;
                left: calc(100% + 0.5rem);
                transform: translate(-0.25rem, -50%);
            }

            :host([tooltip-position="right"]:hover) .tooltip,
            :host([tooltip-position="right"]:focus-within) .tooltip {
                transform: translate(0, -50%);
            }

            @media (prefers-reduced-motion: reduce) {
                .button,
                .tooltip,
                .spinner i {
                    transition: none;
                    animation-duration: 0s;
                }
            }
        `,
    ];

    constructor() {
        super();

        this.label = "";
        this.icon = "";
        this.variant = "primary";
        this.size = "md";
        this.type = "button";
        this.href = "";
        this.target = "_self";
        this.tooltip = "";
        this.tooltipPosition = "top";
        this.disabled = false;
        this.loading = false;
        this.turbo = false;
        this.iconOnly = false;
        this.fullWidth = false;
    }

    render() {
        return html`
            ${this.href ? this.#renderLink() : this.#renderButton()}
            ${
                this.tooltip
                    ? html`
                          <span
                              class="tooltip"
                              role="tooltip"
                          >
                              ${this.tooltip}
                          </span>
                      `
                    : nothing
            }
        `;
    }

    #renderButton() {
        return html`
            <button
                class="button"
                type=${this.type}
                ?disabled=${this.disabled || this.loading}
                aria-disabled=${this.disabled || this.loading ? "true" : "false"}
                aria-label=${this.#ariaLabel()}
                @click=${this.#handleClick}
            >
                ${this.#renderContent()}
            </button>
        `;
    }

    #renderLink() {
        const turboEnabled = this.turbo && this.target === "_self";

        const disabled = this.disabled || this.loading;

        return html`
            <a
                class="button ${disabled ? "disabled" : ""}"
                href=${disabled ? "#" : this.href}
                target=${this.target || "_self"}
                rel=${this.target === "_blank" ? "noopener noreferrer" : nothing}
                data-turbo=${turboEnabled ? "true" : "false"}
                aria-disabled=${disabled ? "true" : "false"}
                aria-label=${this.#ariaLabel()}
                @click=${this.#handleClick}
            >
                ${this.#renderContent()}
            </a>
        `;
    }

    #renderContent() {
        if (this.loading) {
            return html`
                <span
                    class="spinner"
                    aria-hidden="true"
                >
                    <i class="fa-solid fa-spinner"></i>
                </span>

                ${
                    !this.iconOnly
                        ? html` <span class="label"> ${this.label || "Зареждане..."} </span> `
                        : nothing
                }
            `;
        }

        return html`
            ${
                this.icon
                    ? html`
                          <span
                              class="icon"
                              aria-hidden="true"
                          >
                              <i class=${this.icon}></i>
                          </span>
                      `
                    : nothing
            }
            ${
                !this.iconOnly
                    ? html`
                          <span class="label">
                              <slot>${this.label}</slot>
                          </span>
                      `
                    : nothing
            }
        `;
    }

    #handleClick = (event) => {
        if (this.disabled || this.loading) {
            event.preventDefault();
            event.stopPropagation();

            return;
        }

        this.emit("flex-button-click", {
            originalEvent: event,
            button: this,
            href: this.href || null,
            type: this.type,
        });

        if (this.href) {
            return;
        }

        const form = this.closest("flex-form");

        if (this.type === "submit") {
            event.preventDefault();

            form?.submit();

            return;
        }

        if (this.type === "reset") {
            event.preventDefault();

            form?.reset();
        }
    };

    #ariaLabel() {
        if (!this.iconOnly) {
            return nothing;
        }

        return this.label || this.tooltip || "Button";
    }
}

FlexButton.register("flex-button");
