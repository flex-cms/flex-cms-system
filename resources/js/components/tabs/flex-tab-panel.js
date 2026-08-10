import { LitElement, html } from "lit";

export class FlexTabPanel extends LitElement {
    static properties = {
        key: { type: String, reflect: true },
        label: { type: String },
        icon: { type: String },
        badge: { type: String },
        disabled: { type: Boolean, reflect: true },
        active: { type: Boolean, reflect: true },
    };

    createRenderRoot() {
        return this; // Light DOM за директна работа с форми
    }

    updated(changedProperties) {
        if (changedProperties.has("active")) {
            this.style.display = this.active ? "block" : "none";
        }
    }

    render() {
        return html`<slot></slot>`;
    }
}

if (!customElements.get("flex-tab-panel")) {
    customElements.define("flex-tab-panel", FlexTabPanel);
}
