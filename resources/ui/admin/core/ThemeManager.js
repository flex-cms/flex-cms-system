const STORAGE_KEY = "flex.admin.theme";

const THEME_LIGHT = "light";
const THEME_DARK = "dark";
const THEME_SYSTEM = "system";

const SUPPORTED_PREFERENCES = new Set([THEME_LIGHT, THEME_DARK, THEME_SYSTEM]);

export class ThemeManager {
    #document;

    #storage;

    #systemTheme;

    #lifecycleController = null;

    #preference = THEME_SYSTEM;

    #currentTheme = THEME_LIGHT;

    constructor({
        documentRef = document,
        storage = window.localStorage,
        matchMedia = window.matchMedia.bind(window),
    } = {}) {
        this.#document = documentRef;
        this.#storage = storage;
        this.#systemTheme = matchMedia("(prefers-color-scheme: dark)");
    }

    start() {
        this.stop();

        this.#lifecycleController = new AbortController();

        this.#preference =
            this.#readStoredPreference() ?? this.#readDocumentPreference() ?? THEME_SYSTEM;

        this.#applyTheme();

        this.#systemTheme.addEventListener("change", this.#handleSystemThemeChange, {
            signal: this.#lifecycleController.signal,
        });

        window.addEventListener("storage", this.#handleStorageChange, {
            signal: this.#lifecycleController.signal,
        });

        return this;
    }

    stop() {
        this.#lifecycleController?.abort();
        this.#lifecycleController = null;

        return this;
    }

    preference() {
        return this.#preference;
    }

    currentTheme() {
        return this.#currentTheme;
    }

    isDark() {
        return this.#currentTheme === THEME_DARK;
    }

    setPreference(preference) {
        if (!SUPPORTED_PREFERENCES.has(preference)) {
            throw new TypeError(`Unsupported theme preference [${preference}].`);
        }

        this.#preference = preference;
        this.#writeStoredPreference(preference);
        this.#applyTheme();

        return this;
    }

    toggle() {
        return this.setPreference(this.isDark() ? THEME_LIGHT : THEME_DARK);
    }

    useSystemTheme() {
        return this.setPreference(THEME_SYSTEM);
    }

    #resolvedTheme() {
        if (this.#preference === THEME_SYSTEM) {
            return this.#systemTheme.matches ? THEME_DARK : THEME_LIGHT;
        }

        return this.#preference;
    }

    #applyTheme() {
        const theme = this.#resolvedTheme();
        const root = this.#document.documentElement;

        this.#currentTheme = theme;

        root.dataset.theme = theme;
        root.dataset.themePreference = this.#preference;

        root.style.colorScheme = theme;

        this.#document.dispatchEvent(
            new CustomEvent("flex-theme-change", {
                detail: {
                    theme,
                    preference: this.#preference,
                    isDark: theme === THEME_DARK,
                },
            }),
        );
    }

    #readDocumentPreference() {
        const preference = this.#document.documentElement.dataset.themePreference;

        return SUPPORTED_PREFERENCES.has(preference) ? preference : null;
    }

    #readStoredPreference() {
        try {
            const preference = this.#storage.getItem(STORAGE_KEY);

            return SUPPORTED_PREFERENCES.has(preference) ? preference : null;
        } catch {
            return null;
        }
    }

    #writeStoredPreference(preference) {
        try {
            this.#storage.setItem(STORAGE_KEY, preference);
        } catch {
            /*
             * localStorage може да бъде недостъпен
             * в private режим или при browser policy.
             * Темата продължава да работи само
             * за текущата страница.
             */
        }
    }

    #handleSystemThemeChange = () => {
        if (this.#preference === THEME_SYSTEM) {
            this.#applyTheme();
        }
    };

    #handleStorageChange = (event) => {
        if (event.key !== STORAGE_KEY || !SUPPORTED_PREFERENCES.has(event.newValue)) {
            return;
        }

        this.#preference = event.newValue;
        this.#applyTheme();
    };
}

export const themeManager = new ThemeManager();

export { STORAGE_KEY as FLEX_THEME_STORAGE_KEY, THEME_LIGHT, THEME_DARK, THEME_SYSTEM };
