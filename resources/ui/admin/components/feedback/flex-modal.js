import { css, html, nothing } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";
import { fontAwesomeStyles } from "@admin-ui/styles/font-awesome.styles.js";

export class FlexModal extends FlexElement {
    static properties = {
        open: {
            type: Boolean,
            reflect: true,
        },

        title: {
            type: String,
        },

        size: {
            type: String,
            reflect: true,
        },

        closable: {
            type: Boolean,
            reflect: true,
        },

        closeOnBackdrop: {
            type: Boolean,
            attribute: "close-on-backdrop",
        },

        closeOnEscape: {
            type: Boolean,
            attribute: "close-on-escape",
        },
    };

    static styles = [
        fontAwesomeStyles,

        css`
            :host {
                display: contents;
            }

            .overlay {
                position: fixed;
                z-index: 9999;
                inset: 0;

                display: flex;
                align-items: center;
                justify-content: center;

                padding: 1.5rem;

                background: rgb(15 23 42 / 55%);
                backdrop-filter: blur(3px);

                opacity: 0;
                visibility: hidden;

                transition:
                    opacity 160ms ease,
                    visibility 160ms ease;
            }

            :host([open]) .overlay {
                opacity: 1;
                visibility: visible;
            }

            .modal {
                display: flex;
                width: 100%;
                max-width: 32rem;
                max-height: calc(100vh - 3rem);

                flex-direction: column;

                overflow: hidden;

                border: 1px solid var(--flex-color-border);

                border-radius: var(--flex-radius-lg);

                background: var(--flex-color-surface);

                color: var(--flex-color-text);

                box-shadow: 0 24px 60px rgb(0 0 0 / 20%);

                opacity: 0;

                transform: translateY(0.75rem) scale(0.98);

                transition:
                    opacity 160ms ease,
                    transform 160ms ease;
            }

            :host([open]) .modal {
                opacity: 1;

                transform: translateY(0) scale(1);
            }

            :host([size="sm"]) .modal {
                max-width: 24rem;
            }

            :host([size="md"]) .modal {
                max-width: 32rem;
            }

            :host([size="lg"]) .modal {
                max-width: 48rem;
            }

            :host([size="xl"]) .modal {
                max-width: 64rem;
            }

            :host([size="full"]) .modal {
                width: calc(100vw - 3rem);
                max-width: none;

                height: calc(100vh - 3rem);
                max-height: none;
            }

            .header {
                display: flex;
                flex: 0 0 auto;

                align-items: center;
                justify-content: space-between;

                gap: 1rem;

                padding: 1rem 1.25rem;

                border-bottom: 1px solid var(--flex-color-border);
            }

            .title {
                min-width: 0;

                color: var(--flex-color-text);

                font-size: 1rem;
                font-weight: 700;
                line-height: 1.5rem;
            }

            .close {
                display: inline-flex;

                width: 2rem;
                height: 2rem;

                flex: 0 0 2rem;

                align-items: center;
                justify-content: center;

                border: 0;

                border-radius: var(--flex-radius-md);

                background: transparent;

                color: var(--flex-color-text-muted);

                cursor: pointer;

                transition:
                    background 120ms ease,
                    color 120ms ease;
            }

            .close:hover {
                background: var(--flex-color-surface-muted);

                color: var(--flex-color-text);
            }

            .close:focus-visible {
                outline: none;

                box-shadow: 0 0 0 3px
                    color-mix(in srgb, var(--flex-color-primary-500) 15%, transparent);
            }

            .body {
                min-height: 0;

                flex: 1 1 auto;

                overflow-y: auto;

                padding: 1.25rem;
            }

            .footer {
                display: flex;
                flex: 0 0 auto;

                align-items: center;
                justify-content: flex-end;

                gap: 0.5rem;

                padding: 1rem 1.25rem;

                border-top: 1px solid var(--flex-color-border);
            }

            .footer[hidden] {
                display: none;
            }

            @media (max-width: 640px) {
                .overlay {
                    align-items: flex-end;

                    padding: 0;
                }

                .modal {
                    max-width: none;
                    max-height: calc(100vh - 1rem);

                    border-right: 0;
                    border-bottom: 0;
                    border-left: 0;

                    border-radius: 1rem 1rem 0 0;

                    transform: translateY(1.5rem);
                }

                :host([size="sm"]) .modal,
                :host([size="md"]) .modal,
                :host([size="lg"]) .modal,
                :host([size="xl"]) .modal {
                    max-width: none;
                }

                :host([size="full"]) .modal {
                    width: 100%;
                    height: 100vh;
                    max-height: 100vh;

                    border-radius: 0;
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .overlay,
                .modal {
                    transition: none;
                }
            }
        `,
    ];

    constructor() {
        super();

        this.open = false;

        this.title = "";

        this.size = "md";

        this.closable = true;

        this.closeOnBackdrop = true;

        this.closeOnEscape = true;

        this.#previousActiveElement = null;
    }

    #previousActiveElement;

