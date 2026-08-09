<?php

declare(strict_types=1);

namespace Flex\Core\Http;

use Closure;
use Flex\Core\Http\Contracts\ExceptionHandlerInterface;
use Flex\Core\Http\Exceptions\HttpException;
use Throwable;

final readonly class ExceptionHandler implements ExceptionHandlerInterface
{
    /** @param null|Closure(Throwable): void $logger */
    public function __construct(
        private bool $debug = false,
        private ?Closure $logger = null,
    ) {
    }

    public function render(Request $request, Throwable $exception): Response
    {
        $this->report($exception);

        $status = $exception instanceof HttpException ? $exception->statusCode() : 500;
        $headers = $exception instanceof HttpException ? $exception->headers() : [];
        $message = $this->message($exception, $status);

        if ($request->expectsJson()) {
            $payload = [
                'status' => 'error',
                'message' => $message,
            ];

            if ($this->debug) {
                $payload['exception'] = get_class($exception);
                $payload['debug'] = [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTrace(),
                ];
            }

            return Response::json($payload, $status, $headers);
        }

        $details = $this->debug
            ? sprintf(
                '<pre>%s\n%s:%d\n\n%s</pre>',
                htmlspecialchars(get_class($exception), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($exception->getFile(), ENT_QUOTES, 'UTF-8'),
                $exception->getLine(),
                htmlspecialchars($exception->getTraceAsString(), ENT_QUOTES, 'UTF-8'),
            )
            : '';

        return Response::html(
            sprintf(
                '<h1>%d - %s</h1>%s',
                $status,
                htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
                $details,
            ),
            $status,
            $headers,
        );
    }

    private function report(Throwable $exception): void
    {
        if ($this->logger !== null) {
            ($this->logger)($exception);
            return;
        }

        error_log(sprintf(
            '[%s] %s in %s:%d',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
        ));
    }

    private function message(Throwable $exception, int $status): string
    {
        if ($this->debug || $exception instanceof HttpException) {
            return $exception->getMessage() !== ''
                ? $exception->getMessage()
                : $this->defaultMessage($status);
        }

        return 'Възникна вътрешна грешка в сървъра.';
    }

    private function defaultMessage(int $status): string
    {
        return match ($status) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            default => 'Internal Server Error',
        };
    }
}
