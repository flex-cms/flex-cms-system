import { html } from "lit";
import { FlexElement } from "../base/flex-element.js";

export class FlexButton extends FlexElement {
    static properties = {
        label: { type: String },
        variant: { type: String },
        type: { type: String },
        icon: { type: String },
        iconPosition: { type: String, attribute: "icon-position" },
        disabled: { type: Boolean },
        loading: { type: Boolean, reflect: true },
        loadingText: { type: String, attribute: "loading-text" },
        autoLoading: { type: Boolean, attribute: "auto-loading" },
    };

    constructor() {
        super();

        this.label = "Бутон";
        this.variant = "primary";
        this.type = "button";
        this.icon = "";
        this.iconPosition = "left";
        this.disabled = false;
        this.loading = false;
        this.loadingText = "Зареждане...";
        this.autoLoading = true;

        this.form = null;
        this.handleFormSubmit = this.handleFormSubmit.bind(this);
        this.handleFormComplete = this.handleFormComplete.bind(this);
        this.handlePageShow = this.handlePageShow.bind(this);
    }

    firstUpdated() {
        this.bindToClosestForm();
        window.addEventListener("pageshow", this.handlePageShow);
    }

    disconnectedCallback() {
        this.unbindFromForm();
        window.removeEventListener("pageshow", this.handlePageShow);
        super.disconnectedCallback();
    }

    bindToClosestForm() {
        this.unbindFromForm();
        this.form = this.closest("form");

        if (!this.form) return;

        this.form.addEventListener("submit", this.handleFormSubmit);
        this.form.addEventListener("flex-submit-end", this.handleFormComplete);
        this.form.addEventListener("flex-submit-error", this.handleFormComplete);
        this.form.addEventListener("reset", this.handleFormComplete);
    }

    unbindFromForm() {
        if (!this.form) return;

        this.form.removeEventListener("submit", this.handleFormSubmit);
        this.form.removeEventListener("flex-submit-end", this.handleFormComplete);
        this.form.removeEventListener("flex-submit-error", this.handleFormComplete);
        this.form.removeEventListener("reset", this.handleFormComplete);
        this.form = null;
    }

    handleFormSubmit(event) {
        if (!this.autoLoading || this.type !== "submit") return;

        const submitter = event.submitter;
        const nativeButton = this.querySelector("button");

        // При няколко submit бутона зарежда само реално натиснатият.
        if (submitter && submitter !== nativeButton) return;

        this.loading = true;
    }

    handleFormComplete() {
        this.loading = false;
    }

    handlePageShow() {
        this.loading = false;
    }

    render() {
        const variants = {
            primary: "bg-blue-600 hover:bg-blue-700 focus:ring-blue-300 text-white",
            danger: "bg-red-600 hover:bg-red-700 focus:ring-red-300 text-white",
            secondary: "bg-gray-200 hover:bg-gray-300 focus:ring-gray-300 text-gray-800",
        };

        const isDisabled = this.disabled || this.loading;
        const iconClasses = this.normalizedIconClasses;
        const icon = iconClasses
            ? html`<i
                  class="${iconClasses}"
                  aria-hidden="true"
              ></i>`
            : null;
        const spinner = html`
            <i
                class="fa-solid fa-spinner fa-spin"
                aria-hidden="true"
            ></i>
        `;

        return html`
            <button
                type=${this.normalizedType}
                class="inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 font-medium
                       transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2
                       disabled:cursor-not-allowed disabled:opacity-60
                       ${variants[this.variant] ?? variants.primary}"
                ?disabled=${isDisabled}
                aria-busy=${this.loading ? "true" : "false"}
                @click=${this.handleClick}
            >
                ${
                    this.loading
                        ? html`${spinner}<span>${this.loadingText}</span>`
                        : html`
                              ${this.iconPosition !== "right" ? icon : null}
                              <span>${this.label}</span>
                              ${this.iconPosition === "right" ? icon : null}
                          `
                }
            </button>
        `;
    }

    get normalizedType() {
        return ["button", "submit", "reset"].includes(this.type) ? this.type : "button";
    }

    get normalizedIconClasses() {
        return String(this.icon ?? "")
            .split(/\s+/)
            .filter((className) => /^(fa[srlbdkt]?|fa-[a-z0-9-]+)$/.test(className))
            .join(" ");
    }

    handleClick(event) {
        if (this.disabled || this.loading) return;

        this.dispatchEvent(
            new CustomEvent("flex-click", {
                bubbles: true,
                composed: true,
                detail: {
                    originalEvent: event,
                    form: this.form,
                },
            }),
        );
    }

    startLoading() {
        this.loading = true;
    }

    stopLoading() {
        this.loading = false;
    }
}

if (!customElements.get("flex-button")) {
    customElements.define("flex-button", FlexButton);
}
