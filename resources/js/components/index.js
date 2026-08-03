import { html } from "lit";
import { loadRoutes } from "../helpers/load-routes.js";

// Global Components
import "./button/flex-button.js";
import "./alert/flex-alert.js";
import "./form/flex-form.js";
import "./input/flex-input.js";
import "./grid/flex-grid.js";
import "./table/flex-table.js";
import "./date/flex-date.js";
import "./select/flex-select.js";

// Admin Views
loadRoutes([
    {
        paths: ["/admin/users", "/admin/users/index"],
        load: () => import("../admin/views/users/index.js"),
    },
]);