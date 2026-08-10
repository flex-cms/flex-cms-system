function resolveValue(value, row, ...args) {
    return typeof value === "function" ? value(row, ...args) : value;
}

async function postAction({ endpoint, row, table, getPayload, successMessage }) {
    await axios.post(resolveValue(endpoint, row), getPayload(row));

    await table?.fetchData();

    if (successMessage) {
        window.notify(resolveValue(successMessage, row), "success");
    }
}

export function createResourceActions({
    row,
    table,

    editUrl,
    toggleEndpoint,
    trashEndpoint,
    restoreEndpoint,
    forceDeleteEndpoint,

    activeKey = "is_active",
    getName = (row) => row.name,
    getPayload = (row) => ({ id: row.id }),

    toggleSuccessMessage,
    trashSuccessMessage,
    restoreSuccessMessage,
    forceDeleteSuccessMessage,
}) {
    const name = getName(row);
    const isDeleted = Boolean(row.deleted_at);
    const isActive = Boolean(row[activeKey]);

    /*
     * Действия за записи в кошчето
     */
    if (isDeleted) {
        return [
            {
                key: "restore",
                label: "Възстановяване",
                icon: "fa-solid fa-trash-arrow-up",

                handler: async (currentRow) => {
                    await postAction({
                        endpoint: restoreEndpoint,
                        row: currentRow,
                        table,
                        getPayload,
                        successMessage:
                            restoreSuccessMessage ?? `${name} беше възстановен успешно.`,
                    });
                },
            },
            {
                divider: true,
            },
            {
                key: "force-delete",
                label: "Изтриване завинаги",
                icon: "fa-solid fa-trash",
                danger: true,

                handler: async (currentRow) => {
                    const confirmed = window.confirm(
                        `Сигурни ли сте, че искате да изтриете ${name} завинаги? Това действие не може да бъде отменено.`,
                    );

                    if (!confirmed) {
                        return;
                    }

                    await postAction({
                        endpoint: forceDeleteEndpoint,
                        row: currentRow,
                        table,
                        getPayload,
                        successMessage:
                            forceDeleteSuccessMessage ?? `${name} беше изтрит завинаги.`,
                    });
                },
            },
        ];
    }

    /*
     * Действия за нормални записи
     */
    return [
        {
            key: "edit",
            label: "Редактиране",
            icon: "fa-solid fa-pen-to-square",

            handler: (currentRow) => {
                window.location.href = resolveValue(editUrl, currentRow);
            },
        },
        {
            key: "toggle",
            label: isActive ? "Деактивиране" : "Активиране",
            icon: isActive ? "fa-solid fa-times" : "fa-solid fa-check",

            handler: async (currentRow) => {
                await postAction({
                    endpoint: toggleEndpoint,
                    row: currentRow,
                    table,
                    getPayload,

                    successMessage: toggleSuccessMessage
                        ? (row) => toggleSuccessMessage(row, isActive)
                        : isActive
                          ? `${name} беше деактивиран успешно.`
                          : `${name} беше активиран успешно.`,
                });
            },
        },
        {
            divider: true,
        },
        {
            key: "trash",
            label: "Преместване в кошчето",
            icon: "fa-solid fa-trash-can",
            danger: true,

            handler: async (currentRow) => {
                const confirmed = window.confirm(
                    `Сигурни ли сте, че искате да преместите ${name} в кошчето?`,
                );

                if (!confirmed) {
                    return;
                }

                await postAction({
                    endpoint: trashEndpoint,
                    row: currentRow,
                    table,
                    getPayload,
                    successMessage: trashSuccessMessage ?? `${name} беше преместен в кошчето.`,
                });
            },
        },
    ];
}
