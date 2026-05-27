<?php

use Flex\Core\UI\Form;
?>

<div class="border-b border-slate-200 dark:border-slate-700 mb-5">
    <nav class="flex space-x-1" aria-label="Tabs">
        <?php foreach ($definedGroups as $key => $label): ?>
            <?php $isActive = ($key === $currentGroup); ?>
            <a href="/admin/settings/<?= $key ?>" class="<?= $isActive
                  ? 'text-primary dark:text-white'
                  : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-200'
                  ?> flex items-center px-6 py-4 font-semibold border-b-2 transition-all duration-200">

                <i class="fa-solid <?= $this->getGroupIcon($key) ?> mr-2"></i>
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </nav>
</div>

<form action="/admin/settings/<?= $currentGroup ?>/update" method="POST">
    <?php
    $viewFile = __DIR__ . '/' . $currentGroup . '.php';
    if (file_exists($viewFile)) {
        include $viewFile;
    } else {
        echo '<p class="text-slate-500 italic">Секцията не е намерена.</p>';
    }
    ?>

    <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-700">
        <?php Form::submit('Запазване на промените', 'fa-save'); ?>
    </div>
</form>
