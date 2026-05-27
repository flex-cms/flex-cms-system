<?php

namespace Flex\Core\Controllers;

use Flex\Core\Services\MediaManager;
use Flex\Models\Page;
use Flex\Core\Routing\View;
use Flex\Core\Controllers\BaseController;

class PageController extends BaseController
{
    use HandlesMedia;

    public function index()
    {
        $query = Page::orderBy('name', 'asc');

        if (!empty($_GET['search'])) {
            $search = trim($_GET['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('slug', 'LIKE', "%{$search}%");
            });
        }

        $pages = $query->get();

        $this->render(View::make('admin/pages/index', [
            'title' => 'Управление на страници',
            'pages' => $pages,
            'primaryButton' => [
                'label' => 'Нова страница',
                'url' => '/admin/pages/create',
                'icon' => 'fa-plus'
            ]
        ], 'admin'));
    }

    public function create()
    {
        $this->render(View::make('admin/pages/form', [
            'title' => 'Нова страница',
            'page' => new Page()
        ], 'admin'));
    }

    public function edit($id)
    {
        $page = Page::findOrFail($id);

        $this->render(View::make('admin/pages/form', [
            'title' => 'Редактиране на страница',
            'page' => $page
        ], 'admin'));
    }

    public function store()
    {
        $exclude = ['name', 'slug', 'submit', '_token'];
        $options = array_diff_key($_POST, array_flip($exclude));

        $options = $this->handleFileUploads($options, 'pages');

        $page = Page::create([
            'name' => $_POST['name'],
            'slug' => $_POST['slug'],
            'options' => $options
        ]);

        View::redirect('/admin/pages/edit/' . $page->id);
    }

    public function update($id)
    {
        $page = Page::findOrFail($id);

        $options = $this->handleFileUploads($page->options->getArrayCopy(), 'pages');

        $exclude = ['name', 'slug', 'submit', '_token'];
        $newOptions = array_diff_key($_POST, array_flip($exclude));

        $options = array_merge($options, $newOptions);

        $page->update([
            'name' => $_POST['name'],
            'slug' => $_POST['slug'],
            'options' => $options
        ]);

        View::redirect('/admin/pages/edit/' . $page->id);
    }

    public function delete($id)
    {
        $page = Page::findOrFail($id);
        $page->delete();

        View::redirect('/admin/pages');
    }

    public function deleteImage($id)
    {
        $page = Page::findOrFail($id);
        $manager = new MediaManager();

        $imagePath = $page->options['featured_image'] ?? null;

        if ($imagePath) {
            $manager->remove($imagePath);

            $options = $page->options;
            unset($options['featured_image']);
            $page->options = $options;
            $page->save();
        }

        return json_encode(['success' => true]);
    }
}
