<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Filters\Shared\StatusFilter;
use Flex\Core\Helpers\Flash;
use Flex\Core\Mail\Services\EmailService;
use Flex\Core\Controllers\BaseController;
use Flex\Core\Routing\View;
use Flex\Core\Traits\CrudHelper;
use Flex\Core\Traits\HandlesTableFilters;
use Flex\Models\EmailTemplate;

class EmailTemplateController extends BaseController
{
    use HandlesTableFilters, CrudHelper;

    protected string $indexTitle = 'Имейл шаблони';
    protected string $createTitle = 'Нов шаблон';
    protected string $editTitle = 'Редактиране на шаблон';
    protected array $messages = [];

    public function __construct()
    {
        $this->initMessages();
    }

    private function initMessages(): void
    {
        $this->messages = [
            'delete_success' => 'Шаблонът беше успешно преместен в кошчето.',
            'force_delete_success' => 'Шаблонът беше успешно премахнат завинаги.',
            'restore_success' => 'Шаблонът беше успешно възстановен.',
            'toggle_active' => 'Шаблонът беше активиран успешно!',
            'toggle_inactive' => 'Шаблонът беше деактивиран успешно!',
            'create_success' => 'Имейл шаблонът беше създаден успешно!',
            'update_success' => 'Имейл шаблонът беше обновен успешно!'
        ];
    }

    #[UseExceptions]
    public function index()
    {
        $query = EmailTemplate::query();

        if (!empty($_GET['category'])) {
            $query->where('category', $_GET['category']);
        }

        $query = $this->applyFilters(
            $query,
            ['name', 'subject'],
            ['name', 'slug', 'category', 'created_at'],
            ['status' => StatusFilter::class],
            'name'
        );

        $data = [
            'title' => $this->indexTitle,
            'primaryButton' => $this->createButton('/admin/email-templates/create', $this->createTitle),
            'templates' => $query->get(),
            'categories' => EmailTemplate::distinct()->pluck('category')->toArray(),
        ];

        render_view('admin/email-templates/index', $data);
    }

    #[UseExceptions]
    public function create()
    {
        $data = [
            'title' => $this->createTitle,
            'categoryOptions' => $this->getCategoryOptions(),
            'template' => new EmailTemplate(),
            'primaryButton' => $this->createButton('/admin/email-templates/create', $this->createTitle)
        ];

        render_view('admin/email-templates/form', $data);
    }

    #[UseExceptions]
    public function store()
    {
        $data = $this->prepareData($_POST);
        $template = EmailTemplate::create($data);

        Flash::success($this->messages['create_success']);
        View::redirect('/admin/email-templates/edit/' . $template->id);
    }

    #[UseExceptions]
    public function edit($id)
    {
        $template = EmailTemplate::findOrFail($id);

        $data = [
            'title' => $this->editTitle,
            'categoryOptions' => $this->getCategoryOptions(),
            'template' => $template,
            'primaryButton' => $this->createButton('/admin/email-templates/create', $this->createTitle)
        ];

        render_view('admin/email-templates/form', $data);
    }

    #[UseExceptions]
    public function update($id)
    {
        $template = EmailTemplate::findOrFail($id);
        $data = $this->prepareData($_POST, $template);

        $template->update($data);

        Flash::success($this->messages['update_success']);
        View::redirect('/admin/email-templates/edit/' . $template->id);
    }

    #[UseExceptions]
    public function delete()
    {
        $this->deleteRecord(EmailTemplate::class);
        return $this->jsonResponse(true, $this->messages['delete_success']);
    }

    #[UseExceptions]
    public function forceDelete()
    {
        $this->forceDeleteRecord(EmailTemplate::class);
        return $this->jsonResponse(true, $this->messages['force_delete_success']);
    }

    #[UseExceptions]
    public function restore()
    {
        $this->restoreRecord(EmailTemplate::class);
        return $this->jsonResponse(true, $this->messages['restore_success']);
    }

    #[UseExceptions]
    public function toggle()
    {
        $result = $this->toggleStatus(EmailTemplate::class, 'is_active');
        $msgKey = $result['new_status'] ? 'toggle_active' : 'toggle_inactive';
        return $this->jsonResponse($result['success'], $this->messages[$msgKey]);
    }

    private function prepareData(array $post, $model = null): array
    {
        $data = $this->buildUpdateData($post, $model, [
            'name',
            'slug',
            'category',
            'subject',
            'body',
            'is_active'
        ]);

        $data['variables'] = $this->prepareVariables($data['body'] ?? '', $post['var_desc'] ?? []);

        return $data;
    }

    private function getCategoryOptions(): array
    {
        $categories = EmailTemplate::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->toArray();
        return array_combine($categories, $categories);
    }

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