    connectedCallback() {
        super.connectedCallback();

        document.addEventListener("keydown", this.#handleKeydown);
    }

    disconnectedCallback() {
        document.removeEventListener("keydown", this.#handleKeydown);

        if (this.open) {
            this.#unlockBody();
        }

        super.disconnectedCallback();
    }

    updated(changedProperties) {
        if (changedProperties.has("open")) {
            if (this.open) {
                this.#handleOpened();
            } else {
                this.#handleClosed();
            }
        }
    }

    render() {
        return html`
            <div
                class="overlay"
                @mousedown=${this.#handleBackdrop}
            >
                <section
                    class="modal"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby=${this.title ? "modal-title" : nothing}
                    @mousedown=${this.#stopPropagation}
                >
                    <header class="header">
                        <div
                            class="title"
                            id="modal-title"
                        >
                            <slot name="title"> ${this.title} </slot>
                        </div>

                        ${
                            this.closable
                                ? html`
                                      <button
                                          class="close"
                                          type="button"
                                          aria-label="Затвори"
                                          @click=${() => this.hide("close-button")}
                                      >
                                          <i class="fa-solid fa-xmark"></i>
                                      </button>
                                  `
                                : nothing
                        }
                    </header>

                    <div class="body">
                        <slot></slot>
                    </div>

                    <footer class="footer">
                        <slot
                            name="footer"
                            @slotchange=${this.#handleFooterSlot}
                        ></slot>
                    </footer>
                </section>
            </div>
        `;
    }

    show() {
        if (this.open) {
            return;
        }

        const event = new CustomEvent("flex-modal-before-open", {
            bubbles: true,
            composed: true,
            cancelable: true,
        });

        if (!this.dispatchEvent(event)) {
            return;
        }

        this.#previousActiveElement = document.activeElement;

        this.open = true;

        this.emit("flex-modal-open", {
            modal: this,
        });
    }

    hide(reason = "api") {
        if (!this.open) {
            return;
        }

        const event = new CustomEvent("flex-modal-before-close", {
            bubbles: true,
            composed: true,
            cancelable: true,

            detail: {
                reason,
            },
        });

        if (!this.dispatchEvent(event)) {
            return;
        }

        this.open = false;

        this.emit("flex-modal-close", {
            modal: this,
            reason,
        });
    }

    toggle() {
        if (this.open) {
            this.hide("toggle");
        } else {
            this.show();
        }
    }

    #handleOpened() {
        this.#lockBody();

        this.updateComplete.then(() => {
            this.#focusFirstElement();
        });
    }

    #handleClosed() {
        this.#unlockBody();

        if (this.#previousActiveElement instanceof HTMLElement) {
            this.#previousActiveElement.focus();
        }

        this.#previousActiveElement = null;
    }

    #handleBackdrop = (event) => {
        if (event.target !== event.currentTarget) {
            return;
        }

        if (!this.closable || !this.closeOnBackdrop) {
            return;
        }

        this.hide("backdrop");
    };

    #handleKeydown = (event) => {
        if (!this.open) {
            return;
        }

        if (event.key === "Escape" && this.closable && this.closeOnEscape) {
            event.preventDefault();

            this.hide("escape");

            return;
        }

        if (event.key === "Tab") {
            this.#trapFocus(event);
        }
    };

    #stopPropagation = (event) => {
        event.stopPropagation();
    };

    #focusableElements() {
        const modal = this.renderRoot?.querySelector(".modal");

        if (!modal) {
            return [];
        }

        /*
         * Елементи в Shadow DOM.
         */
        const internal = Array.from(
            modal.querySelectorAll(
                [
                    "button:not([disabled])",
                    "[href]",
                    "input:not([disabled])",
                    "select:not([disabled])",
                    "textarea:not([disabled])",
                    "[tabindex]:not([tabindex='-1'])",
                ].join(","),
            ),
        );

        /*
         * Slotted Flex компоненти.
         */
        const slotted = Array.from(
            this.querySelectorAll(
                [
                    "flex-button",
                    "flex-input",
                    "flex-dropdown",
                    "flex-checkbox",
                    "flex-radio-group",
                    "flex-date-picker",
                    "[tabindex]:not([tabindex='-1'])",
                ].join(","),
            ),
        ).filter((element) => !element.disabled);

        return [...internal, ...slotted];
    }

    #focusFirstElement() {
        const elements = this.#focusableElements();

        if (elements.length > 0) {
            elements[0].focus?.();

            return;
        }

        this.renderRoot?.querySelector(".close")?.focus();
    }

    #trapFocus(event) {
        const elements = this.#focusableElements();

        if (elements.length === 0) {
            return;
        }

        const first = elements[0];

        const last = elements[elements.length - 1];

        const active = document.activeElement;

        if (event.shiftKey && active === first) {
            event.preventDefault();

            last.focus?.();

            return;
        }

        if (!event.shiftKey && active === last) {
            event.preventDefault();

            first.focus?.();
        }
    }

    #lockBody() {
        document.documentElement.style.overflow = "hidden";
    }

    #unlockBody() {
        document.documentElement.style.overflow = "";
    }

    #handleFooterSlot = (event) => {
        const slot = event.target;

        const footer = slot.closest(".footer");

        if (!footer) {
            return;
        }

        footer.hidden = slot.assignedElements().length === 0;
    };
}

FlexModal.register("flex-modal");
