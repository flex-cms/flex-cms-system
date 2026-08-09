import * as Turbo from "@hotwired/turbo";

export class TurboManager {
    #started = false;

    #lifecycleController = null;

    constructor({ progressBarDelay = 180 } = {}) {
        this.progressBarDelay = progressBarDelay;
    }

    start() {
        if (this.#started) {
            return this;
        }

        this.#started = true;
        this.#lifecycleController = new AbortController();

        Turbo.start();

        if (typeof Turbo.setProgressBarDelay === "function") {
            Turbo.setProgressBarDelay(this.progressBarDelay);
        }

        this.#listen("turbo:before-visit", this.#handleNavigationStart);

        this.#listen("turbo:before-fetch-request", this.#handleBeforeFetchRequest);

        this.#listen("turbo:before-render", this.#handleBeforeRender);

        this.#listen("turbo:render", this.#handleRender);

        this.#listen("turbo:load", this.#handleNavigationEnd);

        this.#listen("turbo:before-cache", this.#handleBeforeCache);

        this.#listen("turbo:submit-start", this.#handleSubmitStart);

        this.#listen("turbo:submit-end", this.#handleSubmitEnd);

        this.#listen("turbo:fetch-request-error", this.#handleRequestError);

        document.documentElement.dataset.turboReady = "true";

        return this;
    }

    stop() {
        this.#lifecycleController?.abort();
        this.#lifecycleController = null;
        this.#started = false;

        delete document.documentElement.dataset.turboReady;

        return this;
    }

    isStarted() {
        return this.#started;
    }

    visit(url, options = {}) {
        Turbo.visit(url, options);
    }

    clearCache() {
        if (typeof Turbo.cache?.clear === "function") {
            Turbo.cache.clear();
        }
    }

    #listen(eventName, listener) {
        document.addEventListener(eventName, listener, {
            signal: this.#lifecycleController.signal,
        });
    }

    #setNavigating(isNavigating) {
        const root = document.documentElement;

        if (isNavigating) {
            root.dataset.turboNavigating = "true";
        } else {
            delete root.dataset.turboNavigating;
        }
    }

    #emit(eventName, detail = {}) {
        document.dispatchEvent(
            new CustomEvent(eventName, {
                detail,
                bubbles: true,
                composed: true,
            }),
        );
    }

    #handleNavigationStart = (event) => {
        this.#setNavigating(true);

        this.#emit("flex-navigation-start", {
            url: event.detail?.url ?? null,
            action: event.detail?.action ?? null,
        });
    };

    #handleBeforeFetchRequest = (event) => {
        const headers = event.detail?.fetchOptions?.headers;

        if (headers instanceof Headers) {
            headers.set("X-Flex-Turbo", "1");
        } else if (headers && typeof headers === "object") {
            headers["X-Flex-Turbo"] = "1";
        }
    };

    #handleBeforeRender = (event) => {
        this.#emit("flex-before-render", {
            newBody: event.detail?.newBody ?? null,
        });
    };

    #handleRender = () => {
        this.#emit("flex-render");
    };

    #handleNavigationEnd = (event) => {
        this.#setNavigating(false);

        this.#emit("flex-navigation-end", {
            url: event.detail?.url ?? window.location.href,

            timing: event.detail?.timing ?? null,
        });
    };

    #handleBeforeCache = () => {
        this.#setNavigating(false);

        this.#emit("flex-before-cache");
    };

    #handleSubmitStart = (event) => {
        this.#setNavigating(true);

        this.#emit("flex-submit-start", {
            formSubmission: event.detail?.formSubmission ?? null,
        });
    };

    #handleSubmitEnd = (event) => {
        const succeeded = event.detail?.success === true;

        if (!succeeded) {
            this.#setNavigating(false);
        }

        this.#emit("flex-submit-end", {
            success: succeeded,
            fetchResponse: event.detail?.fetchResponse ?? null,
        });
    };

    #handleRequestError = (event) => {
        this.#setNavigating(false);

        this.#emit("flex-navigation-error", {
            error: event.detail?.error ?? null,
        });
    };
}

export const turboManager = new TurboManager();
