import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";

export class FlexAdminHeader extends FlexElement {
    static properties = {
        title: {
            type: String,
        },

        userName: {
            type: String,
            attribute: "user-name",
        },

        userEmail: {
            type: String,
            attribute: "user-email",
        },

        userInitial: {
            type: String,
            attribute: "user-initial",
        },
    };

    static styles = css`
        :host {
            display: block;
            width: 100%;
            min-width: 0;
        }

        .header {
            display: flex;
            min-height: var(--flex-header-height);
            align-items: center;
            justify-content: space-between;
            gap: var(--flex-space-4);
            padding: 0 var(--flex-space-4);
        }

        .start,
        .end,
        .user {
            display: flex;
            align-items: center;
        }

        .start {
            min-width: 0;
            gap: var(--flex-space-3);
        }

        .end {
            flex-shrink: 0;
            gap: var(--flex-space-3);
        }

        .toggle {
            display: inline-flex;
            width: 2.5rem;
            height: 2.5rem;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            padding: 0;
            border: 1px solid transparent;
            border-radius: var(--flex-radius-md);
            background: transparent;
            color: var(--flex-color-text-muted);
            cursor: pointer;
            transition:
                color var(--flex-duration-fast) var(--flex-easing),
                border-color var(--flex-duration-fast) var(--flex-easing),
                background var(--flex-duration-fast) var(--flex-easing),
                transform var(--flex-duration-fast) var(--flex-easing);
        }

        .toggle:hover {
            border-color: var(--flex-color-border);
            background: var(--flex-color-surface-muted);
            color: var(--flex-color-primary-600);
        }

        .toggle:active {
            transform: scale(0.96);
        }

        .toggle:focus-visible {
            outline: 3px solid var(--flex-color-focus);
            outline-offset: 2px;
        }

        .title-container {
            min-width: 0;
        }

        .breadcrumbs {
            min-height: 0;
        }

        .breadcrumbs slot {
            display: block;
        }

        h1 {
            margin: 0;
            overflow: hidden;
            color: var(--flex-color-text);
            font-size: var(--flex-font-size-lg);
            font-weight: 650;
            line-height: 1.3;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: var(--flex-space-2);
        }

        .separator {
            width: 1px;
            height: 2rem;
            background: var(--flex-color-border);
        }

        .user {
            min-width: 0;
            gap: var(--flex-space-3);
        }

        .user-details {
            min-width: 0;
            text-align: right;
        }

        .user-name,
        .user-email {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-name {
            max-width: 12rem;
            color: var(--flex-color-text);
            font-size: var(--flex-font-size-sm);
            font-weight: 600;
            line-height: 1.3;
        }

        .user-email {
            max-width: 12rem;
            margin-top: var(--flex-space-1);
            color: var(--flex-color-text-muted);
            font-size: var(--flex-font-size-xs);
            line-height: 1.2;
        }

        .avatar {
            display: flex;
            width: 2.5rem;
            height: 2.5rem;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--flex-color-primary-500);
            border-radius: var(--flex-radius-full);
            background: linear-gradient(
                135deg,
                var(--flex-color-primary-500),
                var(--flex-color-primary-700)
            );
            color: #ffffff;
            font-size: var(--flex-font-size-sm);
            font-weight: 700;
            box-shadow: var(--flex-shadow-sm);
        }

        @media (max-width: 767px) {
            .header {
                padding: 0 var(--flex-space-3);
            }

            .user-details,
            .separator {
                display: none;
            }

            h1 {
                max-width: 45vw;
            }
        }

        @media (max-width: 479px) {
            .actions {
                display: none;
            }

            h1 {
                max-width: 42vw;
            }
        }
    `;

    constructor() {
        super();

        this.title = "Табло";
        this.userName = "Гост";
        this.userEmail = "";
        this.userInitial = "G";
    }

    render() {
        return html`
            <div class="header">
                <div class="start">
                    <button
                        class="toggle"
                        type="button"
                        aria-label="Превключи навигацията"
                        title="Превключи навигацията"
                        @click=${this.#toggleSidebar}
                    >
                        <i
                            class="fa-solid fa-bars"
                            aria-hidden="true"
                        ></i>
                    </button>

                    <div class="title-container">
                        <div class="breadcrumbs">
                            <slot name="breadcrumbs"></slot>
                        </div>

                        <h1 title=${this.title}>${this.title}</h1>
                    </div>
                </div>

                <div class="end">
                    <div class="actions">
                        <slot name="actions"></slot>
                    </div>

                    <flex-theme-toggle> </flex-theme-toggle>

                    <div
                        class="separator"
                        aria-hidden="true"
                    ></div>

                    <div class="user">
                        <div class="user-details">
                            <span class="user-name"> ${this.userName} </span>

                            ${
                                this.userEmail
                                    ? html` <span class="user-email"> ${this.userEmail} </span> `
                                    : null
                            }
                        </div>

                        <div
                            class="avatar"
                            aria-hidden="true"
                        >
                            ${this.#normalizedInitial()}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    #toggleSidebar = () => {
        this.emit("flex-sidebar-toggle");
    };

    #normalizedInitial() {
        const initial = this.userInitial || this.userName || "G";

        return initial.trim().charAt(0).toLocaleUpperCase("bg-BG");
    }
}

FlexAdminHeader.register("flex-admin-header");
