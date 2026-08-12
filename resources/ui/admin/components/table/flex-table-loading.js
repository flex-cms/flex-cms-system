import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";

export class FlexTableLoading extends FlexElement {
    static properties = {
        rows: {
            type: Number,
        },

        columns: {
            type: Number,
        },
    };

    static styles = css`
        :host {
            display: table-row-group;
        }

        tr {
            border-top: 1px solid var(--flex-table-border, rgb(226 232 240));
        }

        td {
            padding: 0.875rem 1rem;
        }

        .skeleton {
            height: 0.9rem;
            border-radius: 9999px;
            background: var(--flex-table-skeleton, rgb(226 232 240));
            animation: pulse 1.4s ease-in-out infinite;
        }

        @keyframes pulse {
            0%,
            100% {
                opacity: 0.55;
            }

            50% {
                opacity: 1;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .skeleton {
                animation: none;
            }
        }
    `;

    constructor() {
        super();

        this.rows = 6;
        this.columns = 4;
    }

    render() {
        const rows = Math.max(1, Number(this.rows) || 1);
        const columns = Math.max(1, Number(this.columns) || 1);

        return html`${Array.from({ length: rows }, (_, rowIndex) => html`
            <tr aria-hidden="true">
                ${Array.from({ length: columns }, (_, columnIndex) => html`
                    <td>
                        <div class="skeleton" style=${`width:${55 + ((rowIndex + columnIndex) % 4) * 10}%`}></div>
                    </td>
                `)}
            </tr>
        `)}`;
    }
}

FlexTableLoading.register("flex-table-loading");
