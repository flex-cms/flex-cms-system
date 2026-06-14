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
    protected string $deleteSuccessMessage;
    protected string $deleteErrorMessage;

    public function __construct()
    {
        $this->indexTitle               = 'Управление на страници';
        $this->createTitle              = 'Нова страница';
        $this->editTitle                = 'Редактиране на страница';
        $this->createBtn                = 'Нова страница';
        $this->deleteSuccessMessage     = 'Изтрито успешно.';
        $this->deleteErrorMessage       = 'Тази страница не съществува.';
    }

    #[UseExceptions]
    public function index()
    {
        $pages = $this->applyFilters(
            Page::query(),
            ['name', 'slug'],
            ['name', 'slug', 'created_at'],
            ['status' => StatusFilter::class],
            'name'
        )->get();

        $this->renderAdmin('pages/index', [
            'title' => $this->indexTitle,
            'pages' => $pages,
            'primaryButton' => $this->createButton('/admin/pages/create', $this->createTitle)
        ]);
    }

    #[UseExceptions]
    public function create()
    {
        $this->renderAdmin('pages/form', [
            'title' => $this->createTitle,
            'page' => new Page(),
            'primaryButton' => $this->createButton('/admin/pages/create', $this->createTitle)
        ]);
    }

    #[UseExceptions]
    public function edit($id)
    {
        $page = Page::findOrFail($id);

        $this->renderAdmin('pages/form', [
            'title' => $this->editTitle,
            'page' => $page,
            'primaryButton' => $this->createButton('/admin/pages/create', $this->createTitle)
        ]);
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
        $data = $this->prepareData($_POST, $page);

        $page->update($data);

        View::redirect('/admin/pages/edit/' . $page->id);
    }

    #[UseExceptions]
    public function delete()
    {
        return $this->deleteRecord(Page::class);
    }

    #[UseExceptions]
    public function toggle()
    {
        $result = $this->toggleStatus(Page::class, 'is_active');

        if (!$result['success']) {
            return $this->jsonResponse(false, $result['message']);
        }

        $statusText = $result['new_status'] ? 'активирана' : 'деактивирана';
        
        return $this->jsonResponse(true, "Страницата беше {$statusText} успешно!");
    }

    #[UseExceptions]
    private function prepareData(array $post, $model = null): array
    {
        $post = $this->normalizeCheckboxes($post);

        $data = $this->buildUpdateData($post, $model, ['name', 'slug', 'is_active', 'created_at' => 'default_date']);

        if (empty($data['slug'])) {
            $data['slug'] = SlugHelper::generate($data['name']);
        }

        $currentOptions = $model ? $model->options->getArrayCopy() : [];
        $data['options'] = $this->mergeOptions($post, $currentOptions);
        $data['options'] = $this->handleFileUploads($data['options'], 'pages');

        return $data;
    }
}
