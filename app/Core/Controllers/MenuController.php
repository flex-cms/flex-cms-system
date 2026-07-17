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
use Flex\Models\Menu;
use Flex\Core\Routing\View;
use Flex\Core\Controllers\BaseController;

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
                'label' => is_array($item) ? $item['title'] : $item->title,
                'url' => is_array($item) ? $item['url'] : $item->url,
                'target' => is_array($item) ? $item['target'] : $item->target,
                'description' => is_array($item) ? ($item['description'] ?? '') : ($item->description ?? ''),
                'is_active'   => is_array($item) ? (isset($item['is_active']) ? (int)$item['is_active'] : 1) : (int)($item->is_active ?? 1),
            ];
        })->toArray();

        $data = [
            'title' => $this->editTitle,
            'menu' => $menu,
            'menuItems' => $menuItems,
            'primaryButton' => $this->createButton('/admin/menus/create', $this->createTitle)
        ];

        render_view('admin/menus/form', $data);
    }

    #[UseExceptions]
    public function store()
    {
        $post = $_POST;
        $data = $this->prepareData($post);

        $menu = Menu::create($data);

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

        $menu->update($data);

        $menu->items()->delete();

        if (!empty($data['prepared_menu_items'])) {
            foreach ($data['prepared_menu_items'] as $itemData) {
                $menu->items()->create($itemData);
            }
        }

        Flash::success($this->messages['update_success']);
        View::redirect('/admin/menus/edit/' . $menu->id);
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
    #[UseExceptions]
    private function prepareData(array $post, $model = null): array
    {
        $slugExists = Menu::where('slug', $post['slug'] ?? '')
            ->where('id', '!=', $model->id ?? 0)
            ->exists();

        if ($slugExists) {
            throw new Exception('Slug вече съществува. Моля, изберете друг.');
        }

        $data = $this->buildUpdateData($post, $model, [
            'name',
            'slug',
            'is_active',
            'created_at' => 'default_date'
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = SlugHelper::generate($data['name']);
        }

        $currentOptions = $model?->options?->getArrayCopy() ?? [];
        $data['options'] = $this->mergeOptions($post, $currentOptions);
        $data['options'] = $this->handleFileUploads($data['options'], 'menus');

        $preparedItems = [];
        if (!empty($post['menu_items']) && is_array($post['menu_items'])) {
            foreach ($post['menu_items'] as $index => $item) {
                if (empty($item['label']) && empty($item['url'])) {
                    continue;
                }

                $preparedItems[] = [
                    'title' => $item['label'] ?? '',
                    'url' => $item['url'] ?? '',
                    'target' => $item['target'] ?? '_self',
                    'order' => $index,
                    'parent_id' => null,
                    'description' => $item['description'] ?? '',
                    'is_active'   => $item['is_active'] ?: 0,
                ];
            }
        }

        $data['prepared_menu_items'] = $preparedItems;

        return $data;
    }
}