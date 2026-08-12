import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";

export class FlexTableSortIndicator extends FlexElement {
    static properties = {
        direction: {
            type: String,
            reflect: true,
        },
    };

    static styles = css`
        :host {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1rem;
            height: 1rem;
            color: var(--flex-table-sort-icon, rgb(148 163 184));
        }

        :host([direction="asc"]),
        :host([direction="desc"]) {
            color: var(--flex-table-sort-icon-active, rgb(79 70 229));
        }

        i {
            font-size: 0.7rem;
        }
    `;

    constructor() {
        super();

        this.direction = "";
    }

    render() {
        const icon = this.direction === "asc"
            ? "fa-solid fa-arrow-up"
            : this.direction === "desc"
                ? "fa-solid fa-arrow-down"
                : "fa-solid fa-sort";

        return html`
            <i class=${icon} aria-hidden="true"></i>
        `;
    }
}

FlexTableSortIndicator.register("flex-table-sort-indicator");
