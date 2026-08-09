<?php

declare(strict_types=1);

$title = isset($title) && is_string($title)
    ? $title
    : 'Нов Flex Admin UI';

$description = isset($description)
    && is_string($description)
        ? $description
        : '';

$escape = static fn (
    mixed $value
): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);
?>

<section class="flex-preview">
    <header class="flex-page-header">
        <div>
            <h2 class="flex-page-title">
                <?= $escape($title) ?>
            </h2>

            <?php if ($description !== ''): ?>
                <p class="flex-page-description">
                    <?= $escape($description) ?>
                </p>
            <?php endif; ?>
        </div>
    </header>

    <div class="flex-preview-grid">
        <article class="flex-surface flex-preview-card">
            <div class="flex-preview-card__icon">
                <i
                    class="fa-solid fa-bolt"
                    aria-hidden="true"
                ></i>
            </div>

            <div>
                <h3>Turbo навигация</h3>

                <p>
                    Навигация без пълно презареждане
                    на документа.
                </p>
            </div>
        </article>

        <article class="flex-surface flex-preview-card">
            <div class="flex-preview-card__icon">
                <i
                    class="fa-solid fa-cubes"
                    aria-hidden="true"
                ></i>
            </div>

            <div>
                <h3>Lit компоненти</h3>

                <p>
                    Капсулирани компоненти със
                    собствен lifecycle и Shadow DOM.
                </p>
            </div>
        </article>

        <article class="flex-surface flex-preview-card">
            <div class="flex-preview-card__icon">
                <i
                    class="fa-solid fa-shield-halved"
                    aria-hidden="true"
                ></i>
            </div>

            <div>
                <h3>Изолирана архитектура</h3>

                <p>
                    Текущият Alpine интерфейс остава
                    напълно непроменен.
                </p>
            </div>
        </article>
    </div>

    <section class="flex-surface flex-preview-section">
        <div class="flex-surface-header">
            <h3>Theme компонент</h3>

            <p>
                Компонентът работи независимо от
                Alpine.js.
            </p>
        </div>

        <div class="flex-surface-body">
            <div class="flex-preview-theme-row">
                <flex-theme-toggle>
                </flex-theme-toggle>

                <span>
                    Натисни бутона, за да смениш
                    светлата и тъмната тема.
                </span>
            </div>
        </div>
    </section>

    <section class="flex-surface flex-preview-section">
        <div class="flex-surface-header">
            <h3>Navigation boundary</h3>

            <p>
                Turbo се използва само при изрично
                разрешени линкове.
            </p>
        </div>

        <div class="flex-surface-body">
            <div class="flex-preview-actions">
                <a
                    class="flex-preview-button flex-preview-button--primary"
                    href="/admin/ui-preview?navigation=turbo"
                    data-turbo="true"
                >
                    <i
                        class="fa-solid fa-bolt"
                        aria-hidden="true"
                    ></i>

                    Turbo презареждане
                </a>

                <a
                    class="flex-preview-button"
                    href="/admin"
                    data-turbo="false"
                >
                    <i
                        class="fa-solid fa-arrow-up-right-from-square"
                        aria-hidden="true"
                    ></i>

                    Legacy админ панел
                </a>
            </div>
        </div>
    </section>

    <section class="flex-surface flex-preview-section">
        <div class="flex-surface-header">
            <h3>Текущ migration статус</h3>
        </div>

        <div class="flex-surface-body">
            <ul class="flex-preview-checklist">
                <li>
                    <i class="fa-solid fa-check"></i>
                    Отделна Vite entry точка
                </li>

                <li>
                    <i class="fa-solid fa-check"></i>
                    Отделен CSS design system
                </li>

                <li>
                    <i class="fa-solid fa-check"></i>
                    Turbo lifecycle manager
                </li>

                <li>
                    <i class="fa-solid fa-check"></i>
                    Lit base element
                </li>

                <li>
                    <i class="fa-solid fa-check"></i>
                    Responsive admin shell
                </li>

                <li>
                    <i class="fa-solid fa-check"></i>
                    Theme без Alpine.js
                </li>
            </ul>
        </div>
    </section>
</section>