import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";

export class FlexTab extends FlexElement {
    static properties = {
        value: {
            type: String,
        },

        label: {
            type: String,
        },

        icon: {
            type: String,
        },

        disabled: {
            type: Boolean,
            reflect: true,
        },

        active: {
            type: Boolean,
            reflect: true,
        },
    };

    static styles = css`
        :host {
            display: none;
        }

        :host([active]) {
            display: block;
        }

        .panel {
            display: block;
            width: 100%;
        }
    `;

    constructor() {
        super();

        this.value = "";
        this.label = "";
        this.icon = "";

        this.disabled = false;
        this.active = false;
    }

    render() {
        return html`
            <div
                class="panel"
                role="tabpanel"
            >
                <slot></slot>
            </div>
        `;
    }
}

FlexTab.register("flex-tab");
