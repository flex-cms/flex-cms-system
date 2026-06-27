import dayjs from "dayjs";

export default (date) => ({
    date,
    text: "",
    interval: null,

    init() {
        this.update();

        this.interval = setInterval(() => {
            this.update();
        }, 60000);
    },

    update() {
        this.text = dayjs(this.date).fromNow();
    },

    destroy() {
        clearInterval(this.interval);
    },
});
