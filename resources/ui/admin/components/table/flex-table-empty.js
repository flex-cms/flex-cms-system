import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";
import { fontAwesomeStyles } from "@admin-ui/styles/font-awesome.styles.js";

export class FlexTableEmpty extends FlexElement {
    static properties = {
        colspan: {
            type: Number,
        },

        title: {
            type: String,
        },

        description: {
            type: String,
        },

        icon: {
            type: String,
        },
    };

    static styles = [
        fontAwesomeStyles,
        css`
            :host {
                display: table-row-group;
            }

            td {
                padding: 3rem 1rem;
                text-align: center;
            }

            .content {
                display: inline-flex;
                max-width: 28rem;
                flex-direction: column;
                align-items: center;
                gap: 0.5rem;
            }

            .icon {
                display: inline-flex;
                height: 2.75rem;
                width: 2.75rem;
                align-items: center;
                justify-content: center;
                border-radius: 0.75rem;
                background: var(--flex-table-empty-icon-bg, rgb(248 250 252));
                color: var(--flex-table-empty-icon-color, rgb(100 116 139));
            }

            .title {
                margin: 0;
                font-size: 0.875rem;
                font-weight: 600;
                color: var(--flex-table-text-strong, rgb(15 23 42));
            }

            .description {
                margin: 0;
                font-size: 0.8125rem;
                line-height: 1.25rem;
                color: var(--flex-table-text-muted, rgb(100 116 139));
            }
        `,
    ];

    constructor() {
        super();

        this.colspan = 1;
        this.title = "Няма записи";
        this.description = "Все още няма налични данни за показване.";
        this.icon = "fa-solid fa-table-list";
    }

    render() {
        return html`
            <tr>
                <td colspan=${this.colspan}>
                    <div class="content">
                        <div class="icon">
                            <i
                                class=${this.icon}
                                aria-hidden="true"
                            ></i>
                        </div>

                        <p class="title">${this.title}</p>
                        <p class="description">${this.description}</p>
                    </div>
                </td>
            </tr>
        `;
    }
}

FlexTableEmpty.register("flex-table-empty");
