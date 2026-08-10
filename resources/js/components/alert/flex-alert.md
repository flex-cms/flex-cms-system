# `<flex-alert>`

Lit Web Component за контекстни съобщения във Flex CMS. Работи в Light DOM и използва TailwindCSS и Font Awesome.

## Импорт

```js
import "./alert/flex-alert.js";
```

## Основен пример

```html
<flex-alert
    type="success"
    title="Промените са запазени"
    message="Страницата беше обновена успешно."
    dismissible
></flex-alert>
```

## Типове

| Тип       | Предназначение             | Икона по подразбиране     |
| --------- | -------------------------- | ------------------------- |
| `info`    | Обща информация            | `fa-circle-info`          |
| `success` | Успешна операция           | `fa-circle-check`         |
| `warning` | Предупреждение             | `fa-triangle-exclamation` |
| `danger`  | Грешка или опасно действие | `fa-circle-exclamation`   |

Непозната стойност се визуализира като `info`.

## Атрибути и свойства

| Атрибут       | JS свойство   | Тип       | По подразбиране | Описание                                              |
| ------------- | ------------- | --------- | --------------- | ----------------------------------------------------- |
| `type`        | `type`        | `String`  | `info`          | Типът на съобщението.                                 |
| `title`       | `title`       | `String`  | празно          | Незадължително заглавие.                              |
| `message`     | `message`     | `String`  | празно          | Основният текст.                                      |
| `icon`        | `icon`        | `String`  | автоматична     | Собствени Font Awesome класове.                       |
| `dismissible` | `dismissible` | `Boolean` | `false`         | Показва бутон за затваряне.                           |
| `duration`    | `duration`    | `Number`  | `0`             | Автоматично затваряне в милисекунди; `0` го изключва. |
| `open`        | `open`        | `Boolean` | `true`          | Определя дали alert-ът е видим. Отразява се в DOM.    |

При boolean HTML атрибут самото наличие означава `true`. За промяна на `open` и `dismissible` към `false` използвай JavaScript свойствата.

## Примери

```html
<flex-alert
    type="info"
    message="Попълнете всички задължителни полета."
></flex-alert>

<flex-alert
    type="warning"
    title="Непубликувани промени"
    message="Напускането на страницата ще ги изтрие."
    dismissible
></flex-alert>

<flex-alert
    type="danger"
    title="Възникна грешка"
    message="Записът не можа да бъде създаден."
    icon="fa-solid fa-database"
></flex-alert>

<flex-alert
    type="success"
    message="Настройките са запазени."
    duration="5000"
></flex-alert>
```

Стойностите на `title` и `message`, генерирани от PHP, трябва да бъдат защитени с `htmlspecialchars(..., ENT_QUOTES)`.

## Събитие `flex-alert-close`

Изпраща се при затваряне чрез бутона, таймера или публичния метод:

```js
document.addEventListener("flex-alert-close", (event) => {
    console.log(event.detail.reason); // button, timeout или api
    console.log(event.detail.type);
});
```

Събитието преминава нагоре в DOM.

## Публични методи

```js
const alert = document.querySelector("flex-alert");

alert.close(); // reason: api
alert.show();
```

## Достъпност

- `warning` и `danger` използват `role="alert"`.
- `info` и `success` използват `role="status"`.
- `danger` използва `aria-live="assertive"`; останалите типове са `polite`.
- Иконите са декоративни и са скрити от screen reader.
- Бутонът за затваряне има достъпно име.

## Ограничения

- Компонентът показва кратък обикновен текст, а не произволен HTML.
- За интерактивно или форматирано съдържание е по-подходящ отделен компонент или бъдеща slot функционалност.
- Автоматичното затваряне започва при свързване на компонента или промяна на `duration`/`open`.
