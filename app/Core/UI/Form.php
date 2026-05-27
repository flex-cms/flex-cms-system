<?php

namespace Flex\Core\UI;

use Flex\Core\Auth;

class Form
{
    public static function input(string $name, string $label, array $attrs = []): void
    {
        $value = $attrs['value'] ?? '';
        $type = $attrs['type'] ?? 'text';
        $placeholder = $attrs['placeholder'] ?? '';
        $required = isset($attrs['required']) ? 'required' : '';
        $disabled = isset($attrs['disabled']) ? 'disabled' : '';
        $extra = $attrs['extra'] ?? '';

        $customClass = $attrs['class'] ?? '';

        ?>
        <div>
            <label for="<?= $name ?>" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                <?= $label ?>         <?= $required ? '<span class="text-rose-500">*</span>' : '' ?>
            </label>
            <input type="<?= $type ?>" name="<?= $name ?>" id="<?= $name ?>" value="<?= htmlspecialchars($value) ?>"
                placeholder="<?= $placeholder ?>" <?= $required ?>         <?= $disabled ?>         <?= $extra ?>
                class="w-full px-4 py-2.5 rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:focus:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-primary transition-all outline-none <?= $customClass ?>">
        </div>
        <?php
    }

    public static function textarea(string $name, string $label, array $attrs = []): void
    {
        $value = $attrs['value'] ?? '';
        $rows = $attrs['rows'] ?? 3;

        ?>
        <div>
            <label for="<?= $name ?>" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                <?= $label ?>
            </label>
            <textarea name="<?= $name ?>" id="<?= $name ?>" rows="<?= $rows ?>"
                class="w-full px-4 py-2.5 rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:focus:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none"><?= htmlspecialchars($value) ?></textarea>
        </div>
        <?php
    }

    public static function select(string $name, string $label, array $options = [], array $attrs = []): void
    {
        $selectedValue = $attrs['value'] ?? '';
        $required = isset($attrs['required']) ? 'required' : '';
        $extra = $attrs['extra'] ?? '';

        ?>
        <div>
            <label for="<?= $name ?>" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                <?= $label ?>         <?= $required ? '<span class="text-rose-500">*</span>' : '' ?>
            </label>
            <div class="relative">
                <select name="<?= $name ?>" id="<?= $name ?>" <?= $required ?>         <?= $extra ?>
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none appearance-none cursor-pointer">
                    <?php foreach ($options as $value => $text): ?>
                        <option value="<?= $value ?>" <?= $value == $selectedValue ? 'selected' : '' ?>>
                            <?= $text ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                </div>
            </div>
        </div>
        <?php
    }

