<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Filters\Shared\StatusFilter;
use Flex\Core\Helpers\Flash;
use Flex\Core\Helpers\SlugHelper;
use Flex\Core\Traits\CrudHelper;
use Flex\Core\Traits\HandlesMedia;
use Flex\Core\Traits\HandlesTableFilters;
use Flex\Models\Menu;
use Flex\Core\Routing\View;
use Flex\Core\Controllers\BaseController;
use Flex\Models\MenuItem;
use InvalidArgumentException;

class MenuController extends BaseController
{
    use HandlesMedia, HandlesTableFilters, CrudHelper;

    protected string $indexTitle;
    protected string $createTitle;
    protected string $editTitle;
    protected string $createBtn;
    protected array $messages = [];

    public function __construct()
    {
        $this->indexTitle = 'Управление на менюта';
        $this->createTitle = 'Ново меню';
        $this->editTitle = 'Редактиране на меню';
        $this->createBtn = 'Ново меню';

        $this->initMessages();
    }

    private function initMessages(): void
    {
        $this->messages = [
            'delete_success' => 'Менюто беше успешно преместена в кошчето.',
            'force_delete_success' => 'Менюто беше успешно премахнато завинаги.',
            'restore_success' => 'Менюто беше успешно възстановено от кошчето. По подразбиране то автоматично е деактивирано.',
            'toggle_active' => 'Менюто беше активирано успешно!',
            'toggle_inactive' => 'Менюто беше деактивирано успешно!',
            'error_generic' => 'Възникна неочаквана грешка.',
            'create_success' => 'Менюто беше създадено успешно!',
            'update_success' => 'Менюто беше обновено успешно!'
        ];
    }

    #[UseExceptions]
    public function index()
    {
        $menus = $this->applyFilters(
            Menu::with('items'),
            ['name', 'slug'],
            ['name', 'slug', 'created_at'],
            ['status' => StatusFilter::class],
            'name'
        )->get();

        $data = [
            'title' => $this->indexTitle,
            'menus' => $menus,
            'primaryButton' => $this->createButton('/admin/menus/create', $this->createTitle)
        ];

        render_view('admin/menus/index', $data);
    }

    #[UseExceptions]
    public function create()
    {
        $data = [
            'title' => $this->createTitle,
            'menu' => new Menu(),
            'menuItems' => [],
            'primaryButton' => $this->createButton('/admin/menus/create', $this->createTitle)
        ];

        render_view('admin/menus/form', $data);
    }

    #[UseExceptions]
    public function edit($id)
    {
        $menu = Menu::with('items')->findOrFail($id);

        $menuItems = collect($menu->items)->map(function ($item) {
            return [
                'id' => $item->id,
                'label' => $item->title,
                'url' => $item->url,
                'target' => $item->target,
                'description' => $item->description ?? '',
                'is_active' => (int) $item->is_active,
            ];
        })->toArray();

        $data = [
            'title' => $this->editTitle,
            'menu' => $menu,
            'menuItems' => $menuItems,
            'primaryButton' => $this->createButton(
                '/admin/menus/create',
                $this->createTitle
            )
        ];

        render_view('admin/menus/form', $data);
    }

    #[UseExceptions]
    public function store()
    {
        $post = $_POST;
        $data = $this->prepareData($post);

        $menu = Menu::create($data);

        $this->syncMenuItems($menu, $post['menu_items'] ?? []);

        if (!empty($data['prepared_menu_items'])) {
            foreach ($data['prepared_menu_items'] as $itemData) {
                $menu->items()->create($itemData);
            }
        }

        Flash::success($this->messages['create_success']);
        View::redirect('/admin/menus/edit/' . $menu->id);
    }

    #[UseExceptions]
    public function update($id)
    {
        $menu = Menu::findOrFail($id);
        $post = $_POST;

        $data = $this->prepareData($post, $menu);

        $menu->update([
            'name' => $data['name'] ?? $menu->name,
            'slug' => $data['slug'] ?? $menu->slug,
            'is_active' => $data['is_active'] ?? $menu->is_active,
            'options' => $data['options'] ?? $menu->options,
        ]);

        if (isset($post['_menu_items_present'])) {
            $this->syncMenuItems($menu, $post['menu_items'] ?? []);
        }

        if (isset($post['menu_items'])) {
            $this->syncMenuItems($menu, $post['menu_items']);
        }

        Flash::success($this->messages['update_success']);
        View::redirect('/admin/menus/edit/' . $menu->id);
    }

    protected function syncMenuItems(Menu $menu, array $items, ?int $parentId = null): void
    {
        $existingIds = [];

        foreach ($items as $index => $itemData) {

            $id = $itemData['id'] ?? null;

            $attributes = [
                'title' => $itemData['label'] ?? $itemData['title'] ?? '',
                'url' => $itemData['url'] ?? '',
                'target' => $itemData['target'] ?? '_self',
                'description' => $itemData['description'] ?? null,
                'order' => $index,
                'menu_id' => $menu->id,
                'parent_id' => $parentId,
                'is_active' => (int) ($itemData['is_active'] ?? 0),
            ];

            if ($id) {
                $item = MenuItem::where('menu_id', $menu->id)
                    ->find($id);

                if ($item) {
                    $item->update($attributes);
                } else {
                    $item = $menu->items()->create($attributes);
                }
            } else {
                $item = $menu->items()->create($attributes);
            }

            $existingIds[] = $item->id;

            $this->syncMenuItems(
                $menu,
                is_array($itemData['children'] ?? null) ? $itemData['children'] : [],
                $item->id
            );
        }

        $removedItems = MenuItem::where('menu_id', $menu->id)
            ->where('parent_id', $parentId);

        if ($existingIds !== []) {
            $removedItems->whereNotIn('id', $existingIds);
        }

        $removedItems->delete();
    }

