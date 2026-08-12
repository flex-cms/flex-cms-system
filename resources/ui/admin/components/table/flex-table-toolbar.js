import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";

export class FlexTableToolbar extends FlexElement {
    static styles = css`
        :host {
            display: block;
        }

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.9rem 1rem;
            border-bottom: 1px solid var(--flex-table-border, rgb(226 232 240));
            background: var(--flex-table-bg, rgb(255 255 255));
        }

        .left,
        .right {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
        }

        .left {
            min-width: 0;
            flex: 1 1 20rem;
        }

        .right {
            flex: 0 0 auto;
            justify-content: flex-end;
        }

        ::slotted(*) {
            max-width: 100%;
        }
    `;

    render() {
        return html`
            <div class="toolbar">
                <div class="left">
                    <slot></slot>
                </div>

                <div class="right">
                    <slot name="actions"></slot>
                </div>
            </div>
        `;
    }
}

FlexTableToolbar.register("flex-table-toolbar");
