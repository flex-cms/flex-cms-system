# `<flex-form>`

`<flex-form>` надгражда стандартна HTML форма с незадължително Axios изпращане, състояние за зареждане, известия и събития. Истинският `<form>` остава в PHP view, затова браузърната валидация, `FormData`, файловете и обикновеният submit продължават да работят.

## Основен пример

```html
<flex-form ajax reset-on-success>
    <form action="/admin/pages/store" method="POST">
        <input name="name" required>

        <flex-button
            type="submit"
            label="Запази"
            loading-text="Запазване..."
        ></flex-button>
    </form>
</flex-form>
```

`<form>` трябва да е директно дете на компонента. Ако се използва `form-selector`, компонентът търси формата чрез подадения CSS selector.

## Атрибути

| Атрибут | Тип | По подразбиране | Описание |
| --- | --- | --- | --- |
| `ajax` | boolean | `false` | Изпраща формата чрез глобалния `window.axios`. Без него се изпълнява стандартен браузърен submit. |
| `reset-on-success` | boolean | `false` | Изчиства формата след успешна заявка. |
| `success-message` | string | `Промените са запазени успешно.` | Резервен текст при успех. `response.data.message` има предимство. |
| `error-message` | string | `Възникна грешка...` | Резервен текст при грешка. `error.response.data.message` има предимство. |
| `show-alerts` | boolean | `true` | Показва автоматичен `<flex-alert>` пред формата. За изключване използвайте DOM свойството `showAlerts = false`. |
| `form-selector` | string | празно | CSS selector за формата, когато тя не е директно дете. |
| `submitting` | boolean | `false` | Отразено read-only състояние по време на заявката. |

Boolean атрибутите са включени чрез присъствието си. Например `ajax="false"` пак означава включено; за изключване атрибутът трябва да липсва.

## Събития

| Събитие | `detail` | Кога се изпраща |
| --- | --- | --- |
| `flex-form-submit` | `form`, `submitter` | Преди Axios заявката. |
| `flex-form-success` | `form`, `response` | След успешен отговор. |
| `flex-form-error` | `form`, `error` | При Axios или конфигурационна грешка. |
| `flex-form-invalid` | `form` | При невалидни HTML полета. |

Компонентът изпраща и `flex-submit-end` или `flex-submit-error` върху самия `<form>`. Така `<flex-button type="submit">` автоматично прекратява своето loading състояние.

```js
document.addEventListener('flex-form-success', (event) => {
    console.log(event.detail.response.data);
});
```

## Публични методи

- `submit()` — извиква `requestSubmit()` и запазва валидацията и submit събитията.
- `reset()` — изчиства формата и автоматичното известие.
- `bindForm()` — свързва компонента отново след динамична подмяна на формата.

## Отговор от PHP endpoint

Препоръчителен JSON при успех:

```json
{"message":"Страницата беше създадена успешно."}
```

При грешка endpoint-ът трябва да върне подходящ HTTP статус, например `422`, и може да подаде общо съобщение:

```json
{"message":"Моля, проверете въведените данни."}
```

## Файлове и CSRF

`FormData` се използва автоматично и поддържа `<input type="file">`. CSRF token може да бъде стандартно hidden поле. Не задавайте ръчно `Content-Type`; Axios добавя правилната multipart граница.

## Зависимости

- `lit`;
- глобален `window.axios` само при `ajax`;
- `<flex-alert>` за автоматичните известия;
- `<flex-button>` не е задължителен, но е препоръчителен за автоматичен loading UI.
