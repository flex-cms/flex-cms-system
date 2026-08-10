export function createToggleAction({
    endpoint,
    table,
    activeKey = "is_active",
    getPayload = (row) => ({ id: row.id }),
    activeLabel = "Деактивирай",
    inactiveLabel = "Активирай",
    activeIcon = "fa-solid fa-times",
    inactiveIcon = "fa-solid fa-check",
    successMessage,
    onToggled,
}) {
    return (row) => {
        const isActive = Boolean(row[activeKey]);

        return {
            key: "toggle",
            label: isActive ? activeLabel : inactiveLabel,
            icon: isActive ? activeIcon : inactiveIcon,

            handler: async (currentRow) => {
                await axios.post(
                    typeof endpoint === "function" ? endpoint(currentRow) : endpoint,
                    getPayload(currentRow),
                );

                if (typeof onToggled === "function") {
                    await onToggled(currentRow);
                } else {
                    await table?.fetchData();
                }

                const notification = successMessage
                    ? successMessage(currentRow, isActive)
                    : isActive
                      ? "Елементът беше деактивиран успешно."
                      : "Елементът беше активиран успешно.";

                window.notify(notification, "success");
            },
        };
    };
}
