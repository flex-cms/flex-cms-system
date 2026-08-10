import { Notyf } from "notyf";
import "notyf/notyf.min.css";

class NotificationManager {
    constructor() {
        this.instance = null;
    }

    start() {
        this.instance?.dismissAll?.();

        this.instance = new Notyf({
            duration: 3500,
            position: {
                x: "left",
                y: "bottom",
            },
            dismissible: true,
            ripple: false,

            types: [
                {
                    type: "success",
                    background: "#15803d",
                    icon: {
                        className: "fa-solid fa-check",
                        tagName: "i",
                        color: "#ffffff",
                    },
                },
                {
                    type: "error",
                    background: "#b91c1c",
                    icon: {
                        className: "fa-solid fa-xmark",
                        tagName: "i",
                        color: "#ffffff",
                    },
                },
            ],
        });

        window.flexNotify = this.instance;

        return this.instance;
    }

    success(message) {
        this.instance?.success(message);
    }

    error(message) {
        this.instance?.error(message);
    }

    dismissAll() {
        this.instance?.dismissAll?.();
    }
}

export const notificationManager = new NotificationManager();
