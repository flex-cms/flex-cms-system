export default () => ({
    password: "",
    password_confirmation: "",
    score: -1,
    showPassword: false,

    init() {
        this.$watch("password", (value) => {
            this.evaluateStrength(value);
        });
    },

    evaluateStrength(val) {
        if (!val || val === "") {
            this.score = -1;
            return;
        }

        let s = 0;
        if (val.length >= 8) s++;
        if (/[a-z]/.test(val) && /[A-Z]/.test(val)) s++;
        if (/\d/.test(val)) s++;
        if (/[^A-Za-z0-9]/.test(val)) s++;

        this.score = s;
    },

    togglePassword() {
        this.showPassword = !this.showPassword;
    },

    generatePassword() {
        const length = 14;
        const charset =
            "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!#$%&()*+<=>?@[]^_{}|";
        let generated = "";

        const arr = new Uint32Array(length);
        if (window.crypto && window.crypto.getRandomValues) {
            window.crypto.getRandomValues(arr);
            for (let i = 0; i < length; i++) {
                generated += charset[arr[i] % charset.length];
            }
        } else {
            for (let i = 0; i < length; i++) {
                generated += charset[Math.floor(Math.random() * charset.length)];
            }
        }

        this.password = generated;
        this.password_confirmation = generated;
    },
});
