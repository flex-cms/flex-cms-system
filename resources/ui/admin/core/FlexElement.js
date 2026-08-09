import { LitElement } from "lit";

/**
 * Базов клас за всички компоненти от новия Flex Admin UI.
 *
 * Осигурява:
 * - автоматично почистване на event listeners;
 * - Turbo lifecycle hook;
 * - стандартно изпращане на custom events;
 * - безопасна регистрация на custom elements;
 * - опционален light DOM rendering.
 */
export class FlexElement extends LitElement {
    static useLightDom = false;

    #lifecycleController = null;

    connectedCallback() {
        super.connectedCallback();

        this.#lifecycleController = new AbortController();

        this.listen(document, "turbo:before-cache", this.#handleBeforeTurboCache);

        this.onConnect();
    }

    disconnectedCallback() {
        this.onDisconnect();

        this.#lifecycleController?.abort();
        this.#lifecycleController = null;

        super.disconnectedCallback();
    }

    createRenderRoot() {
        if (this.constructor.useLightDom) {
            return this;
        }

        return super.createRenderRoot();
    }

    /**
     * Lifecycle hook за наследяващите компоненти.
     */
    onConnect() {}

    /**
     * Cleanup hook за наследяващите компоненти.
     */
    onDisconnect() {}

    /**
     * Извиква се преди Turbo да кешира страницата.
     *
     * Подходящо е за:
     * - затваряне на dropdown менюта;
     * - премахване на modal overlays;
     * - унищожаване на editor instances;
     * - нулиране на временни loading състояния.
     */
    beforeTurboCache() {}

    /**
     * Добавя listener, който автоматично се премахва
     * при disconnectedCallback().
     */
    listen(target, eventName, listener, options = {}) {
        if (!target || typeof target.addEventListener !== "function") {
            throw new TypeError("FlexElement listener target must support addEventListener().");
        }

        if (!this.#lifecycleController) {
            this.#lifecycleController = new AbortController();
        }

        target.addEventListener(eventName, listener, {
            ...options,
            signal: this.#lifecycleController.signal,
        });
    }

    /**
     * Изпраща event през Shadow DOM границата.
     */
    emit(eventName, detail = {}, options = {}) {
        return this.dispatchEvent(
            new CustomEvent(eventName, {
                detail,
                bubbles: true,
                composed: true,
                cancelable: false,
                ...options,
            }),
        );
    }

    /**
     * Изчаква Lit да приключи текущото обновяване.
     */
    async afterRender(callback) {
        await this.updateComplete;

        if (typeof callback === "function") {
            return callback();
        }

        return undefined;
    }

    /**
     * Регистрира компонента само ако името още
     * не е използвано.
     */
    static register(tagName) {
        if (typeof tagName !== "string" || !tagName.includes("-")) {
            throw new TypeError("A custom element name must contain a hyphen.");
        }

        const existing = customElements.get(tagName);

        if (existing && existing !== this) {
            throw new Error(`Custom element [${tagName}] is already registered.`);
        }

        if (!existing) {
            customElements.define(tagName, this);
        }

        return this;
    }

    #handleBeforeTurboCache = () => {
        this.beforeTurboCache();
    };
}

export default FlexElement;
