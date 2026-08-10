import "@fortawesome/fontawesome-free/css/all.min.css";

import { themeManager } from "@admin-ui/core/ThemeManager.js";
import { turboManager } from "@admin-ui/core/TurboManager.js";

import "@admin-ui/components/navigation/flex-admin-header.js";
import "@admin-ui/layouts/flex-admin-shell.js";

import "@admin-ui/components/feedback/flex-theme-toggle.js";
import "@admin-ui/components/navigation/flex-sidebar.js";
import "@admin-ui/components/navigation/flex-nav-group.js";
import "@admin-ui/components/navigation/flex-nav-item.js";
import "@admin-ui/components/actions/flex-button.js";
import "@admin-ui/components/forms/flex-dropdown.js";
import "@admin-ui/components/forms/flex-form.js";

const FLEX_ADMIN_STATE = Symbol.for("flex.admin.ui.state");

function dispatchReadyEvent() {
    document.dispatchEvent(
        new CustomEvent("flex-admin-ready", {
            detail: {
                url: window.location.href,
                version: "1.0.0",
            },
            bubbles: true,
            composed: true,
        }),
    );
}

function initializeAdminUI() {
    if (window[FLEX_ADMIN_STATE]) {
        return window[FLEX_ADMIN_STATE];
    }

    themeManager.start();
    turboManager.start();

    document.documentElement.dataset.flexAdminUi = "ready";

    const api = Object.freeze({
        version: "1.0.0",

        theme: {
            current: () => themeManager.currentTheme(),

            preference: () => themeManager.preference(),

            toggle: () => themeManager.toggle(),

            set: (preference) => themeManager.setPreference(preference),

            useSystem: () => themeManager.useSystemTheme(),
        },

        navigation: {
            visit: (url, options = {}) => turboManager.visit(url, options),

            clearCache: () => turboManager.clearCache(),
        },
    });

    window[FLEX_ADMIN_STATE] = api;

    window.FlexAdminUI = api;

    document.addEventListener("turbo:load", dispatchReadyEvent);

    if (document.readyState === "interactive" || document.readyState === "complete") {
        queueMicrotask(dispatchReadyEvent);
    } else {
        document.addEventListener("DOMContentLoaded", dispatchReadyEvent, {
            once: true,
        });
    }

    return api;
}

initializeAdminUI();
