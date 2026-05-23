<?php

use Flex\Core\UI\Components\Button;
use Flex\Core\UI\Components\Alert;
use Flex\Core\UI\Form;

$fileTree = $fileTree ?? [];

if (!function_exists('renderHtmlTree')) {
    function renderHtmlTree(array $nodes, string $currentPath = '', bool $isFirstLevel = true)
    {
        $ulClass = $isFirstLevel
            ? 'space-y-1.5 list-none'
            : 'pl-4 space-y-1.5 border-l border-slate-200 dark:border-slate-700/60 ml-2 mt-1 list-none';
        ?>
        <ul class="<?= $ulClass ?>">
            <?php foreach ($nodes as $index => $node):
                $nodePath = $currentPath . ($isFirstLevel ? '' : '-') . $index;
                ?>
                <li class="text-sm select-none" data-name="<?= htmlspecialchars($node['name'], ENT_QUOTES, 'UTF-8') ?>"
                    data-type="<?= $node['type'] ?>" id="node-<?= $nodePath ?>">

                    <?php if ($node['type'] === 'folder'): ?>
                        <div class="space-y-1">
                            <div
                                class="flex items-center justify-between py-1 px-1.5 rounded-lg text-slate-700 dark:text-slate-300 font-medium hover:bg-slate-100 dark:hover:bg-slate-700/40 transition-colors w-full group cursor-pointer border border-gray-200 dark:border-gray-700">

                                <div @click="toggleFolder('<?= $nodePath ?>')" class="flex items-center gap-2 flex-1">
                                    <i class="fa-solid fa-chevron-right text-slate-400 dark:text-slate-500 text-[10px] w-3 transition-transform duration-200"
                                        :class="openedFolders['<?= $nodePath ?>'] ? 'rotate-90' : ''"></i>

                                    <i class="fa-solid text-amber-400 dark:text-amber-500/90 text-base w-4 text-center"
                                        :class="openedFolders['<?= $nodePath ?>'] ? 'fa-folder-open' : 'fa-folder'"></i>

                                    <span class="group-hover:text-slate-900 dark:group-hover:text-white transition-colors">
                                        <?= htmlspecialchars($node['name'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </div>

                                <?= Button::make('Копирай частта')
                                    ->icon('fa-regular fa-copy')
                                    ->type('button')
                                    ->attr('@click.stop="copySubStructure(\'' . $nodePath . '\')"')
                                    ->watch('copiedFolder', $nodePath, 'fa-solid fa-check text-emerald-500', 'Копирано!') ?>
                            </div>

                            <?php if (!empty($node['children'])): ?>
                                <div x-show="openedFolders['<?= $nodePath ?>']" x-collapse style="display: none;">
                                    <?php renderHtmlTree($node['children'], $nodePath, false); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php else: ?>
                        <div
                            class="flex items-center gap-2 p-3 ml-5 border border-gray-200 dark:border-gray-700 text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/20 transition-colors">
                            <?php
                            $icon = match ($node['extension'] ?? '') {
                                'php' => 'fa-brands fa-php text-indigo-500 text-base',
                                'js' => 'fa-brands fa-js text-yellow-500',
                                'css' => 'fa-brands fa-css3-alt text-blue-500',
                                'json' => 'fa-solid fa-code text-emerald-500',
                                'md' => 'fa-solid fa-file-lines text-slate-400',
                                'env' => 'fa-solid fa-gear text-rose-500',
                                default => 'fa-solid fa-file text-slate-400/80'
                            };
                            ?>
                            <i class="<?= $icon ?> w-4 text-center"></i>
                            <span class="font-mono">
                                <?= htmlspecialchars($node['name'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
    }
}

function getAllFolderKeys(array $nodes, string $currentPath = '', bool $isFirstLevel = true): array
{
    $keys = [];
    foreach ($nodes as $index => $node) {
        if ($node['type'] === 'folder') {
            $nodePath = $currentPath . ($isFirstLevel ? '' : '-') . $index;
            $keys[] = $nodePath;
            if (!empty($node['children'])) {
                $keys = array_merge($keys, getAllFolderKeys($node['children'], $nodePath, false));
            }
        }
    }
    return $keys;
}
$allKeysJson = json_encode(getAllFolderKeys($fileTree));

$toggleButton = Button::make('Разгъни всички')
    ->icon('fa-solid fa-folder-plus')
    ->type('button')
    ->attr('@click="toggleAll()"')
    ->toggle('isAllOpen', 'fa-solid fa-folder-minus', 'Сгъни всички');

$copyButton = Button::make('Копирай проекта')
    ->icon('fa-regular fa-copy')
    ->type('button')
    ->attr('@click="copyWholeStructure()"')
    ->toggle('copiedProject', 'fa-solid fa-check text-emerald-500', 'Копирано!');
?>

<?php Form::section(function () use ($fileTree, $allKeysJson, $toggleButton, $copyButton) { ?>
    <div x-data="fileTreeManager(<?= htmlspecialchars($allKeysJson, ENT_QUOTES, 'UTF-8') ?>)">

        <div class="flex flex-wrap items-center gap-2 mb-5 pb-4 border-b border-slate-100 dark:border-slate-700/40">
            <?= $toggleButton ?>
            <?= $copyButton ?>
        </div>

        <div id="tree-container" class="overflow-x-auto pl-1">
            <?php if (!empty($fileTree)): ?>
                <?php renderHtmlTree($fileTree, '', true); ?>
            <?php else: ?>
                <?= Alert::make('Основната директория е празна или недостъпна.') ?>
            <?php endif; ?>
        </div>
    </div>
<?php }, 'Структура на сайта'); ?>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('fileTreeManager', (allKeys) => ({
            openedFolders: {},
            allFolderKeys: allKeys,
            isAllOpen: false,
            copied: false,

            init() {
                this.allFolderKeys.forEach(key => {
                    this.openedFolders[key] = false;
                });

                const toggleBtn = document.querySelector('[ @click="toggleAll()" ]');
                if (toggleBtn) {
                    toggleBtn.setAttribute('x-text', "isAllOpen ? 'Сгъни всички' : 'Разгъни всички'");
                    const iconNode = toggleBtn.querySelector('i');
                    if (iconNode) {
                        iconNode.setAttribute(':class', "isAllOpen ? 'fa-folder-minus' : 'fa-folder-plus'");
                    }
                }

                const copyBtn = document.getElementById('main-copy-btn');
                if (copyBtn) {
                    copyBtn.setAttribute('x-text', "copied ? 'Копирано!' : 'Копирай проекта'");
                    const copyIcon = copyBtn.querySelector('i');
                    if (copyIcon) {
                        copyIcon.setAttribute(':class', "copied ? 'fa-check text-emerald-300' : 'fa-regular fa-copy'");
                    }
                }
            },

            toggleFolder(key) {
                this.openedFolders[key] = !this.openedFolders[key];
            },

            toggleAll() {
                this.isAllOpen = !this.isAllOpen;
                this.allFolderKeys.forEach(key => {
                    this.openedFolders[key] = this.isAllOpen;
                });
            },

            parseDOMToText(containerElement) {
                let text = '';
                const parseLevel = (el, depth = 0) => {
                    const ul = el.querySelector('ul');
                    if (!ul) return;

                    Array.from(ul.children).forEach(li => {
                        const type = li.getAttribute('data-type');
                        const name = li.getAttribute('data-name');
                        const indent = '    '.repeat(depth);

                        if (type === 'folder') {
                            text += `${indent}📂 ${name}/\n`;
                            parseLevel(li, depth + 1);
                        } else if (type === 'file') {
                            text += `${indent}📄 ${name}\n`;
                        }
                    });
                };
                parseLevel(containerElement);
                return text;
            },

            executeCopy(text) {
                navigator.clipboard.writeText(text).then(() => {
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                }).catch(() => {
                    alert('Неуспешно копиране на структурата.');
                });
            },

            copyWholeStructure() {
                const container = document.getElementById('tree-container');
                const structureText = this.parseDOMToText(container);

                navigator.clipboard.writeText(structureText).then(() => {
                    this.copiedProject = true;
                    setTimeout(() => this.copiedProject = false, 2000);
                });
            },

            copySubStructure(nodePath) {
                const folderLi = document.getElementById(`node-${nodePath}`);
                if (!folderLi) return;

                const folderName = folderLi.getAttribute('data-name');
                let text = `📂 ${folderName}/\n`;
                text += this.parseDOMToText(folderLi);

                navigator.clipboard.writeText(text).then(() => {
                    this.copiedFolder = nodePath;
                    setTimeout(() => this.copiedFolder = null, 2000);
                });
            }
        }));
    });
</script>
