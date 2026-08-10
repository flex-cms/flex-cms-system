import axios from "axios";

export default (deleteUrl = "") => ({
    loading: {},

    deleteItem(id) {
        if (!deleteUrl) {
            alert("Грешка: Не е зададен URL адрес за изтриване.");
            return;
        }

        if (!confirm("Сигурни ли сте, че искате да извършите това действие?")) {
            return;
        }

        this.loading[id] = true;

        axios
            .post(deleteUrl, { id: id })
            .then((res) => {
                this.loading[id] = false;
                if (res.data && res.data.success) {
                    alert(res.data.message);
                    window.location.reload();
                } else {
                    alert(res.data.message || "Възникна грешка при изтриването.");
                }
            })
            .catch(() => {
                this.loading[id] = false;
                alert("Грешка при комуникация със сървъра.");
            });
    },
});
