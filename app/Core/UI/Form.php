<?php

namespace Flex\Core\UI;

use Flex\Core\Auth;

class Form
{
    public static function videoGallery(string $name, string $label, array $options = []): void
    {
        $videos = $options['value'] ?? [];
        $jsData = htmlspecialchars(json_encode($videos), ENT_QUOTES, 'UTF-8');
        $componentId = 'video_gallery_' . preg_replace('/[^a-zA-Z0-9]/', '_', $name);

        ?>
        <div x-data="videoGalleryManager(<?= $jsData ?>)" id="<?= $componentId ?>" class="space-y-4">
            <label class="block font-semibold text-slate-700 dark:text-slate-300"><?= $label ?></label>

            <input type="hidden" :name="'<?= $name ?>'" :value="JSON.stringify(videos)">

            <input type="file" multiple accept="video/*" class="hidden" :id="'<?= $name ?>_input'" 
                @change="addVideos($event.target.files)">

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <template x-for="(video, index) in videos" :key="index">
                    <div class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden bg-slate-50 dark:bg-slate-900 group">
                        <div class="relative bg-black h-48 group">
                            <video :id="'vid_' + index" :src="video.url" class="w-full h-full object-contain" @ended="video.isPlaying = false"></video>
                            
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/40 gap-4">
                                <button type="button" @click="togglePlay(video, index)" class="text-white text-2xl hover:text-indigo-400">
                                    <i class="fas" :class="video.isPlaying ? 'fa-pause' : 'fa-play'"></i>
                                </button>
                                <button type="button" @click="toggleFullscreen(index)" class="text-white text-2xl hover:text-indigo-400">
                                    <i class="fas fa-expand"></i>
                                </button>
                            </div>
                            
                            <button type="button" @click="removeVideo(index)" class="absolute top-2 right-2 bg-rose-500/80 hover:bg-rose-600 text-white rounded-full w-6 h-6 flex items-center justify-center z-10">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>

                        <div class="p-3 bg-white dark:bg-slate-800">
                            <div x-show="video.isUploading" class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 mb-3 overflow-hidden">
                                <div class="bg-indigo-600 h-2 transition-all duration-300" 
                                    :style="'width: ' + video.progress + '%'"></div>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-xs text-slate-500 truncate max-w-[50%]" x-text="video.file ? video.file.name : 'Видео'"></span>
                                
                                <button type="button" 
                                        @click="uploadVideo(video, index)" 
                                        :disabled="video.isUploading || video.isUploaded"
                                        class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors flex items-center gap-2"
                                        :class="video.isUploaded ? 'bg-green-100 text-green-700' : 'bg-indigo-600 hover:bg-indigo-700 text-white'">
                                    
                                    <i class="fas" :class="video.isUploading ? 'fa-spinner fa-spin' : (video.isUploaded ? 'fa-check' : 'fa-cloud-upload-alt')"></i>
                                    <span x-text="video.isUploading ? video.progress + '%' : (video.isUploaded ? 'Качено' : 'Качи')"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <button type="button" @click="document.getElementById('<?= $name ?>_input').click()" 
                        class="h-48 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg flex flex-col items-center justify-center text-slate-400 hover:text-indigo-500 hover:border-indigo-400 transition-all bg-slate-50 dark:bg-slate-800/50">
                    <i class="fas fa-video text-2xl mb-2"></i>
                    <span class="text-sm font-medium">Качи видео</span>
                </button>
            </div>
        </div>
        <?php
    }

