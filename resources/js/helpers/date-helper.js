import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime.js";
import utc from "dayjs/plugin/utc.js";
import timezone from "dayjs/plugin/timezone.js";
import "dayjs/locale/bg.js";

dayjs.extend(relativeTime);
dayjs.extend(utc);
dayjs.extend(timezone);

export class DateHelper {
    static get settings() {
        const config = window.AppConfig || {};
        return {
            locale: config.locale || "bg",
            timezone: config.timezone || "Europe/Sofia",
            dateFormat: this.convertPhpToDayjsFormat(
                config.dateFormat || "d.m.Y",
            ),
            timeFormat: "HH:mm",
        };
    }

    static convertPhpToDayjsFormat(phpFormat) {
        const replacements = {
            d: "DD",
            m: "MM",
            Y: "YYYY",
            y: "YY",
            H: "HH",
            i: "mm",
            s: "ss",
        };
        return phpFormat.replace(
            /[dmyHis]/g,
            (match) => replacements[match] || match,
        );
    }

    static createDayjs(date = null) {
        const settings = this.settings;

        let d = date ? dayjs(date) : dayjs();

        if (settings.timezone) {
            d = d.tz(settings.timezone);
        }

        return d.locale(settings.locale);
    }

    static format(date = null, includeTime = false, customFormat = null) {
        const d = this.createDayjs(date);

        if (!d.isValid()) return "—";

        if (customFormat) {
            return d.format(customFormat);
        }

        const settings = this.settings;
        let formatStr = settings.dateFormat;
        if (includeTime) {
            formatStr += ` : ${settings.timeFormat}`;
        }

        return d.format(formatStr);
    }

    static fromNow(date = null) {
        const d = this.createDayjs(date);

        if (!d.isValid()) return "—";

        return d.fromNow();
    }

    static iso(date = null) {
        const d = this.createDayjs(date);
        return d.isValid() ? d.toISOString() : null;
    }
}