<?php

declare(strict_types=1);

namespace Flex\Core\View;

use Flex\Core\View\Contracts\ViewRendererInterface;
use Throwable;

final readonly class ViewRenderer implements ViewRendererInterface
{
    public function __construct(private ViewFinder $finder)
    {
    }

    public function render(string $view, array $data = [], ?string $layout = null): string
    {
        $data['title'] ??= 'Flex CMS';
        $content = $this->renderFile($this->finder->find($view), $data);

        if ($layout === null || $layout === '') {
            return $content;
        }

        return $this->renderFile(
            $this->finder->findLayout($layout),
            array_replace($data, ['content' => $content]),
        );
    }

    public function response(string $view, array $data = [], ?string $layout = null, int $status = 200): ViewResponse
    {
        return new ViewResponse($this->render($view, $data, $layout), $status);
    }

    private function renderFile(string $file, array $data): string
    {
        $level = ob_get_level();
        ob_start();

        try {
            extract($data, EXTR_SKIP);
            include $file;
            return (string) ob_get_clean();
        } catch (Throwable $exception) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $exception;
        }
    }
}