    public static function file(string $name, string $label, array $attrs = []): void
    {
        $id = 'file_' . $name;
        $currentImage = $attrs['current_image'] ?? null;
        $description = $attrs['description'] ?? null;
        $inputName = $name; 

        ?>
        <div x-data="{ previewUrl: null }" class="space-y-1.5">
            <label class="block font-semibold text-slate-700 dark:text-slate-300">
                <?= $label ?>
            </label>
            
            <div class="flex items-center gap-4 p-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-md">
                <input type="file" name="<?= $inputName ?>" id="<?= $id ?>" class="hidden" 
                    @change="previewUrl = URL.createObjectURL($event.target.files[0])">

                <div @click="document.getElementById('<?= $id ?>').click()" 
                    class="w-40 h-40 shrink-0 border border-slate-300 dark:border-slate-600 rounded bg-white dark:bg-slate-800 flex items-center justify-center cursor-pointer hover:ring-2 hover:ring-indigo-500 transition-all overflow-hidden relative shadow-sm">
                    
                    <img x-show="previewUrl" :src="previewUrl" class="w-full h-full object-cover" x-cloak>
                    <?php if ($currentImage): ?>
                        <img x-show="!previewUrl" src="<?= $currentImage ?>" class="w-full h-full object-cover">
                    <?php endif; ?>
                    
                    <div x-show="!previewUrl && !<?= $currentImage ? 'true' : 'false' ?>" class="text-slate-400">
                        <i class="fa-solid fa-plus text-sm"></i>
                    </div>
                </div>

                <div class="flex flex-col">
                    <?php if ($description): ?>
                        <span class="text-slate-500 dark:text-slate-400"><?= $description ?></span>
                    <?php endif; ?>
                    
                    <button type="button" @click="document.getElementById('<?= $id ?>').click()" 
                            class="font-semibold mt-1 self-start">
                        <?= $currentImage ? 'Промяна' : 'Качване' ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    public static function gallery(string $name, string $label, array $options = []): void
    {
        $images = $options['value'] ?? [];
        $jsData = htmlspecialchars(json_encode($images), ENT_QUOTES, 'UTF-8');
        $componentId = 'gallery_' . preg_replace('/[^a-zA-Z0-9]/', '_', $name);

        ?>
        <div x-data="galleryManager(<?= $jsData ?>)" id="<?= $componentId ?>" class="space-y-4">
            <label class="block font-semibold text-slate-700 dark:text-slate-300"><?= $label ?></label>

            <input type="hidden" :name="'<?= $name ?>'" :value="JSON.stringify(images)">

            <input type="file" multiple class="hidden" :id="'<?= $name ?>_input'" 
                @change="addImages($event.target.files)">

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <template x-for="(url, index) in images" :key="index">
                    <div class="relative aspect-square border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden group cursor-move"
                        draggable="true"
                        @dragstart="dragStart(index)"
                        @dragover.prevent="dragOver(index)"
                        @dragend="dragEnd()"
                        :class="dragging === index ? 'opacity-50 border-indigo-500' : 'opacity-100'">
                        
                        <img :src="url" class="w-full h-full object-cover">
                        
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-between p-2">
                            
                            <div class="flex justify-between">
                                <button type="button" @click="move(index, -1)" :disabled="index === 0"
                                        class="bg-black/50 text-white rounded w-6 h-6 flex items-center justify-center disabled:opacity-30">
                                    <i class="fas fa-arrow-left text-xs"></i>
                                </button>
                                <button type="button" @click="move(index, 1)" :disabled="index === images.length - 1"
                                        class="bg-black/50 text-white rounded w-6 h-6 flex items-center justify-center disabled:opacity-30">
                                    <i class="fas fa-arrow-right text-xs"></i>
                                </button>
                            </div>

                            <button type="button" @click="removeImage(index)" 
                                    class="ml-auto bg-rose-500 text-white rounded-full w-6 h-6 flex items-center justify-center">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    </div>
                </template>

                <button type="button" @click="document.getElementById('<?= $name ?>_input').click()" 
                        class="aspect-square border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg flex flex-col items-center justify-center text-slate-400 hover:text-indigo-500 hover:border-indigo-400 transition-all bg-slate-50 dark:bg-slate-800/50">
                    <i class="fas fa-plus text-2xl mb-2"></i>
                    <span class="text-sm font-medium">Качи</span>
                </button>
            </div>

            <div x-show="images.length === 0" 
                class="p-4 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-400 text-sm flex items-center gap-3">
                <i class="fas fa-info-circle"></i>
                <span>Все още няма качени снимки в галерията. Използвайте бутона „Качи“, за да добавите.</span>
            </div>

            <div x-show="showFullscreen" 
                x-cloak
                class="fixed inset-0 z-9999 flex items-center justify-center bg-black/95 p-4"
                @keydown.escape.window="closeFullscreen()"
                @click.self="closeFullscreen()">
                
                <button type="button" @click="closeFullscreen()" class="absolute top-5 right-5 text-white text-3xl hover:text-gray-300 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
                
                <img :src="activeImage" class="max-w-full max-h-full object-contain shadow-2xl">
            </div>
        </div>
        <?php
    }

    public static function create(array $options = []): void
    {
        $action = $options['action'] ?? '';
        $method = $options['method'] ?? 'POST';
        $enctype = ($options['files'] ?? false) ? 'enctype="multipart/form-data"' : '';
        
        echo "<form action='{$action}' 
                    method='{$method}' 
                    {$enctype} 
                    class='space-y-6' 
                    x-data='{ isSubmitting: false }' 
                    @submit='if (\$el.checkValidity()) isSubmitting = true;'>";
    }

    public static function close(): void
    {
        echo "</form>";
    }

    public static function input(string $name, string $label, array $attrs = []): void
    {
        $value = $attrs['value'] ?? '';
        $placeholder = $attrs['placeholder'] ?? '';
        $required = isset($attrs['required']) ? 'required' : '';
        $disabled = isset($attrs['disabled']) ? 'disabled' : '';
        $extra = $attrs['extra'] ?? '';
        $xModel = isset($attrs['x-model']) ? "x-model=\"{$attrs['x-model']}\"" : '';
        $customClass = $attrs['class'] ?? '';
        $icon = $attrs['icon'] ?? '';

        $typeAttr = '';
        if (isset($attrs['type'])) {
            $typeAttr = 'type="' . $attrs['type'] . '"';
        } elseif (isset($attrs[':type'])) {
            $typeAttr = ':type="' . $attrs[':type'] . '"';
        } else {
            $typeAttr = 'type="text"';
        }

        $ignoredKeys = ['value', 'type', ':type', 'placeholder', 'required', 'disabled', 'extra', 'x-model', 'class', 'icon'];
        $dynamicAttrs = '';
        foreach ($attrs as $key => $val) {
            if (!in_array($key, $ignoredKeys)) {
                $dynamicAttrs .= " {$key}=\"{$val}\"";
            }
        }

        ?>
        <div>
            <?php if (!empty($icon)): ?>
                <i class="absolute top-4 left-3 fa-solid text-slate-500 hover:text-slate-200 <?= $icon ?>"></i>
            <?php endif; ?>
            <?php if (!empty($label)): ?>
                <label for="<?= $name ?>" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    <?= $label ?> <?= $required ? '<span class="text-rose-500">*</span>' : '' ?>
                </label>
            <?php endif; ?>
            <input <?= $typeAttr ?> name="<?= $name ?>" id="<?= $name ?>" value="<?= htmlspecialchars($value) ?>"
                placeholder="<?= $placeholder ?>" <?= $required ?> <?= $disabled ?> <?= $extra ?> <?= $xModel ?> <?= $dynamicAttrs ?>
                class="<?= !empty($icon) ? 'pr-4 pl-9' : 'px-4' ?> w-full py-2.5 rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:focus:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-primary transition-all outline-none <?= $customClass ?>">
        </div>
        <?php
    }

    public static function textarea(string $name, string $label, array $attrs = []): void
    {
        $value = $attrs['value'] ?? '';
        $rows = $attrs['rows'] ?? 3;
        $placeholder = $attrs['placeholder'] ?? '';
        $xModel = isset($attrs['x-model']) ? "x-model=\"{$attrs['x-model']}\"" : '';

        ?>
        <div>
            <label for="<?= $name ?>" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                <?= $label ?>
            </label>
            <textarea name="<?= $name ?>" id="<?= $name ?>" rows="<?= $rows ?>" placeholder="<?= $placeholder ?>" <?= $xModel ?> class="w-full px-4 py-2.5 rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:focus:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none"><?= isset($attrs['x-model']) ? '' : htmlspecialchars($value) ?></textarea>
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
        $value = $options['value'] ?? false;
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
                <input type="hidden" name="<?= $name ?>" value="0">
                
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
            @click="$dispatch('form-submitting')" 
            :disabled="isSubmitting"
            class="inline-flex items-center gap-2 px-6 py-2 rounded-md text-lg transition-all shadow-sm active:scale-95 <?= $class ?> disabled:opacity-70 disabled:cursor-not-allowed">
            
            <span x-show="!isSubmitting">
                <?php if ($icon): ?>
                    <i class="fa-solid <?= $icon ?> text-lg"></i>
                <?php endif; ?>
            </span>
            
            <span x-show="isSubmitting" x-cloak>
                <i class="fa-solid fa-spinner fa-spin text-lg"></i>
            </span>

            <span x-text="isSubmitting ? 'Моля, изчакайте...' : '<?= $label ?>'"></span>
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

    public static function date(string $name, string $label, array $attrs = []): void
    {
        $value = $attrs['value'] ?? '';
        $id = 'date_' . $name;
        
        ?>
        <div class="space-y-1.5">
            <label class="block font-semibold text-slate-700 dark:text-slate-300"><?= $label ?></label>
            <input type="text" 
                name="<?= $name ?>" 
                id="<?= $id ?>" 
                value="<?= $value ?>"
                x-data="datepicker()" 
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 focus:ring-2 focus:ring-indigo-500 transition-all">
        </div>
        <?php
    }

    public static function row(callable $slot, int $cols = 2): void
    {
        echo "<div style='grid-template-columns: repeat($cols, minmax(0, 1fr));' class='grid gap-5'>";
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
        ?>

        <div x-cloak x-data="uiSection('<?= $sectionId ?>', <?= $isOpen ? 'true' : 'false' ?>)"
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
                <div class="p-5 space-y-5">
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
        <div class="min-w-60" x-data="{ 
            open: false, 
            selected: '<?= $selected ?>', 
            label: '<?= $selectedText ?>',
            placement: 'bottom' 
        }">
            <label class="block font-semibold text-slate-700 dark:text-slate-300"><?= $label ?></label>

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

                <ul x-show="open" x-cloak x-transition :class="placement === 'top' ? 'bottom-full mb-1' : 'top-full mt-1'"
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
    
    public static function heading(string $text, string $tag = 'h2', string $class = 'text-lg md:text-xl font-semibold'): void
    {
        echo "<$tag class='$class'>$text</$tag>";
    }

    public static function repeater(string $name, string $label, array $options = []): void
    {
        $items = $options['value'] ?? [[]];
        $fields = $options['fields'] ?? [];
        $jsData = htmlspecialchars(json_encode($items), ENT_QUOTES, 'UTF-8');

        ?>
        <div x-data="repeater(<?= $jsData ?>)" class="space-y-6">
            <label class="block font-semibold text-slate-700 dark:text-slate-300"><?= $label ?></label>

            <template x-for="(item, index) in items" :key="index">
                <div class="p-5 bg-slate-50 dark:bg-slate-900/30 border border-slate-200 dark:border-slate-700 rounded-lg relative group">
                    <div class="grid gap-4">
                        <?php foreach ($fields as $key => $field): ?>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    <?= $field['label'] ?>
                                </label>

                                <?php if ($field['type'] === 'textarea'): ?>
                                    <textarea 
                                        :name="'<?= $name ?>[' + index + '][<?= $key ?>]'" 
                                        x-model="item.<?= $key ?>" 
                                        rows="3"
                                        class="w-full px-4 py-2.5 rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none"
                                    ></textarea>
                                <?php else: ?>
                                    <input 
                                        type="text"
                                        :name="'<?= $name ?>[' + index + '][<?= $key ?>]'" 
                                        x-model="item.<?= $key ?>" 
                                        class="w-full px-4 py-2.5 rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none"
                                    >
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="button" @click="removeItem(index)" 
                            class="absolute -right-2 -top-2 bg-rose-500 text-white rounded-full w-6 h-6 flex items-center justify-center shadow-lg hover:bg-rose-600">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
            </template>

            <button type="button" @click="addItem()" 
                    class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-plus"></i> Добави нов елемент
            </button>
        </div>
        <?php
    }
}