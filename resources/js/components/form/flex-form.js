import { html } from "lit";
import { FlexElement } from "../base/flex-element.js";

export class FlexForm extends FlexElement {
    static properties = {
        ajax: { type: Boolean, reflect: true },
        resetOnSuccess: { type: Boolean, attribute: "reset-on-success" },
        successMessage: { type: String, attribute: "success-message" },
        errorMessage: { type: String, attribute: "error-message" },
        showAlerts: { type: Boolean, attribute: "show-alerts" },
        submitting: { type: Boolean, reflect: true },
        formSelector: { type: String, attribute: "form-selector" },
        formClass: { type: String, attribute: "form-class" },
        action: { type: String },
        method: { type: String },
        enctype: { type: String },
        novalidate: { type: Boolean },
    };

    constructor() {
        super();
        this.ajax = false;
        this.resetOnSuccess = false;
        this.successMessage = "Промените са запазени успешно.";
        this.errorMessage = "Възникна грешка при изпращането на формата.";
        this.showAlerts = true;
        this.submitting = false;
        this.formSelector = "";
        this.formClass = "";
        this.action = "";
        this.method = "POST";
        this.enctype = "";
        this.novalidate = false;
        this.form = null;
        this.alert = null;
        this.handleSubmit = this.handleSubmit.bind(this);
    }

    connectedCallback() {
        super.connectedCallback();
        this.ensureFormElement();
        queueMicrotask(() => this.bindForm());
    }

    disconnectedCallback() {
        this.unbindForm();
        super.disconnectedCallback();
    }

    createRenderRoot() {
        return this; // Връщаме Light DOM, за да функционират естествено браузърните форми и слотовете
    }

    /**
     * Ако няма съществуващ <form>, обвива съдържанието в новосъздаден <form> елемент.
     */
    ensureFormElement() {
        let existingForm = this.formSelector
            ? this.querySelector(this.formSelector)
            : this.querySelector(":scope > form");

        if (!existingForm) {
            existingForm = document.createElement("form");

            // Преместваме всички съществуващи деца вътре във формата
            while (this.firstChild) {
                existingForm.appendChild(this.firstChild);
            }

            this.appendChild(existingForm);
        }

        // Прехвърляме основните атрибути върху реалния <form>
        if (this.id) existingForm.id = `${this.id}-internal-form`;
        if (this.action) existingForm.action = this.action;
        if (this.method) existingForm.method = this.method;
        if (this.enctype) existingForm.enctype = this.enctype;
        if (this.novalidate) existingForm.novalidate = true;
        if (this.formClass) existingForm.className = this.formClass;

        this.form = existingForm;
    }

    bindForm() {
        this.unbindForm();
        if (!this.form) {
            this.form = this.formSelector
                ? this.querySelector(this.formSelector)
                : this.querySelector(":scope > form");
        }

        if (!this.form) {
            console.warn(
                "<flex-form> не можа да открие или създаде <form> елемент.",
            );
            return;
        }

        this.form.addEventListener("submit", this.handleSubmit);
    }

    unbindForm() {
        this.form?.removeEventListener("submit", this.handleSubmit);
        this.form = null;
    }

    async handleSubmit(event) {
        if (!this.ajax) return;

        event.preventDefault();
        if (!this.form || this.submitting) return;

        if (!this.form.checkValidity()) {
            this.form.reportValidity();
            this.emit("flex-form-invalid", { form: this.form });
            return;
        }

        const axiosClient = globalThis.axios;
        if (!axiosClient?.request) {
            const error = new Error("Axios не е наличен в window.axios.");
            this.finishWithError(error);
            return;
        }

        this.submitting = true;
        this.removeAlert();
        this.emit("flex-form-submit", {
            form: this.form,
            submitter: event.submitter,
        });

        try {
            const method = (this.form.method || "GET").toLowerCase();
            const formData = new FormData(this.form);
            const response = await axiosClient.request({
                url: this.form.action || window.location.href,
                method,
                ...(method === "get"
                    ? { params: formData }
                    : { data: formData }),
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });

            if (this.resetOnSuccess) this.form.reset();
            this.showAlert(
                "success",
                this.responseMessage(response) || this.successMessage,
            );
            this.form.dispatchEvent(
                new CustomEvent("flex-submit-end", {
                    bubbles: true,
                    detail: { response },
                }),
            );
            this.emit("flex-form-success", { form: this.form, response });
        } catch (error) {
            this.finishWithError(error);
        } finally {
            this.submitting = false;
        }
    }

    finishWithError(error) {
        this.submitting = false;
        const message = error?.response?.data?.message || this.errorMessage;
        this.showAlert("danger", message);
        this.form?.dispatchEvent(
            new CustomEvent("flex-submit-error", {
                bubbles: true,
                detail: { error },
            }),
        );
        this.emit("flex-form-error", { form: this.form, error });
    }

    responseMessage(response) {
        return typeof response?.data?.message === "string"
            ? response.data.message
            : "";
    }

    showAlert(type, message) {
        if (!this.showAlerts || !this.form) return;
        this.removeAlert();
        this.alert = document.createElement("flex-alert");
        this.alert.type = type;
        this.alert.message = message;
        this.alert.dismissible = true;
        this.form.before(this.alert);
    }

    removeAlert() {
        this.alert?.remove();
        this.alert = null;
    }

    emit(name, detail) {
        this.dispatchEvent(
            new CustomEvent(name, { bubbles: true, composed: true, detail }),
        );
    }

    submit() {
        this.form?.requestSubmit();
    }

    reset() {
        this.form?.reset();
        this.removeAlert();
    }
}

if (!customElements.get("flex-form")) {
    customElements.define("flex-form", FlexForm);
}
