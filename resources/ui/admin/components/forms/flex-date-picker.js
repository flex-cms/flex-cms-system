import { css, html, nothing } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";
import { fontAwesomeStyles } from "@admin-ui/styles/font-awesome.styles.js";

export class FlexDatePicker extends FlexElement {
    static formAssociated = true;

    static properties = {
        name: { type: String },
        label: { type: String },
        value: { type: String },

        placeholder: { type: String },
        helper: { type: String },
        error: { type: String },

        min: { type: String },
        max: { type: String },

        open: {
            type: Boolean,
            reflect: true,
        },

        required: {
            type: Boolean,
            reflect: true,
        },

        disabled: {
            type: Boolean,
            reflect: true,
        },

        readonly: {
            type: Boolean,
            reflect: true,
        },

        fullWidth: {
            type: Boolean,
            attribute: "full-width",
            reflect: true,
        },

        currentMonth: {
            state: true,
        },

        placement: {
            type: String,
            reflect: true,
        },
    };

    static styles = [
        fontAwesomeStyles,

        css`
            :host {
                position: relative;
                display: inline-block;
                width: auto;
            }

            :host([full-width]) {
                display: block;
                width: 100%;
            }

            .field {
                display: flex;
                flex-direction: column;
                gap: 0.4rem;
            }

            .label {
                color: var(--flex-color-text);
                font-size: 0.8125rem;
                font-weight: 600;
            }

            .required {
                margin-left: 0.125rem;
                color: #dc2626;
            }

            .control {
                position: relative;
            }

            .trigger {
                display: flex;
                width: 100%;
                min-height: 2.625rem;
                align-items: center;
                gap: 0.75rem;

                padding: 0.5rem 0.75rem;

                border: 1px solid var(--flex-color-border);
                border-radius: var(--flex-radius-md);

                background: var(--flex-color-surface);
                color: var(--flex-color-text);

                font: inherit;
                font-size: 0.875rem;

                text-align: left;
                cursor: pointer;

                transition:
                    border-color var(--flex-duration-fast) var(--flex-easing),
                    box-shadow var(--flex-duration-fast) var(--flex-easing);
            }

            .trigger:hover:not(:disabled) {
                border-color: var(--flex-color-text-muted);
            }

            .trigger:focus-visible,
            :host([open]) .trigger {
                outline: none;

                border-color: var(--flex-color-primary-500);

                box-shadow: 0 0 0 3px
                    color-mix(in srgb, var(--flex-color-primary-500) 15%, transparent);
            }

            .trigger:disabled {
                opacity: 0.55;
                cursor: not-allowed;
            }

            .calendar-icon {
                display: inline-flex;
                width: 1rem;
                align-items: center;
                justify-content: center;

                color: var(--flex-color-text-muted);
            }

            .value {
                min-width: 0;
                flex: 1;
            }

            .placeholder {
                color: var(--flex-color-text-muted);
            }

            .calendar {
                position: absolute;
                z-index: 1100;

                left: 0;

                width: 19rem;

                padding: 0.75rem;

                border: 1px solid var(--flex-color-border);
                border-radius: var(--flex-radius-lg);

                background: var(--flex-color-surface);

                box-shadow: 0 14px 30px rgb(0 0 0 / 12%);

                opacity: 0;
                visibility: hidden;

                transition:
                    opacity var(--flex-duration-fast) var(--flex-easing),
                    transform var(--flex-duration-fast) var(--flex-easing),
                    visibility var(--flex-duration-fast) var(--flex-easing);
            }

            :host([placement="bottom"]) .calendar {
                top: calc(100% + 0.4rem);
                bottom: auto;
            }

            :host([placement="top"]) .calendar {
                top: auto;
                bottom: calc(100% + 0.4rem);
            }

            :host([open]) .calendar {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
            }

            :host([placement="bottom"]) .calendar {
                top: calc(100% + 0.4rem);
                bottom: auto;

                transform: translateY(-0.25rem);
            }

            :host([placement="top"]) .calendar {
                top: auto;
                bottom: calc(100% + 0.4rem);

                transform: translateY(0.25rem);
            }

            :host([open]) .calendar {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
            }

            .calendar-header {
                display: flex;
                align-items: center;
                justify-content: space-between;

                margin-bottom: 0.75rem;
            }

            .month-title {
                color: var(--flex-color-text);
                font-size: 0.875rem;
                font-weight: 700;
            }

            .nav-button {
                display: inline-flex;
                width: 2rem;
                height: 2rem;

                align-items: center;
                justify-content: center;

                border: 0;
                border-radius: var(--flex-radius-md);

                background: transparent;
                color: var(--flex-color-text-muted);

                cursor: pointer;
            }

            .nav-button:hover {
                background: var(--flex-color-surface-muted);
                color: var(--flex-color-text);
            }

            .weekdays,
            .days {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 0.2rem;
            }

            .weekday {
                display: flex;
                height: 1.75rem;

                align-items: center;
                justify-content: center;

                color: var(--flex-color-text-muted);

                font-size: 0.7rem;
                font-weight: 700;
            }

            .day {
                display: inline-flex;
                aspect-ratio: 1;

                align-items: center;
                justify-content: center;

                border: 0;
                border-radius: var(--flex-radius-md);

                background: transparent;
                color: var(--flex-color-text);

                font: inherit;
                font-size: 0.8rem;

                cursor: pointer;
            }

            .day:hover:not(:disabled) {
                background: var(--flex-color-surface-muted);
            }

            .day.selected {
                background: var(--flex-color-primary-600);
                color: #ffffff;
            }

            .day.today:not(.selected) {
                color: var(--flex-color-primary-600);
                font-weight: 700;

                box-shadow: inset 0 0 0 1px var(--flex-color-primary-300);
            }

            .day:disabled {
                opacity: 0.3;
                cursor: not-allowed;
            }

            .day.empty {
                pointer-events: none;
            }

            .message {
                color: var(--flex-color-text-muted);
                font-size: 0.75rem;
            }

            :host([invalid]) .trigger {
                border-color: #dc2626;
            }

            :host([invalid]) .message {
                color: #dc2626;
            }

            .calendar-title {
                display: flex;
                align-items: center;
                gap: 0.375rem;
            }

            .month-button {
                border: 0;
                background: transparent;

                color: var(--flex-color-text);

                font: inherit;
                font-size: 0.875rem;
                font-weight: 700;

                text-transform: capitalize;
            }

            .year-select {
                min-height: 2rem;

                padding: 0.25rem 0.4rem;

                border: 1px solid var(--flex-color-border);

                border-radius: var(--flex-radius-md);

                background: var(--flex-color-surface);

                color: var(--flex-color-text);

                font: inherit;
                font-size: 0.8125rem;
                font-weight: 600;

                cursor: pointer;
            }

            .year-select:hover {
                border-color: var(--flex-color-text-muted);
            }

            .year-select:focus {
                outline: none;

                border-color: var(--flex-color-primary-500);

                box-shadow: 0 0 0 3px
                    color-mix(in srgb, var(--flex-color-primary-500) 15%, transparent);
            }
        `,
    ];

