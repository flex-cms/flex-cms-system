import settingsDefinition from "../settings.json";

const FALLBACK_CONFIG = Object.freeze({
    locale: settingsDefinition.defaults?.locale || "bg",
    timezone: settingsDefinition.defaults?.timezone || "Europe/Sofia",
    dateFormat: settingsDefinition.defaults?.date_format || "d.m.Y",
    timeFormat: settingsDefinition.defaults?.time_format || "H:i",
});

const DATE_FORMATS = new Set(
    Object.keys(settingsDefinition.date_formats || {}),
);

const TOKEN_OPTIONS = {
    d: { day: "2-digit" },
    j: { day: "numeric" },
    m: { month: "2-digit" },
    n: { month: "numeric" },
    M: { month: "short" },
    F: { month: "long" },
    Y: { year: "numeric" },
    y: { year: "2-digit" },
    D: { weekday: "short" },
    l: { weekday: "long" },
    H: { hour: "2-digit", hourCycle: "h23" },
    G: { hour: "numeric", hourCycle: "h23" },
    h: { hour: "2-digit", hourCycle: "h12" },
    g: { hour: "numeric", hourCycle: "h12" },
    i: { minute: "2-digit" },
    s: { second: "2-digit" },
    A: { hour: "numeric", hourCycle: "h12" },
    a: { hour: "numeric", hourCycle: "h12" },
};

const TOKEN_PARTS = {
    d: "day",
    j: "day",
    m: "month",
    n: "month",
    M: "month",
    F: "month",
    Y: "year",
    y: "year",
    D: "weekday",
    l: "weekday",
    H: "hour",
    G: "hour",
    h: "hour",
    g: "hour",
    i: "minute",
    s: "second",
    A: "dayPeriod",
    a: "dayPeriod",
};

export class DateFormatter {
    constructor() {
        this.config = { ...FALLBACK_CONFIG };
        this.readyPromise = null;
    }

    ready() {
        if (this.readyPromise === null) {
            this.readyPromise = this.#load();
        }

        return this.readyPromise;
    }

    reload() {
        this.readyPromise = this.#load();

        return this.readyPromise;
    }

    configure(config = {}) {
        const dateFormat = String(
            config.date_format ?? config.dateFormat ?? this.config.dateFormat,
        );

        const locale = String(config.locale ?? this.config.locale);

        const timezone = String(config.timezone ?? this.config.timezone);

        const timeFormat = String(
            config.time_format ?? config.timeFormat ?? this.config.timeFormat,
        );

        this.config = {
            locale: locale || FALLBACK_CONFIG.locale,
            timezone: this.#validTimezone(timezone)
                ? timezone
                : FALLBACK_CONFIG.timezone,
            dateFormat: DATE_FORMATS.has(dateFormat)
                ? dateFormat
                : FALLBACK_CONFIG.dateFormat,
            timeFormat: timeFormat || FALLBACK_CONFIG.timeFormat,
        };

        return this;
    }

    format(value, options = {}) {
        const date = this.#date(value);

        if (date === null) {
            return "—";
        }

        const locale = String(options.locale ?? this.config.locale);

        const timezone = String(options.timezone ?? this.config.timezone);

        const format = String(options.format ?? this.config.dateFormat);

        return this.#formatPattern(
            date,
            format,
            locale,
            this.#validTimezone(timezone) ? timezone : FALLBACK_CONFIG.timezone,
        );
    }

    formatDateTime(value, options = {}) {
        const dateFormat = String(options.dateFormat ?? this.config.dateFormat);

        const timeFormat = String(options.timeFormat ?? this.config.timeFormat);

        return this.format(value, {
            ...options,
            format: `${dateFormat} ${timeFormat}`,
        });
    }

    #formatPattern(date, pattern, locale, timezone) {
        let result = "";
        let escaped = false;

        for (const character of pattern) {
            if (escaped) {
                result += character;
                escaped = false;

                continue;
            }

            if (character === "\\") {
                escaped = true;

                continue;
            }

            if (!TOKEN_OPTIONS[character]) {
                result += character;

                continue;
            }

            result += this.#formatToken(date, character, locale, timezone);
        }

        return result;
    }

    #formatToken(date, token, locale, timezone) {
        const formatter = new Intl.DateTimeFormat(locale, {
            ...TOKEN_OPTIONS[token],
            timeZone: timezone,
        });

        const partType = TOKEN_PARTS[token];

        const value = formatter
            .formatToParts(date)
            .find((part) => part.type === partType)?.value;

        if (value === undefined) {
            return formatter.format(date);
        }

        if (token === "a") {
            return value.toLocaleLowerCase(locale);
        }

        if (token === "A") {
            return value.toLocaleUpperCase(locale);
        }

        return value;
    }

    #date(value) {
        if (value instanceof Date) {
            return Number.isNaN(value.getTime()) ? null : value;
        }

        if (value === null || value === undefined || value === "") {
            return null;
        }

        const date = new Date(value);

        return Number.isNaN(date.getTime()) ? null : date;
    }

    #validTimezone(timezone) {
        try {
            new Intl.DateTimeFormat("en", {
                timeZone: timezone,
            }).format();

            return true;
        } catch {
            return false;
        }
    }

    async #load() {
        try {
            const response = await fetch("/admin/settings/runtime/date", {
                method: "GET",
                credentials: "same-origin",
                headers: {
                    Accept: "application/json",
                    "X-Flex-Request": "XMLHttpRequest",
                },
            });

            if (!response.ok) {
                throw new Error(
                    `Date settings request failed with status ${response.status}.`,
                );
            }

            this.configure(await response.json());
        } catch (error) {
            console.warn(
                "Настройките за форматиране на дата не можаха да бъдат заредени.",
                error,
            );

            this.configure(FALLBACK_CONFIG);
        }

        document.dispatchEvent(
            new CustomEvent("flex-date-settings-ready", {
                detail: {
                    ...this.config,
                },
            }),
        );

        return this;
    }
}

export const dateFormatter = new DateFormatter();

export const formatDate = (value, options = {}) =>
    dateFormatter.format(value, options);

export const formatDateTime = (value, options = {}) =>
    dateFormatter.formatDateTime(value, options);
