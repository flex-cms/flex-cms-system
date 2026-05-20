import axios from "axios";

export default () => ({
    updating: false,
    message: "",
    error: "",

    startUpdate() {
        this.updating = true;
        this.error = "";
        this.message = "Сваляне на архива от GitHub и обновяване...";

        axios
            .post("/admin/update")
            .then((res) => {
                this.updating = false;
                if (res.data.success) {
                    alert(res.data.message);
                    window.location.reload();
                } else {
                    this.error = res.data.message;
                }
            })
            .catch(() => {
                this.updating = false;
                this.error = "Грешка при комуникация със сървъра.";
            });
    },
});