    constructor() {
        super();

        this.name = "";
        this.label = "";
        this.value = "";

        this.placeholder = "Изберете дата";
        this.helper = "";
        this.error = "";

        this.min = "";
        this.max = "";

        this.open = false;
        this.required = false;
        this.disabled = false;
        this.readonly = false;
        this.fullWidth = false;

        this.currentMonth = this.#initialMonth();

        this.internals = this.attachInternals();

        this.placement = "bottom";
    }

    connectedCallback() {
        super.connectedCallback();

        this.#syncFormValue();
        this.#validate();

        document.addEventListener("click", this.#handleOutsideClick);

        document.addEventListener("keydown", this.#handleKeydown);

        window.addEventListener("resize", this.#handleViewportChange);

        window.addEventListener("scroll", this.#handleViewportChange, true);
    }

    disconnectedCallback() {
        document.removeEventListener("click", this.#handleOutsideClick);

        document.removeEventListener("keydown", this.#handleKeydown);

        super.disconnectedCallback();

        window.removeEventListener("resize", this.#handleViewportChange);

        window.removeEventListener("scroll", this.#handleViewportChange, true);
    }

    updated(changedProperties) {
        if (changedProperties.has("value") || changedProperties.has("disabled")) {
            this.#syncFormValue();
        }

        if (changedProperties.has("value") || changedProperties.has("required")) {
            this.#validate();
        }

        if (changedProperties.has("error")) {
            this.toggleAttribute("invalid", Boolean(this.error));
        }
    }

    render() {
        return html`
            <div class="field">
                ${
                    this.label
                        ? html`
                              <div class="label">
                                  ${this.label}
                                  ${
                                      this.required
                                          ? html` <span class="required"> * </span> `
                                          : nothing
                                  }
                              </div>
                          `
                        : nothing
                }

                <div class="control">
                    <button
                        class="trigger"
                        type="button"
                        ?disabled=${this.disabled}
                        aria-haspopup="dialog"
                        aria-expanded=${this.open ? "true" : "false"}
                        @click=${this.#toggle}
                    >
                        <span
                            class="calendar-icon"
                            aria-hidden="true"
                        >
                            <i class="fa-regular fa-calendar"></i>
                        </span>

                        <span
                            class="value
                                ${this.value ? "" : "placeholder"}"
                        >
                            ${this.value ? this.#formatDisplayDate(this.value) : this.placeholder}
                        </span>
                    </button>

                    <div
                        class="calendar"
                        role="dialog"
                        aria-label="Избор на дата"
                    >
                        ${this.#renderCalendar()}
                    </div>
                </div>

                ${
                    this.error || this.helper
                        ? html`
                              <div
                                  class="message"
                                  role=${this.error ? "alert" : nothing}
                              >
                                  ${this.error || this.helper}
                              </div>
                          `
                        : nothing
                }
            </div>
        `;
    }

    #renderCalendar() {
        const year = this.currentMonth.getFullYear();

        const month = this.currentMonth.getMonth();

        const firstDay = new Date(year, month, 1);

        const daysInMonth = new Date(year, month + 1, 0).getDate();

        const offset = (firstDay.getDay() + 6) % 7;

        const cells = [];

        for (let index = 0; index < offset; index++) {
            cells.push(null);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            cells.push(new Date(year, month, day));
        }

        return html`
            <div class="calendar-header">
                <button
                    class="nav-button"
                    type="button"
                    @click=${this.#previousMonth}
                    aria-label="Предишен месец"
                >
                    <i class="fa-solid fa-chevron-left"></i>
                </button>

                <div class="calendar-title">
                    <button
                        class="month-button"
                        type="button"
                    >
                        ${this.#monthName(this.currentMonth)}
                    </button>

                    <select
                        class="year-select"
                        .value=${String(year)}
                        @change=${this.#changeYear}
                    >
                        ${this.#years().map(
                            (itemYear) => html`
                                <option
                                    value=${itemYear}
                                    ?selected=${itemYear === year}
                                >
                                    ${itemYear}
                                </option>
                            `,
                        )}
                    </select>
                </div>

                <button
                    class="nav-button"
                    type="button"
                    @click=${this.#nextMonth}
                    aria-label="Следващ месец"
                >
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

            <div class="weekdays">
                ${["Пн", "Вт", "Ср", "Чт", "Пт", "Сб", "Нд"].map(
                    (day) => html` <div class="weekday">${day}</div> `,
                )}
            </div>

            <div class="days">
                ${cells.map((date) =>
                    date ? this.#renderDay(date) : html` <span class="day empty"></span> `,
                )}
            </div>
        `;
    }

    #renderDay(date) {
        const value = this.#toDateValue(date);

        const selected = value === this.value;

        const today = value === this.#toDateValue(new Date());

        const disabled = this.#isDateDisabled(value);

        return html`
            <button
                class="
                    day
                    ${selected ? "selected" : ""}
                    ${today ? "today" : ""}
                "
                type="button"
                ?disabled=${disabled}
                @click=${() => this.#selectDate(value)}
            >
                ${date.getDate()}
            </button>
        `;
    }

    #selectDate(value) {
        if (this.disabled || this.readonly) {
            return;
        }

        this.value = value;

        this.error = "";
        this.open = false;

        this.#syncFormValue();
        this.#validate();

        this.emit("flex-change", {
            name: this.name,
            value: this.value,
        });
    }

    #toggle = async (event) => {
        event.stopPropagation();

        if (this.disabled || this.readonly) {
            return;
        }

        if (this.open) {
            this.open = false;

            return;
        }

        /*
         * Първо го отваряме, за да може
         * браузърът да изчисли реалния
         * размер на календара.
         */
        this.open = true;

        await this.updateComplete;

        this.#updatePlacement();
    };

    #previousMonth = (event) => {
        event.stopPropagation();

        const current = this.currentMonth;

        this.currentMonth = new Date(current.getFullYear(), current.getMonth() - 1, 1);
    };

    #nextMonth = (event) => {
        event.stopPropagation();

        const current = this.currentMonth;

        this.currentMonth = new Date(current.getFullYear(), current.getMonth() + 1, 1);
    };

    #initialMonth() {
        if (this.value) {
            const date = this.#parseDate(this.value);

            if (date) {
                return new Date(date.getFullYear(), date.getMonth(), 1);
            }
        }

        const now = new Date();

        return new Date(now.getFullYear(), now.getMonth(), 1);
    }

    #parseDate(value) {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
            return null;
        }

