export default (config = {}) => ({
    editor: null,
    wrap: config.wrap !== false,

    init() {
        ace.config.set("workerPath", "https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.3/");
        this.editor = ace.edit(this.$refs.editorContainer);

        const isDark = document.documentElement.classList.contains("dark");
        this.editor.setTheme(isDark ? "ace/theme/monokai" : "ace/theme/chrome");
        this.editor.session.setMode(`ace/mode/${config.mode || "html"}`);

        this.editor.commands.addCommand({
            name: "formatCode",
            bindKey: { win: "Ctrl-Shift-F", mac: "Command-Shift-F" },
            exec: (editor) => {
                this.format();
            },
            readOnly: false,
        });

        this.editor.commands.addCommand({
            name: "minifyCode",
            bindKey: { win: "Ctrl-Shift-M", mac: "Command-Shift-M" },
            exec: (editor) => this.minify(),
        });

        this.editor.setOptions({
            fontSize: "16px",
            fontFamily: "'JetBrains Mono', 'Fira Code', 'Monaco', monospace",
            wrap: this.wrap,
            showPrintMargin: false,
            enableBasicAutocompletion: true,
            enableLiveAutocompletion: true,
        });

        const initialValue = this.$refs.valueStore.value;
        this.editor.setValue(initialValue, 1);
        this.$refs.textarea.value = initialValue;

        this.editor.session.on("change", () => {
            this.$refs.textarea.value = this.editor.getValue();
        });
    },

    toggleWrap() {
        this.wrap = !this.wrap;
        this.editor.setOption("wrap", this.wrap);
    },

    format() {
        try {
            const beautify = ace.require("ace/ext/beautify");
            beautify.beautify(this.editor.session);
        } catch (e) {
            console.error("Beautify разширението не е заредено правилно", e);
        }
    },

    minify() {
        let code = this.editor.getValue();

        let minified = code
            .replace(/\/\*[\s\S]*?\*\/|([^\\:]|^)\/\/.*$/gm, "")
            .replace(/\s+/g, " ")
            .replace(/\s*([{}|:;,])\s*/g, "$1")
            .trim();

        this.editor.setValue(minified, 1);
    },
});
