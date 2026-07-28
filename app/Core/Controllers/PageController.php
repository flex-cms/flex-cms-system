<?php

namespace Flex\Core\Controllers;

use Exception;

use Illuminate\Database\Capsule\Manager as Capsule;
use Flex\Attributes\UseExceptions;
use Flex\Core\Filters\Shared\StatusFilter;
use Flex\Core\Helpers\Flash;
use Flex\Core\Helpers\SlugHelper;
use Flex\Core\Traits\CrudHelper;
use Flex\Core\Traits\HandlesMedia;
use Flex\Core\Traits\HandlesTableFilters;
use Flex\Core\Routing\View;
use Flex\Core\Controllers\BaseController;
use Flex\Models\Page;
use Flex\Models\PageElement;

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
        $page = Page::with(['pageOptions', 'elements.children'])->findOrFail($id);

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
        $page = Capsule::connection()->transaction(function () {
            $page = Page::create(
                $this->prepareData($_POST)
            );

            $this->syncPageOptions($page, $_POST);
            $this->syncPageElements($page, $_POST);

            return $page;
        });

        Flash::success($this->messages['create_success']);
        View::redirect('/admin/pages/edit/' . $page->id);
    }

    #[UseExceptions]
    public function update(int $id)
    {
        $page = Capsule::connection()->transaction(function () use ($id) {
            $page = Page::findOrFail($id);

            $oldFullSlug = $page->full_slug;

            $page->update(
                $this->prepareData($_POST, $page)
            );

            $this->syncPageOptions($page, $_POST);
            $this->syncPageElements($page, $_POST);

            $page->refresh();

            if ($oldFullSlug !== $page->full_slug) {
                $this->syncChildrenPaths($page);
            }

            return $page;
        });

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
            'created_at' => 'default_date',
        ]);

        $data['slug'] = $this->resolveSlug($data);
        $this->ensureSlugIsUnique($data['slug'], $model);

        $parentId = $this->resolveParentId($data, $model);

        $data['parent_id'] = $parentId;
        $data['full_slug'] = $this->calculateFullSlug(
            $data['slug'],
            $parentId
        );

        return $data;
    }

    private function normalizeOptionValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(
                fn($item) => $this->normalizeOptionValue($item),
                $value
            );
        }

        if ($value === 'on') {
            return '1';
        }

        if ($value === 'off') {
            return '0';
        }

        return $value;
    }

    private function prepareOptions(array $post, ?Page $page = null): array
    {
        $excludedFields = [
            'name',
            'slug',
            'is_active',
            'parent_id',
            'position',
            'created_at',
            'elements',
        ];

        $currentOptions = $page
            ? $page->pageOptions()
                ->pluck('option_value', 'option_key')
                ->toArray()
            : [];

        $submittedOptions = array_diff_key(
            $post,
            array_flip($excludedFields)
        );

        $options = array_merge(
            $currentOptions,
            $submittedOptions
        );

        $options = $this->handleFileUploads(
            $options,
            'pages',
            [
                'featured_image',
                'tablet_image',
                'mobile_image',
            ]
        );
        
        return $options;
    }

    private function syncPageOptions(Page $page, array $post): void
    {
        $options = $this->prepareOptions($post, $page);
        $savedKeys = array_keys($options);

        foreach ($options as $key => $value) {
            $page->pageOptions()->updateOrCreate(
                [
                    'option_key' => $key,
                ],
                [
                    'option_value' => $this->encodeOptionValue($value),
                ]
            );
        }

        if ($savedKeys === []) {
            $page->pageOptions()->delete();

            return;
        }

        $page->pageOptions()
            ->whereNotIn('option_key', $savedKeys)
            ->delete();
    }

    private function encodeOptionValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value) || is_object($value)) {
            return json_encode(
                $value,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }

    private function prepareElements(array $post): array
    {
        $elements = $post['elements'] ?? [];

        return is_array($elements) ? $elements : [];
    }

    private function syncPageElements(Page $page, array $post): void
    {
        $elements = $this->prepareElements($post);

        foreach ($elements as &$element) {
            if (($element['element_type'] ?? null) !== 'hero') {
                continue;
            }

            $settings = is_array($element['settings'] ?? null)
                ? $element['settings']
                : [];

            $fileOptions = [
                'hero_background_image' => $settings['background_image'] ?? null,
            ];

            $fileOptions = $this->handleFileUploads(
                $fileOptions,
                'pages/elements',
                ['hero_background_image']
            );

            $settings['background_image'] =
                $fileOptions['hero_background_image'] ?? null;

            $element['settings'] = $settings;
        }

        unset($element);

        $keptIds = [];

        $this->syncElementLevel(
            $page,
            $elements,
            null,
            $keptIds
        );

        $query = PageElement::where('page_id', $page->id);

        if ($keptIds !== []) {
            $query->whereNotIn('id', $keptIds);
        }

        $query->delete();
    }

    private function syncElementLevel(
        Page $page,
        array $elements,
        ?int $parentId,
        array &$keptIds
    ): void {
        foreach ($elements as $position => $elementData) {
            if (!is_array($elementData)) {
                continue;
            }

            $elementId = isset($elementData['id'])
                ? (int) $elementData['id']
                : null;

            $element = null;

            if ($elementId) {
                $element = PageElement::where('page_id', $page->id)
                    ->where('id', $elementId)
                    ->first();
            }

            if (!$element) {
                $element = new PageElement();
                $element->page_id = $page->id;
            }

            $element->parent_id = $parentId;
            $element->element_type = $elementData['element_type']
                ?? $elementData['type']
                ?? 'text';
            $element->position = $elementData['position']
                ?? $position;
            $element->settings = $this->resolveElementSettings(
                $elementData
            );

            $element->save();

            $keptIds[] = $element->id;

            $children = $elementData['children'] ?? [];

            if (is_array($children) && $children !== []) {
                $this->syncElementLevel(
                    $page,
                    $children,
                    $element->id,
                    $keptIds
                );
            }
        }
    }

    private function resolveElementSettings(array $elementData): array
    {
        if (
            isset($elementData['settings']) &&
            is_array($elementData['settings'])
        ) {
            return $elementData['settings'];
        }

        return array_diff_key($elementData, array_flip([
            'id',
            'page_id',
            'parent_id',
            'element_type',
            'type',
            'position',
            'children',
        ]));
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