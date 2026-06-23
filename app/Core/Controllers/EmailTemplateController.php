<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Filters\Shared\StatusFilter;
use Flex\Core\Services\EmailService;
use Flex\Core\Controllers\BaseController;
use Flex\Core\Routing\View;
use Flex\Core\Traits\CrudHelper;
use Flex\Core\Traits\HandlesTableFilters;
use Flex\Models\EmailTemplate;

class EmailTemplateController extends BaseController
{
    use HandlesTableFilters, CrudHelper;

    #[UseExceptions]
    public function index()
    {
        $query = $this->applyFilters(
            EmailTemplate::query(),
            ['name', 'subject'],
            ['name', 'slug', 'category', 'created_at'],
            ['status' => StatusFilter::class],
            'name'
        );

        $data = [
            'title' => 'Имейл шаблони',
            'primaryButton' => $this->createButton('/admin/email-templates/create', 'Нов шаблон'),
            'templates' => $query->get(),
            'categories' => EmailTemplate::distinct()->pluck('category')->toArray(),
            'currentSort' => $_GET['sort'] ?? 'name',
            'currentDir' => $_GET['direction'] ?? 'asc',
        ];

        render_view('admin/email-templates/index', $data);
    }

    #[UseExceptions]
    public function create()
    {
        $categories = EmailTemplate::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->toArray();

        $categoryOptions = array_combine($categories, $categories);

        $data = [
            'title' => 'Създаване на нов шаблон',
            'categoryOptions' => $categoryOptions,
            'template' => null,
            'primaryButton' => $this->createButton('/admin/email-templates/create', 'Нов шаблон')
        ];

        render_view('admin/email-templates/form', $data);
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

        $categories = EmailTemplate::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->toArray();

        $categoryOptions = array_combine($categories, $categories);

        $data = [
            'title' => 'Редактиране на шаблон: ' . $template->name,
            'categoryOptions' => $categoryOptions,
            'template' => $template,
            'primaryButton' => $this->createButton('/admin/email-templates/create', 'Нов шаблон')
        ];

        render_view('admin/email-templates/form', $data);
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
    public function delete()
    {
        return $this->deleteRecord(EmailTemplate::class);
    }

    #[UseExceptions]
    public function restore()
    {
        return $this->restoreRecord(EmailTemplate::class);
    }

    #[UseExceptions]
    public function toggle()
    {
        $result = $this->toggleStatus(EmailTemplate::class, 'is_active');

        if (!$result['success']) {
            return $this->jsonResponse(false, $result['message']);
        }

        $statusText = $result['new_status'] ? 'активиран' : 'деактивиран';

        return $this->jsonResponse(true, "Имейл шаблонът беше {$statusText} успешно!");
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
