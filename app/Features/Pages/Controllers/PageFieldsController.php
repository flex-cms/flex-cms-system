<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Controllers;

use Flex\Core\Assets\AdminAssetRegistry;
use Flex\Core\Helpers\Flash;
use Flex\Core\Http\Exceptions\ForbiddenHttpException;
use Flex\Core\Http\JsonResponse;
use Flex\Core\Http\RedirectResponse;
use Flex\Core\Http\Request;
use Flex\Core\View\ViewResponse;
use Flex\Features\AdminUI\Services\AdminUIRenderer;
use Flex\Features\Pages\Data\PageFieldType;
use Flex\Features\Pages\Exceptions\InvalidPageFieldException;
use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Services\PageFieldService;
use Flex\Features\Pages\Services\PageOptionService;
use Flex\Features\Pages\Services\PageService;

final readonly class PageFieldsController
{
    public function __construct(
        private PageService $pages,
        private PageOptionService $options,
        private PageFieldService $fields,
        private AdminUIRenderer $adminUI,
        private AdminAssetRegistry $assets
    ) {
    }

    public function index(int $pageId): ViewResponse|RedirectResponse
    {
        $page = $this->pages->findOrFail($pageId);

        if (!$this->isEnabled($page)) {
            return $this->restrictedRedirect();
        }
        $this->assets->script('Pages', 'page-fields');

        return $this->view('Pages::fields/index', 'Полета: ' . $page->name, $page);
    }

    public function apiIndex(Request $request, int $pageId): JsonResponse
    {
        $page = $this->page($pageId);

        return new JsonResponse($this->fields->paginate($page, $request->queryAll()));
    }

    public function create(int $pageId): ViewResponse|RedirectResponse
    {
        $page = $this->pages->findOrFail($pageId);

        if (!$this->isEnabled($page)) {
            return $this->restrictedRedirect();
        }

        return $this->view('Pages::fields/create', 'Ново поле', $page, [
            'field' => null,
            'types' => PageFieldType::options(),
        ]);
    }

    public function store(Request $request, int $pageId): JsonResponse
    {
        try {
            $page = $this->page($pageId);
            $field = $this->fields->create($page, $request->input());

            Flash::success('Полето беше създадено успешно.');

            return new JsonResponse([
                'success' => true,
                'message' => 'Полето беше създадено успешно.',
                'id' => (int) $field->id,
                'redirect' => '/admin/pages/' . $pageId . '/fields',
            ]);
        } catch (InvalidPageFieldException $exception) {
            return $this->validationError($exception);
        }
    }

    public function edit(int $pageId, int $fieldId): ViewResponse|RedirectResponse
    {
        $page = $this->pages->findOrFail($pageId);

        if (!$this->isEnabled($page)) {
            return $this->restrictedRedirect();
        }

        return $this->view('Pages::fields/edit', 'Редактиране на поле', $page, [
            'field' => $this->fields->findOrFail($page, $fieldId),
            'types' => PageFieldType::options(),
        ]);
    }

    public function update(Request $request, int $pageId, int $fieldId): JsonResponse
    {
        try {
            $page = $this->page($pageId);
            $field = $this->fields->update($page, $fieldId, $request->input());

            return new JsonResponse([
                'success' => true,
                'message' => 'Полето беше обновено успешно.',
                'id' => (int) $field->id,
            ]);
        } catch (InvalidPageFieldException $exception) {
            return $this->validationError($exception);
        }
    }

    public function delete(int $pageId, int $fieldId): JsonResponse
    {
        $page = $this->page($pageId);
        $this->fields->delete($page, $fieldId);

        return new JsonResponse(['success' => true, 'message' => 'Полето беше изтрито успешно.']);
    }

    public function importForm(int $pageId): ViewResponse|RedirectResponse
    {
        $page = $this->pages->findOrFail($pageId);

        if (!$this->isEnabled($page)) {
            return $this->restrictedRedirect();
        }

        return $this->view('Pages::fields/import', 'Импорт на полета', $page);
    }

    public function import(Request $request, int $pageId): JsonResponse
    {
        try {
            $count = $this->fields->import($this->page($pageId), (string) $request->input('fields_json', ''));

            Flash::success(sprintf('Успешно импортирани полета: %d.', $count));

            return new JsonResponse([
                'success' => true,
                'message' => sprintf('Успешно импортирани полета: %d.', $count),
                'redirect' => '/admin/pages/' . $pageId . '/fields',
            ]);
        } catch (InvalidPageFieldException $exception) {
            return $this->validationError($exception);
        }
    }

    private function page(int $id): Page
    {
        $page = $this->pages->findOrFail($id);

        if (!$this->isEnabled($page)) {
            throw new ForbiddenHttpException('Допълнителните полета не са активирани за тази страница.');
        }

        return $page;
    }

    private function isEnabled(Page $page): bool
    {
        return (bool) $this->options->value(
            $page,
            'is_with_page_options',
            false
        );
    }

    private function restrictedRedirect(): RedirectResponse
    {
        Flash::error('Допълнителните полета не са активирани за тази страница.');

        return new RedirectResponse('/admin/pages');
    }

    private function view(string $view, string $title, Page $page, array $data = []): ViewResponse
    {
        return $this->adminUI->response($view, ['title' => $title, 'page' => $page, ...$data]);
    }

    private function validationError(\Throwable $exception): JsonResponse
    {
        return new JsonResponse(['success' => false, 'message' => $exception->getMessage()], 422);
    }
}
