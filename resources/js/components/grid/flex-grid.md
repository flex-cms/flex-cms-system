# `<flex-grid>`

Responsive CSS Grid компонент за подреждане на произволни HTML и Lit елементи. API-то следва основната логика на Tailwind Grid, без да създава динамични Tailwind класове.

## Основен пример

```html
<flex-grid
    cols="1"
    md-cols="2"
    gap="5"
>
    <flex-input
        name="name"
        label="Име"
    ></flex-input>
    <flex-input
        name="slug"
        label="Slug"
    ></flex-input>
</flex-grid>
```

До ширина `767px` гридът е с една колона. От `768px` нагоре е с две колони.

## Атрибути

| Атрибут   | Тип    | По подразбиране | Описание                               |
| --------- | ------ | --------------- | -------------------------------------- |
| `cols`    | Number | `1`             | Брой колони от 1 до 12.                |
| `sm-cols` | Number | наследява       | Колони от `640px` нагоре.              |
| `md-cols` | Number | наследява       | Колони от `768px` нагоре.              |
| `lg-cols` | Number | наследява       | Колони от `1024px` нагоре.             |
| `xl-cols` | Number | наследява       | Колони от `1280px` нагоре.             |
| `gap`     | String | `0`             | Общо разстояние между редове и колони. |
| `gap-x`   | String | наследява `gap` | Хоризонтално разстояние.               |
| `gap-y`   | String | наследява `gap` | Вертикално разстояние.                 |

Колоните приемат стойности от `1` до `12`. Невалидна responsive стойност се пропуска и наследява предходната.

## Разстояния

`gap`, `gap-x` и `gap-y` приемат стойности от Tailwind spacing скалата:

```html
<flex-grid
    cols="3"
    gap="4"
></flex-grid>
```

`gap="4"` е равно на `1rem`. Поддържаните стойности са `0`, `0.5`, `1`, `1.5`, `2`, `2.5`, `3`, `3.5`, `4`, `5`, `6`, `7`, `8`, `9`, `10`, `11`, `12`, `14`, `16`, `20` и `24`.

Може да се използва и CSS размер:

```html
<flex-grid
    cols="2"
    gap-x="24px"
    gap-y="1.5rem"
></flex-grid>
```

## Responsive пример

```html
<flex-grid
    cols="1"
    sm-cols="2"
    lg-cols="3"
    xl-cols="4"
    gap="6"
>
    <article>Елемент 1</article>
    <article>Елемент 2</article>
    <article>Елемент 3</article>
    <article>Елемент 4</article>
</flex-grid>
```

## Използване във `<flex-form>`

```html
<flex-form
    action="/admin/pages/store"
    method="POST"
    ajax
>
    <flex-grid
        cols="1"
        md-cols="2"
        gap="5"
    >
        <flex-input
            name="name"
            label="Име"
            required
        ></flex-input>
        <flex-input
            name="slug"
            label="Slug"
            required
        ></flex-input>
        <flex-input
            type="number"
            name="position"
            label="Позиция"
        ></flex-input>
    </flex-grid>

    <flex-button
        type="submit"
        label="Запази"
    ></flex-button>
</flex-form>
```

Компонентът не променя полетата и техните `name`/`value` стойности. Той управлява само layout-а.

## JavaScript API

Свойствата могат да се променят динамично:

```js
const grid = document.querySelector("flex-grid");
grid.cols = 1;
grid.mdCols = 3;
grid.gap = "6";
```

Компонентът не изпраща собствени събития, защото не управлява данни или потребителски действия.

## Персонализиране с CSS

Може да се зададат CSS променливите директно:

```css
flex-grid.products {
    --flex-grid-gap: 2rem;
    --flex-grid-lg-cols: 5;
}
```

Стойностите от HTML атрибутите се записват като inline CSS променливи и имат предимство пред външните правила.
