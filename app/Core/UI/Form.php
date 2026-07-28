<?php

namespace Flex\Core\UI;

use Flex\Core\Auth;
use Flex\Core\Controllers\SettingsController;

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
                    <div
                        class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden bg-slate-50 dark:bg-slate-900 group">
                        <div class="relative bg-black h-48 group">
                            <video :id="'vid_' + index" :src="video.url" class="w-full h-full object-contain"
                                @ended="video.isPlaying = false"></video>

                            <div
                                class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/40 gap-4">
                                <button type="button" @click="togglePlay(video, index)"
                                    class="text-white text-2xl hover:text-indigo-400">
                                    <i class="fas" :class="video.isPlaying ? 'fa-pause' : 'fa-play'"></i>
                                </button>
                                <button type="button" @click="toggleFullscreen(index)"
                                    class="text-white text-2xl hover:text-indigo-400">
                                    <i class="fas fa-expand"></i>
                                </button>
                            </div>

                            <button type="button" @click="removeVideo(index)"
                                class="absolute top-2 right-2 bg-rose-500/80 hover:bg-rose-600 text-white rounded-full w-6 h-6 flex items-center justify-center z-10">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>

                        <div class="p-3 bg-white dark:bg-slate-800">
                            <div x-show="video.isUploading"
                                class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 mb-3 overflow-hidden">
                                <div class="bg-indigo-600 h-2 transition-all duration-300"
                                    :style="'width: ' + video.progress + '%'"></div>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-xs text-slate-500 truncate max-w-[50%]"
                                    x-text="video.file ? video.file.name : 'Видео'"></span>

                                <button type="button" @click="uploadVideo(video, index)"
                                    :disabled="video.isUploading || video.isUploaded"
                                    class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors flex items-center gap-2"
                                    :class="video.isUploaded ? 'bg-green-100 text-green-700' : 'bg-indigo-600 hover:bg-indigo-700 text-white'">

                                    <i class="fas"
                                        :class="video.isUploading ? 'fa-spinner fa-spin' : (video.isUploaded ? 'fa-check' : 'fa-cloud-upload-alt')"></i>
                                    <span
                                        x-text="video.isUploading ? video.progress + '%' : (video.isUploaded ? 'Качено' : 'Качи')"></span>
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

    public static function image(
        string $name,
        string $label,
        array $attrs = []
    ): void {
        $id = 'file_' . $name;
        $removeInputId = 'remove_' . $name;
        $currentImage = $attrs['current_image'] ?? null;
        $fallbackImage = $attrs['fallback_image']
            ?? '/assets/images/no-image.png';
        $description = $attrs['description'] ?? null;
        $title = $attrs['title'] ?? '';
        $hasCurrentImage = !empty($currentImage);

        $componentConfig = htmlspecialchars(
            json_encode([
                'currentImage' => $currentImage,
                'fallbackImage' => $fallbackImage,
                'inputId' => $id,
                'removeInputId' => $removeInputId,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ENT_QUOTES,
            'UTF-8'
        );
        ?>
        <div
            x-data="imageUpload(<?= $componentConfig ?>)"
            x-on:destroy="destroy()"
            class="space-y-2"
        >
            <label
                for="<?= htmlspecialchars($id) ?>"
                class="block font-semibold text-slate-700 dark:text-slate-300"
            >
                <?= htmlspecialchars($label) ?>
            </label>

            <input
                type="hidden"
                name="<?= htmlspecialchars($name) ?>_remove"
                id="<?= htmlspecialchars($removeInputId) ?>"
                value="0"
            >

            <input
                type="file"
                name="<?= htmlspecialchars($name) ?>"
                id="<?= htmlspecialchars($id) ?>"
                accept="image/*"
                class="hidden"
                @change="handleFile($event)"
            >

            <div class="flex flex-col gap-3">
                <div
                    title="<?= htmlspecialchars($title) ?>"
                    class="group relative flex h-40 w-40 items-center justify-center overflow-hidden rounded border border-slate-300 bg-white shadow-sm transition-all dark:border-slate-600 dark:bg-slate-800"
                >
                    <img
                        x-show="previewUrl"
                        :src="previewUrl"
                        class="h-full w-full object-cover"
                        x-cloak
                        alt=""
                    >

                    <?php if ($hasCurrentImage): ?>
                        <img
                            x-show="!previewUrl && !removed"
                            src="<?= htmlspecialchars($currentImage) ?>"
                            class="h-full w-full object-cover"
                            alt="<?= htmlspecialchars($label) ?>"
                        >
                    <?php endif; ?>

                    <img
                        x-show="!previewUrl && (removed || !currentImage)"
                        :src="fallbackImage"
                        class="h-full w-full object-cover"
                        alt=""
                        x-cloak
                    >

                    <button
                        type="button"
                        @click="selectFile()"
                        class="absolute inset-0 flex items-center justify-center bg-black/0 text-white opacity-0 transition-all group-hover:bg-black/40 group-hover:opacity-100"
                        aria-label="Избери изображение"
                    >
                        <i class="fa-solid fa-camera text-xl"></i>
                    </button>
                </div>

                <div class="flex flex-col items-start gap-1">
                    <?php if ($description): ?>
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            <?= htmlspecialchars($description) ?>
                        </span>
                    <?php endif; ?>

                    <button
                        type="button"
                        @click="selectFile()"
                        class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300"
                    >
                        <span x-show="!previewUrl">
                            <?= $hasCurrentImage
                                ? 'Промяна'
                                : 'Избор на файл' ?>
                        </span>

                        <span x-show="previewUrl" x-cloak>
                            Избери друг файл
                        </span>
                    </button>

                    <button
                        type="button"
                        x-show="previewUrl"
                        @click="cancelNewFile()"
                        class="text-sm font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
                        x-cloak
                    >
                        Отказ от новия файл
                    </button>

                    <button
                        type="button"
                        x-show="previewUrl || (!removed && currentImage)"
                        @click="removeImage()"
                        class="text-sm font-semibold text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                        x-cloak
                    >
                        <i class="fa-solid fa-trash-can mr-1"></i>
                        Премахване
                    </button>

                    <button
                        type="button"
                        x-show="removed && currentImage"
                        @click="restoreImage()"
                        class="text-sm font-semibold text-slate-600 hover:text-slate-800 dark:text-slate-300 dark:hover:text-white"
                        x-cloak
                    >
                        Възстановяване
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

            <input type="file" multiple class="hidden" :id="'<?= $name ?>_input'" @change="addImages($event.target.files)">

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <template x-for="(url, index) in images" :key="index">
                    <div class="relative aspect-square border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden group cursor-move"
                        draggable="true" @dragstart="dragStart(index)" @dragover.prevent="dragOver(index)" @dragend="dragEnd()"
                        :class="dragging === index ? 'opacity-50 border-indigo-500' : 'opacity-100'">

                        <img :src="url" class="w-full h-full object-cover">

                        <div
                            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-between p-2">

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

            <div x-show="showFullscreen" x-cloak class="fixed inset-0 z-9999 flex items-center justify-center bg-black/95 p-4"
                @keydown.escape.window="closeFullscreen()" @click.self="closeFullscreen()">

                <button type="button" @click="closeFullscreen()"
                    class="absolute top-5 right-5 text-white text-3xl hover:text-gray-300 transition-colors">
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
        
        $required = (!empty($attrs['required']) && $attrs['required'] === true) ? 'required' : '';
        $disabled = (!empty($attrs['disabled']) && $attrs['disabled'] === true) ? 'disabled' : '';
        
        $extra = $attrs['extra'] ?? '';
        $xModel = isset($attrs['x-model']) ? "x-model=\"{$attrs['x-model']}\"" : '';
        $customClass = $attrs['class'] ?? '';
        $icon = $attrs['icon'] ?? '';
        $hint = $attrs['hint'] ?? '';

        $typeAttr = '';
        if (isset($attrs['type'])) {
            $typeAttr = 'type="' . $attrs['type'] . '"';
        } elseif (isset($attrs[':type'])) {
            $typeAttr = ':type="' . $attrs[':type'] . '"';
        } else {
            $typeAttr = 'type="text"';
        }

        $ignoredKeys = ['value', 'type', ':type', 'placeholder', 'required', 'disabled', 'extra', 'x-model', 'class', 'icon', 'hint', 'options', 'fields'];
        $dynamicAttrs = '';
        foreach ($attrs as $key => $val) {
            if (!in_array($key, $ignoredKeys)) {
                $dynamicAttrs .= " {$key}=\"{$val}\"";
            }
        }

        $hasXModel = isset($attrs['x-model']) || isset($attrs[':value']);

        ?>
        <div class="<?= !empty($icon) ? 'relative' : '' ?>">
            
            <?php if (!empty($label)): ?>
                <label for="<?= $name ?>" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    <?= htmlspecialchars($label) ?> 
                    <?= $required ? '<span class="text-rose-500">*</span>' : '' ?>
                </label>
            <?php endif; ?>

            <?php if (!empty($icon)): ?>
                <i class="absolute top-9 left-3 fa-solid text-slate-500 <?= $icon ?>"></i>
            <?php endif; ?>

            <input <?= $typeAttr ?> name="<?= $name ?>" id="<?= $name ?>" <?= $hasXModel ? '' : 'value="' . htmlspecialchars($value) . '"' ?>
                placeholder="<?= htmlspecialchars($placeholder) ?>" <?= $required ?> <?= $disabled ?> <?= $extra ?> <?= $xModel ?> <?= $dynamicAttrs ?>
                class="<?= !empty($icon) ? 'pr-4 pl-9' : 'px-4' ?> w-full py-2.5 rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:focus:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-primary transition-all outline-none <?= $customClass ?>">

            <?php if (!empty($hint)): ?>
                <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400"><?= htmlspecialchars($hint) ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function textarea(string $name, string $label, array $attrs = []): void
    {
        $value = $attrs['value'] ?? '';
        $rows = $attrs['rows'] ?? 3;
        $placeholder = $attrs['placeholder'] ?? '';

        $excludeAttrs = ['value', 'rows', 'placeholder', 'label', 'type', 'options', 'fields'];
        
        $htmlAttrs = [];
        foreach ($attrs as $attrKey => $attrVal) {
            if (!in_array($attrKey, $excludeAttrs, true)) {
                if ($attrVal === true) {
                    $htmlAttrs[] = htmlspecialchars($attrKey);
                } elseif ($attrVal !== false && $attrVal !== null) {
                    $htmlAttrs[] = sprintf('%s="%s"', htmlspecialchars($attrKey), htmlspecialchars($attrVal));
                }
            }
        }
        
        $customAttrsString = implode(' ', $htmlAttrs);
        $hasXModel = isset($attrs['x-model']) || isset($attrs[':value']);

        ?>
        <div>
            <label for="<?= htmlspecialchars($name) ?>" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                <?= $label ?>
            </label>
            <textarea 
                name="<?= htmlspecialchars($name) ?>" 
                id="<?= htmlspecialchars($name) ?>" 
                rows="<?= (int)$rows ?>" 
                placeholder="<?= htmlspecialchars($placeholder) ?>" 
                <?= $customAttrsString ?>
                class="w-full px-4 py-2.5 rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:focus:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none"
            ><?= $hasXModel ? '' : htmlspecialchars($value) ?></textarea>
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
            <?php if (!empty($label)): ?>
                <label for="<?= $name ?>" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    <?= $label ?> <?= $required ? '<span class="text-rose-500">*</span>' : '' ?>
                </label>
            <?php endif; ?>
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
        $toggleId = 'toggle-' . bin2hex(random_bytes(4));
        $value = $options['value'] ?? false;
        $description = $options['description'] ?? null;
        $xModel = $options['attr']['x-model'] ?? null;
        $dynamicName = $options['attr'][':name'] ?? null;

        $localVar = 'toggle_' . str_replace('-', '_', $toggleId);
        $model = $xModel ?? $localVar;

        $attributes = '';
        if (isset($options['attr']) && is_array($options['attr'])) {
            foreach ($options['attr'] as $attr => $val) {
                if (in_array($attr, ['options', 'fields', 'x-model', ':name'], true)) continue;
                $attributes .= " {$attr}=\"{$val}\"";
            }
        }

        $nameAttr = $dynamicName ? ":name=\"{$dynamicName}\"" : "name=\"{$name}\"";

        $dataAttr = $xModel ? '' : "x-data=\"{ {$localVar}: " . ($value ? 'true' : 'false') . " }\"";
        ?>
        <div class="flex items-center gap-4" <?= $dataAttr ?> x-id="['<?= $toggleId ?>']">
            <label :for="$id('<?= $toggleId ?>')" class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                <input type="hidden" <?= $nameAttr ?> :value="<?= $model ?> ? 1 : 0">

                <input type="checkbox"
                    :id="$id('<?= $toggleId ?>')"
                    <?= $nameAttr ?>
                    value="1"
                    class="sr-only peer"
                    x-model="<?= $model ?>"
                    <?= $attributes ?>>

                <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-indigo-600 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all shadow-inner">
                </div>
            </label>

            <div class="flex flex-col select-none cursor-pointer" @click="document.getElementById($id('<?= $toggleId ?>')).click()">
                <span class="font-semibold text-slate-800 dark:text-slate-200 leading-tight"><?= $label ?></span>
                <?php if ($description): ?>
                    <span class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?= $description ?></span>
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
        <button type="<?= $type ?>" @click="$dispatch('form-submitting')" :disabled="isSubmitting"
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
            <input type="text" name="<?= $name ?>" id="<?= $id ?>" value="<?= $value ?>" x-data="datepicker()"
                class="w-full py-2.5 px-4 rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:focus:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-primary transition-all outline-none">
        </div>
        <?php
    }

    public static function row(callable $slot, array|int $cols = 1): void
    {
        $gridClass = "";

        if (is_array($cols)) {
            foreach ($cols as $breakpoint => $count) {
                $gridClass .= " {$breakpoint}:grid-cols-{$count}";
            }
        } else {
            $gridClass = "grid-cols-{$cols}";
        }

        echo "<div class='grid gap-5 {$gridClass}'>";
        $slot();
        echo "</div>";
    }

    public static function section(callable $slot, string|null $title = null, string|null $id = null, bool $isWithBottomMargin = true): void
    {
        $sectionId = $id ?? 'section_' . substr(md5($title ?? 'default'), 0, 8);
        $user = Auth::user();

        $isOpen = $_SESSION['ui_states'][$sectionId]
            ?? $user->options['ui_states'][$sectionId]
            ?? true;
        ?>

        <div x-cloak x-data="uiSection('<?= $sectionId ?>', <?= $isOpen ? 'true' : 'false' ?>)"
            class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md shadow-sm <?= $isWithBottomMargin ? 'mb-5' : '' ?>">

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

    public static function customSelect(string $name, string $label, array $options = [], string $selected = '', array $attrs = []): void
    {
        $selectedText = $options[$selected] ?? 'Изберете...';
        $xModel = $attrs['x-model'] ?? null;
        $dynamicName = $attrs[':name'] ?? null;
        $optionsJson = htmlspecialchars(json_encode($options, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
        
        $excludeAttrs = ['x-model', ':name', 'options', 'value', 'type', 'fields', 'label'];
        $htmlAttrs = [];
        foreach ($attrs as $attrKey => $attrVal) {
            if (!in_array($attrKey, $excludeAttrs, true)) {
                if ($attrVal === true) {
                    $htmlAttrs[] = htmlspecialchars($attrKey);
                } elseif ($attrVal !== false && $attrVal !== null) {
                    $htmlAttrs[] = sprintf('%s="%s"', htmlspecialchars($attrKey), htmlspecialchars($attrVal));
                }
            }
        }
        $selectAttrsString = implode(' ', $htmlAttrs);
        ?>
        <div class="min-w-60" 
            x-data="{ 
                open: false, 
                selected: '<?= htmlspecialchars($selected, ENT_QUOTES, 'UTF-8') ?>', 
                options: <?= $optionsJson ?>,
                placement: 'bottom',
                get labelText() {
                    return this.options[this.selected] || 'Изберете...';
                }
            }"
            x-modelable="selected"
            <?= $xModel ? 'x-model="' . htmlspecialchars($xModel, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
        >
            <?php if (!empty($label)): ?>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5"><?= $label ?></label>
            <?php endif; ?>
            
            <select
                <?= $dynamicName
                    ? ':name="' . htmlspecialchars($dynamicName, ENT_QUOTES, 'UTF-8') . '"'
                    : 'name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"' ?>
                <?= $selectAttrsString ?>
                x-model="selected"
                class="hidden"
            >
                <?php foreach ($options as $val => $text): ?>
                    <option value="<?= htmlspecialchars($val) ?>" :selected="selected == '<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>'"><?= htmlspecialchars($text) ?></option>
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
                    <span x-text="labelText"></span>
                    <i class="fas fa-chevron-down text-xs text-slate-400"></i>
                </button>

                <ul x-show="open" x-cloak x-transition :class="placement === 'top' ? 'bottom-full mb-1' : 'top-full mt-1'"
                    class="absolute z-50 w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md shadow-lg max-h-60 overflow-y-auto">
                    <?php foreach ($options as $val => $text): ?>
                        <li @click="selected = '<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>'; open = false"
                            class="px-4 py-2 cursor-pointer hover:bg-indigo-50 dark:hover:bg-indigo-900/50"
                            :class="selected == '<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>' ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : ''">
                            <?= htmlspecialchars($text) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php
    }

    public static function customSelectWithInput(string $name, string $label, array $options = [], string $selected = ''): void
    {
        $isCustom = !array_key_exists($selected, $options) && !empty($selected);
        $selectedText = $isCustom ? $selected : ($options[$selected] ?? 'Изберете...');
        
        $initialData = json_encode([
            'selected' => $selected,
            'label' => $selectedText,
            'isCustom' => $isCustom,
            'customValue' => $isCustom ? $selected : ''
        ]);

        ?>
        <div class="min-w-60" x-data="customSelectWithInput(<?= htmlspecialchars($initialData) ?>)">
            <?php if (!empty($label)): ?>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5"><?= $label ?></label>
            <?php endif; ?>

            <div class="relative w-full">
                <div x-show="isCustom" class="relative">
                    <input type="text" name="<?= $name ?>" x-ref="customInput" x-model="customValue" 
                        @keydown.backspace="if(customValue === '') { isCustom = false; open = true; }"
                        class="outline-0 w-full pl-4 pr-10 py-2.5 bg-white dark:bg-slate-700 border border-indigo-500 rounded-md focus:ring-2 focus:ring-indigo-500"
                        placeholder="Въведете стойност...">
                    
                    <button type="button" @click="clearCustom()"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <button type="button" x-show="!isCustom" @click="toggleMenu()"
                    class="w-full flex justify-between items-center px-4 py-2.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-md focus:ring-2 focus:ring-indigo-500">
                    <span x-text="label"></span>
                    <i class="fas fa-chevron-down text-xs text-slate-400"></i>
                </button>
                
                <input type="hidden" name="<?= $name ?>" :value="isCustom ? customValue : selected">

                <ul x-show="open" @click.away="open = false" x-cloak
                    class="absolute z-999 w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md shadow-xl mt-1 max-h-60 overflow-y-auto">
                    <?php foreach ($options as $val => $text): ?>
                        <li @click="selectOption('<?= $val ?>', '<?= addslashes($text) ?>')"
                            class="px-4 py-2 cursor-pointer hover:text-white hover:bg-primary dark:hover:bg-primary"> <?= $text ?> </li>
                    <?php endforeach; ?>
                    <li @click="toggleCustom()"
                        class="px-4 py-2 cursor-pointer border-t border-slate-100 dark:border-slate-700 hover:bg-primary dark:hover:bg-primary hover:text-white italic"> + Добави друга стойност... </li>
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
        $items = $options['value'] ?? [];
        $fields = $options['fields'] ?? [];
        $loadUrl = $options['loadUrl'] ?? null;
        $saveUrl = $options['saveUrl'] ?? null;

        $jsData = htmlspecialchars(json_encode($items), ENT_QUOTES, 'UTF-8');
        $jsLoadUrl = $loadUrl ? "'" . $loadUrl . "'" : 'null';
        $jsSaveUrl = $saveUrl ? "'" . $saveUrl . "'" : 'null';
        ?>
        <div x-data="repeater(<?= $jsData ?>, 'title', <?= $jsLoadUrl ?>, <?= $jsSaveUrl ?>)" x-cloak>
            <input type="hidden" name="_<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>_present" value="1">

            <div x-show="isLoading" class="text-sm text-slate-500">
                Зареждане...
            </div>

            <div x-show="!isLoading">
                <div class="menu-tree">
                    <template x-for="(item, index) in items" :key="item.id ?? item._id ?? index">
                        <?php self::renderRepeaterItem('item', 'index', $fields, $name, 0); ?>
                    </template>
                </div>
            </div>

            <button
                type="button"
                @click="addItem()"
                class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors"
            >
                <i class="fas fa-plus"></i>
                Добави нов елемент
            </button>

        </div>
        <?php
    }

    private static function renderRepeaterItem(
        string $item,
        string $indexVar,
        array $fields,
        string $baseName,
        int $level = 0
    ): void {
        $childItemVar = 'item' . ($level + 1);
        $childIndexVar = 'index' . ($level + 1);
        ?>
        <div
            class="menu-sortable-item"
            :data-id="String(<?= $item ?>.id ?? <?= $item ?>._id ?? '')"
            :data-key="<?= $item ?>._key"
        >

            <input
                type="hidden"
                :name="'<?= $baseName ?>[' + <?= $indexVar ?> + '][id]'"
                x-model="<?= $item ?>.id"
            >

            <div class="menu-item flex flex-col">

                <div class="bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden shadow-sm">

                    <div class="px-5 py-3.5 flex items-center justify-between">

                        <div class="flex items-center gap-3 flex-1">

                            <i class="fas fa-grip-vertical drag-handle cursor-move text-slate-400"></i>

                            <div
                                @click="<?= $item ?>._open = !<?= $item ?>._open"
                                class="flex-1 cursor-pointer"
                            >
                                <span
                                    class="font-medium text-sm"
                                    x-text="(<?= $item ?>.label || '').trim() || ('Елемент #' + (<?= $indexVar ?> + 1))"
                                ></span>
                            </div>

                        </div>

                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                @click.stop="addChild(<?= $item ?>)"
                                class="text-primary hover:bg-slate-800 rounded-full w-8 h-8"
                                title="Добави поделемент"
                            >
                                <i class="fas fa-level-down-alt"></i>
                            </button>

                            <button
                                type="button"
                                @click.stop="removeItem(<?= $item ?>._key)"
                                class="text-red-400 hover:bg-slate-800 rounded-full w-8 h-8"
                                title="Премахни елемента и неговите поделементи"
                            >
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>

                    </div>


                    <div
                        x-show="<?= $item ?>._open"
                        x-collapse
                    >

                        <div class="p-5 grid gap-4">

                            <?php foreach ($fields as $key => $field): ?>

                                <?php if (($field['type'] ?? '') !== 'repeater'): ?>

                                    <div class="space-y-1.5">

                                        <label class="text-xs font-semibold uppercase">
                                            <?= $field['label'] ?? '' ?>
                                        </label>

                                        <?php

                                        $field[':name'] =
                                            "'{$baseName}[' + {$indexVar} + '][{$key}]'";

                                        $field['x-model'] =
                                            "{$item}.{$key}";

                                        self::renderField(
                                            $key,
                                            $field
                                        );

                                        ?>

                                    </div>

                                <?php endif; ?>

                            <?php endforeach; ?>

                        </div>

                    </div>

                </div>


                <?php foreach ($fields as $key => $field): ?>

                    <?php if (($field['type'] ?? '') === 'repeater'): ?>

                        <div class="menu-tree pl-5 space-y-3 mt-2">

                            <template
                                x-for="(<?= $childItemVar ?>, <?= $childIndexVar ?>) in (<?= $item ?>.<?= $key ?> || [])"
                                :key="<?= $childItemVar ?>.id ?? <?= $childItemVar ?>._id ?? <?= $childIndexVar ?>"
                            >

                                <?php

                                self::renderRepeaterItem(
                                    $childItemVar,
                                    $childIndexVar,
                                    $field['fields'] ?? [],
                                    "{$baseName}[' + {$indexVar} + '][{$key}]",
                                    $level + 1
                                );

                                ?>

                            </template>

                        </div>

                    <?php endif; ?>

                <?php endforeach; ?>

            </div>

        </div>
        <?php
    }

    private static function renderField(string $key, array $field): void
    {
        $name = $field['name'] ?? $key;

        switch ($field['type'] ?? 'text') {
            case 'textarea':
                self::textarea($name, '', $field);
                break;

            case 'select':
                self::customSelect($name, '', $field['options'] ?? [], '', $field);
                break;

            case 'toggle':
                $field['attr'] = [
                    'x-model' => $field['x-model'] ?? null,
                    ':name' => $field[':name'] ?? null,
                ];
                self::toggle($name, '', $field);
                break;

            default:
                self::input($name, '', $field);
                break;
        }
    }

    public static function tabs(array $items, string $activeKey): void
    {
        ?>
        <div class="border-b border-slate-200 dark:border-slate-700 mb-5">
            <nav class="flex space-x-1" aria-label="Tabs">
                <?php foreach ($items as $key => $item): ?>
                    <?php
                    $label = is_array($item) ? ($item['label'] ?? $key) : $item;
                    $icon = is_array($item) ? ($item['icon'] ?? '') : null;
                    $url = is_array($item) ? ($item['url'] ?? '#') : '#';
                    $isActive = ($key === $activeKey);

                    $activeClass = 'text-primary border-primary dark:text-white dark:border-white';
                    $inactiveClass = 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-200';
                    ?>
                    <a href="<?= $url ?>"
                        class="<?= $isActive ? $activeClass : $inactiveClass ?> flex items-center px-4 py-2 font-semibold border-b-2 transition-all duration-200">
                        <?php if ($icon): ?>
                            <i class="fa-solid <?= $icon ?> mr-2"></i>
                        <?php endif; ?>
                        <?= htmlspecialchars($label) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
        <?php
    }

    public static function renderTabs(array $groups, string $currentGroup, string $urlPrefix): void
    {
        $tabs = [];
        foreach ($groups as $key => $value) {
            $tabs[$key] = [
                'label' => is_array($value) ? ($value['label'] ?? $key) : $value,
                'icon' => SettingsController::getGroupIcon($key) ?? null,
                'url' => $urlPrefix . $key
            ];
        }

        self::tabs($tabs, $currentGroup);
    }
}