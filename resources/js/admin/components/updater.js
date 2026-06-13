export default function updater() {
    return {
        isUpdating: false,
        progress: 0,
        message: "",
        error: null,

        async startUpdate() {
            if (this.isUpdating) return;

            this.isUpdating = true;
            this.message = "Изтегляне на актуализацията...";
            this.progress = 10;

            try {
                const response = await fetch("/admin/update/process", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                });

                const data = await response.json();

                if (data.success) {
                    this.progress = 100;
                    this.message = "Актуализацията е успешна! Презареждане...";
                    setTimeout(() => location.reload(), 2000);
                } else {
                    throw new Error(
                        data.message || "Възникна грешка при ъпдейта.",
                    );
                }
            } catch (err) {
                this.error = err.message;
                this.isUpdating = false;
                this.message = "";
            }
        },
    };
}
