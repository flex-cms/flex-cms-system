<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Controllers;

use Flex\Core\Assets\AdminAssetRegistry;
use Flex\Core\Helpers\Flash;
use Flex\Core\Http\Exceptions\ForbiddenHttpException;
use Flex\Core\Http\JsonResponse;
use Flex\Core\Http\Request;
use Flex\Core\Http\RedirectResponse;
use Flex\Core\View\ViewResponse;
use Flex\Features\AdminUI\Services\AdminUIRenderer;
use Flex\Features\Pages\Data\PageElementNode;
use Flex\Features\Pages\Exceptions\InvalidPageElementException;
use Flex\Features\Pages\Services\PageElementService;
use Flex\Features\Pages\Services\PageOptionService;
use Flex\Features\Pages\Services\PageService;

final readonly class PageContentController
{
    public function __construct(
        private PageService $pages,
        private PageOptionService $options,
        private PageElementService $elements,
        private AdminUIRenderer $adminUI,
        private AdminAssetRegistry $assets
    ) {
    }

    public function edit(int $id): ViewResponse|RedirectResponse
    {
        $page = $this->pages->findOrFail($id);

        if (!$this->builderEnabled($page)) {
            Flash::error('Page builder-ът не е активиран за тази страница.');

            return new RedirectResponse('/admin/pages');
        }
        $this->assets->script('Pages', 'page-content');

        return $this->adminUI->response('Pages::content/edit', [
            'title' => 'Съдържание: ' . $page->name,
            'page' => $page,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $page = $this->pages->findOrFail($id);
        $this->assertBuilderEnabled($page);

        return new JsonResponse([
            'data' => $this->definitions(
                $this->elements->tree($page)
            ),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $definitions = $request->input('elements', []);

        if (!is_array($definitions) || !array_is_list($definitions)) {
            return $this->validationError(
                'Елементите на страницата трябва да бъдат списък.'
            );
        }

        try {
            $page = $this->pages->findOrFail($id);
            $this->assertBuilderEnabled($page);
            $nodes = $this->elements->replace($page, $definitions);

            return new JsonResponse([
                'success' => true,
                'message' => 'Съдържанието беше запазено успешно.',
                'data' => $this->definitions($nodes),
            ]);
        } catch (InvalidPageElementException $exception) {
            return $this->validationError($exception->getMessage());
        }
    }

    /**
     * @param list<PageElementNode> $nodes
     * @return list<array<string, mixed>>
     */
    private function definitions(array $nodes): array
    {
        return array_map(
            static fn (PageElementNode $node): array => $node->toDefinition(),
            $nodes
        );
    }

    private function validationError(string $message): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'message' => $message,
        ], 422);
    }

    private function assertBuilderEnabled(
        \Flex\Features\Pages\Models\Page $page
    ): void {
        if (!$this->builderEnabled($page)) {
            throw new ForbiddenHttpException(
                'Page builder-ът не е активиран за тази страница.'
            );
        }
    }

    private function builderEnabled(
        \Flex\Features\Pages\Models\Page $page
    ): bool {
        return (bool) $this->options->value(
            $page,
            'is_with_page_options',
            false
        );
    }
}
