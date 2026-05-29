<?php
use Flex\Core\UI\Table;

$templates = $templates ?? [];
$categoryOptions = ['' => 'Всички категории'];
foreach ($categories as $cat) {
    $categoryOptions[$cat] = $cat;
}
?>

<div x-data="tableManager({
    deleteUrl: '/admin/email-templates/delete',
    confirmDeleteMessage: 'Сигурни ли сте, че искате да изтриете този шаблон?'
})">
<?php Table::header(slot: function ($_ = null) use ($categoryOptions) { ?>
    <?php Table::search('Търсене на шаблон...'); ?>
    <?php Table::select('category', $categoryOptions, $_GET['category'] ?? ''); ?>
    <?php Table::submit('Приложи'); ?>
    <?php Table::reset('/admin/email-templates'); ?>
<?php }); ?>

<?php Table::create($templates)
    ->column('Име на шаблон', function ($t, $_ = null) {
        return Table::textCell($t->name ?? '---');
    }, 'name')

    ->column('Slug', function ($t, $_ = null) {
        return Table::statusBadge($t->slug ?? '', 'code');
    }, 'slug')

    ->column('Категория', function ($t, $_ = null) {
        return Table::textCell($t->category ?? '---');
    }, 'category')

    ->column('Действия', function ($t, $_ = null) {
        if (!isset($t->id)) return '';
        ob_start(); ?>
            <div class="flex justify-end">
                <?= Table::actionsMenu(slot: function ($t) {
                    ob_start(); ?>

                    <?= Table::actionLink(
                        "/admin/email-templates/edit/{$t->id}",
                        'Редактирай',
                        'fa-solid fa-pen-to-square'
                    ) ?>

                    <?= Table::actionButton(
                        click: "deleteItem({$t->id})",
                        label: 'Изтрий',
                        icon: 'fa-solid fa-trash-can',
                        type: 'delete'
                    ) ?>

                    <?php return ob_get_clean();
                }, item: $t); ?>
            </div>
        <?php return ob_get_clean();
    }, null, 'right')
    ->render('mt-5'); ?>
</div>