    public static function toggle(string $name, string $label, array $options = []): void
    {
        $value = $options['value'] ?? true;
        $id = $options['id'] ?? 'toggle-' . bin2hex(random_bytes(4));
        $description = $options['description'] ?? null;
        $checked = $value ? 'checked' : '';

        $attributes = '';
        if (isset($options['attr']) && is_array($options['attr'])) {
            foreach ($options['attr'] as $attr => $val) {
                $attributes .= " {$attr}=\"{$val}\"";
            }
        }

        ?>
        <div class="flex items-center gap-4">
            <label for="<?= $id ?>" class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                <input type="checkbox" name="<?= $name ?>" id="<?= $id ?>" value="1" class="sr-only peer" <?= $checked ?>
                    <?= $attributes ?>>

                <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer 
                            peer-checked:after:translate-x-full peer-checked:after:border-white 
                            after:content-[''] after:absolute after:top-0.5 after:left-0.5 
                            after:bg-white after:border-slate-300 after:border after:rounded-full 
                            after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600 shadow-inner">
                </div>
            </label>

            <div class="flex flex-col select-none cursor-pointer" onclick="document.getElementById('<?= $id ?>').click()">
                <span class="font-semibold text-slate-800 dark:text-slate-200 leading-tight">
                    <?= $label ?>
                </span>
                <?php if ($description): ?>
                    <span class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed max-w-sm">
                        <?= $description ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public static function submit(string $label = 'Запази', string $icon = 'fa-check', array $options = []): void
    {
        $class = $options['class'] ?? 'bg-indigo-600 hover:bg-indigo-700 text-white';
        $type = $options['type'] ?? 'submit';
        ?>
        <button type="<?= $type ?>"
            class="inline-flex items-center gap-2 px-6 py-2 rounded-md text-lg transition-all shadow-sm active:scale-95 <?= $class ?>">
            <?php if ($icon): ?>
                <i class="fa-solid <?= $icon ?> text-lg"></i>
            <?php endif; ?>
            <?= $label ?>
        </button>
        <?php
    }

    public static function color(string $name, string $label, array $options = []): void
    {
        $value = $options['value'] ?? '#6366f1';
        $id = $options['id'] ?? 'color-' . bin2hex(random_bytes(4));
        ?>
        <div class="flex flex-col gap-2">
            <label for="<?= $id ?>" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                <?= $label ?>
            </label>
            <div
                class="flex items-center gap-3 p-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm hover:border-indigo-300 transition-colors">
                <input type="color" name="<?= $name ?>" id="<?= $id ?>" value="<?= $value ?>"
                    class="w-10 h-10 border-0 p-0 bg-transparent cursor-pointer rounded-lg overflow-hidden [&::-webkit-color-swatch-wrapper]:p-0 [&::-webkit-color-swatch]:border-none">

                <input type="text" value="<?= strtoupper($value) ?>"
                    oninput="document.getElementById('<?= $id ?>').value = this.value"
                    class="text-sm font-mono text-slate-600 dark:text-slate-400 bg-transparent border-none focus:ring-0 p-0 uppercase"
                    maxlength="7">
            </div>
        </div>
        <?php
    }

    public static function row(callable $slot, int $cols = 2): void
    {
        echo "<div class='space-y-5 grid grid-cols-1 md:grid-cols-{$cols} gap-6'>";
        $slot();
        echo "</div>";
    }

    public static function section(callable $slot, string|null $title = null, string|null $id = null): void
    {
        $sectionId = $id ?? 'section_' . substr(md5($title ?? 'default'), 0, 8);
        $user = Auth::user();

        $isOpen = $_SESSION['ui_states'][$sectionId]
            ?? $user->options['ui_states'][$sectionId]
            ?? true;

        $stateJs = $isOpen ? 'true' : 'false';
        ?>

        <div x-data="uiSection('<?= $sectionId ?>', <?= $stateJs ?>)"
            class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md shadow-sm mb-5">

            <?php if ($title): ?>
                <div @click="toggle()"
                    class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 cursor-pointer flex items-center justify-between group">

                    <h3 class="font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider select-none">
                        <?= $title ?>
                    </h3>

                    <div class="text-slate-400 group-hover:text-primary transition-transform duration-300"
                        :class="isOpen ? 'rotate-180' : ''">
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                </div>
            <?php endif; ?>

            <div x-show="isOpen" x-collapse x-cloak>
                <div class="p-6">
                    <?php $slot(); ?>
                </div>
            </div>
        </div>
        <?php
    }

    public static function codeEditor(string $name, string $label, array $options = []): void
    {
        $value = $options['value'] ?? '';
        $mode = $options['mode'] ?? 'html';
        $wrap = $options['wrap'] ?? true;

        $config = htmlspecialchars(json_encode([
            'mode' => $mode,
            'wrap' => $wrap,
        ]), ENT_QUOTES, 'UTF-8');

        ob_start();
        ?>
        <div x-data="codeEditor(<?= $config ?>)">
            <div class="flex justify-between items-center mb-1">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300"><?= $label ?></label>

                <div class="flex gap-2">
                    <?= Table::actionButton("format()", "Форматирай", "fas fa-magic", "neutral") ?>
                    <?= Table::actionButton("minify()", "Минифицирай", "fas fa-compress-alt", "neutral") ?>
                    <?= Table::actionButton("toggleWrap()", "Wrap", "fas fa-text-width", "neutral") ?>
                </div>
            </div>

            <div class="mb-4">
                <textarea x-ref="valueStore" class="hidden"><?= htmlspecialchars($value) ?></textarea>

                <div x-ref="editorContainer"
                    class="w-full h-96 border border-slate-300 dark:border-slate-700 rounded-lg shadow-sm">
                </div>

                <textarea name="<?= $name ?>" x-ref="textarea" class="hidden"><?= htmlspecialchars($value) ?></textarea>
            </div>
        </div>
        <?php
        echo ob_get_clean();
    }

    public static function multiselect(string $name, string $label, array $options = [], array $selected = []): void
    {
        ?>
        <div class="mb-4" x-data="tomSelect">
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5"><?= $label ?></label>

            <select name="<?= $name ?>[]" multiple class="w-full">
                <?php foreach ($options as $val => $text): ?>
                    <option value="<?= $val ?>" <?= in_array($val, $selected) ? 'selected' : '' ?>>
                        <?= $text ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    public static function customSelect(string $name, string $label, array $options = [], string $selected = ''): void
    {
        $selectedText = $options[$selected] ?? 'Изберете...';
        ?>
        <div x-data="{ 
            open: false, 
            selected: '<?= $selected ?>', 
            label: '<?= $selectedText ?>',
            placement: 'bottom' 
        }">
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5"><?= $label ?></label>
            
            <select name="<?= $name ?>" class="hidden">
                <?php foreach ($options as $val => $text): ?>
                    <option value="<?= $val ?>" :selected="selected === '<?= $val ?>'"><?= $text ?></option>
                <?php endforeach; ?>
            </select>

            <div class="relative" x-init="$watch('open', value => {
                if (value) {
                    const rect = $el.getBoundingClientRect();
                    const windowHeight = window.innerHeight;
                    placement = (windowHeight - rect.bottom < 300) ? 'top' : 'bottom';
                }
            })">
                <button type="button" @click="open = !open" @click.away="open = false"
                    class="w-full flex justify-between items-center px-4 py-2.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-md focus:ring-2 focus:ring-indigo-500">
                    <span x-text="label"></span>
                    <i class="fas fa-chevron-down text-xs text-slate-400"></i>
                </button>

                <ul x-show="open" 
                    x-cloak
                    x-transition
                    :class="placement === 'top' ? 'bottom-full mb-1' : 'top-full mt-1'"
                    class="absolute z-50 w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md shadow-lg max-h-60 overflow-y-auto">
                    <?php foreach ($options as $val => $text): ?>
                        <li @click="selected = '<?= $val ?>'; label = '<?= $text ?>'; open = false"
                            class="px-4 py-2 cursor-pointer hover:bg-indigo-50 dark:hover:bg-indigo-900/50"
                            :class="selected === '<?= $val ?>' ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : ''">
                            <?= $text ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php
    }
}
