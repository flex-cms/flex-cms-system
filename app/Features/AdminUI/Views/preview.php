<?php

declare(strict_types=1);

$title = isset($title) && is_string($title)
    ? $title
    : 'Нов Flex Admin UI';

$description = isset($description)
    && is_string($description)
        ? $description
        : '';

$activeTab = isset($activeTab)
    && is_string($activeTab)
        ? $activeTab
        : 'overview';

$escape = static function (
    mixed $value
): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
};

$tabs = [
    'overview' => [
        'label' => 'Преглед',
        'icon' => 'fa-house',
    ],

    'components' => [
        'label' => 'Компоненти',
        'icon' => 'fa-cubes',
    ],

    'navigation' => [
        'label' => 'Навигация',
        'icon' => 'fa-arrow-right-arrow-left',
    ],
];
?>

<turbo-frame
    id="admin-ui-preview-tabs"
    data-turbo-action="advance"
>
    <section class="flex-surface flex-preview-tabs">
        <nav
            class="flex-preview-tabs__navigation"
            role="tablist"
            aria-label="Admin UI примерни табове"
        >
            <?php foreach (
                $tabs as $tab => $configuration
            ): ?>
                <?php $isActive =
                    $activeTab === $tab; ?>

                <a
                    class="flex-preview-tab<?= $isActive
                        ? ' flex-preview-tab--active'
                        : '' ?>"
                    href="/admin/ui-preview?tab=<?= rawurlencode(
                        $tab
                    ) ?>"
                    role="tab"
                    aria-selected="<?= $isActive
                        ? 'true'
                        : 'false' ?>"
                    data-turbo-action="advance"
                >
                    <i
                        class="fa-solid <?= $escape(
                            $configuration['icon']
                        ) ?>"
                        aria-hidden="true"
                    ></i>

                    <span>
                        <?= $escape(
                            $configuration['label']
                        ) ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div
            class="flex-preview-tabs__panel"
            role="tabpanel"
            tabindex="0"
        >
            <?php if (
                $activeTab === 'overview'
            ): ?>
                <h3>Turbo Frame преглед</h3>

                <p>
                    При натискане на таб Turbo
                    заявява същия PHP endpoint и
                    заменя само съдържанието на
                    този frame.
                </p>

                <code>
                    ?tab=overview
                </code>

            <?php elseif (
                $activeTab === 'components'
            ): ?>
                <h3>Lit компоненти</h3>

                <p>
                    Новият интерфейс използва
                    custom elements с Shadow DOM
                    и собствен lifecycle.
                </p>

                <ul>
                    <li>flex-admin-shell</li>
                    <li>flex-admin-header</li>
                    <li>flex-sidebar</li>
                    <li>flex-nav-item</li>
                    <li>flex-theme-toggle</li>
                </ul>

                <code>
                    ?tab=components
                </code>

            <?php elseif (
                $activeTab === 'navigation'
            ): ?>
                <h3>Turbo навигация</h3>

                <p>
                    Атрибутът
                    <code>data-turbo-action="advance"</code>
                    записва промяната в browser
                    history.
                </p>

                <p>
                    Бутоните Back и Forward на
                    браузъра могат да сменят
                    активния таб.
                </p>

                <code>
                    ?tab=navigation
                </code>
            <?php endif; ?>
        </div>
    </section>
</turbo-frame>
