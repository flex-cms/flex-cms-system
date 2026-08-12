import { dateFormatter, formatDate, formatDateTime } from "./date-formatter.js";

window.FlexDate = Object.freeze({
    ready: () => dateFormatter.ready(),
    reload: () => dateFormatter.reload(),
    format: formatDate,
    formatDateTime,
});

dateFormatter.ready();

document.addEventListener("flex-form-success", (event) => {
    if (event.target?.id !== "settings-general-form") {
        return;
    }

    dateFormatter.reload();
});

export { dateFormatter, formatDate, formatDateTime };
