<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Filters\Shared\StatusFilter;
use Flex\Core\Helpers\SlugHelper;
use Flex\Core\Traits\CrudHelper;
use Flex\Core\Traits\HandlesMedia;
use Flex\Core\Traits\HandlesTableFilters;
use Flex\Core\Traits\RequestHelper;
use Flex\Models\Page;
use Flex\Core\Routing\View;
use Flex\Core\Controllers\BaseController;

class PageController extends BaseController
{
    use HandlesMedia, HandlesTableFilters, RequestHelper, CrudHelper;

    protected string $indexTitle;
    protected string $createTitle;
    protected string $editTitle;
    protected string $createBtn;
    protected array $messages = [];

    public function __construct()
    {
        $this->indexTitle = 'Управление на страници';
        $this->createTitle = 'Нова страница';
        $this->editTitle = 'Редактиране на страница';
        $this->createBtn = 'Нова страница';

        $this->initMessages();
    }

    private function initMessages(): void
    {
        $this->messages = [
            'delete_success' => 'Страницата беше успешно преместена в кошчето.',
            'force_delete_success' => 'Страницата беше успешно премахната завинаги.',
            'restore_success' => 'Страницата беше успешно възстановена от кошчето. По подразбиране тя автоматично е деактивирана.',
            'toggle_active' => 'Страницата беше активирана успешно!',
            'toggle_inactive' => 'Страницата беше деактивирана успешно!',
            'error_generic' => 'Възникна неочаквана грешка.'
        ];
    }

    #[UseExceptions]
    public function index()
    {
        $pages = $this->applyFilters(
            Page::query()
                ->orderBy('position'),
            ['name', 'slug'],
            ['name', 'slug', 'created_at'],
            ['status' => StatusFilter::class],
            'name'
        )->get();

        $data = [
            'title' => $this->indexTitle,
            'pages' => Page::getFlattenedTree($pages),
            'primaryButton' => $this->createButton('/admin/pages/create', $this->createTitle)
        ];

        render_view('admin/pages/index', $data);
    }

    #[UseExceptions]
    public function create()
    {
        $data = [
            'title' => $this->createTitle,
            'page' => new Page(),
            'pages' => Page::getFlattenedTree(Page::all()),
            'primaryButton' => $this->createButton('/admin/pages/create', $this->createTitle)
        ];

        render_view('admin/pages/form', $data);
    }

    #[UseExceptions]
    public function edit($id)
    {
        $page = Page::findOrFail($id);

        $allPages = Page::where('id', '!=', $id)->get();

        $data = [
            'title' => $this->editTitle,
            'page' => $page,
            'pages' => Page::getFlattenedTree($allPages),
            'primaryButton' => $this->createButton('/admin/pages/create', $this->createTitle)
        ];

        render_view('admin/pages/form', $data);
    }

    #[UseExceptions]
    public function store()
    {
        $data = $this->prepareData($_POST);
        $page = Page::create($data);

        View::redirect('/admin/pages/edit/' . $page->id);
    }

    #[UseExceptions]
    public function update($id)
    {
        $page = Page::findOrFail($id);

        $oldFullSlug = $page->full_slug;

        $data = $this->prepareData($_POST, $page);
        $page->update($data);

        $page->refresh();

        if ($oldFullSlug !== $page->full_slug) {
            $this->syncChildrenPaths($page);
        }

        View::redirect('/admin/pages/edit/' . $page->id);
    }

    #[UseExceptions]
    public function delete()
    {
        $this->deleteRecord(Page::class);
        return $this->jsonResponse(true, $this->messages['delete_success']);
    }

    #[UseExceptions]
    public function forceDelete()
    {
        $this->forceDeleteRecord(Page::class);
        return $this->jsonResponse(true, $this->messages['force_delete_success']);
    }

    #[UseExceptions]
    public function toggle()
    {
        $result = $this->toggleStatus(Page::class, 'is_active');

        if (!$result['success']) {
            return $this->jsonResponse(false, $result['message']);
        }

        $msgKey = $result['new_status'] ? 'toggle_active' : 'toggle_inactive';
        return $this->jsonResponse(true, $this->messages[$msgKey]);
    }

    #[UseExceptions]
    public function restore()
    {
        $this->restoreRecord(Page::class);
        return $this->jsonResponse(true, $this->messages['restore_success']);
    }

    #[UseExceptions]
    public function updatePosition()
    {
        $data = $this->getJsonInput();

        $id = $data['id'] ?? null;
        $position = $data['position'] ?? null;

        if (!is_numeric($id) || !is_numeric($position)) {
            return $this->jsonResponse(false, 'Невалидни данни.');
        }

        $page = Page::findOrFail($id);

        $page->position = (int) $position;
        $page->save();

        return $this->jsonResponse(true, 'Позицията беше променена успешно.');
    }

    #[UseExceptions]
    public function reorder()
    {
        $data = $this->getJsonInput();

        if (!isset($data['items']) || !is_array($data['items'])) {
            return $this->jsonResponse(false, 'Невалидни данни.');
        }

        foreach ($data['items'] as $item) {

            if (
                !isset($item['id'], $item['position']) ||
                !is_numeric($item['id']) ||
                !is_numeric($item['position'])
            ) {
                continue;
            }

            $page = Page::find($item['id']);

            if (!$page) {
                continue;
            }

            $page->position = (int) $item['position'];
            $page->save();
        }

        return $this->jsonResponse(true, 'Редът беше запазен успешно.');
    }

    #[UseExceptions]
    private function prepareData(array $post, $model = null): array
    {
        $post = $this->normalizeCheckboxes($post);

        $data = $this->buildUpdateData($post, $model, [
            'name',
            'slug',
            'is_active',
            'parent_id',
            'position',
            'created_at' => 'default_date'
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = SlugHelper::generate($data['name']);
        }

        if (isset($data['parent_id']) && $data['parent_id'] === '') {
            $data['parent_id'] = null;
        }

        if ($model && isset($data['parent_id']) && $data['parent_id'] == $model->id) {
            $data['parent_id'] = null;
        }

        $data['full_slug'] = $this->calculateFullSlug($data['slug'], $data['parent_id'] ?? null);

        $currentOptions = $model ? $model->options->getArrayCopy() : [];
        $data['options'] = $this->mergeOptions($post, $currentOptions);
        $data['options'] = $this->handleFileUploads($data['options'], 'pages');

        return $data;
    }

    private function calculateFullSlug($slug, $parentId)
    {
        if (!$parentId)
            return $slug;

        $parent = Page::find($parentId);
        return $parent ? ($parent->full_slug . '/' . $slug) : $slug;
    }

    private function syncChildrenPaths($page)
    {
        foreach ($page->children as $child) {
            $child->full_slug = $page->full_slug . '/' . $child->slug;
            $child->save();

            $this->syncChildrenPaths($child);
        }
    }
}