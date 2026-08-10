import Swal from "sweetalert2";

window.notify = function (message, type = "error", onHidden = null) {
    const isSuccess = type === "success";

    Swal.fire({
        text: message,
        icon: type,
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didDestroy: () => {
            if (typeof onHidden === "function") {
                onHidden();
            }
        },
    });
};

window.removeRowWithAnimation = function (element, duration = 300) {
    const row = element.closest("tr");
    if (!row) return;

    const tbody = row.closest("tbody");
    row.style.transition = `all ${duration}ms ease`;
    row.style.opacity = "0";

    setTimeout(() => {
        row.remove();
        if (tbody && tbody.querySelectorAll("tr").length === 0) {
            window.location.reload();
        }
    }, duration);
};

window.checkTableEmptyAndReload = function (tbody) {
    if (tbody && tbody.querySelectorAll("tr").length === 0) {
        window.location.reload();
    }
};

window.removeRowWithAnimation = function (element, duration = 300) {
    const row = element.closest("tr");
    if (!row) return;

    const tbody = row.closest("tbody");

    row.style.transition = `all ${duration}ms ease`;
    row.style.opacity = "0";

    setTimeout(() => {
        row.remove();
        window.checkTableEmptyAndReload(tbody);
    }, duration);
};
