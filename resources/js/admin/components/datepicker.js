import flatpickr from "flatpickr";
import { Bulgarian } from "flatpickr/dist/l10n/bg.js";
import "flatpickr/dist/flatpickr.min.css";

export default function initDatepicker() {
    return {
        init() {
            flatpickr(this.$el, {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                time_24hr: true,
                locale: Bulgarian,
            });
        },
    };
}
