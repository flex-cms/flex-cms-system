import "@admin-ui/components/builder/flex-page-builder.js";

async function initializePageContent() {
    const builder = document.querySelector("#page-content-builder");
    const saveButton = document.querySelector("#page-content-save");

    if (!builder || !saveButton || builder.dataset.initialized === "true") return;

    builder.dataset.initialized = "true";
    const pageId = Number(builder.dataset.pageId);
    if (!pageId) return;

    try {
        const response = await request(`/api/admin/pages/${pageId}/elements`);
        builder.setElements(response.data ?? []);
        saveButton.disabled = true;
    } catch (error) {
        window.flexNotify?.error(error.message);
        return;
    }

    builder.addEventListener("flex-page-builder-change", () => {
        saveButton.disabled = false;
    });

    saveButton.addEventListener("click", async () => {
        saveButton.loading = true;

        try {
            const response = await request(`/api/admin/pages/${pageId}/elements`, {
                method: "PUT",
                body: JSON.stringify({ elements: builder.value() }),
            });
            builder.setElements(response.data ?? []);
            saveButton.disabled = true;
            window.flexNotify?.success(response.message);
        } catch (error) {
            window.flexNotify?.error(error.message);
        } finally {
            saveButton.loading = false;
        }
    });
}

async function request(endpoint, options = {}) {
    const response = await fetch(endpoint, {
        method: options.method ?? "GET",
        body: options.body,
        credentials: "same-origin",
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            "X-Flex-Request": "XMLHttpRequest",
        },
    });
    const data = await response.json();
    if (!response.ok || data.success === false) throw new Error(data.message || "Заявката беше неуспешна.");
    return data;
}

document.addEventListener("turbo:load", initializePageContent);
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializePageContent, { once: true });
} else {
    initializePageContent();
}
