import { html } from "lit";
import { FlexElement } from "@/components/base/flex-element";

export class FlexAlert extends FlexElement {
    static properties = {
        type: { type: String, reflect: true },
        title: { type: String },
        message: { type: String },
        icon: { type: String },
        dismissible: { type: Boolean },
        duration: { type: Number },
        open: { type: Boolean, reflect: true },
    };

    constructor() {
        super();

        this.type = "info";
        this.title = "";
        this.message = "";
        this.icon = "";
        this.dismissible = false;
        this.duration = 0;
        this.open = true;
        this.closeTimer = null;
    }

    connectedCallback() {
        super.connectedCallback();
        this.startCloseTimer();
    }

    disconnectedCallback() {
        this.clearCloseTimer();
        super.disconnectedCallback();
    }

    updated(changedProperties) {
        if (changedProperties.has("duration") || changedProperties.has("open")) {
            this.startCloseTimer();
        }
    }

    render() {
        if (!this.open) return null;

        const variants = {
            info: {
                wrapper: "border-blue-200 bg-blue-50 text-blue-900",
                icon: "text-blue-600",
                close: "text-blue-700 hover:bg-blue-100 focus:ring-blue-500",
                defaultIcon: "fa-solid fa-circle-info",
            },
            success: {
                wrapper: "border-green-200 bg-green-50 text-green-900",
                icon: "text-green-600",
                close: "text-green-700 hover:bg-green-100 focus:ring-green-500",
                defaultIcon: "fa-solid fa-circle-check",
            },
            warning: {
                wrapper: "border-amber-200 bg-amber-50 text-amber-900",
                icon: "text-amber-600",
                close: "text-amber-700 hover:bg-amber-100 focus:ring-amber-500",
                defaultIcon: "fa-solid fa-triangle-exclamation",
            },
            danger: {
                wrapper: "border-red-200 bg-red-50 text-red-900",
                icon: "text-red-600",
                close: "text-red-700 hover:bg-red-100 focus:ring-red-500",
                defaultIcon: "fa-solid fa-circle-exclamation",
            },
        };

        const variant = variants[this.normalizedType];
        const iconClasses = this.normalizedIconClasses || variant.defaultIcon;

        return html`
            <div
                class="flex w-full items-start gap-3 rounded-lg border p-4 ${variant.wrapper}"
                role=${
                    this.normalizedType === "danger" || this.normalizedType === "warning"
                        ? "alert"
                        : "status"
                }
                aria-live=${this.normalizedType === "danger" ? "assertive" : "polite"}
            >
                <i
                    class="mt-0.5 shrink-0 ${iconClasses} ${variant.icon}"
                    aria-hidden="true"
                ></i>

                <div class="min-w-0 flex-1">
                    ${
                        this.title
                            ? html`<p class="font-semibold leading-5">${this.title}</p>`
                            : null
                    }
                    ${
                        this.message
                            ? html`<p class="${this.title ? "mt-1 " : ""}text-sm leading-5">
                                  ${this.message}
                              </p>`
                            : null
                    }
                </div>

                ${
                    this.dismissible
                        ? html`
                              <button
                                  type="button"
                                  class="-m-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md
                                   transition-colors focus:outline-none focus:ring-2 ${variant.close}"
                                  aria-label="Затвори съобщението"
                                  @click=${() => this.close("button")}
                              >
                                  <i
                                      class="fa-solid fa-xmark"
                                      aria-hidden="true"
                                  ></i>
                              </button>
                          `
                        : null
                }
            </div>
        `;
    }

    get normalizedType() {
        return ["info", "success", "warning", "danger"].includes(this.type) ? this.type : "info";
    }

    get normalizedIconClasses() {
        return String(this.icon ?? "")
            .split(/\s+/)
            .filter((className) => /^(fa[srlbdkt]?|fa-[a-z0-9-]+)$/.test(className))
            .join(" ");
    }

    startCloseTimer() {
        this.clearCloseTimer();

        if (!this.open || !Number.isFinite(this.duration) || this.duration <= 0) return;

        this.closeTimer = window.setTimeout(() => this.close("timeout"), this.duration);
    }

    clearCloseTimer() {
        if (this.closeTimer === null) return;
        window.clearTimeout(this.closeTimer);
        this.closeTimer = null;
    }

    close(reason = "api") {
        if (!this.open) return;

        this.clearCloseTimer();
        this.open = false;
        this.dispatchEvent(
            new CustomEvent("flex-alert-close", {
                bubbles: true,
                composed: true,
                detail: { reason, type: this.normalizedType },
            }),
        );
    }

    show() {
        this.open = true;
    }
}

if (!customElements.get("flex-alert")) {
    customElements.define("flex-alert", FlexAlert);
}
