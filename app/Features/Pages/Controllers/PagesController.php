<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Controllers;

use Flex\Core\Assets\AdminAssetRegistry;
use Flex\Core\Http\JsonResponse;
use Flex\Core\Http\Request;
use Flex\Core\View\ViewResponse;
use Flex\Features\AdminUI\Services\AdminUIRenderer;
use Flex\Features\Pages\Exceptions\InvalidPageElementException;
use Flex\Features\Pages\Services\PageElementService;
use Flex\Features\Pages\Services\PageOptionService;
use Flex\Features\Pages\Services\PageService;
use JsonException;

final readonly class PagesController
{
    private const OPTION_KEYS = [
        'page_template',
        'page_options_key',
        'excerpt',
        'use_full_slug',
        'is_with_page_options',
        'featured_image',
        'tablet_image',
        'mobile_image',
    ];

    public function __construct(
        private PageService $pages,
        private PageOptionService $options,
        private PageElementService $elements,
        private AdminUIRenderer $adminUI,
        private AdminAssetRegistry $assets
    ) {
    }

    public function index(Request $request): ViewResponse
    {
        $this->assets->script('Pages', 'pages');

        return $this->adminUI->response('Pages::index', [
            'title' => 'Управление на страници',
            'primaryButton' => [
                'url' => '/admin/pages/create',
                'label' => 'Нова страница',
            ],
        ]);
    }

    public function apiIndex(Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->pages->paginate($request->queryAll())
        );
    }

    public function bulk(Request $request): JsonResponse
    {
        try {
            $result = $this->pages->bulk($request->input());

            return new JsonResponse([
                'success' => true,
                'message' => $result['message'],
                'affected' => $result['affected'],
            ]);
        } catch (\Flex\Features\Pages\Exceptions\InvalidPageDataException $exception) {
            return new JsonResponse([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function create(): ViewResponse
    {
        return $this->formResponse('Pages::create', 'Нова страница');
    }

    public function edit(int $id): ViewResponse
    {
        $page = $this->pages->findOrFail($id);

        return $this->formResponse(
            'Pages::edit',
            'Редактиране на страница',
            $page
        );
    }

    public function store(Request $request): JsonResponse
    {
        $page = $this->pages->create($this->pageData($request));
        $this->syncRelatedData($request, $page);

        return new JsonResponse([
            'success' => true,
            'message' => 'Страницата беше създадена успешно.',
            'id' => (int) $page->id,
            'redirect' => '/admin/pages/edit/' . (int) $page->id,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $page = $this->pages->update($id, $this->pageData($request));
        $this->syncRelatedData($request, $page);

        return new JsonResponse([
            'success' => true,
            'message' => 'Страницата беше обновена успешно.',
            'id' => (int) $page->id,
        ]);
    }

    public function delete(int $id): JsonResponse
    {
        $this->pages->delete($id);

        return $this->actionResponse(
            'Страницата беше преместена в кошчето.'
        );
    }

    public function restore(int $id): JsonResponse
    {
        $this->pages->restore($id);

        return $this->actionResponse(
            'Страницата беше възстановена успешно.'
        );
    }

    public function forceDelete(int $id): JsonResponse
    {
        $this->pages->forceDelete($id);

        return $this->actionResponse(
            'Страницата беше изтрита завинаги.'
        );
    }

    public function toggle(int $id): JsonResponse
    {
        $page = $this->pages->toggle($id);

        return $this->actionResponse(
            $page->is_active
                ? 'Страницата беше активирана успешно.'
                : 'Страницата беше деактивирана успешно.',
            ['is_active' => (bool) $page->is_active]
        );
    }

    public function reorder(Request $request): JsonResponse
    {
        $positions = $request->input('positions', []);

        if (!is_array($positions) || !array_is_list($positions)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Невалидни данни за подреждане.',
            ], 422);
        }

        $this->pages->reorder($positions);

        return new JsonResponse([
            'success' => true,
            'message' => 'Позициите бяха обновени успешно.',
        ]);
    }

    private function formResponse(
        string $view,
        string $title,
        ?\Flex\Features\Pages\Models\Page $page = null
    ): ViewResponse {
        return $this->adminUI->response($view, [
            'title' => $title,
            'page' => $page,
            'parentPages' => $this->pages->tree(),
            'options' => $page === null ? [] : $this->options->values($page),
            'elements' => $page === null ? [] : $this->elements->tree($page),
            'templates' => [],
        ]);
    }

    /** @return array<string, mixed> */
    private function pageData(Request $request): array
    {
        return [
            ...$request->only(['name', 'slug', 'parent_id', 'position']),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function syncRelatedData(
        Request $request,
        \Flex\Features\Pages\Models\Page $page
    ): void {
        $options = $request->input('options', []);

        if (!is_array($options)) {
            $options = [];
        }

        $options['use_full_slug'] = $request->boolean(
            'options.use_full_slug'
        );
        $options['is_with_page_options'] = $request->boolean(
            'options.is_with_page_options'
        );

        // Omitted values (notably existing media paths) must survive a form save.
        $this->options->saveMany($page, $options, self::OPTION_KEYS);
        $this->elements->replace($page, $this->elementDefinitions($request));
    }

    /** @return list<array<string, mixed>> */
    private function elementDefinitions(Request $request): array
    {
        $json = trim($request->string('elements_json', '[]'));

        try {
            $elements = json_decode(
                $json === '' ? '[]' : $json,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            throw new InvalidPageElementException(
                'Page elements must contain valid JSON.',
                previous: $exception
            );
        }

        if (!is_array($elements) || !array_is_list($elements)) {
            throw new InvalidPageElementException(
                'Page elements must be a JSON list.'
            );
        }

        return $elements;
    }

    /** @param array<string, mixed> $data */
    private function actionResponse(string $message, array $data = []): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'message' => $message,
            ...$data,
        ]);
    }
}