        const [year, month, day] = value.split("-").map(Number);

        const date = new Date(year, month - 1, day);

        if (
            date.getFullYear() !== year ||
            date.getMonth() !== month - 1 ||
            date.getDate() !== day
        ) {
            return null;
        }

        return date;
    }

    #toDateValue(date) {
        const year = date.getFullYear();

        const month = String(date.getMonth() + 1).padStart(2, "0");

        const day = String(date.getDate()).padStart(2, "0");

        return `${year}-${month}-${day}`;
    }

    #formatDisplayDate(value) {
        const date = this.#parseDate(value);

        if (!date) {
            return value;
        }

        return new Intl.DateTimeFormat("bg-BG", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
        }).format(date);
    }

    #monthLabel(date) {
        return new Intl.DateTimeFormat("bg-BG", {
            month: "long",
            year: "numeric",
        }).format(date);
    }

    #isDateDisabled(value) {
        if (this.min && value < this.min) {
            return true;
        }

        if (this.max && value > this.max) {
            return true;
        }

        return false;
    }

    #syncFormValue() {
        this.internals.setFormValue(this.disabled || !this.value ? null : this.value);
    }

    #validate(showError = false) {
        let message = "";

        if (this.required && !this.value) {
            message = "Моля, изберете дата.";
        } else if (this.value && !this.#parseDate(this.value)) {
            message = "Невалидна дата.";
        } else if (this.min && this.value && this.value < this.min) {
            message = `Минималната дата е ${this.#formatDisplayDate(this.min)}.`;
        } else if (this.max && this.value && this.value > this.max) {
            message = `Максималната дата е ${this.#formatDisplayDate(this.max)}.`;
        }

        if (message) {
            this.internals.setValidity(
                {
                    customError: true,
                },
                message,
            );

            if (showError) {
                this.error = message;
            }

            return false;
        }

        this.internals.setValidity({});

        if (showError) {
            this.error = "";
        }

        return true;
    }

    #updatePlacement() {
        const trigger = this.renderRoot?.querySelector(".trigger");

        const calendar = this.renderRoot?.querySelector(".calendar");

        if (!trigger || !calendar) {
            return;
        }

        const triggerRect = trigger.getBoundingClientRect();

        /*
         * Ако календарът още е скрит,
         * offsetHeight може да е 0.
         *
         * Затова използваме приблизителна
         * височина като fallback.
         */
        const calendarHeight = calendar.offsetHeight || 320;

        const gap = 8;

        const spaceBelow = window.innerHeight - triggerRect.bottom;

        const spaceAbove = triggerRect.top;

        /*
         * Предпочитаме bottom.
         *
         * Ако няма достатъчно място отдолу
         * и отгоре има повече място,
         * отваряме нагоре.
         */
        if (spaceBelow < calendarHeight + gap && spaceAbove > spaceBelow) {
            this.placement = "top";

            return;
        }

        this.placement = "bottom";
    }

    #years() {
        const currentYear = new Date().getFullYear();

        const minYear = this.min ? Number(this.min.slice(0, 4)) : currentYear - 100;

        const maxYear = this.max ? Number(this.max.slice(0, 4)) : currentYear + 20;

        const years = [];

        for (let year = minYear; year <= maxYear; year++) {
            years.push(year);
        }

        return years;
    }

    #changeYear = (event) => {
        const year = Number(event.target.value);

        if (!Number.isInteger(year)) {
            return;
        }

        this.currentMonth = new Date(year, this.currentMonth.getMonth(), 1);
    };

    #monthName(date) {
        return new Intl.DateTimeFormat("bg-BG", {
            month: "long",
        }).format(date);
    }

    checkValidity() {
        return this.#validate();
    }

    reportValidity() {
        const valid = this.#validate(true);

        if (!valid) {
            this.renderRoot?.querySelector(".trigger")?.focus();
        }

        return valid;
    }

    formResetCallback() {
        this.value = "";
        this.error = "";
        this.open = false;

        this.currentMonth = this.#initialMonth();

        this.#syncFormValue();
        this.#validate();
    }

    formDisabledCallback(disabled) {
        this.disabled = disabled;
    }

    #handleOutsideClick = (event) => {
        if (!this.contains(event.target)) {
            this.open = false;
        }
    };

    #handleKeydown = (event) => {
        if (event.key === "Escape" && this.open) {
            this.open = false;
        }
    };

    #handleViewportChange = () => {
        if (!this.open) {
            return;
        }

        this.#updatePlacement();
    };
}

FlexDatePicker.register("flex-date-picker");
