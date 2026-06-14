<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Services\EmailService;
use Flex\Core\Controllers\BaseController;
use Flex\Core\Routing\View;
use Flex\Models\EmailTemplate;

class EmailTemplateController extends BaseController
{
    #[UseExceptions]
    public function index()
    {
        $allowedSortFields = ['name', 'slug', 'category', 'created_at'];
        $sort = in_array($_GET['sort'] ?? '', $allowedSortFields) ? $_GET['sort'] : 'name';
        $direction = (isset($_GET['direction']) && $_GET['direction'] === 'desc') ? 'desc' : 'asc';

        $query = EmailTemplate::orderBy($sort, $direction);

        if (!empty($_GET['category'])) {
            $query->where('category', $_GET['category']);
        }

        if (!empty($_GET['search'])) {
            $search = trim($_GET['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('subject', 'LIKE', "%{$search}%");
            });
        }

        $categories = EmailTemplate::distinct()->pluck('category')->toArray();

        return $this->render(View::make('admin/email-templates/index', [
            'title' => 'Имейл шаблони',
            'primaryButton' => [
                'label' => 'Нов шаблон',
                'url' => '/admin/email-templates/create',
                'icon' => 'fa-plus'
            ],
            'templates' => $query->get(),
            'categories' => $categories,
            'currentSort' => $sort,
            'currentDir' => $direction
        ], 'admin'));
    }

    #[UseExceptions]
    public function create()
    {
        return $this->render(View::make('admin/email-templates/form', [
            'title' => 'Създаване на нов шаблон',
            'template' => null
        ], 'admin'));
    }

    #[UseExceptions]
    public function store()
    {
        $variables = $this->prepareVariables($_POST['body'] ?? '', $_POST['var_desc'] ?? []);

        EmailTemplate::create([
            'name' => $_POST['name'],
            'slug' => $_POST['slug'],
            'category' => $_POST['category'] ?? 'General',
            'subject' => $_POST['subject'],
            'body' => $_POST['body'],
            'variables' => $variables
        ]);

        View::redirect('/admin/email-templates');
    }

    #[UseExceptions]
    public function edit($id)
    {
        $template = EmailTemplate::findOrFail($id);

        return $this->render(View::make('admin/email-templates/form', [
            'title' => 'Редактиране на шаблон: ' . $template->name,
            'template' => $template
        ], 'admin'));
    }

    #[UseExceptions]
    public function update($id)
    {
        $template = EmailTemplate::findOrFail($id);
        $variables = $this->prepareVariables($_POST['body'] ?? '', $_POST['var_desc'] ?? []);

        $template->update([
            'name' => $_POST['name'],
            'slug' => $_POST['slug'],
            'category' => $_POST['category'],
            'subject' => $_POST['subject'],
            'body' => $_POST['body'],
            'variables' => $variables
        ]);

        View::redirect('/admin/email-templates/edit/' . $template->id);
    }

    #[UseExceptions]
    public function destroy()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;

        if (!$id)
            return;

        $template = EmailTemplate::findOrFail($id);
        $template->delete();

        echo json_encode(['success' => true]);
        exit;
    }

    #[UseExceptions]
    protected function prepareVariables($body, $inputDescriptions)
    {
        $foundVars = EmailService::extractVariables($body);
        $variablesToSave = [];

        foreach ($foundVars as $var) {
            $variablesToSave[] = [
                'key' => $var,
                'label' => $inputDescriptions[$var] ?? ''
            ];
        }

        return json_encode($variablesToSave);
    }
}
