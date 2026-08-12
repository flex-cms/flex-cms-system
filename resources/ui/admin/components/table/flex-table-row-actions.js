import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";
import { fontAwesomeStyles } from "@admin-ui/styles/font-awesome.styles.js";

export class FlexTableRowActions extends FlexElement {
    static properties = {
        row: {
            attribute: false,
        },

        actions: {
            attribute: false,
        },

        disabled: {
            type: Boolean,
            reflect: true,
        },

        open: {
            type: Boolean,
            reflect: true,
        },

        menuTop: {
            type: Number,
            state: true,
        },

        menuLeft: {
            type: Number,
            state: true,
        },
    };

    static styles = [
        fontAwesomeStyles,
        css`
            :host {
                position: relative;
                display: inline-flex;
                justify-content: flex-end;
            }

            .trigger {
                display: inline-flex;
                height: 2rem;
                width: 2rem;
                align-items: center;
                justify-content: center;
                border: 1px solid var(--flex-table-control-border, rgb(203 213 225));
                border-radius: 0.5rem;
                background: var(--flex-table-control-bg, rgb(255 255 255));
                color: var(--flex-table-control-text, rgb(51 65 85));
                cursor: pointer;
            }

            .trigger:hover:not(:disabled) {
                background: var(--flex-table-control-hover, rgb(248 250 252));
            }

            .trigger:disabled {
                cursor: not-allowed;
                opacity: 0.55;
            }

            .menu {
                position: fixed;
                z-index: 9999;
                min-width: 13rem;
                overflow: hidden;
                border: 1px solid var(--flex-table-border, rgb(226 232 240));
                border-radius: 0.65rem;
                background: var(--flex-table-bg, rgb(255 255 255));
                box-shadow: 0 14px 35px rgb(15 23 42 / 0.14);
            }

            .action {
                display: flex;
                width: 100%;
                align-items: center;
                gap: 0.55rem;
                border: 0;
                padding: 0.65rem 0.8rem;
                background: transparent;
                color: var(--flex-table-control-text, rgb(51 65 85));
                cursor: pointer;
                font: inherit;
                font-size: 0.8125rem;
                text-align: left;
            }

            .action:hover:not(:disabled) {
                background: var(--flex-table-control-hover, rgb(248 250 252));
            }

            .action[data-destructive="true"] {
                color: var(--flex-table-danger-text, rgb(185 28 28));
            }

            .action:disabled {
                cursor: not-allowed;
                opacity: 0.55;
            }

            .separator {
                height: 1px;
                background: var(--flex-table-border, rgb(226 232 240));
            }
        `,
    ];

    constructor() {
        super();

        this.row = null;
        this.actions = [];
        this.disabled = false;
        this.open = false;

        this.boundDocumentClick = this.#handleDocumentClick.bind(this);
        this.boundOtherMenuOpen = this.#handleOtherMenuOpen.bind(this);

        this.menuTop = 0;
        this.menuLeft = 0;
    }

    connectedCallback() {
        super.connectedCallback();

        document.addEventListener("click", this.boundDocumentClick);

        document.addEventListener("flex-table-row-actions-open", this.boundOtherMenuOpen);
    }

    disconnectedCallback() {
        document.removeEventListener("click", this.boundDocumentClick);

        document.removeEventListener("flex-table-row-actions-open", this.boundOtherMenuOpen);

        super.disconnectedCallback();
    }

    render() {
        const actions = this.#resolvedActions();

        if (actions.length === 0) {
            return html``;
        }

        return html`
            <button
                type="button"
                class="trigger"
                aria-label="Действия"
                aria-expanded=${this.open ? "true" : "false"}
                ?disabled=${this.disabled}
                @click=${this.#toggle}
            >
                <i
                    class="fa-solid fa-ellipsis-vertical"
                    aria-hidden="true"
                ></i>
            </button>

            ${
                this.open
                    ? html`
                          <div
                              class="menu"
                              role="menu"
                              style=${`
                                top: ${this.menuTop}px;
                                left: ${this.menuLeft}px;
                            `}
                          >
                              ${actions.map(
                                  (action, index) => html`
                                      ${
                                          action.separatorBefore && index > 0
                                              ? html`<div class="separator"></div>`
                                              : ""
                                      }

                                      <button
                                          type="button"
                                          class="action"
                                          role="menuitem"
                                          data-action=${action.key}
                                          data-destructive=${action.destructive === true ? "true" : "false"}
                                          ?disabled=${this.disabled || action.disabled === true}
                                          @click=${() => this.#select(action)}
                                      >
                                          ${
                                              action.icon
                                                  ? html`<i
                                                        class=${action.icon}
                                                        aria-hidden="true"
                                                    ></i>`
                                                  : ""
                                          }

                                          <span>${action.label}</span>
                                      </button>
                                  `,
                              )}
                          </div>
                      `
                    : ""
            }
        `;
    }

    #resolvedActions() {
        const source = typeof this.actions === "function" ? this.actions(this.row) : this.actions;

        return Array.isArray(source) ? source.filter(Boolean) : [];
    }

    #toggle(event) {
        event.stopPropagation();

        if (!this.open) {
            document.dispatchEvent(
                new CustomEvent("flex-table-row-actions-open", {
                    detail: {
                        source: this,
                    },
                }),
            );

            const trigger = event.currentTarget;
            const rect = trigger.getBoundingClientRect();

            const menuWidth = 208;

            this.menuTop = rect.bottom + 6;

            this.menuLeft = Math.max(8, rect.right - menuWidth);
        }

        this.open = !this.open;
    }

    #select(action) {
        this.open = false;

        this.emit("flex-table-row-action", {
            action: action.key,
            definition: action,
            row: this.row,
        });
    }

    #handleDocumentClick(event) {
        if (!this.open) {
            return;
        }

        if (!event.composedPath().includes(this)) {
            this.open = false;
        }
    }

    #handleOtherMenuOpen(event) {
        if (event.detail?.source !== this && this.open) {
            this.open = false;
        }
    }
}

FlexTableRowActions.register("flex-table-row-actions");
