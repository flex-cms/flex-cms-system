import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";
import { fontAwesomeStyles } from "@admin-ui/styles/font-awesome.styles.js";

import {
    themeManager,
    THEME_DARK,
    THEME_LIGHT,
    THEME_SYSTEM,
} from "@admin-ui/core/ThemeManager.js";

export class FlexThemeToggle extends FlexElement {
    static properties = {
        theme: {
            type: String,
            reflect: true,
        },

        preference: {
            type: String,
            reflect: true,
        },
    };

    constructor() {
        super();

        this.theme = themeManager.currentTheme();

        this.preference = themeManager.preference();
    }

    onConnect() {
        this.#syncTheme();

        this.listen(document, "flex-theme-change", this.#handleThemeChange);
    }

    beforeTurboCache() {
        this.#syncTheme();
    }

    render() {
        const isDark = this.theme === THEME_DARK;

        const label = isDark ? "Премини към светла тема" : "Премини към тъмна тема";

        return html`
            <flex-button
                icon-only
                icon="fa-solid fa-bars"
                variant="secondary"
                tooltip="Редактиране"
                tooltip-position="bottom"
                type="button"
                title=${label}
                aria-label=${label}
                aria-pressed=${String(isDark)}
                @click=${this.#toggleTheme}
            >
                ${
                    this.preference === THEME_SYSTEM
                        ? html`
                              <span
                                  class="preference"
                                  title="Използва системната тема"
                                  aria-hidden="true"
                              >
                                  <i class="fa-solid fa-desktop"></i>
                              </span>
                          `
                        : null
                }

                <span class="visually-hidden"> ${label} </span>
            </flex-button>
        `;
    }

    #toggleTheme = () => {
        themeManager.toggle();
    };

    #handleThemeChange = (event) => {
        this.theme = event.detail?.theme ?? THEME_LIGHT;

        this.preference = event.detail?.preference ?? THEME_SYSTEM;
    };

    #syncTheme() {
        this.theme = themeManager.currentTheme();

        this.preference = themeManager.preference();
    }
}

FlexThemeToggle.register("flex-theme-toggle");
