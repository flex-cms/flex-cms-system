import axios from "axios";
import tableManager from "./table-manager";

export default (config = {}) => {
    const baseTable = tableManager(config);

    return {
        ...baseTable,

        updateUrl: config.updateUrl || null,

        async updatePlugin(id) {
            if (!this.updateUrl || this.loading[id]) return;

            if (
                !confirm(
                    "Сигурни ли сте, че искате да обновите този плъгин до последната версия от GitHub?",
                )
            ) {
                return;
            }

            this.loading[id] = true;

            try {
                const res = await axios.post(this.updateUrl, { id: id });

                if (res.data && res.data.success) {
                    alert(
                        res.data.message ||
                            "Плъгинът беше актуализиран успешно!",
                    );
                    window.location.reload();
                } else {
                    alert(
                        res.data.message || "Възникна грешка при обновяването.",
                    );
                }
            } catch (error) {
                alert(
                    "Грешка при комуникация със сървъра по време на обновяването.",
                );
            } finally {
                this.loading[id] = false;
            }
        },
    };
};
