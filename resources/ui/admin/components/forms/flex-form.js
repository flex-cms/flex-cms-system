import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";

export class FlexForm extends FlexElement {
    static properties = {
        action: {
            type: String,
        },

        method: {
            type: String,
        },

        mode: {
            type: String,
            reflect: true,
        },

        enctype: {
            type: String,
        },

        loading: {
            type: Boolean,
            reflect: true,
        },

        disabled: {
            type: Boolean,
            reflect: true,
        },

        resetOnSuccess: {
            type: Boolean,
            attribute: "reset-on-success",
        },

        credentials: {
            type: String,
        },
    };

    static styles = css`
        :host {
            display: block;
            width: 100%;
        }

        form {
            display: block;
            width: 100%;
        }

        :host([loading]) {
            cursor: progress;
        }

        :host([loading]) ::slotted(*) {
            pointer-events: none;
        }
    `;

    constructor() {
        super();

        this.action = "";
        this.method = "POST";
        this.mode = "server";

        this.enctype = "application/x-www-form-urlencoded";

        this.loading = false;
        this.disabled = false;

        this.resetOnSuccess = false;

        this.credentials = "same-origin";
    }

    render() {
        return html`
            <form
                action=${this.action}
                method=${this.method}
                enctype=${this.enctype}
                @submit=${this.#handleSubmit}
                @reset=${this.#handleReset}
            >
                <slot></slot>
            </form>
        `;
    }

    #checkValidity(form) {
        let valid = form.checkValidity();

        const fields = this.querySelectorAll("[name]");

        for (const field of fields) {
            if (typeof field.checkValidity !== "function") {
                continue;
            }

            if (!field.checkValidity()) {
                valid = false;
            }
        }

        return valid;
    }

    #reportValidity(form) {
        form.reportValidity();

        const fields = this.querySelectorAll("[name]");

        for (const field of fields) {
            field.reportValidity?.();
        }
    }

    async #handleSubmit(event) {
        if (this.disabled || this.loading) {
            event.preventDefault();

            return;
        }

        const form = event.currentTarget;

        if (!this.#checkValidity(form)) {
            event.preventDefault();

            this.#reportValidity(form);

            this.emit("flex-form-invalid", {
                form,
            });

            return;
        }

        /*
         * Server-side mode.
         *
         * Оставяме browser-а да изпрати
         * нормалната заявка.
         */
        if (this.mode !== "api") {
            this.emit("flex-form-submit", {
                form,
                mode: "server",
            });

            return;
        }

        /*
         * API mode.
         */
        event.preventDefault();

        await this.#submitApi(form);
    }

    async #submitApi(form) {
        this.loading = true;

        const formData = this.#createFormData(form);

        this.emit("flex-form-submit", {
            form,
            formData,
            mode: "api",
        });

        try {
            const response = await this.#request(formData);

            const data = await this.#parseResponse(response);

            if (!response.ok) {
                throw new FlexFormRequestError(response, data);
            }

            if (this.resetOnSuccess) {
                form.reset();
            }

            this.emit("flex-form-success", {
                form,
                response,
                data,
            });
        } catch (error) {
            this.emit("flex-form-error", {
                form,
                error,
                response: error.response ?? null,
                data: error.data ?? null,
            });
        } finally {
            this.loading = false;

            this.emit("flex-form-complete", {
                form,
            });
        }
    }

    #createFormData(form) {
        /*
         * Първо взимаме стандартните
         * HTML form controls.
         */
        const formData = new FormData(form);

        /*
         * След това добавяме Flex form
         * components от light DOM.
         */
        const fields = this.querySelectorAll("[name]");

        for (const field of fields) {
            if (field.disabled || !field.name) {
                continue;
            }

            /*
             * Native controls вече са
             * обработени от FormData(form).
             */
            if (
                field instanceof HTMLInputElement ||
                field instanceof HTMLSelectElement ||
                field instanceof HTMLTextAreaElement
            ) {
                continue;
            }

            /*
             * Custom Flex components.
             */
            if (!("value" in field)) {
                continue;
            }

            const value = field.value;

            if (value === null || value === undefined) {
                continue;
            }

            formData.append(field.name, String(value));
        }

        return formData;
    }

    async #request(formData) {
        const method = this.method.toUpperCase().trim();

        const action = this.action || window.location.href;

        /*
         * GET / HEAD:
         *
         * FormData става query string.
         */
        if (method === "GET" || method === "HEAD") {
            const url = new URL(action, window.location.href);

            for (const [key, value] of formData.entries()) {
                if (typeof value === "string") {
                    url.searchParams.append(key, value);
                }
            }

            return fetch(url.toString(), {
                method,
                credentials: this.credentials,
                headers: {
                    Accept: "application/json",
                    "X-Flex-Request": "XMLHttpRequest",
                },
            });
        }

        /*
         * POST / PUT / PATCH / DELETE
         */
        return fetch(action, {
            method,

            body: formData,

            credentials: this.credentials,

            headers: {
                Accept: "application/json",
                "X-Flex-Request": "XMLHttpRequest",
            },
        });
    }

    async #parseResponse(response) {
        const contentType = response.headers.get("content-type") ?? "";

        if (contentType.includes("application/json")) {
            return response.json();
        }

        return response.text();
    }

    #handleReset = (event) => {
        this.emit("flex-form-reset", {
            form: event.currentTarget,
        });
    };

    get form() {
        return this.renderRoot?.querySelector("form") ?? null;
    }

    submit() {
        this.form?.requestSubmit();
    }

    reset() {
        this.form?.reset();
    }

    formData() {
        if (!this.form) {
            return new FormData();
        }

        return this.#createFormData(this.form);
    }
}

class FlexFormRequestError extends Error {
    constructor(response, data) {
        super(`Request failed with status ${response.status}`);

        this.name = "FlexFormRequestError";

        this.response = response;

        this.data = data;
    }
}

FlexForm.register("flex-form");
