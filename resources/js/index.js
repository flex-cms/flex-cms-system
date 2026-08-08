import { html } from "lit";
import { loadRoutes } from "@/helpers/load-routes";

// Global Components
import "@/components/button/flex-button";
import "@/components/alert/flex-alert";
import "@/components/form/flex-form";
import "@/components/input/flex-input";
import "@/components/grid/flex-grid";
import "@/components/table/flex-table";
import "@/components/date/flex-date";
import "@/components/select/flex-select";
import "@/components/tabs/flex-tab-panel";
import "@/components/tabs/flex-tabs";
import "@/components/toggle/flex-toggle";

// Admin Views
loadRoutes([
    {
        paths: ["/admin/users", "/admin/users/index"],
        load: () => import("@/admin/views/users/index"),
    },
    {
        paths: ["/admin/users/create", "/admin/users/edit"],
        load: () => import("@/admin/views/users/form"),
    },
    {
        paths: ["/admin/users/roles", "/admin/users/roles/index"],
        load: () => import("@/admin/views/roles/index"),
    },
    {
        paths: ["/admin/users/permissions", "/admin/users/permissions/index"],
        load: () => import("@/admin/views/permissions/index"),
    },
]);
