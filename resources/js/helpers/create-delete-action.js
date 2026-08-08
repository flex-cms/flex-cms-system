export function createDeleteAction({
    endpoint,
    table,
    getName = (row) => row.name,
    getPayload = (row) => ({ id: row.id }),
    confirmMessage,
    successMessage,
    onDeleted,
}) {
    return {
        key: "delete",
        label: "Изтриване",
        icon: "fa-solid fa-trash",
        danger: true,

        handler: async (row) => {
            const name = getName(row);

            const message = confirmMessage
                ? confirmMessage(row, name)
                : `Сигурни ли сте, че искате да изтриете ${name}?`;

            if (!window.confirm(message)) {
                return;
            }

            await axios.post(
                typeof endpoint === "function" ? endpoint(row) : endpoint,
                getPayload(row),
            );

            if (typeof onDeleted === "function") {
                await onDeleted(row);
            } else {
                await table?.fetchData();
            }

            const notification = successMessage
                ? successMessage(row, name)
                : `${name} беше изтрит успешно.`;

            window.notify(notification, "success");
        },
    };
}
