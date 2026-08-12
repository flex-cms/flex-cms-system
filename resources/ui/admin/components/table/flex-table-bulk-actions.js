import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";
import { fontAwesomeStyles } from "@admin-ui/styles/font-awesome.styles.js";

export class FlexTableBulkActions extends FlexElement {
    static properties = {
        count: {
            type: Number,
        },

        actions: {
            attribute: false,
        },

        disabled: {
            type: Boolean,
            reflect: true,
        },
    };

    static styles = [
        fontAwesomeStyles,
        css`
            :host {
                display: block;
            }

            .bar {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                padding: 0.75rem 1rem;
                border-bottom: 1px solid var(--flex-table-border, rgb(226 232 240));
                background: var(--flex-table-selection-bg, rgb(238 242 255));
            }

            .count {
                color: var(--flex-table-selection-text, rgb(67 56 202));
                font-size: 0.875rem;
                font-weight: 600;
            }

            .actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            button {
                display: inline-flex;
                min-height: 2.25rem;
                align-items: center;
                gap: 0.45rem;
                border: 1px solid var(--flex-table-control-border, rgb(203 213 225));
                border-radius: 0.55rem;
                padding: 0.45rem 0.75rem;
                background: var(--flex-table-control-bg, rgb(255 255 255));
                color: var(--flex-table-control-text, rgb(51 65 85));
                cursor: pointer;
                font: inherit;
                font-size: 0.8125rem;
                font-weight: 600;
            }

            button:hover:not(:disabled) {
                background: var(--flex-table-control-hover, rgb(248 250 252));
            }

            button[data-destructive="true"] {
                border-color: var(--flex-table-danger-border, rgb(254 202 202));
                color: var(--flex-table-danger-text, rgb(185 28 28));
            }

            button:disabled {
                cursor: not-allowed;
                opacity: 0.55;
            }
        `,
    ];

    constructor() {
        super();

        this.count = 0;
        this.actions = [];
        this.disabled = false;
    }

    render() {
        return html`
            <div class="bar">
                <div class="count">${this.count} избрани</div>

                <div class="actions">
                    ${this.actions.map(
                        (action) => html`
                            <button
                                type="button"
                                data-action=${action.key}
                                data-destructive=${action.destructive === true ? "true" : "false"}
                                ?disabled=${this.disabled}
                                @click=${() => this.#handleAction(action)}
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
            </div>
        `;
    }

    #handleAction(action) {
        this.emit("flex-table-bulk-action", {
            action: action.key,
            definition: action,
        });
    }
}

FlexTableBulkActions.register("flex-table-bulk-actions");
