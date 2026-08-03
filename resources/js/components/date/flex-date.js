import { html } from "lit";
import { FlexElement } from "../base/flex-element.js";
import { DateHelper } from "../../helpers/date-helper.js";

export class FlexDate extends FlexElement {
    static properties = {
        date: { type: String },
        includeTime: { type: Boolean, attribute: "include-time" },
        relative: { type: Boolean },
        tooltip: { type: Boolean },
        format: { type: String },
    };

    constructor() {
        super();
        this.date = "";
        this.includeTime = false;
        this.relative = false;
        this.tooltip = true;
        this.format = "";
    }

    render() {
        if (!this.date) return html`—`;

        // Точна дата за ховър подсказка (Tooltip)
        const exactDate = DateHelper.format(this.date, true);
        let displayText = "";

        if (this.relative) {
            displayText = DateHelper.fromNow(this.date);
        } else if (this.format) {
            displayText = DateHelper.format(this.date, false, this.format);
        } else {
            displayText = DateHelper.format(this.date, this.includeTime);
        }

        if (this.tooltip) {
            return html`
                <span title="${exactDate}" class="cursor-help underline decoration-dotted decoration-gray-400">
                    ${displayText}
                </span>
            `;
        }

        return html`${displayText}`;
    }
}

if (!customElements.get("flex-date")) {
    customElements.define("flex-date", FlexDate);
}