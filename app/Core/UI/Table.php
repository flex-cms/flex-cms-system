<?php

namespace Flex\Core\UI;

class Table
{
    protected iterable $items;
    protected array $columns = [];

    public function __construct(iterable $items)
    {
        $this->items = $items;
    }

    public static function create(iterable $items): self
    {
        return new self($items);
    }

    public static function statusToggle(int|string $id, string $deactiveLabel = 'Деактивиране', string $activeLabel = 'Активиране', string $clickAction = 'toggleStatus'): string
    {
        ob_start();
        ?>
        <template x-if="statuses[<?= $id ?>]">
            <?= self::actionButton(
                click: "{$clickAction}({$id})",
                label: $deactiveLabel,
                icon: 'fa-solid fa-power-off',
                type: 'danger',
                extraAttributes: ":disabled=\"loading[{$id}]\""
            ) ?>
        </template>
        <template x-if="!statuses[<?= $id ?>]">
            <?= self::actionButton(
                click: "{$clickAction}({$id})",
                label: $activeLabel,
                icon: 'fa-solid fa-play',
                type: 'success',
                extraAttributes: ":disabled=\"loading[{$id}]\""
            ) ?>
        </template>
        <?php
        return ob_get_clean();
    }

    public function column(
        string $label, 
        callable $renderer, 
        ?string $sortKey = null, 
        string $align = 'left', 
        ?callable $linkUrl = null,
        string $target = '_self'
    ): self {
        $validAlignments = ['left', 'center', 'right'];
        if (!in_array($align, $validAlignments, true)) {
            $align = 'left';
        }

        $wrappedRenderer = function ($row) use ($renderer, $linkUrl, $target) {
            $value = $renderer($row);

            if ($linkUrl) {
                $url = $linkUrl($row);
                
                ob_start();
                ?>
                <a href="<?= htmlspecialchars($url) ?>" target="<?= htmlspecialchars($target) ?>" class="text-primary hover:underline">
                    <?= $value ?>
                </a>
                <?php
                return ob_get_clean();
            }

            return $value;
        };

        $this->columns[] = [
            'label'    => $label,
            'renderer' => $wrappedRenderer,
            'sortKey'  => $sortKey,
            'align'    => $align,
        ];
        
        return $this;
    }

