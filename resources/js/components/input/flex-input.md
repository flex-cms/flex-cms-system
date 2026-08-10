# `<flex-input>`

Универсално формулярно поле за Flex CMS. Компонентът поддържа `text`, `number` и `textarea` и рендерира истински HTML контрол в Light DOM. Затова браузърната валидация, `FormData`, стандартното изпращане и `<flex-form>` работят без допълнителна интеграция.

# `<flex-form>`

`<flex-form>` надгражда стандартна HTML форма с незадължително Axios изпращане, състояние за зареждане, известия и събития. Компонентът автоматично генерира вътрешен `<form>` елемент[cite: 1, 2], така че няма нужда да го дефинирате ръчно. Браузърната валидация, `FormData`, файловете и обикновеният submit продължават да работят стандартно.

## Основен пример

````html
<flex-form
    action="/admin/pages/store"
    method="POST"
    form-class="flex flex-col gap-5"
    ajax
    reset-on-success
>
    <div class="flex flex-col gap-5">
        <flex-input
            type="text"
            name="name"
            label="Име на страницата"
            required
        ></flex-input>
    </div>

    <flex-button
        type="submit"
        label="Запази"
        loading-text="Запазване..."
    ></flex-button>
</flex-form>

## Типове ```html
<flex-input
    type="text"
    name="name"
    label="Име"
></flex-input>

<flex-input
    type="number"
    name="sort_order"
    label="Позиция"
    min="0"
    max="100"
    step="1"
    value="0"
></flex-input>

<flex-input
    type="textarea"
    name="description"
    label="Описание"
    rows="6"
    maxlength="500"
></flex-input>
````

Непознат `type` се преобразува автоматично до `text`.

## Атрибути и свойства

| Атрибут                  | Тип     | По подразбиране | Предназначение                          |
| ------------------------ | ------- | --------------- | --------------------------------------- |
| `type`                   | string  | `text`          | `text`, `number` или `textarea`         |
| `name`                   | string  | празно          | Име на полето, изпращано към PHP        |
| `label`                  | string  | празно          | Етикет над контролата                   |
| `value`                  | string  | празно          | Текуща стойност                         |
| `placeholder`            | string  | празно          | Placeholder текст                       |
| `help-text`              | string  | празно          | Помощен текст под полето                |
| `error`                  | string  | празно          | Грешка и червен невалиден стил          |
| `required`               | boolean | `false`         | Задължително поле                       |
| `disabled`               | boolean | `false`         | Забранено поле; не влиза във `FormData` |
| `readonly`               | boolean | `false`         | Само за четене                          |
| `autocomplete`           | string  | празно          | Стандартна autocomplete стойност        |
| `input-id`               | string  | генериран       | Собствено `id` за контролата            |
| `min`, `max`, `step`     | string  | празно          | Ограничения за `number`                 |
| `minlength`, `maxlength` | number  | няма            | Ограничения за `text` и `textarea`      |
| `rows`                   | number  | `4`             | Височина на `textarea` в редове         |

Boolean атрибутите се изключват чрез премахването им, а не чрез стойност `"false"`.

## Използване с `<flex-form>`

```html
<flex-form ajax>
    <form
        action="/admin/pages/store"
        method="POST"
    >
        <flex-input
            type="text"
            name="name"
            label="Име"
            required
        ></flex-input>
        <flex-input
            type="textarea"
            name="description"
            label="Описание"
        ></flex-input>
        <flex-button
            type="submit"
            label="Запази"
        ></flex-button>
    </form>
</flex-form>
```

PHP получава стойностите по стандартния начин:

```php
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
```

При задаване на начална PHP стойност тя трябва да бъде HTML escaped:

```php
<flex-input
    name="name"
    label="Име"
    value="<?= htmlspecialchars($page->name ?? '', ENT_QUOTES, 'UTF-8') ?>"
></flex-input>
```

## Събития

| Събитие       | Кога се изпраща                      | `event.detail`                   |
| ------------- | ------------------------------------ | -------------------------------- |
| `flex-input`  | При всяка промяна по време на писане | `value`, `name`, `originalEvent` |
| `flex-change` | При native `change`                  | `value`, `name`, `originalEvent` |

```js
document.addEventListener("flex-input", (event) => {
    console.log(event.detail.name, event.detail.value);
});
```

## JavaScript API

```js
const field = document.querySelector('flex-input[name="name"]');

field.value = "Ново име";
field.error = "Това име вече съществува.";
field.focus();

field.checkValidity();
field.reportValidity();
```

Публичните членове са `value`, `error`, `inputElement`, `focus()`, `checkValidity()` и `reportValidity()`.

## Грешки от сървъра

При validation response грешката може да се зададе директно:

```js
document.addEventListener("flex-form-error", (event) => {
    const errors = event.detail.error?.response?.data?.errors ?? {};

    for (const [name, messages] of Object.entries(errors)) {
        const field = event.target.querySelector(`flex-input[name="${CSS.escape(name)}"]`);
        if (field) field.error = Array.isArray(messages) ? messages[0] : messages;
    }
});
```

## Достъпност

- `label` е свързан с контролата чрез `for` и `id`.
- `required` се обозначава визуално и чрез native атрибут.
- Помощният текст и грешката се свързват чрез `aria-describedby`.
- При грешка се задават `aria-invalid="true"` и `role="alert"`.
