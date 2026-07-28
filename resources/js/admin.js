import "../css/app.css";
import "@fortawesome/fontawesome-free/css/all.min.css";

import $ from "jquery";
window.$ = window.jQuery = $;

import Sortable from "sortablejs";
window.Sortable = Sortable;

import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";
import "dayjs/locale/bg";

dayjs.extend(relativeTime);
dayjs.locale("bg");

import axios from "./admin/axios.js";
window.axios = axios;

import "ace-builds/src-noconflict/ace.js";
import "ace-builds/src-noconflict/mode-html.js";
import "ace-builds/src-noconflict/theme-monokai.js";
import "ace-builds/src-noconflict/ext-beautify.js";
import "tom-select/dist/css/tom-select.bootstrap5.css";

import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
import { Bulgarian } from "flatpickr/dist/l10n/bg.js";

window.flatpickr = flatpickr;
window.Bulgarian = Bulgarian;

import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";

import "./admin/helpers.js";

const PAGES_GLOBAL = ["/admin"];
const PAGES_SPECIFIC = [
    "/admin/pages",
    "/admin/menus",
    "/admin/posts",
    "/admin/users",
    "/admin/profile",
    "/admin/settings",
    "/admin/navigation",
    "/admin/media",
    "/admin/email-templates",
];

const isPage = (pages) => {
    const currentPath = window.location.pathname;
    return pages.some(
        (path) => currentPath === path || currentPath.startsWith(path + "/"),
    );
};

window.Alpine = Alpine;
Alpine.plugin(collapse);

async function init() {
    if (isPage(PAGES_GLOBAL)) {
        Alpine.data(
            "loading",
            (await import("./admin/components/loading.js")).default,
        );
        Alpine.data(
            "relativeTime",
            (await import("./admin/components/relative-time.js")).default,
        );
        Alpine.data(
            "sidebar",
            (await import("./admin/components/sidebar.js")).default,
        );
        Alpine.data(
            "uiSection",
            (await import("./admin/components/ui-section.js")).default,
        );
        Alpine.data(
            "alertComponent",
            (await import("./admin/components/alert.js")).default,
        );
        Alpine.data(
            "updater",
            (await import("./admin/components/updater.js")).default,
        );
        Alpine.data(
            "deleteManager",
            (await import("./admin/components/deleteManager.js")).default,
        );
        Alpine.data(
            "tableManager",
            (await import("./admin/components/table-manager.js")).default,
        );
        Alpine.data(
            "pluginManager",
            (await import("./admin/components/plugin-manager")).default,
        );
        Alpine.data(
            "sortable",
            (await import("./admin/components/sortable.js")).default,
        );
        Alpine.data(
            "repeater",
            (await import("./admin/components/repeater.js")).default,
        );
        Alpine.data(
            "imageUpload",
            (await import("./admin/components/image-upload.js")).default,
        );
    }

    if (isPage(PAGES_SPECIFIC)) {
        Alpine.data(
            "datepicker",
            (await import("./admin/components/datepicker")).default,
        );
        Alpine.data(
            "passwordStrength",
            (await import("./admin/components/password-strength.js")).default,
        );
        Alpine.data(
            "tomSelect",
            (await import("./admin/components/select.js")).default,
        );
        Alpine.data(
            "customSelectWithInput",
            (await import("./admin/components/custom-select-with-input.js"))
                .default,
        );
        Alpine.data(
            "codeEditor",
            (await import("./admin/components/code-editor.js")).default,
        );
    }

    Alpine.start();
}

init();