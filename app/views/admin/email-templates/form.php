<?php
use Flex\Core\UI\Form;
use Flex\Core\UI\Page;
use Flex\Core\UI\Table;

$template = $template ?? null;
$savedVariables = [];

// Безопасно извличане на променливите
if ($template && !empty($template->variables)) {
    $data = is_string($template->variables) ? json_decode($template->variables, true) : $template->variables;
    $savedVariables = is_array($data) ? $data : [];
}

Page::header(
    title: $template ? 'Редактиране на шаблон' : 'Създаване на шаблон',
    backUrl: '/admin/email-templates',
    subtitle: $template ? 'Променете параметрите на имейл шаблона' : 'Дефинирайте нов имейл шаблон за системата'
);
?>

<form action="<?= $template ? '/admin/email-templates/update/' . $template->id : '/admin/email-templates/store' ?>"
    method="POST" class="max-w-5xl space-y-6">

    <?php Form::section(function () use ($template) { ?>
        <?php Form::row(function () use ($template) { ?>
            <?php Form::input('name', 'Име на шаблона', ['value' => $template?->name, 'required' => true]); ?>
            <?php Form::input('slug', 'Slug (Идентификатор)', ['value' => $template?->slug, 'required' => true]); ?>
        <?php }); ?>
        <?php Form::row(function () use ($template) { ?>
            <?php Form::input('category', 'Категория', ['value' => $template?->category]); ?>
            <?php Form::input('subject', 'Тема на имейла', ['value' => $template?->subject, 'required' => true]); ?>
        <?php }); ?>
    <?php }, 'Основни параметри'); ?>

    <?php Form::section(function () use ($template) { ?>
        <div x-data="{ tab: 'edit', content: '' }" class="space-y-4">
            <div class="flex gap-2 border-b border-slate-200 dark:border-slate-700 pb-px">

                <button type="button" @click="tab = 'edit'"
                    :class="tab === 'edit' ? 'text-white bg-primary' : 'border-transparent text-slate-500 hover:text-white hover:bg-primary'"
                    class="px-4 py-2 border-t border-l border-r border-slate-200 dark:border-slate-700 rounded-t-md font-medium transition-colors">
                    Редактиране
                </button>

                <button type="button" @click="tab = 'visual'; content = document.querySelector('[name=\'body\']').value"
                    :class="tab === 'visual' ? 'text-white bg-primary' : 'border-transparent text-slate-500 hover:text-white hover:bg-primary'"
                    class="px-4 py-2 border-t border-l border-r border-slate-200 dark:border-slate-700 rounded-t-md font-medium transition-colors">
                    Визуален преглед
                </button>
            </div>

            <div x-show="tab === 'edit'">
                <?php Form::codeEditor('body', 'HTML Шаблон', [
                    'mode' => 'html',
                    'value' => $template?->body,
                    'class' => 'w-full h-96'
                ]); ?>
            </div>

            <div x-show="tab === 'visual'" class="w-full">
                <div class="bg-white border rounded shadow-inner overflow-hidden">
                    <iframe class="w-full h-200 border-none" :srcdoc="content"></iframe>
                </div>
            </div>
        </div>
    <?php }, 'HTML Шаблон'); ?>

    <?php Form::section(function () use ($savedVariables) { ?>
        <div class="space-y-4">
            <?php if (empty($savedVariables)): ?>
                <p class="text-sm text-slate-500 italic">Няма открити променливи в шаблона. Запишете промените, за да сканирате
                    за такива.</p>
            <?php else: ?>
                <?php foreach ($savedVariables as $var): ?>
                    <?php $key = $var['key'] ?? ''; ?>
                    <div
                        class="flex items-center gap-4 p-3 bg-slate-50 dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700">
                        <div class="w-1/3">
                            <?php echo Table::statusBadge('{{' . htmlspecialchars($key) . '}}', 'code'); ?>
                        </div>
                        <div class="w-2/3">
                            <?php Form::textarea('var_desc[' . htmlspecialchars($key) . ']', 'Описание', [
                                'value' => $var['label'] ?? '',
                                'placeholder' => 'Въведете описание за тази променлива...',
                                'class' => 'w-full'
                            ]); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php }, 'Описание на променливите'); ?>

    <div class="pb-10">
        <?php Form::submit($template ? 'Запазване' : 'Създаване', 'fa-save'); ?>
    </div>

</form>
