export default (config = {}) => ({
    previewUrl: null,
    currentImage: config.currentImage ?? null,
    fallbackImage: config.fallbackImage ?? "/assets/images/no-image.png",
    inputId: config.inputId ?? null,
    removeInputId: config.removeInputId ?? null,
    removed: false,

    selectFile() {
        const input = document.getElementById(this.inputId);

        if (input) {
            input.click();
        }
    },

    handleFile(event) {
        const file = event.target.files?.[0];

        if (!file) {
            return;
        }

        this.revokePreview();

        this.previewUrl = URL.createObjectURL(file);
        this.removed = false;
        this.setRemoveValue("0");
    },

    removeImage() {
        const input = document.getElementById(this.inputId);

        if (input) {
            input.value = "";
        }

        this.revokePreview();

        this.previewUrl = null;
        this.removed = true;
        this.setRemoveValue("1");
    },

    restoreImage() {
        this.removed = false;
        this.setRemoveValue("0");
    },

    cancelNewFile() {
        const input = document.getElementById(this.inputId);

        if (input) {
            input.value = "";
        }

        this.revokePreview();
        this.previewUrl = null;
    },

    setRemoveValue(value) {
        const input = document.getElementById(this.removeInputId);

        if (input) {
            input.value = value;
        }
    },

    revokePreview() {
        if (this.previewUrl) {
            URL.revokeObjectURL(this.previewUrl);
        }
    },

    destroy() {
        this.revokePreview();
    },
});