    public static function textCell(?string $text, string $fallback = '—'): string
    {
        if ($text === null || trim($text) === '') {
            return "<span class=\"text-slate-400 dark:text-slate-500\">{$fallback}</span>";
        }

        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    private function getSortUrl(string $key): string
    {
        $params = $_GET;
        $direction = ($params['sort'] ?? '') === $key && ($params['direction'] ?? '') === 'asc' ? 'desc' : 'asc';

        $params['sort'] = $key;
        $params['direction'] = $direction;

        return '?' . http_build_query($params);
    }

    public function render($addContainerClasses = ''): void
    {
        $currentSort = $_GET['sort'] ?? null;
        $currentDir = $_GET['direction'] ?? 'asc';

        ?>
        <div
            class="bg-white dark:bg-slate-800 shadow-sm rounded-md border border-slate-200 dark:border-slate-700 overflow-hidden <?= $addContainerClasses ?>">
            <div class="overflow-x-auto scrollbar scrollbar-track-slate-100 scrollbar-thumb-slate-300 dark:scrollbar-track-slate-800 dark:scrollbar-thumb-slate-600">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-700/50">
                            <?php foreach ($this->columns as $col): ?>
                                <?php 
                                $align = $col['align'] ?? 'left';
                                $justifyMap = ['left' => 'justify-start', 'center' => 'justify-center', 'right' => 'justify-end'];
                                $justifyClass = $justifyMap[$align];
                                ?>
                                <th
                                    class="px-4 py-2 font-semibold text-slate-500 dark:text-slate-400 text-<?= $align ?>">
                                    <?php if ($col['sortKey']): ?>
                                        <a href="<?= $this->getSortUrl($col['sortKey']) ?>"
                                            class="flex items-center gap-1 hover:text-primary transition-colors <?= $justifyClass ?>">
                                            <?= $col['label'] ?>
                                            <?php if ($currentSort === $col['sortKey']): ?>
                                                <i
                                                    class="fa-solid <?= $currentDir === 'asc' ? 'fa-sort-up' : 'fa-sort-down' ?> text-indigo-500"></i>
                                            <?php else: ?>
                                                <i class="fa-solid fa-sort opacity-30"></i>
                                            <?php endif; ?>
                                        </a>
                                    <?php else: ?>
                                        <?= $col['label'] ?>
                                    <?php endif; ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <?php if (count($this->items) > 0): ?>
                            <?php foreach ($this->items as $item): ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <?php foreach ($this->columns as $col): ?>
                                        <?php $align = $col['align'] ?? 'left'; ?>
                                        <td class="px-4 py-2 text-slate-600 dark:text-slate-300 text-<?= $align ?>">
                                            <?= $col['renderer']($item) ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= count($this->columns) ?>"
                                    class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">
                                    Няма намерени записи.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    public static function header(?callable $slot = null): void
    {
        ?>
        <?php if ($slot): ?>
            <div class="dark:border-slate-700">
                <form method="GET" class="flex flex-wrap gap-2">
                    <?php $slot(); ?>
                </form>
            </div>
        <?php endif; ?>
    <?php
    }

    public static function search(string $placeholder = 'Търсене...', string $name = 'search', string $value = ''): void
    {
        if ($value === '') {
            $value = $_GET[$name] ?? '';
        }
        
        ?>
        <div class="relative w-full max-w-full sm:max-w-xs">
            <?php Form::input($name, '', [
                'value'    => $value,
                'placeholder' => $placeholder,
                'icon' => 'fa-magnifying-glass'
            ]); ?>
        </div>
        <?php
    }

    public static function select(string $name, array $options, string $selected = ''): void
    {
        if ($selected === '') {
            $selected = $_GET[$name] ?? '';
        }
        ?>
        <select name="<?= $name ?>"
            class="w-full max-w-full sm:max-w-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white">
            <?php foreach ($options as $value => $label): ?>
                <option value="<?= $value ?>" <?= (string)$value === (string)$selected ? 'selected' : '' ?>>
                    <?= $label ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public static function submit(string $label = 'Филтрирай', string $icon = 'fa-filter'): void
    {
        ?>
        <button type="submit"
            class="inline-flex items-center px-4 py-2 bg-white hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-sm font-medium rounded-md border border-slate-200 dark:border-slate-700 transition-all outline-none focus:ring-2 focus:ring-slate-400">
            <?php if ($icon): ?>
                <i class="fa-solid <?= $icon ?> mr-2"></i>
            <?php endif; ?>
            <?= $label ?>
        </button>
        <?php
    }

    public static function reset(string $url, string $label = 'Изчисти', string $icon = 'fa-rotate-left'): void
    {
        if (empty($_GET)) {
            return;
        }

        ?>
        <a href="<?= $url ?>"
            class="inline-flex items-center px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 text-sm font-medium rounded-lg transition-all outline-none focus:ring-2 focus:ring-slate-400">
            <?php if ($icon): ?>
                <i class="fa-solid <?= $icon ?> mr-2"></i>
            <?php endif; ?>
            <?= $label ?>
        </a>
        <?php
    }

    public static function tabs(array $tabs, string $activeSlug): void
    {
        ?>
        <div class="border-b border-slate-200 dark:border-slate-700 mb-6">
            <nav class="flex space-x-8" aria-label="Tabs">
                <?php foreach ($tabs as $slug => $label): ?>
                    <a href="?tab=<?= $slug ?>" 
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors <?= $activeSlug === $slug 
                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' 
                            : 'border-transparent hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300' ?>">
                        <?= $label ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
        <?php
    }

    public static function avatar(?string $imageSrc, string $fallbackText, string $bgColor = '#6366f1', int $size = 40): string
    {
        $words = explode(' ', trim($fallbackText));
        $initials = '';
        foreach ($words as $word) {
            $initials .= mb_substr($word, 0, 1, 'UTF-8');
            if (mb_strlen($initials, 'UTF-8') >= 2) break;
        }
        $initials = mb_strtoupper($initials, 'UTF-8');

        $bgStyle = str_starts_with($bgColor, '#') ? "style=\"background-color: {$bgColor};\"" : "";
        $bgClass = !str_starts_with($bgColor, '#') ? $bgColor : "";

        $html = "<div class=\"flex items-center justify-center rounded-full text-white font-semibold select-none overflow-hidden shrink-0 {$bgClass}\" 
                    style=\"width: {$size}px; height: {$size}px; font-size: " . ($size * 0.4) . "px; " . (str_starts_with($bgColor, '#') ? "background-color: {$bgColor};" : "") . "\">";

        if (!empty($imageSrc)) {
            $html .= "<img src=\"{$imageSrc}\" alt=\"{$fallbackText}\" class=\"w-full h-full object-cover\" onerror=\"this.style.display='none'; this.nextElementSibling.style.display='flex';\">";
            $html .= "<span class=\"hidden w-full h-full items-center justify-center\">{$initials}</span>";
        } else {
            $html .= "<span>{$initials}</span>";
        }

        $html .= "</div>";

        return $html;
    }

    public static function actionsMenu(
        callable $slot, 
        $item = null, 
        string $align = 'right'
    ): string {
        $align = strtolower($align) === 'left' ? 'left' : 'right';
        
        $anchorPlacement = ($align === 'left') ? 'bottom-start' : 'bottom-end';
        $alignClass = ($align === 'left') ? 'left-0' : 'right-0';

        ob_start(); ?>
        <div x-data="{ open: false }" @click.away="open = false" class="relative inline-block text-left">
            <button @click="open = !open" type="button" 
                class="flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors focus:outline-none">
                <i class="fa-solid fa-ellipsis-vertical text-base"></i>
            </button>

            <div x-show="open" 
                x-anchor.<?= $anchorPlacement ?>="$el.parentElement"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="fixed z-100 mt-2 py-1 w-44 rounded-md bg-white dark:bg-slate-800 shadow-lg border border-slate-200 dark:border-slate-700 focus:outline-none text-left <?= $alignClass ?>" 
                style="display: none;"
                @click="open = false"> 
                <div class="px-1.5 space-y-0.5">
                    <?= $slot($item) ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function actionLink(string $href, string $label, string $icon, string $type = 'default'): string
    {
        $styles = [
            'default' => 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700',
            'danger'  => 'text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20',
            'success' => 'text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20',
            'info'    => 'text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20'
        ];

        $class = $styles[$type] ?? $styles['default'];

        ob_start(); ?>
        <a href="<?= $href ?>" 
        class="flex w-full items-center px-3 py-2 text-sm rounded-md transition-colors <?= $class ?>">
            <i class="<?= $icon ?> mr-2 w-4 text-center text-slate-400/80"></i> <?= $label ?>
        </a>
        <?php
        return ob_get_clean();
    }

    public static function actionButton(
        string $click, 
        string $label, 
        string $icon, 
        string $type = 'neutral', 
        string $extraAttributes = ''
    ): string {
        $types = [
            'neutral' => [
                'btn' => 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50',
                'icon' => 'text-slate-400 dark:text-slate-500'
            ],
            'success' => [
                'btn' => 'text-emerald-600 dark:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-700/50',
                'icon' => 'text-emerald-500'
            ],
            'danger' => [
                'btn' => 'text-red-600 dark:text-red-400 hover:bg-slate-100 dark:hover:bg-slate-700/50',
                'icon' => 'text-red-500'
            ],
            'delete' => [
                'btn' => 'text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 font-medium border-t border-slate-100 dark:border-slate-700/50 pt-1.5 mt-1 rounded-t-none',
                'icon' => 'text-red-500'
            ]
        ];

        $style = $types[$type] ?? $types['neutral'];
        
        ob_start(); ?>
        <button type="button" @click="<?= $click ?>" <?= $extraAttributes ?>
            class="flex w-full items-center px-3 py-2 text-sm border border-slate-200 rounded-md transition-colors disabled:opacity-50 <?= $style['btn'] ?>">
            <i class="<?= $icon ?> <?= $style['icon'] ?> mr-2 w-4 text-center"></i> <?= $label ?>
        </button>
        <?php
        return ob_get_clean();
    }

    public static function avatarWithLabel(
        ?string $avatarUrl, 
        string $label, 
        string $color = '#6366f1', 
        int $size = 36, 
        bool $isDefault = false
    ): string {
        ob_start(); ?>
        <div class="flex items-center gap-3">
            <?= self::avatar($avatarUrl, $label, $color, $size); ?>
            <div>
                <span class="font-medium text-slate-900 dark:text-white block">
                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    
                    <?php if ($isDefault): ?>
                        <span class="ml-1.5 inline-flex items-center rounded-md bg-indigo-50 dark:bg-indigo-950/40 px-1.5 py-0.5 text-xs font-medium text-indigo-700 dark:text-indigo-400 ring-1 ring-inset ring-indigo-700/10 dark:ring-indigo-400/20 select-none">
                            По подразбиране
                        </span>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function statusBadge(
        string $text, 
        string $type = 'success', 
        int|string|null $reactiveId = null
    ): string {
        $styles = [
            'success' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300',
            'neutral' => 'bg-slate-100 text-slate-500 dark:bg-slate-700/50 dark:text-slate-400',
            'code'    => 'bg-slate-100 dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700 text-indigo-600 dark:text-indigo-400 font-mono text-xs'
        ];

        $class = $styles[$type] ?? $styles['neutral'];

        if ($reactiveId !== null) {
            if (str_contains($text, '|')) {
                [$activeText, $inactiveText] = explode('|', $text, 2);
            } else {
                $isFeminine = ($text === 'Активна' || $text === 'Неактивна');
                $activeText = $isFeminine ? 'Активна' : 'Активен';
                $inactiveText = $isFeminine ? 'Неактивна' : 'Неактивен';
            }

            ob_start(); ?>
            <span x-text="typeof statuses !== 'undefined' && statuses[<?= $reactiveId ?>] ? '<?= htmlspecialchars($activeText, ENT_QUOTES, 'UTF-8') ?>' : '<?= htmlspecialchars($inactiveText, ENT_QUOTES, 'UTF-8') ?>'"
                :class="typeof statuses !== 'undefined' && statuses[<?= $reactiveId ?>] ? '<?= $styles['success'] ?>' : '<?= $styles['neutral'] ?>'"
                class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold select-none">
            </span>
            <?php
            return ob_get_clean();
        }

        if (trim($text) === '') {
            return '<span class="text-slate-400">—</span>';
        }

        $cleanText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        if ($type === 'code') {
            return "<code class='px-2 py-1 rounded select-all {$class}'>{$cleanText}</code>";
        }

        return "<span class='inline-block px-2.5 py-1 rounded-full text-xs font-semibold {$class}'>{$cleanText}</span>";
    }
}
