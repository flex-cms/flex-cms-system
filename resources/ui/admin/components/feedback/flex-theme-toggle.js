import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";

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

    static styles = css`
        :host {
            display: inline-flex;
        }

        button {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            padding: 0;
            border: 1px solid var(--flex-color-border);
            border-radius: var(--flex-radius-md);
            background: var(--flex-color-surface);
            color: var(--flex-color-text-muted);
            cursor: pointer;
            transition:
                color var(--flex-duration-fast) var(--flex-easing),
                border-color var(--flex-duration-fast) var(--flex-easing),
                background var(--flex-duration-fast) var(--flex-easing),
                transform var(--flex-duration-fast) var(--flex-easing);
        }

        button:hover {
            border-color: var(--flex-color-border-strong);
            background: var(--flex-color-surface-hover);
            color: var(--flex-color-primary-600);
        }

        button:active {
            transform: scale(0.96);
        }

        button:focus-visible {
            outline: 3px solid var(--flex-color-focus);
            outline-offset: 2px;
        }

        i {
            font-size: 1rem;
            pointer-events: none;
        }

        .preference {
            position: absolute;
            right: -0.125rem;
            bottom: -0.125rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 0.875rem;
            height: 0.875rem;
            border: 2px solid var(--flex-color-surface);
            border-radius: var(--flex-radius-full);
            background: var(--flex-color-primary-600);
            color: #ffffff;
            font-size: 0.45rem;
        }

        .visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
    `;

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
            <button
                type="button"
                title=${label}
                aria-label=${label}
                aria-pressed=${String(isDark)}
                @click=${this.#toggleTheme}
            >
                <i
                    class=${isDark ? "fa-solid fa-sun" : "fa-solid fa-moon"}
                    aria-hidden="true"
                ></i>

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
            </button>
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
