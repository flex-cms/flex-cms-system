import { css, html, LitElement } from "lit";

export class FlexGrid extends LitElement {
    static properties = {
        cols: { type: Number, reflect: true },
        smCols: { type: Number, attribute: "sm-cols", reflect: true },
        mdCols: { type: Number, attribute: "md-cols", reflect: true },
        lgCols: { type: Number, attribute: "lg-cols", reflect: true },
        xlCols: { type: Number, attribute: "xl-cols", reflect: true },
        gap: { type: String, reflect: true },
        gapX: { type: String, attribute: "gap-x", reflect: true },
        gapY: { type: String, attribute: "gap-y", reflect: true },
    };

    static styles = css`
        :host {
            display: grid;
            box-sizing: border-box;
            grid-template-columns: repeat(
                var(--flex-grid-cols, 1),
                minmax(0, 1fr)
            );
            column-gap: var(--flex-grid-gap-x, var(--flex-grid-gap, 0));
            row-gap: var(--flex-grid-gap-y, var(--flex-grid-gap, 0));
        }

        slot {
            display: contents;
        }

        @media (min-width: 640px) {
            :host {
                grid-template-columns: repeat(
                    var(--flex-grid-sm-cols, var(--flex-grid-cols, 1)),
                    minmax(0, 1fr)
                );
            }
        }

        @media (min-width: 768px) {
            :host {
                grid-template-columns: repeat(
                    var(
                        --flex-grid-md-cols,
                        var(--flex-grid-sm-cols, var(--flex-grid-cols, 1))
                    ),
                    minmax(0, 1fr)
                );
            }
        }

        @media (min-width: 1024px) {
            :host {
                grid-template-columns: repeat(
                    var(
                        --flex-grid-lg-cols,
                        var(
                            --flex-grid-md-cols,
                            var(--flex-grid-sm-cols, var(--flex-grid-cols, 1))
                        )
                    ),
                    minmax(0, 1fr)
                );
            }
        }

        @media (min-width: 1280px) {
            :host {
                grid-template-columns: repeat(
                    var(
                        --flex-grid-xl-cols,
                        var(
                            --flex-grid-lg-cols,
                            var(
                                --flex-grid-md-cols,
                                var(
                                    --flex-grid-sm-cols,
                                    var(--flex-grid-cols, 1)
                                )
                            )
                        )
                    ),
                    minmax(0, 1fr)
                );
            }
        }
    `;

    constructor() {
        super();
        this.cols = 1;
        this.smCols = 0;
        this.mdCols = 0;
        this.lgCols = 0;
        this.xlCols = 0;
        this.gap = "0";
        this.gapX = "";
        this.gapY = "";
    }

    updated() {
        this.setCssVariable(
            "--flex-grid-cols",
            this.normalizeColumns(this.cols, 1),
        );
        this.setResponsiveColumns("--flex-grid-sm-cols", this.smCols);
        this.setResponsiveColumns("--flex-grid-md-cols", this.mdCols);
        this.setResponsiveColumns("--flex-grid-lg-cols", this.lgCols);
        this.setResponsiveColumns("--flex-grid-xl-cols", this.xlCols);
        this.setCssVariable("--flex-grid-gap", this.normalizeSpacing(this.gap));
        this.setOptionalSpacing("--flex-grid-gap-x", this.gapX);
        this.setOptionalSpacing("--flex-grid-gap-y", this.gapY);
    }

    render() {
        return html`<slot></slot>`;
    }

    normalizeColumns(value, fallback = 0) {
        const columns = Number.parseInt(value, 10);
        return Number.isFinite(columns) && columns >= 1 && columns <= 12
            ? columns
            : fallback;
    }

    normalizeSpacing(value) {
        const rawValue = String(value ?? "").trim();
        if (!rawValue) return "0";

        const tailwindScale = {
            0: "0",
            0.5: "0.125rem",
            1: "0.25rem",
            1.5: "0.375rem",
            2: "0.5rem",
            2.5: "0.625rem",
            3: "0.75rem",
            3.5: "0.875rem",
            4: "1rem",
            5: "1.25rem",
            6: "1.5rem",
            7: "1.75rem",
            8: "2rem",
            9: "2.25rem",
            10: "2.5rem",
            11: "2.75rem",
            12: "3rem",
            14: "3.5rem",
            16: "4rem",
            20: "5rem",
            24: "6rem",
        };

        if (tailwindScale[rawValue]) return tailwindScale[rawValue];
        if (/^-?\d*\.?\d+(px|rem|em|%|vw|vh)$/.test(rawValue)) return rawValue;
        return "0";
    }

    setCssVariable(name, value) {
        this.style.setProperty(name, String(value));
    }

    setResponsiveColumns(name, value) {
        const columns = this.normalizeColumns(value);
        columns
            ? this.setCssVariable(name, columns)
            : this.style.removeProperty(name);
    }

    setOptionalSpacing(name, value) {
        String(value ?? "").trim()
            ? this.setCssVariable(name, this.normalizeSpacing(value))
            : this.style.removeProperty(name);
    }
}

if (!customElements.get("flex-grid")) {
    customElements.define("flex-grid", FlexGrid);
}
