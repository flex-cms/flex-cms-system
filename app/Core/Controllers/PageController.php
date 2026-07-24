<?php

namespace Flex\Core\Controllers;

use Exception;
use Flex\Attributes\UseExceptions;
use Flex\Core\Filters\Shared\StatusFilter;
use Flex\Core\Helpers\Flash;
use Flex\Core\Helpers\SlugHelper;
use Flex\Core\Traits\CrudHelper;
use Flex\Core\Traits\HandlesMedia;
use Flex\Core\Traits\HandlesTableFilters;
use Flex\Models\Page;
use Flex\Core\Routing\View;
use Flex\Core\Controllers\BaseController;

class PageController extends BaseController
{
    use HandlesMedia, HandlesTableFilters, CrudHelper;

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
            'error_generic' => 'Възникна неочаквана грешка.',
            'create_success' => 'Страницата беше създадена успешно!',
            'update_success' => 'Страницата беше обновена успешно!',
            'dublicated_slug' => 'Slug вече съществува. Моля, изберете друг.'
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
    public function edit(int $id)
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

        Flash::success($this->messages['create_success']);
        View::redirect('/admin/pages/edit/' . $page->id);
    }

    #[UseExceptions]
    public function update(int $id)
    {
        $page = Page::findOrFail($id);

        $oldFullSlug = $page->full_slug;

        $data = $this->prepareData($_POST, $page);
        $page->update($data);

        $page->refresh();

        if ($oldFullSlug !== $page->full_slug) {
            $this->syncChildrenPaths($page);
        }

        Flash::success($this->messages['update_success']);
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
    public function reorder()
    {
        $this->updatePositionMethod(
            Page::class,
            'position'
        );

        return $this->jsonResponse(true, 'Позицията беше променена успешно.');
    }

    #[UseExceptions]
    private function prepareData(array $post, $model = null): array
    {
        $data = $this->buildUpdateData($post, $model, [
            'name',
            'slug',
            'is_active',
            'parent_id',
            'position',
            'created_at' => 'default_date'
        ]);

        $data['slug'] = $this->resolveSlug($data);
        $this->ensureSlugIsUnique($data['slug'], $model);

        $parentId = $this->resolveParentId($data, $model);
        $data['parent_id'] = $parentId;
        $data['full_slug'] = $this->calculateFullSlug($data['slug'], $parentId);

        $data['options'] = $this->resolveOptions($post, $model);

        return $data;
    }

    private function resolveSlug(array $data): string
    {
        if (!empty($data['slug'])) {
            return $data['slug'];
        }

        return SlugHelper::generate($data['name']);
    }

    #[UseExceptions]
    private function ensureSlugIsUnique(string $slug, $model = null): void
    {
        $slugExists = Page::where('slug', $slug)
            ->where('id', '!=', $model->id ?? 0)
            ->exists();

        if ($slugExists) {
            throw new Exception($this->messages['dublicated_slug']);
        }
    }

    private function resolveParentId(array $data, $model = null): ?int
    {
        if (empty($data['parent_id'])) {
            return null;
        }

        if ($model && $data['parent_id'] == $model->id) {
            return null;
        }

        return (int) $data['parent_id'];
    }

    private function resolveOptions(array $post, $model = null): array
    {
        $currentOptions = $model ? $model->options->getArrayCopy() : [];
        $options = $this->mergeOptions($post, $currentOptions);

        return $this->handleFileUploads($options, 'pages');
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