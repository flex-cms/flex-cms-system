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

    public function column(string $label, callable $renderer, ?string $sortKey = null): self
    {
        $this->columns[] = [
            'label' => $label,
            'renderer' => $renderer,
            'sortKey' => $sortKey
        ];
        return $this;
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
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-700/50">
                            <?php foreach ($this->columns as $col): ?>
                                <th
                                    class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    <?php if ($col['sortKey']): ?>
                                        <a href="<?= $this->getSortUrl($col['sortKey']) ?>"
                                            class="flex items-center gap-1 hover:text-indigo-600 transition-colors">
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
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
        ?>
        <div class="relative w-full max-w-full sm:max-w-xs">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" name="<?= $name ?>" value="<?= htmlspecialchars($value) ?>" placeholder="<?= $placeholder ?>"
                class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all dark:text-white">
        </div>
        <?php
    }

    public static function select(string $name, array $options, string $selected = ''): void
    {
        ?>
        <select name="<?= $name ?>"
            class="w-full max-w-full sm:max-w-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white">
            <?php foreach ($options as $value => $label): ?>
                <option value="<?= $value ?>" <?= $value === $selected ? 'selected' : '' ?>>
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
}