    #[UseExceptions]
    public function delete()
    {
        $this->deleteRecord(Menu::class);
        return $this->jsonResponse(true, $this->messages['delete_success']);
    }

    #[UseExceptions]
    public function forceDelete()
    {
        $this->forceDeleteRecord(Menu::class);
        return $this->jsonResponse(true, $this->messages['force_delete_success']);
    }

    #[UseExceptions]
    public function toggle()
    {
        $result = $this->toggleStatus(Menu::class, 'is_active');

        if (!$result['success']) {
            return $this->jsonResponse(false, $result['message']);
        }

        $msgKey = $result['new_status'] ? 'toggle_active' : 'toggle_inactive';
        return $this->jsonResponse(true, $this->messages[$msgKey]);
    }

    #[UseExceptions]
    public function restore()
    {
        $this->restoreRecord(Menu::class);
        return $this->jsonResponse(true, $this->messages['restore_success']);
    }

    #[UseExceptions]
    public function getItems($menuId)
    {
        $items = MenuItem::where('menu_id', $menuId)
            ->whereNull('parent_id')
            ->with('allChildren')
            ->orderBy('order')
            ->get();

        $data = $items->map(function ($item) {
            return $this->formatItem($item);
        })->toArray();

        return $this->jsonResponse(true, 'Данните са заредени успешно', $data);
    }

    private function formatItem($item)
    {
        return [
            'id' => $item->id,
            'label' => $item->title,
            'name' => $item->title,
            'url' => $item->url,
            'target' => $item->target,
            'description' => $item->description,
            'is_active' => (int) $item->is_active,
            'parent_id' => $item->parent_id,
            '_open' => false,
            'children' => $item->allChildren ? $item->allChildren->map(function ($child) {
                return $this->formatItem($child);
            })->toArray() : []
        ];
    }

    #[UseExceptions]
    public function updateTreePosition($menuId)
    {
        $data = $this->getJsonInput();
        $itemId = (int) ($data['id'] ?? 0);
        $parentId = isset($data['parent_id']) ? (int) $data['parent_id'] : null;

        $item = MenuItem::where('menu_id', (int) $menuId)->find($itemId);
        if (!$item) {
            throw new InvalidArgumentException('Елементът не принадлежи на това меню.');
        }

        if ($parentId !== null) {
            $parent = MenuItem::where('menu_id', (int) $menuId)->find($parentId);

            if (!$parent) {
                throw new InvalidArgumentException('Родителският елемент не принадлежи на това меню.');
            }

            $ancestor = $parent;
            while ($ancestor) {
                if ($ancestor->id === $item->id) {
                    throw new InvalidArgumentException('Елемент не може да бъде преместен в собственото си подменю.');
                }

                $ancestor = $ancestor->parent;
            }
        }
        
        $this->updateTreeAndOrder(
            MenuItem::class,
            'parent_id',
            'order',
            'menu_id'
        );

        return $this->jsonResponse(
            true,
            'Менюто беше обновено успешно.'
        );
    }

    #[UseExceptions]
    private function prepareData(array $post, $model = null): array
    {
        $data = $this->prepareBaseData($post, $model);
        $data['options'] = $this->prepareMenuOptions($post, $model);

        return $data;
    }

    private function prepareBaseData(array $post, $model = null): array
    {
        $data = $this->buildUpdateData($post, $model, [
            'name',
            'slug',
            'is_active',
            'created_at' => 'default_date'
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = SlugHelper::generate($data['name']);
        }

        $data['slug'] = $this->generateUniqueSlug($data['slug'], $model->id ?? 0);

        return $data;
    }

    private function generateUniqueSlug(string $slug, int $excludeId = 0): string
    {
        $baseSlug = $slug;
        $counter = 2;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, int $excludeId = 0): bool
    {
        return Menu::where('slug', $slug)
            ->where('id', '!=', $excludeId)
            ->exists();
    }

    private function prepareMenuOptions(array $post, $model = null): array
    {
        $currentOptions = ($model && is_array($model->options)) ? $model->options : [];

        $options = $this->mergeOptions($post, $currentOptions);
        $options = $this->handleFileUploads($options, 'menus');

        return $options;
    }

    private function prepareMenuItems(array $post): array
    {
        if (empty($post['menu_items'])) {
            return [];
        }

        return $this->processMenuItemsTree($post['menu_items']);
    }

    private function processMenuItemsTree(array $items, ?int $parentId = null): array
    {
        $result = [];

        foreach ($items as $index => $item) {
            if (empty($item['label']) && empty($item['url'])) {
                continue;
            }

            $result[] = $this->prepareMenuItemAttributes($item, $index, $parentId);
        }

        return $result;
    }

    private function prepareMenuItemAttributes(array $item, int $order, ?int $parentId): array
    {
        $prepared = [
            'title' => $item['label'] ?? '',
            'url' => $item['url'] ?? '',
            'target' => $item['target'] ?? '_self',
            'order' => $order,
            'parent_id' => $parentId,
            'description' => $item['description'] ?? '',
            'is_active' => (int) ($item['is_active'] ?? 0),
        ];

        if (!empty($item['children'])) {
            $prepared['children'] = $this->processMenuItemsTree($item['children'], null);
        }

        return $prepared;
    }
}
