import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";

export class FlexSettingsPage extends FlexElement {
    static properties = {
        group: {
            type: String,
        },
    };

    static styles = css`
        :host {
            display: block;
        }

        .form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            padding-top: 1rem;
        }
    `;

    constructor() {
        super();

        this.group = "general";
    }

    render() {
        return html`
            <flex-form
                id="settings-form"
                action=${`/admin/settings/${this.group}`}
                method="POST"
                mode="api"
            >
                <div class="form">
                    <flex-dropdown
                        name="settings[language]"
                        label="Език"
                        value="bg"
                        placeholder="Изберете език"
                        required
                        full-width
                    >
                        <option value="bg">Български</option>

                        <option value="en">English</option>

                        <option value="de">Deutsch</option>
                    </flex-dropdown>

                    <flex-dropdown
                        name="settings[timezone]"
                        label="Часова зона"
                        value="Europe/Sofia"
                        placeholder="Изберете часова зона"
                        required
                        full-width
                    >
                        <option value="Europe/Sofia">Europe/Sofia</option>

                        <option value="Europe/London">Europe/London</option>

                        <option value="UTC">UTC</option>
                    </flex-dropdown>

                    <div class="actions">
                        <flex-button
                            variant="secondary"
                            label="Нулирай"
                            icon="fa-solid fa-rotate-left"
                            @click=${this.#reset}
                        ></flex-button>

                        <flex-button
                            variant="primary"
                            label="Запази"
                            icon="fa-solid fa-floppy-disk"
                            @click=${this.#submit}
                        ></flex-button>
                    </div>
                </div>
            </flex-form>
        `;
    }

    #submit() {
        const form = this.renderRoot.querySelector("#settings-form");

        form?.submit();
    }

    #reset() {
        const form = this.renderRoot.querySelector("#settings-form");

        form?.reset();
    }
}

FlexSettingsPage.register("flex-settings-page");
