import { css, html } from "lit";

import FlexElement from "@admin-ui/core/FlexElement.js";
import { fontAwesomeStyles } from "@admin-ui/styles/font-awesome.styles.js";

export class FlexPageBuilder extends FlexElement {
    static properties = {
        elements: { attribute: false },
        selectedIndex: { type: Number, state: true },
        loading: { type: Boolean, reflect: true },
    };

    static styles = [
        fontAwesomeStyles,
        css`
            :host { display: block; }
            .layout { display: grid; gap: 1rem; grid-template-columns: 14rem minmax(0, 1fr); }
            .panel { border: 1px solid var(--flex-color-border); border-radius: var(--flex-radius-lg); background: var(--flex-color-surface); }
            .header { padding: 1rem; border-bottom: 1px solid var(--flex-color-border); font-weight: 600; }
            .library, .canvas { padding: 1rem; }
            .library { display: grid; gap: .5rem; }
            button { border: 1px solid var(--flex-color-border); border-radius: var(--flex-radius-md); background: var(--flex-color-surface); color: var(--flex-color-text); padding: .65rem .75rem; cursor: pointer; text-align: left; }
            button:hover { background: var(--flex-color-surface-muted); }
            .element { display: flex; align-items: center; gap: .5rem; margin-bottom: .5rem; padding: .75rem; border: 1px solid var(--flex-color-border); border-radius: var(--flex-radius-md); }
            .element.selected { border-color: var(--flex-color-primary-500); box-shadow: 0 0 0 2px color-mix(in srgb, var(--flex-color-primary-500) 12%, transparent); }
            .element-main { min-width: 0; flex: 1; cursor: pointer; }
            .type { font-weight: 600; }
            .meta, .empty { color: var(--flex-color-text-muted); font-size: .75rem; }
            .actions { display: flex; gap: .35rem; }
            .actions button { padding: .4rem .55rem; }
            .danger { color: #dc2626; }
            @media (max-width: 768px) { .layout { grid-template-columns: 1fr; } }
        `,
    ];

    constructor() {
        super();
        this.elements = [];
        this.selectedIndex = -1;
        this.loading = false;
    }

    setElements(elements) {
        this.elements = Array.isArray(elements) ? structuredClone(elements) : [];
        this.selectedIndex = -1;
        return this;
    }

    value() {
        return structuredClone(this.elements);
    }

    render() {
        return html`
            <div class="layout">
                <section class="panel">
                    <div class="header">Елементи</div>
                    <div class="library">
                        ${this.#libraryButton("text", "Текст", "fa-solid fa-align-left")}
                        ${this.#libraryButton("image", "Изображение", "fa-solid fa-image")}
                        ${this.#libraryButton("container", "Контейнер", "fa-solid fa-box")}
                        ${this.#libraryButton("hero", "Hero", "fa-solid fa-panorama")}
                    </div>
                </section>

                <section class="panel">
                    <div class="header">Структура на страницата</div>
                    <div class="canvas">
                        ${this.elements.length === 0
                            ? html`<p class="empty">Добавете първия елемент от библиотеката.</p>`
                            : this.elements.map((element, index) => this.#element(element, index))}
                    </div>
                </section>
            </div>
        `;
    }

    #libraryButton(type, label, icon) {
        return html`<button type="button" @click=${() => this.#add(type)}>
            <i class=${icon} aria-hidden="true"></i> ${label}
        </button>`;
    }

    #element(element, index) {
        return html`<div class=${`element ${this.selectedIndex === index ? "selected" : ""}`}>
            <div class="element-main" @click=${() => { this.selectedIndex = index; }}>
                <div class="type">${element.type || "text"}</div>
                <div class="meta">Позиция ${index}</div>
            </div>
            <div class="actions">
                <button type="button" title="Нагоре" ?disabled=${index === 0} @click=${() => this.#move(index, -1)}><i class="fa-solid fa-arrow-up"></i></button>
                <button type="button" title="Надолу" ?disabled=${index === this.elements.length - 1} @click=${() => this.#move(index, 1)}><i class="fa-solid fa-arrow-down"></i></button>
                <button type="button" class="danger" title="Изтрий" @click=${() => this.#remove(index)}><i class="fa-solid fa-trash"></i></button>
            </div>
        </div>`;
    }

    #add(type) {
        this.elements = [...this.elements, { type, position: this.elements.length, settings: {}, children: [] }];
        this.selectedIndex = this.elements.length - 1;
        this.#changed();
    }

    #move(index, offset) {
        const target = index + offset;
        if (target < 0 || target >= this.elements.length) return;
        const elements = [...this.elements];
        [elements[index], elements[target]] = [elements[target], elements[index]];
        this.elements = elements.map((element, position) => ({ ...element, position }));
        this.selectedIndex = target;
        this.#changed();
    }

    #remove(index) {
        this.elements = this.elements.filter((_element, current) => current !== index)
            .map((element, position) => ({ ...element, position }));
        this.selectedIndex = -1;
        this.#changed();
    }

    #changed() {
        this.emit("flex-page-builder-change", { elements: this.value() });
    }
}

FlexPageBuilder.register("flex-page-builder");
