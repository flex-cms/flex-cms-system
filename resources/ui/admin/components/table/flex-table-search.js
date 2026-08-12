import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";
import { fontAwesomeStyles } from "@admin-ui/styles/font-awesome.styles.js";

export class FlexTableSearch extends FlexElement {
    static properties = {
        value: {
            type: String,
        },

        placeholder: {
            type: String,
        },

        debounce: {
            type: Number,
        },

        disabled: {
            type: Boolean,
            reflect: true,
        },
    };

    static styles = [
        fontAwesomeStyles,
        css`
            :host {
                display: block;
                width: min(100%, 22rem);
            }

            .field {
                position: relative;
            }

            .icon {
                position: absolute;
                top: 50%;
                left: 0.8rem;
                transform: translateY(-50%);
                color: var(--flex-table-text-muted, rgb(100 116 139));
                pointer-events: none;
            }

            input {
                width: 100%;
                height: 2.5rem;
                box-sizing: border-box;
                border: 1px solid var(--flex-table-control-border, rgb(203 213 225));
                border-radius: 0.65rem;
                padding: 0 2.4rem 0 2.35rem;
                background: var(--flex-table-control-bg, rgb(255 255 255));
                color: var(--flex-table-control-text, rgb(51 65 85));
                font: inherit;
                font-size: 0.875rem;
            }

            input::placeholder {
                color: var(--flex-table-text-muted, rgb(100 116 139));
            }

            input:focus {
                outline: 2px solid var(--flex-table-focus, rgb(99 102 241));
                outline-offset: 1px;
            }

            input:disabled {
                cursor: not-allowed;
                opacity: 0.6;
            }

            .clear {
                position: absolute;
                top: 50%;
                right: 0.5rem;
                display: inline-flex;
                height: 1.75rem;
                width: 1.75rem;
                transform: translateY(-50%);
                align-items: center;
                justify-content: center;
                border: 0;
                border-radius: 0.4rem;
                background: transparent;
                color: var(--flex-table-text-muted, rgb(100 116 139));
                cursor: pointer;
            }

            .clear:hover {
                background: var(--flex-table-control-hover, rgb(248 250 252));
            }
        `,
    ];

    constructor() {
        super();

        this.value = "";
        this.placeholder = "Търсене...";
        this.debounce = 400;
        this.disabled = false;

        this.timeout = null;
    }

    disconnectedCallback() {
        super.disconnectedCallback();

        if (this.timeout !== null) {
            clearTimeout(this.timeout);
        }
    }

    render() {
        return html`
            <div class="field">
                <i
                    class="icon fa-solid fa-magnifying-glass"
                    aria-hidden="true"
                ></i>

                <input
                    type="search"
                    .value=${this.value}
                    placeholder=${this.placeholder}
                    ?disabled=${this.disabled}
                    autocomplete="off"
                    @input=${this.#handleInput}
                />

                ${
                    this.value !== ""
                        ? html`
                              <button
                                  type="button"
                                  class="clear"
                                  aria-label="Изчисти търсенето"
                                  ?disabled=${this.disabled}
                                  @click=${this.#clear}
                              >
                                  <i
                                      class="fa-solid fa-xmark"
                                      aria-hidden="true"
                                  ></i>
                              </button>
                          `
                        : ""
                }
            </div>
        `;
    }

    #handleInput(event) {
        this.value = event.currentTarget.value;

        if (this.timeout !== null) {
            clearTimeout(this.timeout);
        }

        this.timeout = window.setTimeout(
            () => {
                this.#emitChange();
            },
            Math.max(0, Number(this.debounce) || 0),
        );
    }

    #clear() {
        if (this.timeout !== null) {
            clearTimeout(this.timeout);
        }

        this.value = "";
        this.#emitChange();
    }

    #emitChange() {
        this.emit("flex-table-search-change", {
            value: this.value.trim(),
        });
    }
}

FlexTableSearch.register("flex-table-search");
